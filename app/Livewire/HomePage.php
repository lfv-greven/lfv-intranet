<?php

namespace App\Livewire;

use Livewire\Component;

class HomePage extends Component
{
    public function render()
    {
        return view('livewire.home-page');
    }

    public function signOut()
    {
        auth()->logout();
        session()->regenerate();

        return $this->redirectRoute('login');
    }
}
