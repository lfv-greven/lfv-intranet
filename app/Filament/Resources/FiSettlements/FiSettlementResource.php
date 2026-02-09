<?php

namespace App\Filament\Resources\FiSettlements;

use App\Enums\FiSettlementStatus;
use App\Filament\Resources\FiSettlements\Pages\ManageFiSettlements;
use App\Models\FiSettlement;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FiSettlementResource extends Resource
{
    protected static ?string $model = FiSettlement::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'FI-Abrechnungen';

    protected static string|\UnitEnum|null $navigationGroup = 'Finanzen';

    protected static ?string $label = 'FI-Abrechnung';

    protected static ?string $pluralLabel = 'FI-Abrechnungen';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount([
                'flights',
                'flights as flights_sent_count' => fn (Builder $query) => $query->whereNotNull('workhour_sent_at'),
                'flights as flights_excluded_count' => fn (Builder $query) => $query->whereNotNull('excluded_reason'),
                'flights as flights_rejected_count' => fn (Builder $query) => $query->where('excluded_reason', 'vf_rejected'),
                'flights as flights_missing_fi_count' => fn (Builder $query) => $query->where('excluded_reason', 'missing_fi_uid'),
                'flights as flights_invalid_time_count' => fn (Builder $query) => $query->where('excluded_reason', 'invalid_flighttime'),
                'flights as flights_missing_category_count' => fn (Builder $query) => $query->where('excluded_reason', 'missing_workhour_category'),
            ]);
    }

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
                            ->afterStateUpdated(function ($state, $set) {
                                if (! $state) {
                                    return;
                                }

                                $period = \Illuminate\Support\Carbon::createFromFormat('Y-m', $state)->startOfMonth();
                                $set('period_from', $period->format('Y-m-d'));
                                $set('period_to', $period->copy()->endOfMonth()->format('Y-m-d'));
                            })
                            ->afterStateHydrated(function ($state, $record, $set) {
                                if (! $record || ! $record->period_from) {
                                    return;
                                }

                                $set('period_month', $record->period_from->format('Y-m'));
                                $set('period_from', $record->period_from->format('Y-m-d'));
                                $set('period_to', $record->period_to?->format('Y-m-d'));
                            }),
                        Hidden::make('period_from')
                            ->required(),
                        Hidden::make('period_to')
                            ->required(),
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
            ->poll('5s')
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
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        if ($state instanceof FiSettlementStatus) {
                            return $state->getLabel();
                        }

                        return FiSettlementStatus::tryFrom((string) $state)?->getLabel() ?? $state;
                    }),
                TextColumn::make('flights_count')
                    ->label('Flüge')
                    ->alignRight(),
                TextColumn::make('flights_sent_count')
                    ->label('Gebucht')
                    ->alignRight(),
                TextColumn::make('flights_excluded_count')
                    ->label('Ausgeschlossen')
                    ->alignRight()
                    ->description(function (FiSettlement $record) {
                        $parts = [];

                        if ($record->flights_missing_fi_count > 0) {
                            $parts[] = "fehlende FI-ID: {$record->flights_missing_fi_count}";
                        }
                        if ($record->flights_invalid_time_count > 0) {
                            $parts[] = "ungültige Zeit: {$record->flights_invalid_time_count}";
                        }
                        if ($record->flights_missing_category_count > 0) {
                            $parts[] = "Kategorie fehlt: {$record->flights_missing_category_count}";
                        }

                        return $parts === [] ? null : implode(' · ', $parts);
                    }),
                TextColumn::make('flights_rejected_count')
                    ->label('VF Fehler')
                    ->alignRight(),
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
