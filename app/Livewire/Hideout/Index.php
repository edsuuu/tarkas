<?php

namespace App\Livewire\Hideout;

use App\Services\TarkovDevService;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public int $level = 0;

    public function render()
    {
        $error = null;
        $searching = trim($this->search) !== '';
        $filtering = $searching || $this->level > 0;
        $stations = collect();
        $levels = collect();

        try {
            $all = collect(app(TarkovDevService::class)->hideoutStations())
                ->sortBy('name')
                ->values();

            // Níveis de upgrade disponíveis em qualquer estação (para o dropdown).
            $levels = $all
                ->flatMap(fn ($station) => collect($station['levels'] ?? [])->pluck('level'))
                ->filter()
                ->unique()
                ->sort()
                ->values();

            $stations = $all;

            // Filtro por nível: mantém só o nível de upgrade escolhido em cada estação.
            if ($this->level > 0) {
                $stations = $stations
                    ->map(function ($station) {
                        $station['levels'] = collect($station['levels'] ?? [])
                            ->filter(fn ($level) => (int) ($level['level'] ?? 0) === $this->level)
                            ->values()
                            ->all();

                        return $station;
                    })
                    ->filter(fn ($station) => count($station['levels']) > 0)
                    ->values();
            }

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

        return view('livewire.hideout.index', compact('stations', 'searching', 'filtering', 'levels', 'error'))
            ->title('Hideout — Tarkas');
    }
}
