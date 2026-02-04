<?php

namespace App\Filament\Resources\Refuelings\Tables;

use App\Enums\RefuelingType;
use App\Jobs\Vf\SendRefueling;
use App\Models\Refueling;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
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
            ->modifyQueryUsing(function ($query) {
                return $query
                    ->with(['aircraft', 'gasStation'])
                    ->select('refuelings.*')
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
                    ->label('Kennzeichen'),
                TextColumn::make('buyer_name')
                    ->label('Name'),
                TextColumn::make('counter_reading')
                    ->numeric(0, null, '.')
                    ->alignRight()
                    ->label('Zählerstand'),
                TextColumn::make('diff')
                    ->numeric(0, null, '.')
                    ->color(fn ($state) => $state < 0 ? 'danger' : null)
                    ->alignRight()
                    ->label('Diff'),
                TextColumn::make('fill_level')
                    ->label('Füllstand')
                    ->numeric(0, null, '.')
                    ->suffix(' l')
                    ->alignRight()
                    ->toggleable(),
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
            ])
            ->recordActions([
                Action::make('send_vf')
                    ->icon('heroicon-s-banknotes')
                    ->iconButton()
                    ->tooltip('Verkauf an Vereinsflieger übertragen')
                    ->visible(fn ($record) => $record->type == RefuelingType::refueling && ! $record->aircraft?->owned)
                    ->disabled(function (Refueling $record) {
                        if ($record->isExported()) {
                            return true;
                        } elseif (! $record->gasStation->vf_articleid) {
                            return true;
                        }

                        return false;
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
}
