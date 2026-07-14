<?php

namespace App\Livewire;

use App\Exceptions\VereinsfliegerDeferred;
use App\Exceptions\VereinsfliegerTransportException;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class LoginPage extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public $data = [];

    public ?string $errorType = null;

    public function login()
    {
        $this->errorType = null;
        $this->dispatch('umami-track', name: 'login_attempt');

        try {
            $this->form->validate();
        } catch (ValidationException $exception) {
            $this->dispatch('umami-track', name: 'login_error', data: [
                'error_type' => 'validation',
            ]);

            throw $exception;
        }

        $data = $this->form->getState();
        $rateLimitKey = 'login:'.hash('sha256', Str::lower((string) $data['email']).'|'.request()->ip());

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $this->setLoginError('rate_limited');

            return;
        }

        RateLimiter::hit($rateLimitKey, 60);

        try {
            $result = Auth::attempt($data, remember: true);
        } catch (VereinsfliegerDeferred) {
            $this->setLoginError('rate_limited');

            return;
        } catch (VereinsfliegerTransportException) {
            $this->setLoginError('service_unavailable');

            return;
        }

        if ($result) {
            RateLimiter::clear($rateLimitKey);
            $this->dispatch('umami-track', name: 'login_success');

            return $this->redirectRoute('home');
        }

        // Login failed
        $this->setLoginError('credentials');
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
        return view('livewire.login-page', [
            'loginMessageTitle' => trim((string) config('services.login_message.title', '')),
            'loginMessageBody' => trim((string) config('services.login_message.body', '')),
        ]);
    }

    private function setLoginError(string $type): void
    {
        $this->errorType = $type;
        $this->dispatch('umami-track', name: 'login_error', data: [
            'error_type' => $type,
        ]);
    }
}
