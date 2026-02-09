<?php

namespace App\Filament\Resources\FiSettlements;

use App\Enums\FiSettlementStatus;
use App\Filament\Resources\FiSettlements\Pages\ManageFiSettlements;
use App\Models\FiSettlement;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FiSettlementResource extends Resource
{
    protected static ?string $model = FiSettlement::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'FI-Abrechnungen';

    protected static string|\UnitEnum|null $navigationGroup = 'Finanzen';

    protected static ?string $label = 'FI-Abrechnung';

    protected static ?string $pluralLabel = 'FI-Abrechnungen';

    private static function monthOptions(): array
    {
        $options = [];
        $current = now()->startOfMonth()->subMonthNoOverflow();

        for ($i = 0; $i < 24; $i++) {
            $key = $current->format('Y-m');
            $options[$key] = $current->translatedFormat('F Y');
            $current->subMonthNoOverflow();
        }

        return $options;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Zeitraum')
                    ->description('Wähle den Monat, für den die Schulflüge als Arbeitsstunden gebucht werden sollen.')
                    ->columns(1)
                    ->compact()
                    ->schema([
                        Select::make('period_month')
                            ->label('Monat')
                            ->options(self::monthOptions())
                            ->searchable()
                            ->required()
                            ->default(now()->subMonthNoOverflow()->format('Y-m'))
                            ->dehydrated(false)
                            ->afterStateHydrated(function ($state, $record, $set) {
                                if (! $record || ! $record->period_from) {
                                    return;
                                }

                                $set('period_month', $record->period_from->format('Y-m'));
                            }),
                    ]),
                Section::make('Berechnung')
                    ->description('Wähle aus, welche Flugarten berücksichtigt werden sollen.')
                    ->columns(1)
                    ->compact()
                    ->schema([
                        Select::make('settings.ftid_filter')
                            ->label('Flugarten berücksichtigen')
                            ->multiple()
                            ->options([
                                2 => 'C - Checkflug',
                                3 => 'F - F-Schlepp',
                                4 => 'P - Passagierflug',
                                5 => 'L - Leistungsflug',
                                6 => 'V - Werkverkehr',
                                8 => 'S - Schulflug',
                                10 => 'N - Privatflug',
                                11 => 'B - Befähigungsüberprüfung',
                                12 => 'Ü - Auffrischungsschulung',
                                13 => 'STMG - Schulflug - Segelflugausbildung (TMG)',
                                14 => 'FS - F-Schlepp Ausbildung Motorflieger',
                                15 => 'S-FI - FI-Ausbildung',
                            ])
                            ->required()
                            ->default([8]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->poll()
            ->columns([
                TextColumn::make('id')
                    ->copyable()
                    ->hidden(app()->isProduction()),
                TextColumn::make('period_from')
                    ->label('Von')
                    ->date('d.m.Y'),
                TextColumn::make('period_to')
                    ->label('Bis')
                    ->date('d.m.Y'),
                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(function ($state) {
                        if ($state instanceof FiSettlementStatus) {
                            return $state->getLabel();
                        }

                        return FiSettlementStatus::tryFrom((string) $state)?->getLabel() ?? $state;
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageFiSettlements::route('/'),
        ];
    }
}
