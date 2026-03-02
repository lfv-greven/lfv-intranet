<?php

namespace App\Livewire;

use App\External\Mattermost;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Livewire\Component;

class ChatSettingsPage extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public function resetPasswordAction(): Action
    {
        return Action::make('resetPassword')
            ->label('zurücksetzen')
            ->color('primary')
            ->link()
            ->extraAttributes([
                'class' => 'ml-3 text-xs font-semibold uppercase tracking-[0.2em]',
            ])
            ->requiresConfirmation()
            ->modalHeading('Passwort zurücksetzen')
            ->modalDescription('Du erhältst einen Link an deine hinterlegte E-Mail-Adresse, um dein Passwort zu ändern.')
            ->action(function (): void {
                $this->dispatch('umami-track', name: 'chat_password_reset_requested');

                $success = Mattermost::requestPasswordReset(auth()->user()->email);

                if ($success) {
                    Notification::make()
                        ->success()
                        ->title('Passwort-Reset versendet')
                        ->body('Prüfe dein E-Mail-Postfach. Wir haben dir eine E-Mail geschickt.')
                        ->send();

                    return;
                }

                Notification::make()
                    ->danger()
                    ->title('Passwort konnte nicht zurückgesetzt werden')
                    ->body('Bitte versuche es später erneut.')
                    ->send();
            });
    }

    public function render()
    {
        return view('livewire.chat-settings-page');
    }
}
