<?php

namespace App\Filament\Resources;

use App\Enums\ExpenseStatus;
use App\Filament\Resources\ExpenseResource\Pages;
use App\Jobs\EnrichExpenseWithIban;
use App\Models\Expense;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
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

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
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
                    ->label('IBAN')
                    ->tooltip('IBAN neu aus dem Vereinsflieger auslesen')
                    ->color('gray')
                    ->button()
                    ->icon('heroicon-o-arrow-path')
                    ->hidden(fn($record) => $record->status != ExpenseStatus::OPEN)
                    ->action(function ($record) {
                        EnrichExpenseWithIban::dispatch($record);

                        Notification::make()
                            ->title('Einen Augenblick...')
                            ->body('Die IBAN wird in den nächsten Sekunden automatisch aktualisiert.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('show_receipt')
                    ->label('Beleg herunterladen')
                    ->color('gray')
                    ->openUrlInNewTab()
                    ->button()
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
