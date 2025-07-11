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
Route::view('/tanken-gespeichert', 'refueling-success-page')->name('refueling.success');
Route::get('/auslagen', \App\Livewire\ExpensesPage::class)->name('expenses');
Route::get('/oil', OilLogPage::class)->name('oil');
Route::get('/chat', ChatSettingsPage::class)->name('chat')->middleware('auth');
Route::get('/department', \App\Livewire\DepartmentPage::class)->name('department')->middleware('auth');
Route::get('/event/{event}', \App\Livewire\EventPage::class)->name('event')->middleware('auth');

Route::get('/login', LoginPage::class)->name('login')->middleware('guest');

Route::get('/vf/login', [AuthController::class, 'vfLogin'])->name('auth.vf-login');
Route::any('/vf/iframe', [AuthController::class, 'vfIframe'])->name('auth.vf-iframe');

Route::get('/dl/Teams.pdf', [\App\Http\Controllers\DepartmentController::class, 'teamDescriptions'])
    ->name('department.descriptions-team');
