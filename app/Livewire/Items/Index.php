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

    /** Item aberto no modal (só o id fica no estado; os dados vêm do cache no render). */
    public ?string $selectedId = null;

    public function updatedSearch(): void
    {
        $this->limit = 48;
    }

    public function loadMore(): void
    {
        $this->limit += 48;
    }

    public function openItem(string $id): void
    {
        $this->selectedId = $id;
    }

    public function closeItem(): void
    {
        $this->selectedId = null;
    }

    public function render()
    {
        $error = null;
        $items = [];
        $selectedItem = null;

        try {
            $items = app(TarkovDevService::class)->searchItems($this->search, $this->limit);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        // Carregar o item do modal não deve derrubar a listagem se falhar.
        if ($this->selectedId !== null) {
            try {
                $selectedItem = app(TarkovDevService::class)->item($this->selectedId) ?: null;
            } catch (\Throwable $e) {
                $selectedItem = null;
            }
        }

        return view('livewire.items.index', compact('items', 'error', 'selectedItem'));
    }
}
