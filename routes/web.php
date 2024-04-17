<?php

use App\Livewire\HomePage;
use App\Livewire\OilLogPage;
use App\Livewire\RefuelingPage;
use Illuminate\Support\Facades\Route;

Route::get('/', HomePage::class)->name('home');
Route::get('/tanken', RefuelingPage::class)->name('refueling');
Route::get('/oil', OilLogPage::class)->name('oil');
