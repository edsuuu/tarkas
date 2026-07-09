<?php

namespace App\Livewire\Barters;

use App\Services\TarkovDevService;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url]
    public string $trader = '';

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
     * Calcula custo (comprar os itens exigidos na flea) e valor de mercado
     * dos itens recebidos, para estimar o lucro da troca.
     */
    protected function enrich(array $barter): array
    {
        $cost = 0;
        $complete = true;

        foreach ($barter['requiredItems'] as $req) {
            $price = $req['item']['lastLowPrice'] ?? $req['item']['avg24hPrice'];
            if ($price === null) {
                $complete = false;
                continue;
            }
            $cost += $price * $req['count'];
        }

        $value = 0;
        foreach ($barter['rewardItems'] as $reward) {
            $price = $reward['item']['avg24hPrice'] ?? $reward['item']['lastLowPrice'];
            if ($price === null) {
                $complete = false;
                continue;
            }
            $value += $price * $reward['count'];
        }

        $barter['cost'] = $cost;
        $barter['value'] = $value;
        $barter['profit'] = $value - $cost;
        $barter['complete'] = $complete;

        return $barter;
    }

    public function render()
    {
        $error = null;
        $barters = collect();
        $traders = collect();
        $total = 0;

        try {
            $all = collect(app(TarkovDevService::class)->barters())->map(fn ($b) => $this->enrich($b));

            $traders = $all->pluck('trader.name')->filter()->unique()->sort()->values();

            $filtered = $all
                ->when($this->trader !== '', fn ($c) => $c->where('trader.name', $this->trader))
                ->sortByDesc('profit')
                ->values();

            $total = $filtered->count();
            $barters = $filtered->take($this->shown);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('livewire.barters.index', compact('barters', 'traders', 'total', 'error'))
            ->title('Trocas (Barters) — Tarkas');
    }
}
