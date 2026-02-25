<?php

namespace App\Livewire;

use App\Models\Expense;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Throwable;

class ExpensesPage extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public array $data = [
        'files' => [],
        'is_paid' => false,
        'is_correct_address' => false,
    ];

    public $saved = false;

    public function store()
    {
        $this->dispatch('umami-track', name: 'expense_submit_attempt');

        try {
            $this->validate();
        } catch (ValidationException $exception) {
            $this->dispatch('umami-track', name: 'expense_submit_error', data: [
                'error_type' => 'validation',
            ]);

            throw $exception;
        }

        try {
            DB::transaction(function () {
                $files = data_get($this->data, 'files');
                foreach ($files as $file) {
                    $filename = $file->store('expenses');

                    Expense::create([
                        'user_id' => auth()->id(),
                        'reason' => $this->data['reason'],
                        'receipt_filename' => $filename,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            $this->dispatch('umami-track', name: 'expense_submit_error', data: [
                'error_type' => 'save_failure',
            ]);

            throw $exception;
        }

        $this->saved = true;

        $this->dispatch('umami-track', name: 'expense_submit_success', data: [
            'receipt_count' => count(data_get($this->data, 'files', [])),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->model(Expense::class)
            ->components([
                Section::make('')
                    ->columns(1)
                    ->schema([
                        TextInput::make('reason')
                            ->required()
                            ->label('Wofür war der Einkauf?'),
                        FileUpload::make('files')
                            ->multiple()
                            ->required()
                            ->name('file')
                            ->label('Belege')
                            ->previewable(false)
                            ->hint('Du kannst mehrere Belege gleichzeitig hochladen.'),
                        Toggle::make('is_paid')
                            ->accepted()
                            ->label('Der Betrag ist bereits vollständig bezahlt und ich bitte um Erstattung auf mein Konto.'),
                        Toggle::make('is_correct_address')
                            ->accepted()
                            ->label('Hinweise zur Rechnungsadresse habe ich geprüft:')
                            ->helperText('Ab einem Betrag von 250 € muss die vollständige Adresse des Vereins als Rechnungsadresse aufgedruckt sein.'),
                    ]),
            ]);
    }

    public function render()
    {
        return view('livewire.expenses-page');
    }
}
