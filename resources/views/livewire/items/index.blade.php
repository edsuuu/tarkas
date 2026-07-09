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
                        <tr class="border-b border-zinc-800/60 transition hover:bg-zinc-800/30">
                            <td class="px-4 py-2">
                                <a href="{{ route('items.show', $item['id']) }}" wire:navigate class="flex items-center gap-3">
                                    @if (! empty($item['iconLink']))
                                        <img src="{{ $item['iconLink'] }}" alt="" class="h-10 w-10 rounded bg-zinc-900 object-contain" loading="lazy">
                                    @endif
                                    <span>
                                        <span class="block font-medium text-zinc-200 hover:text-amber-300">{{ $item['name'] }}</span>
                                        <span class="block text-xs text-zinc-500">{{ $item['shortName'] }}</span>
                                    </span>
                                </a>
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
</div>
