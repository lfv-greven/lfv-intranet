<?php

namespace App\Filament\Resources;

use App\Enums\OilLevelType;
use App\Filament\Resources\AircraftResource\Pages;
use App\Models\Aircraft;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class AircraftResource extends Resource
{
    protected static ?string $model = Aircraft::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
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
                TextColumn::make('registration'),
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
            'index' => Pages\ManageAircraft::route('/'),
        ];
    }
}
