<?php

namespace App\Livewire\Maps;

use App\Services\TarkovDevService;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        $error = null;
        $maps = [];

        try {
            $maps = collect(app(TarkovDevService::class)->maps())
                ->sortBy('name')
                ->values()
                ->all();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('livewire.maps.index', compact('maps', 'error'))
            ->title('Mapas — Tarkas');
    }
}
