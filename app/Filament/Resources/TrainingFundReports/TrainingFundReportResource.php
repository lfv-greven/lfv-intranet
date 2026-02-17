<?php

namespace App\Filament\Resources\TrainingFundReports;

use App\Enums\TrainingFundReportStatus;
use App\Filament\Resources\TrainingFundReports\Pages\ManageTrainingFundReports;
use App\Jobs\TrainingFund\CalculateTrainingFundReport;
use App\Models\TrainingFundReport;
use App\Services\TrainingFundReportService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TrainingFundReportResource extends Resource
{
    protected static ?string $model = TrainingFundReport::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string|\UnitEnum|null $navigationGroup = 'Finanzen';

    protected static ?string $navigationLabel = 'Aus- und Weiterbildungsfonds';

    protected static ?string $modelLabel = 'Aus- und Weiterbildungsfonds';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('month', 'desc')
            ->poll('5s')
            ->columns([
                TextColumn::make('month')
                    ->label('Monat')
                    ->date('m.Y'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        if ($state instanceof TrainingFundReportStatus) {
                            return $state->getLabel();
                        }

                        return TrainingFundReportStatus::tryFrom((string) $state)?->getLabel() ?? $state;
                    })
                    ->color(function ($state) {
                        if ($state instanceof TrainingFundReportStatus) {
                            return $state->getColor();
                        }

                        return TrainingFundReportStatus::tryFrom((string) $state)?->getColor() ?? 'gray';
                    }),
                TextColumn::make('motor_ul_minutes')
                    ->label('Motor/UL Min')
                    ->alignRight()
                    ->numeric(),
                TextColumn::make('motor_ul_amount')
                    ->label('Motor/UL €')
                    ->alignRight()
                    ->money(currency: 'EUR'),
                TextColumn::make('winch_starts')
                    ->label('Winde Starts')
                    ->alignRight()
                    ->numeric(),
                TextColumn::make('winch_amount')
                    ->label('Winde €')
                    ->alignRight()
                    ->money(currency: 'EUR'),
                TextColumn::make('tow_minutes')
                    ->label('Schlepp Min')
                    ->alignRight()
                    ->numeric(),
                TextColumn::make('tow_amount')
                    ->label('Schlepp €')
                    ->alignRight()
                    ->money(currency: 'EUR'),
                TextColumn::make('start_pauschale_quarterly_count')
                    ->label('Start (Q)')
                    ->alignRight()
                    ->numeric(),
                TextColumn::make('start_pauschale_monthly_count')
                    ->label('Start (M)')
                    ->alignRight()
                    ->numeric(),
                TextColumn::make('start_pauschale_amount')
                    ->label('Start €')
                    ->alignRight()
                    ->money(currency: 'EUR'),
                TextColumn::make('total_amount')
                    ->label('Gesamt €')
                    ->alignRight()
                    ->money(currency: 'EUR'),
                TextColumn::make('completed_at')
                    ->label('Berechnet')
                    ->dateTime('d.m.Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('error_message')
                    ->label('Fehler')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('recalculate')
                    ->label('Neu berechnen')
                    ->requiresConfirmation()
                    ->action(function (TrainingFundReport $record, TrainingFundReportService $service): void {
                        $report = $service->queueForMonth($record->month, true);

                        CalculateTrainingFundReport::dispatch($report->month->toDateString(), true);

                        Notification::make()
                            ->title('Berechnung gestartet')
                            ->success()
                            ->send();
                    }),
            ])
            ->filters([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTrainingFundReports::route('/'),
        ];
    }
}
