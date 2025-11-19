<?php

namespace App\Filament\Resources\Refuelings;

use App\Filament\Resources\Refuelings\Pages\CreateRefueling;
use App\Filament\Resources\Refuelings\Pages\EditRefueling;
use App\Filament\Resources\Refuelings\Pages\ListRefuelings;
use App\Filament\Resources\Refuelings\Schemas\RefuelingForm;
use App\Filament\Resources\Refuelings\Tables\RefuelingsTable;
use App\Models\Refueling;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class RefuelingResource extends Resource
{
    protected static ?string $model = Refueling::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Tankstelle';

    protected static ?string $navigationLabel = 'Betankungen';

    protected static ?string $label = 'Betankung';

    protected static ?string $pluralLabel = 'Betankungen';

    public static function form(Schema $schema): Schema
    {
        return RefuelingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RefuelingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRefuelings::route('/'),
            'create' => CreateRefueling::route('/create'),
            'edit' => EditRefueling::route('/{record}/edit'),
        ];
    }
}
