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
        SUM(amount) OVER (
          PARTITION BY gas_station_id
          ORDER BY counter_reading, id
          ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
        ) AS fill_level
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
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
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
}
