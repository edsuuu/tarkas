<?php

namespace App\Livewire\Items;

use App\Services\TarkovDevService;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url(as: 'q')]
    public string $search = '';

    public int $limit = 48;

    public function updatedSearch(): void
    {
        $this->limit = 48;
    }

    public function loadMore(): void
    {
        $this->limit += 48;
    }

    public function render()
    {
        $error = null;
        $items = [];

        try {
            $items = app(TarkovDevService::class)->searchItems($this->search, $this->limit);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('livewire.items.index', compact('items', 'error'))
            ->title('Itens & Flea Market — Tarkas');
    }
}
