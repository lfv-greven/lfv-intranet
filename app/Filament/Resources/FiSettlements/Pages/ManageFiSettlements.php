<?php

namespace App\Filament\Resources\FiSettlements\Pages;

use App\Enums\FiSettlementStatus;
use App\Filament\Resources\FiSettlements\FiSettlementResource;
use App\Jobs\Fi\BuildFiSettlement;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageFiSettlements extends ManageRecords
{
    protected static string $resource = FiSettlementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $month = $data['period_month'] ?? now()->subMonthNoOverflow()->format('Y-m');
                    unset($data['period_month']);

                    $period = \Illuminate\Support\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
                    $data['period_from'] = $period->format('Y-m-d');
                    $data['period_to'] = $period->copy()->endOfMonth()->format('Y-m-d');

                    $data['status'] = FiSettlementStatus::QUEUED;
                    $data['created_by'] = auth()->id();

                    return $data;
                })
                ->after(function ($record): void {
                    BuildFiSettlement::dispatch($record);
                }),
        ];
    }
}
