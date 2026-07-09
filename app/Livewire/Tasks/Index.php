<?php

namespace App\Livewire\Tasks;

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
    public bool $kappa = false;

    public int $shown = 60;

    public function updated(): void
    {
        $this->shown = 60;
    }

    public function loadMore(): void
    {
        $this->shown += 60;
    }

    public function render()
    {
        $error = null;
        $tasks = collect();
        $traders = collect();
        $total = 0;

        try {
            $all = collect(app(TarkovDevService::class)->tasks());

            $traders = $all->pluck('trader.name')->filter()->unique()->sort()->values();

            $filtered = $all
                ->when($this->trader !== '', fn ($c) => $c->where('trader.name', $this->trader))
                ->when($this->kappa, fn ($c) => $c->where('kappaRequired', true))
                ->when($this->search !== '', fn ($c) => $c->filter(
                    fn ($t) => str_contains(mb_strtolower($t['name'] ?? ''), mb_strtolower($this->search))
                ))
                ->values();

            $total = $filtered->count();
            $tasks = $filtered->take($this->shown);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('livewire.tasks.index', compact('tasks', 'traders', 'total', 'error'))
            ->title('Quests — Tarkas');
    }
}
