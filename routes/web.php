<?php

use App\Livewire\RefuelingPage;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::get('/tanken', RefuelingPage::class);
