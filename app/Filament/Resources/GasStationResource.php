<?php

namespace App\Filament\Resources;

use App\Enums\FuelType;
use App\Filament\Resources\GasStationResource\Pages;
use App\Models\GasStation;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GasStationResource extends Resource
{
    protected static ?string $model = GasStation::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Tankstelle';

    protected static ?string $navigationLabel = 'Tankstellen';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                TextColumn::make('refuelings_sum_amount')
                    ->label('Aktueller Füllstand')
                    ->alignRight()
                    ->sum('refuelings', 'amount')
                    ->suffix(' l'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageGasStations::route('/'),
        ];
    }
}
