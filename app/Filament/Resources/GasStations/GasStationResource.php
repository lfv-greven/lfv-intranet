<?php

namespace App\Filament\Resources\GasStations;

use App\Enums\FuelType;
use App\Filament\Resources\GasStations\Pages\ManageGasStations;
use App\Models\GasStation;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GasStationResource extends Resource
{
    protected static ?string $model = GasStation::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Tankstelle';

    protected static ?string $navigationLabel = 'Tankstellen';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required(),
                Select::make('fuel_type')
                    ->options(FuelType::class)
                    ->label('Kraftstoff')
                    ->required(),
                TextInput::make('capacity')
                    ->label('Maximale Kapazität')
                    ->required()
                    ->suffix('l'),
                TextInput::make('vf_articleid')
                    ->label('VF Artikel-ID'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCurrentFilling())
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('fuel_type')
                    ->label('Kraftstoff')
                    ->badge(),
                TextColumn::make('capacity')
                    ->alignRight()
                    ->label('Tank-Kapazität')
                    ->numeric()
                    ->suffix(' l'),
                TextColumn::make('current_filling')
                    ->label('Aktueller Füllstand')
                    ->alignRight()
                    ->numeric()
                    ->suffix(' l'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
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
            'index' => ManageGasStations::route('/'),
        ];
    }
}
