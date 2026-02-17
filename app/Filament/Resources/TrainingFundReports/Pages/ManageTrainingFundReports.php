<?php

namespace App\Filament\Resources\TrainingFundReports\Pages;

use App\Enums\TrainingFundReportStatus;
use App\Filament\Resources\TrainingFundReports\TrainingFundReportResource;
use App\Jobs\TrainingFund\CalculateTrainingFundReport;
use App\Services\TrainingFundReportService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManageTrainingFundReports extends ManageRecords
{
    protected static string $resource = TrainingFundReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('calculate')
                ->label('Berechnung starten')
                ->form([
                    Select::make('month')
                        ->label('Monat')
                        ->required()
                        ->options($this->getRecentMonthOptions())
                        ->searchable()
                        ->placeholder('Monat auswählen')
                        ->default(now()->subMonthNoOverflow()->format('Y-m')),
                    Toggle::make('overwrite')
                        ->label('Berechnung überschreiben')
                        ->default(false),
                ])
                ->action(function (array $data, TrainingFundReportService $service): void {
                    $month = Carbon::createFromFormat('Y-m', $data['month'])->startOfMonth();
                    $overwrite = (bool) ($data['overwrite'] ?? false);

                    $report = $service->queueForMonth($month, $overwrite);

                    if ($report->status === TrainingFundReportStatus::COMPLETED && ! $overwrite) {
                        Notification::make()
                            ->title('Berechnung existiert bereits')
                            ->warning()
                            ->send();

                        return;
                    }

                    CalculateTrainingFundReport::dispatch($report->month->toDateString(), $overwrite);

                    Notification::make()
                        ->title('Berechnung gestartet')
                        ->success()
                        ->send();
                }),
        ];
    }

    private function getRecentMonthOptions(): array
    {
        $options = [];
        $current = now()->subMonthNoOverflow()->startOfMonth();

        for ($i = 0; $i < 24; $i++) {
            $month = $current->copy()->subMonthsNoOverflow($i);
            $options[$month->format('Y-m')] = $month->format('F Y');
        }

        return $options;
    }
}
