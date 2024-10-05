<?php

namespace App\Livewire;

use App\Models\Expense;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ExpensesPage extends Component implements HasForms
{
    use InteractsWithForms;

    public $data = [];

    public $saved = false;

    public function store()
    {
        $this->validate();

        DB::transaction(function () {
            $files = data_get($this->data, 'files');
            foreach ($files as $file) {
                $filename = $file->store('expenses');

                Expense::create([
                    'user_id' => auth()->id(),
                    'reason' => $this->data['reason'],
                    'filename' => $filename,
                ]);
            }
        });

        $this->saved = true;
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Fieldset::make('Beleg')
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
                            ->hint('Du kannst mehrere Belege gleichzeitig hochladen.'),
                        Toggle::make('is_paid')
                            ->label('Ich bestätige, dass der Betrag bereits vollständig bezahlt ist und bitte um Erstattung auf mein Konto.'),
                    ]),
            ]);
    }

    public function render()
    {
        return view('livewire.expenses-page');
    }
}
