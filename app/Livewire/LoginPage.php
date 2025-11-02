<?php

namespace App\Livewire;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LoginPage extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public $data = [];

    public $error = false;

    public function login()
    {
        $this->error = false;

        if (! $this->form->validate()) {
            return;
        }

        $data = $this->form->getState();
        $result = Auth::attempt($data, remember: true);

        if ($result) {
            return $this->redirectRoute('home');
        }

        // Login failed
        $this->error = true;
    }

    public function submitAction(): Action
    {
        return Action::make('submit')
            ->icon('heroicon-o-lock-closed')
            ->label('Anmelden')
            ->submit('login')
            ->extraAttributes([
                'class' => 'w-full',
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                TextInput::make('email')
                    ->label('E-Mail-Adresse')
                    ->required()
                    ->email(),
                TextInput::make('password')
                    ->label('Passwort')
                    ->required()
                    ->password(),
            ]);
    }

    public function render()
    {
        return view('livewire.login-page');
    }
}
