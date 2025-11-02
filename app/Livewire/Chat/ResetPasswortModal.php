<?php

namespace App\Livewire\Chat;

use App\External\Mattermost;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use LivewireUI\Modal\ModalComponent;

class ResetPasswortModal extends ModalComponent implements HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    public $status;

    public function doResetPassword()
    {
        $success = Mattermost::requestPasswordReset(auth()->user()->email);

        if ($success) {
            $this->status = 'success';
        } else {
            $this->status = 'error';
        }
    }

    public function render()
    {
        return view('livewire.chat.reset-passwort-modal');
    }
}
