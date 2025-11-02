<?php

namespace App\Filament\Resources\Aircraft;

use App\Enums\OilLevelType;
use App\Filament\Resources\Aircraft\Pages\ManageAircraft;
use App\Models\Aircraft;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class AircraftResource extends Resource
{
    protected static ?string $model = Aircraft::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Flotte';

    protected static string|\UnitEnum|null $navigationGroup = 'Flugbetrieb';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('registration')
                    ->required(),
                Select::make('oil_level_type')
                    ->label('Wie wird der Ölstand gemessen?')
                    ->required()
                    ->options(OilLevelType::class),
                Toggle::make('owned')
                    ->live()
                    ->label('Gehört dem Verein'),
                TextInput::make('billing_memberid')
                    ->hidden(fn ($get) => $get('owned'))
                    ->placeholder('Optional. Standardmäßig wird der aktuelle Pilot genutzt.')
                    ->label('Abrechnung über Mitgliedsnummer'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultGroup('owned')
            ->groupingSettingsHidden()
            ->groups([
                Group::make('owned')
                    ->label('Status')
                    ->orderQueryUsing(fn ($query) => $query->orderBy('owned', 'desc'))
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn ($record) => match ($record->owned) {
                        true => 'Vereinsflugzeug',
                        false => 'Privat',
                    }),
            ])
            ->columns([
                TextColumn::make('registration')
                    ->searchable(),
                IconColumn::make('owned')
                    ->label('Vereinsflugzeug')
                    ->boolean(),
                TextColumn::make('billing_memberid')
                    ->label('Abrechnung'),
                TextColumn::make('oil_level')
                    ->alignRight()
                    ->numeric()
                    ->getStateUsing(fn ($record) => $record->getOilLevel())
                    ->suffix(fn ($record) => match ($record->oil_level_type) {
                        OilLevelType::absolute => ' qts',
                        OilLevelType::relative => ' %',
                    })
                    ->label('Ölstand'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('csv_export')
                    ->tooltip('Ölbuch herunterladen')
                    ->iconButton()
                    ->visible(fn (Aircraft $record) => $record->owned)
                    ->icon('heroicon-s-table-cells')
                    ->action(function (Aircraft $record) {
                        return response()->streamDownload(function () use ($record) {
                            echo 'Datum,Ölstand,Nachgefüllt'.PHP_EOL;

                            foreach ($record->oilLogs()->orderBy('created_at', 'asc')->lazy() as $log) {
                                echo implode(',', [
                                    $log->created_at->toIso8601String(),
                                    $log->oil_level,
                                    $log->oil_refilled,
                                ]).PHP_EOL;
                            }
                        }, "Ölbuch {$record->registration}.csv");
                    }),
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
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
            'index' => ManageAircraft::route('/'),
        ];
    }
}
