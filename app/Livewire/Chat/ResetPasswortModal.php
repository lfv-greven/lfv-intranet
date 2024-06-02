<?php

namespace App\Livewire\Chat;

use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Support\Facades\Http;
use LivewireUI\Modal\ModalComponent;

class ResetPasswortModal extends ModalComponent
{
    use InteractsWithForms;

    public $status;

    public function doResetPassword()
    {
        $res = Http::asJson()
            ->acceptJson()
            ->baseUrl(config('services.tasks.url'))
            ->post('mm/reset-user-password', [
                'email' => auth()->user()->email,
            ]);

        if ($res->ok()) {
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
