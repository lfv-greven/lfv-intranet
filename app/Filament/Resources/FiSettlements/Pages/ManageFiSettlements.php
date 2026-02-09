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
