<?php

namespace App\Filament\Resources\Departments;

use App\Filament\Resources\Departments\Pages\ManageDepartments;
use App\Models\Department;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $navigationLabel = 'Teams';

    protected static string|\UnitEnum|null $navigationGroup = 'Mitglieder';

    protected static ?string $label = 'Team';

    protected static ?string $pluralLabel = 'Teams';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required(),
                TextInput::make('max_members')
                    ->label('Maximale Anzahl an Mitgliedern')
                    ->numeric()
                    ->required()
                    ->minValue(0),
                RichEditor::make('description')
                    ->toolbarButtons(['bold', 'underline', 'italic', 'bulletList', 'orderedList', 'undo', 'redo'])
                    ->label('Beschreibung'),
                Placeholder::make('users')
                    ->label('Teilnehmer')
                    ->content(fn ($record) => new HtmlString($record->users->map(fn ($row) => sprintf("%s \n (%s)", $row->name, $row->email))->join('<br>'))),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->sortable(),
                TextColumn::make('members')
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
            'index' => ManageDepartments::route('/'),
        ];
    }
}
