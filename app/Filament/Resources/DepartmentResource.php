<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Models\Department;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $navigationLabel = 'Teams';

    protected static ?string $navigationGroup = 'Mitglieder';

    protected static ?string $label = 'Team';

    protected static ?string $pluralLabel = 'Teams';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                TextInput::make('name')
                    ->label('Name')
                    ->required(),
                TextInput::make('max_members')
                    ->label('Maximale Anzahl an Mitgliedern')
                    ->numeric()
                    ->required()
                    ->minValue(0),
                RichEditor::make('description')
                    ->toolbarButtons(['bold', 'underline', 'italic'])
                    ->label('Beschreibung'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('members')
                    ->label('Teilnhemer')
                    ->getStateUsing(fn (Department $record) => $record->max_members == null
                        ? $record->users()->count()
                        : sprintf(
                            '%s/%s',
                            $record->users()->count(),
                            $record->max_members,
                        ))
                    ->numeric(),
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
            'index' => Pages\ManageDepartments::route('/'),
        ];
    }
}
