<div>
    <a href="{{ route('items.index') }}" wire:navigate class="mb-4 inline-flex items-center gap-1 text-sm text-zinc-400 hover:text-amber-300">
        ← Voltar para itens
    </a>

    <x-api-error :message="$error" />

    @if ($item)
        @include('livewire.items.partials.detail', ['item' => $item])
    @elseif (! $error)
        <p class="text-zinc-500">Item não encontrado.</p>
    @endif
</div>
