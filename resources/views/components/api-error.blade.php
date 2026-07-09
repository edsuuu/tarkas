@props(['message' => null])
@if ($message)
    <div class="rounded-xl border border-red-900 bg-red-950/40 p-4 text-red-300">
        <p class="font-semibold">Falha ao consultar a API tarkov.dev</p>
        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
        <button wire:click="$refresh" class="mt-3 rounded-md bg-red-900/80 px-3 py-1.5 text-sm text-red-100 hover:bg-red-800">
            Tentar novamente
        </button>
    </div>
@endif
