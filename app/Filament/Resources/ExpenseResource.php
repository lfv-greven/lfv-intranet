<?php

namespace App\Filament\Resources;

use App\Enums\ExpenseStatus;
use App\Filament\Resources\ExpenseResource\Pages;
use App\Jobs\EnrichExpenseWithIban;
use App\Models\Expense;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Auslagen';

    protected static ?string $navigationGroup = 'Finanzen';

    protected static ?string $label = 'Auslagen';

    protected static ?string $pluralLabel = 'Auslagen';

    public static function getNavigationBadge(): ?string
    {
        return Expense::open()->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                TextInput::make('reason')
                    ->required()
                    ->label('Wofür war der Einkauf?'),
                Select::make('user_id')
                    ->label('Mitglied')
                    ->searchable()
                    ->preload()
                    ->relationship('user', modifyQueryUsing: fn ($query) => $query->orderBy('lastname')->orderBy('firstname'))
                    ->getOptionLabelFromRecordUsing(fn ($record) => sprintf('%s %s', $record->firstname, $record->lastname)),
                FileUpload::make('receipt_filename')
                    ->required()
                    ->directory('expenses')
                    ->label('Belege')
                    ->moveFiles()
                    ->disk(config('filesystems.default'))
                    ->previewable(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->copyable()
                    ->hidden(app()->isProduction()),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->label('Erstellt')
                    ->date('d.m.Y'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Von'),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Für'),
                Tables\Columns\TextColumn::make('iban')
                    ->label('IBAN')
                    ->copyable()
                    ->copyMessage('Kopiert'),
                Tables\Columns\SelectColumn::make('status')
                    ->selectablePlaceholder(false)
                    ->alignRight()
                    ->options(ExpenseStatus::class),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('reload_iban')
                    ->tooltip('IBAN neu aus dem Vereinsflieger auslesen')
                    ->color('gray')
                    ->iconButton()
                    ->icon('heroicon-o-arrow-path')
                    ->hidden(fn ($record) => $record->status != ExpenseStatus::OPEN)
                    ->action(function ($record) {
                        EnrichExpenseWithIban::dispatch($record);

                        Notification::make()
                            ->title('Einen Augenblick...')
                            ->body('Die IBAN wird in den nächsten Sekunden automatisch aktualisiert.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('show_receipt')
                    ->color('gray')
                    ->openUrlInNewTab()
                    ->iconButton()
                    ->icon('heroicon-s-eye')
                    ->action(function ($record) {
                        return Storage::download($record->receipt_filename, basename($record->receipt_filename));
                    }),
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
            'index' => Pages\ManageExpenses::route('/'),
        ];
    }
}
