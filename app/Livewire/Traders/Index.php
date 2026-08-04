<?php

namespace App\Livewire\Traders;

use App\Services\TarkovDevService;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $currency = '';

    public function render()
    {
        $error = null;
        $traders = collect();
        $currencies = collect();
        $total = 0;

        try {
            $all = collect(app(TarkovDevService::class)->traders());

            $currencies = $all->pluck('currency.name')->filter()->unique()->sort()->values();
            $needle = mb_strtolower(trim($this->search));

            $traders = $all
                ->when($this->currency !== '', fn ($c) => $c->where('currency.name', $this->currency))
                ->when($needle !== '', fn ($c) => $c->filter(fn ($trader) => $this->traderMatches($trader, $needle)))
                ->values();

            $total = $traders->count();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('livewire.traders.index', compact('traders', 'currencies', 'total', 'error'));
    }

    private function traderMatches(array $trader, string $needle): bool
    {
        $haystack = collect([
            $trader['name'] ?? '',
            $trader['description'] ?? '',
            $trader['currency']['name'] ?? '',
        ])->filter()->implode(' ');

        return str_contains(mb_strtolower($haystack), $needle);
    }
}
