<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Livewire\Ammo\Index as AmmoIndex;
use App\Livewire\Barters\Index as BartersIndex;
use App\Livewire\Crafts\Index as CraftsIndex;
use App\Livewire\Dashboard;
use App\Livewire\Hideout\Index as HideoutIndex;
use App\Livewire\Items\Index as ItemsIndex;
use App\Livewire\Items\Show as ItemsShow;
use App\Livewire\Maps\Index as MapsIndex;
use App\Livewire\Tasks\Index as TasksIndex;
use App\Livewire\Traders\Index as TradersIndex;
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

Route::middleware('throttle:60,1')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/itens', ItemsIndex::class)->name('items.index');
    Route::get('/itens/{id}', ItemsShow::class)->name('items.show');
    Route::get('/municao', AmmoIndex::class)->name('ammo.index');
    Route::get('/quests', TasksIndex::class)->name('tasks.index');
    Route::get('/hideout', HideoutIndex::class)->name('hideout.index');
    Route::get('/traders', TradersIndex::class)->name('traders.index');
    Route::get('/trocas', BartersIndex::class)->name('barters.index');
    Route::get('/crafts', CraftsIndex::class)->name('crafts.index');
    Route::get('/mapas', MapsIndex::class)->name('maps.index');
});
