<?php

namespace App\Livewire\Hideout;

use App\Services\TarkovDevService;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url(as: 'q')]
    public string $search = '';

    /** Estação atualmente expandida (accordion controlado, uma por vez). */
    public ?string $open = null;

    public function toggle(string $stationId): void
    {
        $this->open = $this->open === $stationId ? null : $stationId;
    }

    public function render()
    {
        $error = null;
        $stations = collect();
        $searching = trim($this->search) !== '';

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

        return view('livewire.hideout.index', compact('stations', 'searching', 'error'))
            ->title('Hideout — Tarkas');
    }
}
