<?php

namespace App\Livewire;

use App\Models\Event;
use Livewire\Component;

class HomePage extends Component
{
    public $event;

    public function mount()
    {
        $this->event = Event::first();
    }

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
