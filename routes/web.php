<?php

use App\Http\Controllers\AuthController;
use App\Livewire\ChatSettingsPage;
use App\Livewire\HomePage;
use App\Livewire\LoginPage;
use App\Livewire\OilLogPage;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('auth')->group(function () {
    Route::get('/home', HomePage::class)->name('home');
    Route::get('/auslagen', \App\Livewire\ExpensesPage::class)->name('expenses');
    Route::get('/chat', ChatSettingsPage::class)->name('chat');
    Route::get('/department', \App\Livewire\DepartmentPage::class)->name('department');
    Route::get('/event/{event}', \App\Livewire\EventPage::class)->name('event');
});

Route::middleware(config('auth.login_required') ? ['auth'] : [])->group(function () {
    Route::livewire('/tanken', 'refueling-page')->name('refueling');
    Route::livewire('/tanken-gespeichert', 'refueling-success-page')->name('refueling.success');
    Route::get('/oil', OilLogPage::class)->name('oil');
    Route::livewire('/oil-gespeichert', 'oil-log-success-page')->name('oil.success');
});

Route::get('/login', LoginPage::class)->name('login')->middleware('guest');

Route::get('/vf/login', [AuthController::class, 'vfLogin'])->name('auth.vf-login');
Route::any('/vf/iframe', [AuthController::class, 'vfIframe'])->name('auth.vf-iframe');

Route::get('/dl/Teams.pdf', [\App\Http\Controllers\DepartmentController::class, 'teamDescriptions'])
    ->name('department.descriptions-team');
