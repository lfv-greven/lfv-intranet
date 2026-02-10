<?php

namespace App\Filament\Resources\Expenses;

use App\Enums\ExpenseStatus;
use App\Filament\Resources\Expenses\Pages\ManageExpenses;
use App\Jobs\EnrichExpenseWithIban;
use App\Models\Expense;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Auslagen';

    protected static string|\UnitEnum|null $navigationGroup = 'Finanzen';

    protected static ?string $label = 'Auslagen';

    protected static ?string $pluralLabel = 'Auslagen';

    public static function getNavigationBadge(): ?string
    {
        return Expense::open()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return Expense::open()->count() > 0 ? 'info' : 'gray';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
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
            ->poll('5s')
            ->columns([
                TextColumn::make('id')
                    ->copyable()
                    ->hidden(app()->isProduction()),
                TextColumn::make('created_at')
                    ->sortable()
                    ->label('Erstellt')
                    ->date('d.m.Y'),
                TextColumn::make('user.name')
                    ->label('Von'),
                TextColumn::make('reason')
                    ->label('Für'),
                TextColumn::make('iban')
                    ->label('IBAN')
                    ->copyable()
                    ->copyMessage('Kopiert')
                    ->formatStateUsing(fn ($state) => iban_to_obfuscated_format($state)),
                SelectColumn::make('status')
                    ->selectablePlaceholder(false)
                    ->alignRight()
                    ->disabled(fn (Expense $record) => empty($record->iban) || $record->status == ExpenseStatus::APPROVED)
                    ->options(ExpenseStatus::class),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('reload_iban')
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
                Action::make('show_receipt')
                    ->color('gray')
                    ->iconButton()
                    ->icon('heroicon-s-eye')
                    ->url(function ($record) {
                        return Storage::temporaryUrl(
                            $record->receipt_filename,
                            now()->addMinute(),
                        );
                    }, shouldOpenInNewTab: true),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageExpenses::route('/'),
        ];
    }
}
