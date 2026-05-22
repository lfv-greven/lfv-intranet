<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DepartmentController;
use App\Livewire\ChatSettingsPage;
use App\Livewire\DepartmentPage;
use App\Livewire\EventPage;
use App\Livewire\ExpensesPage;
use App\Livewire\HomePage;
use App\Livewire\LoginPage;
use App\Livewire\OilLogPage;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/auslagen', ExpensesPage::class)->name('expenses');
    Route::get('/chat', ChatSettingsPage::class)->name('chat');
    Route::get('/department', DepartmentPage::class)->name('department');
    Route::get('/event/{event}', EventPage::class)->name('event');
});

Route::middleware(config('auth.login_required') ? ['auth'] : [])->group(function () {
    Route::get('/', HomePage::class);
    Route::get('/home', HomePage::class)->name('home');
    Route::livewire('/tanken', 'refueling-page')->name('refueling');
    Route::livewire('/tanken-gespeichert', 'refueling-success-page')->name('refueling.success');
    Route::get('/oil', OilLogPage::class)->name('oil');
    Route::livewire('/oil-gespeichert', 'oil-log-success-page')->name('oil.success');
});

Route::get('/login', LoginPage::class)->name('login')->middleware('guest');

Route::get('/vf/login', [AuthController::class, 'vfLogin'])->name('auth.vf-login');
Route::any('/vf/iframe', [AuthController::class, 'vfIframe'])->name('auth.vf-iframe');

Route::get('/dl/Teams.pdf', [DepartmentController::class, 'teamDescriptions'])
    ->name('department.descriptions-team');
