<?php

namespace App\Livewire\Items;

use App\Services\TarkovDevService;
use Livewire\Component;

class Show extends Component
{
    public string $itemId;

    public function mount(string $id): void
    {
        $this->itemId = $id;
    }

    public function render()
    {
        $error = null;
        $item = null;

        try {
            $item = app(TarkovDevService::class)->item($this->itemId);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('livewire.items.show', compact('item', 'error'))
            ->title(($item['name'] ?? 'Item').' — Tarkas');
    }
}
