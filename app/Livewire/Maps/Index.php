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
                ->map(function (array $map) {
                    $map['name'] = $this->englishMapName($map);

                    return $map;
                })
                ->sortBy('name')
                ->values()
                ->all();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('livewire.maps.index', compact('maps', 'error'))
            ->title('Mapas — Tarkas');
    }

    private function englishMapName(array $map): string
    {
        return match ($map['normalizedName'] ?? '') {
            'customs' => 'Customs',
            'factory' => 'Factory',
            'ground-zero' => 'Ground Zero',
            'ground-zero-21' => 'Ground Zero 21+',
            'ground-zero-tutorial' => 'Ground Zero Tutorial',
            'icebreaker' => 'Icebreaker',
            'interchange' => 'Interchange',
            'lighthouse' => 'Lighthouse',
            'night-factory' => 'Night Factory',
            'reserve' => 'Reserve',
            'shoreline' => 'Shoreline',
            'streets-of-tarkov' => 'Streets of Tarkov',
            'terminal' => 'Terminal',
            'the-lab' => 'The Lab',
            'the-labyrinth' => 'The Labyrinth',
            'woods' => 'Woods',
            default => $map['name'] ?? 'Unknown Map',
        };
    }
}
