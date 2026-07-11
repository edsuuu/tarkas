<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-100">Itens & Flea Market</h1>
        <p class="text-sm text-zinc-500">Preços da flea market e melhor venda a traders</p>
    </div>

    <div class="mb-4 flex items-center gap-3">
        <input type="search" wire:model.live.debounce.400ms="search" placeholder="Buscar item (ex.: Salewa, GPU, colete)…"
               class="w-full max-w-md rounded-lg border border-zinc-700 bg-[#1a1d26] px-4 py-2 text-sm text-zinc-200 placeholder-zinc-500 focus:border-amber-500 focus:outline-none">
        <div wire:loading.delay class="text-sm text-amber-400">Buscando…</div>
    </div>

    <x-api-error :message="$error" />

    @if (! $error)
        <div class="overflow-x-auto rounded-xl border border-zinc-800 bg-[#14171f]">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-800 text-left text-xs uppercase tracking-wide text-zinc-500">
                        <th class="px-4 py-3">Item</th>
                        <th class="px-4 py-3">Categoria</th>
                        <th class="px-4 py-3 text-right">Flea (média 24h)</th>
                        <th class="px-4 py-3 text-right">Variação 48h</th>
                        <th class="px-4 py-3 text-right">Melhor venda a trader</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        @php
                            $bestTrader = collect($item['sellFor'] ?? [])
                                ->filter(fn ($s) => ($s['vendor']['normalizedName'] ?? '') !== 'flea-market')
                                ->sortByDesc('priceRUB')
                                ->first();
                            $change = $item['changeLast48hPercent'];
                        @endphp
                        <tr wire:key="item-{{ $item['id'] }}"
                            wire:click="openItem('{{ $item['id'] }}')"
                            class="group cursor-pointer border-b border-zinc-800/60 transition hover:bg-zinc-800/30">
                            <td class="px-4 py-2">
                                <div class="flex items-center gap-3">
                                    @if (! empty($item['iconLink']))
                                        <img src="{{ $item['iconLink'] }}" alt="" class="h-10 w-10 rounded bg-zinc-900 object-contain" loading="lazy">
                                    @endif
                                    <span>
                                        <span class="block font-medium text-zinc-200 group-hover:text-amber-300">{{ $item['name'] }}</span>
                                        <span class="block text-xs text-zinc-500">{{ $item['shortName'] }}</span>
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-2 text-zinc-400">{{ $item['category']['name'] ?? '—' }}</td>
                            <td class="px-4 py-2 text-right"><x-price :value="$item['avg24hPrice']" class="text-zinc-200" /></td>
                            <td class="px-4 py-2 text-right">
                                @if ($change === null)
                                    <span class="text-zinc-600">—</span>
                                @else
                                    <span class="{{ $change >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                        {{ $change >= 0 ? '+' : '' }}{{ number_format($change, 2, ',', '.') }}%
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-right">
                                @if ($bestTrader)
                                    <x-price :value="$bestTrader['priceRUB']" class="text-zinc-200" />
                                    <span class="block text-xs text-zinc-500">{{ $bestTrader['vendor']['name'] }}</span>
                                @else
                                    <span class="text-zinc-600">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-zinc-500">Nenhum item encontrado para "{{ $search }}".</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if (count($items) >= $limit)
            <div class="mt-4 text-center">
                <button wire:click="loadMore" class="rounded-lg border border-zinc-700 bg-[#1a1d26] px-4 py-2 text-sm text-zinc-300 hover:border-amber-500/50 hover:text-amber-300">
                    Carregar mais
                    <span wire:loading.delay wire:target="loadMore">…</span>
                </button>
            </div>
        @endif
    @endif

    {{-- Modal de detalhe do item (abre ao clicar numa linha, sem trocar de página) --}}
    @if ($selectedItem)
        <div class="fixed inset-0 z-[100] overflow-y-auto"
             x-data
             x-on:keydown.escape.window="$wire.closeItem()">
            <div class="fixed inset-0 bg-black/70" wire:click="closeItem"></div>
            <div class="relative flex min-h-full items-start justify-center p-4 sm:p-6">
                <div class="relative w-full max-w-4xl rounded-2xl border border-zinc-800 bg-[#0f1218] shadow-2xl">
                    <div class="sticky top-0 z-10 flex items-center justify-between gap-3 rounded-t-2xl border-b border-zinc-800 bg-[#0f1218]/95 px-5 py-3 backdrop-blur">
                        <span class="truncate text-sm font-semibold text-zinc-300">{{ $selectedItem['name'] }}</span>
                        <button type="button" wire:click="closeItem" aria-label="Fechar"
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-zinc-700 text-zinc-400 transition hover:border-amber-500/60 hover:text-amber-300">✕</button>
                    </div>
                    <div class="max-h-[80vh] overflow-y-auto px-5 pb-5 pt-4">
                        @include('livewire.items.partials.detail', ['item' => $selectedItem])
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Overlay de carregamento ao abrir um item --}}
    <div wire:loading.delay.flex wire:target="openItem"
         class="fixed inset-0 z-[110] hidden items-center justify-center bg-black/50 text-sm text-amber-300">
        Carregando item…
    </div>
</div>
