{{-- Detalhe de um item. Espera $item (array). Usado pela página /itens/{id} e pelo modal da listagem. --}}
<div>
    <div class="mb-6 flex flex-col gap-4 rounded-xl border border-zinc-800 bg-[#14171f] p-5 sm:flex-row sm:items-start">
        @if (! empty($item['gridImageLink']) || ! empty($item['iconLink']))
            <img src="{{ $item['gridImageLink'] ?? $item['iconLink'] }}" alt="{{ $item['name'] }}"
                 class="h-24 w-24 shrink-0 rounded-lg bg-zinc-900 object-contain p-1">
        @endif
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl font-bold text-zinc-100">{{ $item['name'] }}</h1>
            <p class="text-sm text-zinc-500">
                {{ $item['shortName'] }} · {{ $item['category']['name'] ?? '' }}
                · {{ $item['width'] }}×{{ $item['height'] }} slots · {{ number_format($item['weight'], 2, ',', '.') }} kg
            </p>
            <div class="mt-2 flex flex-wrap gap-1.5">
                @foreach ($item['types'] ?? [] as $type)
                    <span class="rounded-full bg-zinc-800 px-2 py-0.5 text-xs text-zinc-400">{{ $type }}</span>
                @endforeach
            </div>
            @if (! empty($item['description']))
                <details class="mt-3">
                    <summary class="cursor-pointer text-sm text-amber-400/80 hover:text-amber-300">Descrição</summary>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-400">{{ $item['description'] }}</p>
                </details>
            @endif
        </div>
        @if (! empty($item['wikiLink']))
            <a href="{{ $item['wikiLink'] }}" target="_blank"
               class="shrink-0 rounded-lg border border-zinc-700 px-3 py-1.5 text-sm text-zinc-300 hover:border-amber-500/50 hover:text-amber-300">
                Wiki ↗
            </a>
        @endif
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        @php
            $change = $item['changeLast48hPercent'];
            $cards = [
                ['label' => 'Preço base', 'value' => $item['basePrice']],
                ['label' => 'Última baixa (flea)', 'value' => $item['lastLowPrice']],
                ['label' => 'Média 24h (flea)', 'value' => $item['avg24hPrice']],
                ['label' => 'Mínima 24h', 'value' => $item['low24hPrice']],
                ['label' => 'Máxima 24h', 'value' => $item['high24hPrice']],
                ['label' => 'Taxa da flea', 'value' => $item['fleaMarketFee']],
            ];
        @endphp
        @foreach ($cards as $card)
            <div class="rounded-xl border border-zinc-800 bg-[#14171f] p-3">
                <p class="text-xs uppercase tracking-wide text-zinc-500">{{ $card['label'] }}</p>
                <p class="mt-1 text-lg font-semibold"><x-price :value="$card['value']" class="text-zinc-100" /></p>
                @if ($card['label'] === 'Média 24h (flea)' && $change !== null)
                    <p class="text-xs {{ $change >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                        {{ $change >= 0 ? '+' : '' }}{{ number_format($change, 2, ',', '.') }}% em 48h
                    </p>
                @endif
            </div>
        @endforeach
    </div>

    @php
        $history = collect($item['historicalPrices'] ?? [])
            ->filter(fn ($p) => $p['price'] !== null)
            ->sortBy('timestamp')
            ->values();
    @endphp
    @if ($history->count() > 1)
        @php
            $min = $history->min('price');
            $max = $history->max('price');
            $range = max($max - $min, 1);
            $n = $history->count();
            $points = $history->map(function ($p, $i) use ($min, $range, $n) {
                $x = round($i / ($n - 1) * 600, 1);
                $y = round(110 - (($p['price'] - $min) / $range) * 100, 1);
                return "{$x},{$y}";
            })->implode(' ');
        @endphp
        <div class="mb-6 rounded-xl border border-zinc-800 bg-[#14171f] p-4">
            <div class="mb-2 flex items-baseline justify-between">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-400">Histórico de preço (últimas 48h)</h2>
                <p class="text-xs text-zinc-500">
                    min <x-price :value="$min" class="text-zinc-400" /> · max <x-price :value="$max" class="text-zinc-400" />
                </p>
            </div>
            <svg viewBox="0 0 600 120" preserveAspectRatio="none" class="h-28 w-full">
                <polyline points="{{ $points }}" fill="none" stroke="#f59e0b" stroke-width="2" vector-effect="non-scaling-stroke" />
            </svg>
        </div>
    @endif

    <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded-xl border border-zinc-800 bg-[#14171f] p-4">
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-zinc-400">Onde vender</h2>
            <table class="w-full text-sm">
                <tbody>
                    @forelse (collect($item['sellFor'] ?? [])->sortByDesc('priceRUB') as $offer)
                        <tr class="border-b border-zinc-800/60 last:border-0">
                            <td class="py-2 text-zinc-300">{{ $offer['vendor']['name'] }}</td>
                            <td class="py-2 text-right">
                                <x-price :value="$offer['priceRUB']" class="font-medium text-zinc-200" />
                                @if (($offer['currency'] ?? 'RUB') !== 'RUB')
                                    <span class="block text-xs text-zinc-500">{{ number_format($offer['price'], 0, ',', '.') }} {{ $offer['currency'] }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td class="py-2 text-zinc-500">Nenhuma oferta de venda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="rounded-xl border border-zinc-800 bg-[#14171f] p-4">
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-zinc-400">Onde comprar</h2>
            <table class="w-full text-sm">
                <tbody>
                    @forelse (collect($item['buyFor'] ?? [])->sortBy('priceRUB') as $offer)
                        <tr class="border-b border-zinc-800/60 last:border-0">
                            <td class="py-2 text-zinc-300">{{ $offer['vendor']['name'] }}</td>
                            <td class="py-2 text-right">
                                <x-price :value="$offer['priceRUB']" class="font-medium text-zinc-200" />
                                @if (($offer['currency'] ?? 'RUB') !== 'RUB')
                                    <span class="block text-xs text-zinc-500">{{ number_format($offer['price'], 0, ',', '.') }} {{ $offer['currency'] }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td class="py-2 text-zinc-500">Nenhuma oferta de compra.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        @if (! empty($item['usedInTasks']))
            <div class="rounded-xl border border-zinc-800 bg-[#14171f] p-4">
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-zinc-400">Usado nas quests</h2>
                <ul class="space-y-1.5 text-sm">
                    @foreach ($item['usedInTasks'] as $task)
                        <li class="text-zinc-300">
                            {{ $task['name'] }}
                            <span class="text-zinc-500">— {{ $task['trader']['name'] ?? '' }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (! empty($item['craftsFor']) || ! empty($item['craftsUsing']) || ! empty($item['bartersFor']) || ! empty($item['bartersUsing']))
            <div class="rounded-xl border border-zinc-800 bg-[#14171f] p-4">
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-zinc-400">Crafts & Trocas</h2>
                <div class="space-y-3 text-sm">
                    @if (! empty($item['craftsFor']))
                        <div>
                            <p class="mb-1 text-xs text-zinc-500">Produzido em:</p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($item['craftsFor'] as $craft)
                                    <span class="rounded-full bg-zinc-800 px-2 py-0.5 text-xs text-zinc-300">{{ $craft['station']['name'] }} nv. {{ $craft['level'] }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @if (! empty($item['craftsUsing']))
                        <div>
                            <p class="mb-1 text-xs text-zinc-500">Insumo de crafts em:</p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($item['craftsUsing'] as $craft)
                                    <span class="rounded-full bg-zinc-800 px-2 py-0.5 text-xs text-zinc-300">{{ $craft['station']['name'] }} nv. {{ $craft['level'] }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @if (! empty($item['bartersFor']))
                        <div>
                            <p class="mb-1 text-xs text-zinc-500">Obtido por troca com:</p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($item['bartersFor'] as $barter)
                                    <span class="rounded-full bg-zinc-800 px-2 py-0.5 text-xs text-zinc-300">{{ $barter['trader']['name'] }} LL{{ $barter['level'] }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @if (! empty($item['bartersUsing']))
                        <div>
                            <p class="mb-1 text-xs text-zinc-500">Usado em trocas com:</p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($item['bartersUsing'] as $barter)
                                    <span class="rounded-full bg-zinc-800 px-2 py-0.5 text-xs text-zinc-300">{{ $barter['trader']['name'] }} LL{{ $barter['level'] }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
