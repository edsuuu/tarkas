<?php

namespace App\Livewire\Crafts;

use App\Services\TarkovDevService;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $station = '';

    #[Url]
    public bool $profitable = false;

    public int $shown = 40;

    public function updated(): void
    {
        $this->shown = 40;
    }

    public function loadMore(): void
    {
        $this->shown += 40;
    }

    /**
     * Calcula custo dos insumos, valor da produção e lucro por hora do craft.
     */
    protected function enrich(array $craft): array
    {
        $cost = 0;
        $complete = true;

        foreach ($craft['requiredItems'] as $req) {
            $price = $req['item']['lastLowPrice'] ?? $req['item']['avg24hPrice'];
            if ($price === null) {
                $complete = false;

                continue;
            }
            $cost += $price * $req['count'];
        }

        $value = 0;
        foreach ($craft['rewardItems'] as $reward) {
            $price = $reward['item']['avg24hPrice'] ?? $reward['item']['lastLowPrice'];
            if ($price === null) {
                $complete = false;

                continue;
            }
            $value += $price * $reward['count'];
        }

        $craft['cost'] = $cost;
        $craft['value'] = $value;
        $craft['profit'] = $value - $cost;
        $craft['profitPerHour'] = $craft['duration'] > 0
            ? (int) round(($value - $cost) / ($craft['duration'] / 3600))
            : null;
        $craft['complete'] = $complete;

        return $craft;
    }

    public function render()
    {
        $error = null;
        $crafts = collect();
        $stations = collect();
        $total = 0;

        try {
            $all = collect(app(TarkovDevService::class)->crafts())->map(fn ($c) => $this->enrich($c));

            $stations = $all->pluck('station.name')->filter()->unique()->sort()->values();
            $needle = mb_strtolower(trim($this->search));

            $filtered = $all
                ->when($this->station !== '', fn ($c) => $c->where('station.name', $this->station))
                ->when($this->profitable, fn ($c) => $c->where('profit', '>', 0))
                ->when($needle !== '', fn ($c) => $c->filter(fn ($craft) => $this->craftMatches($craft, $needle)))
                ->sortByDesc('profit')
                ->values();

            $total = $filtered->count();
            $crafts = $filtered->take($this->shown);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('livewire.crafts.index', compact('crafts', 'stations', 'total', 'error'))
            ->title('Crafts — Tarkas');
    }

    private function craftMatches(array $craft, string $needle): bool
    {
        $haystack = collect([
            $craft['station']['name'] ?? '',
        ])
            ->concat(collect($craft['requiredItems'] ?? [])->pluck('item.name'))
            ->concat(collect($craft['rewardItems'] ?? [])->pluck('item.name'))
            ->filter()
            ->implode(' ');

        return str_contains(mb_strtolower($haystack), $needle);
    }
}
