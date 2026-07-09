<?php

namespace App\Livewire\Traders;

use App\Services\TarkovDevService;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        $error = null;
        $traders = [];

        try {
            $traders = app(TarkovDevService::class)->traders();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('livewire.traders.index', compact('traders', 'error'))
            ->title('Traders — Tarkas');
    }
}
