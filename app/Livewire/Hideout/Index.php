<?php

namespace App\Livewire\Hideout;

use App\Services\TarkovDevService;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url(as: 'q')]
    public string $search = '';

    public function render()
    {
        $error = null;
        $searching = trim($this->search) !== '';
        $stations = collect();

        try {
            $stations = collect(app(TarkovDevService::class)->hideoutStations())
                ->sortBy('name')
                ->values();

            // Busca por item: mantém só as estações/níveis que exigem o item procurado.
            if ($searching) {
                $needle = mb_strtolower(trim($this->search));

                $stations = $stations
                    ->map(function ($station) use ($needle) {
                        $station['levels'] = collect($station['levels'] ?? [])
                            ->filter(fn ($level) => collect($level['itemRequirements'] ?? [])->contains(
                                fn ($req) => str_contains(mb_strtolower($req['item']['name'] ?? ''), $needle)
                            ))
                            ->values()
                            ->all();

                        return $station;
                    })
                    ->filter(fn ($station) => count($station['levels']) > 0)
                    ->values();
            }
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        $stations = $stations->values();

        return view('livewire.hideout.index', compact('stations', 'searching', 'error'))
            ->title('Hideout — Tarkas');
    }
}
