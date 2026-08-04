<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
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
    Route::view('/', 'pages.dashboard')->name('dashboard');
    Route::view('/itens', 'pages.items')->name('items.index');
    Route::view('/itens/{id}', 'pages.item')->name('items.show');
    Route::view('/municao', 'pages.ammo')->name('ammo.index');
    Route::view('/quests', 'pages.tasks')->name('tasks.index');
    Route::view('/hideout', 'pages.hideout')->name('hideout.index');
    Route::view('/traders', 'pages.traders')->name('traders.index');
    Route::view('/trocas', 'pages.barters')->name('barters.index');
    Route::view('/crafts', 'pages.crafts')->name('crafts.index');
    Route::view('/mapas', 'pages.maps')->name('maps.index');
});
