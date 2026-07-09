<?php

namespace App\Livewire\Crafts;

use App\Services\TarkovDevService;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url]
    public string $station = '';

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

            $filtered = $all
                ->when($this->station !== '', fn ($c) => $c->where('station.name', $this->station))
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
}
