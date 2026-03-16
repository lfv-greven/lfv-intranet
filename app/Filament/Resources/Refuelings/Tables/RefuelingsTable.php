<?php

namespace App\Filament\Resources\Refuelings\Tables;

use App\Enums\RefuelingType;
use App\Jobs\Vf\SendRefueling;
use App\Models\Refueling;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class RefuelingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort(fn ($query) => $query
                ->orderByDesc('counter_reading')
                ->orderByDesc('date')
                ->orderByDesc('id')
            )
            ->modifyQueryUsing(function ($query, $livewire) {
                $query = $query
                    ->with(['aircraft', 'gasStation'])
                    ->select('refuelings.*');

                if (! static::shouldHideWindowColumnsFor($livewire)) {
                    return $query
                        ->addSelect(DB::raw('
        GREATEST(0, SUM(amount) OVER (
          PARTITION BY gas_station_id
          ORDER BY counter_reading, id
          ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
        )) AS fill_level
    '))
                        ->addSelect(DB::raw("
        CASE
          WHEN type = 'filling' THEN NULL
          ELSE LAG(counter_reading) OVER (
                 PARTITION BY gas_station_id
                 ORDER BY counter_reading, id
               ) - counter_reading - amount
        END AS diff
    "));
                }

                return $query;
            })
            ->selectCurrentPageOnly()
            ->checkIfRecordIsSelectableUsing(fn (Refueling $record) => ! $record->isExported() && $record->mayBeSold())
            ->columns([
                TextColumn::make('date')
                    ->label('Datum')
                    ->date('d.m.Y H:i'),
                TextColumn::make('buyer_registration')
                    ->badge(fn ($record) => filled($record->aircraft_id))
                    ->color('gray')
                    ->label('Kennzeichen')
                    ->searchable(),
                TextColumn::make('buyer_name')
                    ->label('Name')
                    ->searchable(),
                TextColumn::make('counter_reading')
                    ->numeric(0, null, '.')
                    ->alignRight()
                    ->label('Zählerstand'),
                TextColumn::make('diff')
                    ->numeric(0, null, '.')
                    ->color(fn ($state) => $state < 0 ? 'danger' : null)
                    ->alignRight()
                    ->label('Diff')
                    ->hidden(fn (HasTable $livewire): bool => static::shouldHideWindowColumnsFor($livewire)),
                TextColumn::make('fill_level')
                    ->label('Füllstand')
                    ->numeric(0, null, '.')
                    ->suffix(' l')
                    ->alignRight()
                    ->toggleable()
                    ->hidden(fn (HasTable $livewire): bool => static::shouldHideWindowColumnsFor($livewire)),
                TextColumn::make('amount')
                    ->label('Menge')
                    ->alignRight()
                    ->numeric()
                    ->color(function (Refueling $record, $state) {
                        if ($record->amount < 0) {
                            return 'danger';
                        }
                    })
                    ->suffix(' l'),
                IconColumn::make('comment')
                    ->label('')
                    ->tooltip(fn ($state) => 'Kommentar des Piloten: '.$state)
                    ->icon('heroicon-s-chat-bubble-oval-left-ellipsis'),
            ])
            ->filters([
                Filter::make('date_range')
                    ->label('Zeitraum')
                    ->schema([
                        DatePicker::make('from')
                            ->label('Von'),
                        DatePicker::make('until')
                            ->label('Bis'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                filled($data['from'] ?? null),
                                fn (Builder $query): Builder => $query->whereDate('refuelings.date', '>=', $data['from']),
                            )
                            ->when(
                                filled($data['until'] ?? null),
                                fn (Builder $query): Builder => $query->whereDate('refuelings.date', '<=', $data['until']),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (filled($data['from'] ?? null)) {
                            $indicators[] = Indicator::make('Von '.Carbon::parse($data['from'])->format('d.m.Y'))
                                ->removeField('from');
                        }

                        if (filled($data['until'] ?? null)) {
                            $indicators[] = Indicator::make('Bis '.Carbon::parse($data['until'])->format('d.m.Y'))
                                ->removeField('until');
                        }

                        return $indicators;
                    }),
                SelectFilter::make('buyer_registration')
                    ->label('Kennzeichen')
                    ->options(fn (): array => Refueling::query()
                        ->whereNotNull('buyer_registration')
                        ->where('buyer_registration', '!=', '')
                        ->distinct()
                        ->orderBy('buyer_registration')
                        ->pluck('buyer_registration', 'buyer_registration')
                        ->all())
                    ->searchable(),
            ])
            ->recordActions([
                Action::make('send_vf')
                    ->icon('heroicon-s-banknotes')
                    ->iconButton()
                    ->tooltip('Verkauf an Vereinsflieger übertragen')
                    ->visible(function (Refueling $record) {
                        return
                            // Only bill refuelings, not fillings
                            $record->type == RefuelingType::refueling

                            // Only bill non community planes
                            && ! $record->aircraft?->owned

                            // Prevent already exported
                            && ! $record->isExported()

                            // Article is not yet configured for export
                            && filled($record->gasStation->vf_articleid);
                    })
                    ->action(function (Refueling $record) {
                        SendRefueling::dispatch($record);

                        Notification::make()
                            ->success()
                            ->title('Verkauf wird im Hintergrund übertragen.')
                            ->send();
                    }),
                Action::make('fill_to_capacity')
                    ->icon('heroicon-o-truck')
                    ->iconButton()
                    ->tooltip('Lieferung nach dieser Zeile einfügen')
                    ->visible(fn (Refueling $record) => filled($record->gasStation?->capacity))
                    ->fillForm(function (Refueling $record): array {
                        return [
                            'amount' => static::getAmountToFullAtRecord($record),
                            'date' => now(),
                        ];
                    })
                    ->schema([
                        DateTimePicker::make('date')
                            ->label('Datum')
                            ->seconds(false)
                            ->required()
                            ->default(now()),
                        TextInput::make('amount')
                            ->label('Auffüllen auf Füllstand X Liter')
                            ->helperText(function (Refueling $record): string {
                                $capacity = (int) $record->gasStation->capacity;
                                $suggested = static::getAmountToFullAtRecord($record);

                                return "Vorschlag bis voll ({$capacity} l): {$suggested} l";
                            })
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->suffix(' l'),
                    ])
                    ->modalHeading('Lieferung erfassen')
                    ->modalSubmitActionLabel('Lieferung speichern')
                    ->action(function (Refueling $record, array $data): void {
                        $amount = (int) round((float) $data['amount']);
                        $date = filled($data['date'] ?? null)
                            ? Carbon::parse($data['date'])
                            : now();

                        Refueling::create([
                            'user_id' => auth()->id(),
                            'type' => RefuelingType::filling,
                            'gas_station_id' => $record->gas_station_id,
                            'date' => $date,
                            'aircraft_id' => null,
                            'buyer_name' => auth()->user()->name,
                            'buyer_registration' => null,
                            'counter_reading' => $record->counter_reading,
                            'amount' => $amount,
                            'comment' => 'Lieferung aus Zeilenaktion',
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Lieferung gespeichert')
                            ->send();
                    }),
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->headerActions([
                Action::make('csv_export')
                    ->label('CSV Export')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function ($livewire) {
                        $query = $livewire->getFilteredSortedTableQuery();

                        return response()->streamDownload(function () use ($query) {
                            $output = fopen('php://output', 'w');

                            // UTF-8 BOM for Excel compatibility.
                            fwrite($output, "\xEF\xBB\xBF");

                            fputcsv($output, [
                                'Datum',
                                'Kennzeichen',
                                'Name',
                                'Typ',
                                'Zählerstand',
                                'Menge',
                                'Abrechnungsstatus',
                                'Kommentar',
                            ], ';');

                            foreach ($query->lazy() as $record) {
                                $billingStatus = ! $record->mayBeSold()
                                    ? 'Nicht erforderlich'
                                    : ($record->isExported() ? 'Abgerechnet' : 'Offen');

                                fputcsv($output, [
                                    $record->date?->format('d.m.Y H:i'),
                                    $record->buyer_registration,
                                    $record->buyer_name,
                                    match (data_get($record, 'type')) {
                                        RefuelingType::refueling, 'refueling' => 'Tanken',
                                        RefuelingType::filling, 'filling' => 'Lieferung',
                                        default => '',
                                    },
                                    $record->counter_reading,
                                    $record->amount,
                                    $billingStatus,
                                    $record->comment,
                                ], ';');
                            }

                            fclose($output);
                        }, 'betankungen-'.now()->format('Ymd-His').'.csv', [
                            'Content-Type' => 'text/csv; charset=UTF-8',
                        ]);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('send_vf_bulk')
                        ->label('Verkäufe an VF senden')
                        ->deselectRecordsAfterCompletion()
                        ->action(function ($records) {
                            /** @var Refueling */
                            foreach ($records as $record) {
                                // Already sold
                                if ($record->isExported()) {
                                    continue;
                                }

                                // Not intended to be sold
                                if (! $record->mayBeSold()) {
                                    continue;
                                }

                                // Checks passed, send sale
                                SendRefueling::dispatch($record);
                            }

                            Notification::make()
                                ->success()
                                ->title('Verkäufe werden übertragen.')
                                ->send();
                        }),
                ]),
            ])
            ->paginationPageOptions([50, 100, 250]);
    }

    protected static function shouldHideWindowColumnsFor(mixed $livewire): bool
    {
        if (! is_object($livewire)) {
            return false;
        }

        $hasSearch = filled(data_get($livewire, 'tableSearch'));
        $hasRegistrationFilter = filled(data_get($livewire, 'tableFilters.buyer_registration.value'));

        return $hasSearch || $hasRegistrationFilter;
    }

    protected static function getAmountToFullAtRecord(Refueling $record): int
    {
        $capacity = (int) ($record->gasStation?->capacity ?? 0);
        if ($capacity <= 0) {
            return 0;
        }

        $fillLevelAtRecord = (int) Refueling::query()
            ->where('gas_station_id', $record->gas_station_id)
            ->where(function (Builder $query) use ($record): void {
                $query
                    ->where('counter_reading', '<', $record->counter_reading)
                    ->orWhere(function (Builder $query) use ($record): void {
                        $query
                            ->where('counter_reading', $record->counter_reading)
                            ->where('id', '<=', $record->id);
                    });
            })
            ->sum('amount');

        return max(0, $capacity - $fillLevelAtRecord);
    }
}
