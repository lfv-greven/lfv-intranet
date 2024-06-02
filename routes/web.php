<?php

use App\Http\Controllers\AuthController;
use App\Livewire\ChatSettingsPage;
use App\Livewire\HomePage;
use App\Livewire\LoginPage;
use App\Livewire\OilLogPage;
use App\Livewire\RefuelingPage;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::get('/home', HomePage::class)->name('home');
Route::get('/tanken', RefuelingPage::class)->name('refueling');
Route::get('/oil', OilLogPage::class)->name('oil');
Route::get('/chat', ChatSettingsPage::class)->name('chat')->middleware('auth');

Route::get('/login', LoginPage::class)->name('login')->middleware('guest');
Route::post('/login/iframe', [AuthController::class, 'vfLogin']);
