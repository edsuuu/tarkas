<?php

use Illuminate\Support\Facades\Route;

Route::get('/', App\Livewire\Dashboard::class)->name('dashboard');
Route::get('/itens', App\Livewire\Items\Index::class)->name('items.index');
Route::get('/itens/{id}', App\Livewire\Items\Show::class)->name('items.show');
Route::get('/municao', App\Livewire\Ammo\Index::class)->name('ammo.index');
Route::get('/quests', App\Livewire\Tasks\Index::class)->name('tasks.index');
Route::get('/hideout', App\Livewire\Hideout\Index::class)->name('hideout.index');
Route::get('/traders', App\Livewire\Traders\Index::class)->name('traders.index');
Route::get('/trocas', App\Livewire\Barters\Index::class)->name('barters.index');
Route::get('/crafts', App\Livewire\Crafts\Index::class)->name('crafts.index');
Route::get('/mapas', App\Livewire\Maps\Index::class)->name('maps.index');
