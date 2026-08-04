<?php

namespace App\Livewire\Maps;

use App\Services\TarkovDevService;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $boss = '';

    #[Url]
    public string $faction = '';

    public function render()
    {
        $error = null;
        $maps = collect();
        $bosses = collect();
        $total = 0;

        try {
            $all = collect(app(TarkovDevService::class)->maps())
                ->map(function (array $map) {
                    $map['name'] = $this->englishMapName($map);
                    $map['image'] = $this->mapImage($map);
                    $map['interactiveUrl'] = $this->interactiveUrl($map);

                    return $map;
                });

            $bosses = $all
                ->flatMap(fn ($map) => collect($map['bosses'] ?? [])->pluck('boss.name'))
                ->filter()
                ->unique()
                ->sort()
                ->values();
            $needle = mb_strtolower(trim($this->search));

            $maps = $all
                ->when($this->boss !== '', fn ($c) => $c->filter(fn ($map) => $this->mapHasBoss($map, $this->boss)))
                ->when($this->faction !== '', fn ($c) => $c->filter(fn ($map) => $this->mapHasFactionExtract($map, $this->faction)))
                ->when($needle !== '', fn ($c) => $c->filter(fn ($map) => $this->mapMatches($map, $needle)))
                ->sortBy('name')
                ->values();

            $total = $maps->count();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('livewire.maps.index', compact('maps', 'bosses', 'total', 'error'));
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

    /**
     * SVG hospedado em assets.tarkov.dev por mapa. O nome do arquivo não é
     * derivável do normalizedName (ex.: streets-of-tarkov → StreetsOfTarkov),
     * então mapeamos explicitamente. Mapas sem SVG (the-lab, icebreaker…) ficam sem imagem.
     */
    private const MAP_SVG = [
        'streets-of-tarkov' => 'StreetsOfTarkov',
        'ground-zero' => 'GroundZero',
        'ground-zero-21' => 'GroundZero',
        'ground-zero-tutorial' => 'GroundZero',
        'customs' => 'Customs',
        'factory' => 'Factory',
        'night-factory' => 'Factory',
        'interchange' => 'Interchange',
        'lighthouse' => 'Lighthouse',
        'reserve' => 'Reserve',
        'shoreline' => 'Shoreline',
        'terminal' => 'Terminal',
        'woods' => 'Woods',
    ];

    private function mapImage(array $map): ?string
    {
        $file = self::MAP_SVG[$map['normalizedName'] ?? ''] ?? null;

        return $file ? "https://assets.tarkov.dev/maps/svg/{$file}.svg" : null;
    }

    private function interactiveUrl(array $map): ?string
    {
        $name = $map['normalizedName'] ?? '';

        return $name !== '' ? "https://tarkov.dev/map/{$name}" : null;
    }

    private function mapHasBoss(array $map, string $boss): bool
    {
        return collect($map['bosses'] ?? [])->contains(fn ($spawn) => ($spawn['boss']['name'] ?? null) === $boss);
    }

    private function mapHasFactionExtract(array $map, string $faction): bool
    {
        return collect($map['extracts'] ?? [])->contains(fn ($extract) => ($extract['faction'] ?? 'shared') === $faction);
    }

    private function mapMatches(array $map, string $needle): bool
    {
        $haystack = collect([
            $map['name'] ?? '',
            $map['description'] ?? '',
            $map['players'] ?? '',
            $map['raidDuration'] ?? '',
        ])
            ->concat(collect($map['bosses'] ?? [])->pluck('boss.name'))
            ->concat(collect($map['extracts'] ?? [])->pluck('name'))
            ->concat(collect($map['accessKeys'] ?? [])->pluck('name'))
            ->filter()
            ->implode(' ');

        return str_contains(mb_strtolower($haystack), $needle);
    }
}
