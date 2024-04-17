<?php

use App\Livewire\OilLogPage;
use App\Livewire\RefuelingPage;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::get('/tanken', RefuelingPage::class);
Route::get('/oil', OilLogPage::class);
