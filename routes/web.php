<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Livewire\Dashboard;
use App\Livewire\Items\Index;
use App\Livewire\Items\Show;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');

    Route::get('/cadastro', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/cadastro', [RegisteredUserController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('register.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/', Dashboard::class)->name('dashboard');
Route::get('/itens', Index::class)->name('items.index');
Route::get('/itens/{id}', Show::class)->name('items.show');
Route::get('/municao', App\Livewire\Ammo\Index::class)->name('ammo.index');
Route::get('/quests', App\Livewire\Tasks\Index::class)->name('tasks.index');
Route::get('/hideout', App\Livewire\Hideout\Index::class)->name('hideout.index');
Route::get('/traders', App\Livewire\Traders\Index::class)->name('traders.index');
Route::get('/trocas', App\Livewire\Barters\Index::class)->name('barters.index');
Route::get('/crafts', App\Livewire\Crafts\Index::class)->name('crafts.index');
Route::get('/mapas', App\Livewire\Maps\Index::class)->name('maps.index');
