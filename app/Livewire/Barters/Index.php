<?php

namespace App\Livewire\Barters;

use App\Services\TarkovDevService;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $trader = '';

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
            $needle = mb_strtolower(trim($this->search));

            $filtered = $all
                ->when($this->trader !== '', fn ($c) => $c->where('trader.name', $this->trader))
                ->when($this->profitable, fn ($c) => $c->where('profit', '>', 0))
                ->when($needle !== '', fn ($c) => $c->filter(fn ($barter) => $this->barterMatches($barter, $needle)))
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

    private function barterMatches(array $barter, string $needle): bool
    {
        $haystack = collect([
            $barter['trader']['name'] ?? '',
            $barter['taskUnlock']['name'] ?? '',
        ])
            ->concat(collect($barter['requiredItems'] ?? [])->pluck('item.name'))
            ->concat(collect($barter['rewardItems'] ?? [])->pluck('item.name'))
            ->filter()
            ->implode(' ');

        return str_contains(mb_strtolower($haystack), $needle);
    }
}
