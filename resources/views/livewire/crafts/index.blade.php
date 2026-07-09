<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-100">Crafts do Hideout</h1>
        <p class="text-sm text-zinc-500">Lucro estimado por produção: valor de flea do resultado menos custo dos insumos</p>
    </div>

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <input type="search" wire:model.live.debounce.400ms="search" placeholder="Buscar item produzido ou insumo…"
               class="w-full max-w-xs rounded-lg border border-zinc-700 bg-[#1a1d26] px-4 py-2 text-sm text-zinc-200 placeholder-zinc-500 focus:border-amber-500 focus:outline-none">
        <select wire:model.live="station"
                class="rounded-lg border border-zinc-700 bg-[#1a1d26] px-3 py-2 text-sm text-zinc-200 focus:border-amber-500 focus:outline-none">
            <option value="">Todas as estações</option>
            @foreach ($stations as $s)
                <option value="{{ $s }}">{{ $s }}</option>
            @endforeach
        </select>
        <label class="flex cursor-pointer items-center gap-2 text-sm text-zinc-300">
            <input type="checkbox" wire:model.live="profitable" class="h-4 w-4 rounded border-zinc-600 bg-[#1a1d26] accent-amber-500">
            Só lucrativos
        </label>
        <span class="text-sm text-zinc-500">{{ $total }} crafts, ordenados por lucro</span>
        <div wire:loading.delay class="text-sm text-amber-400">Carregando…</div>
    </div>

    <x-api-error :message="$error" />

    @php
        $fmtTime = function (?int $seconds) {
            if (! $seconds) {
                return '—';
            }
            $h = intdiv($seconds, 3600);
            $m = intdiv($seconds % 3600, 60);
            return trim(($h ? "{$h}h " : '').($m ? "{$m}min" : ($h ? '' : "{$seconds}s")));
        };
    @endphp

    <div class="columns-1 gap-3 md:columns-2 xl:columns-3">
        @foreach ($crafts as $craft)
            <div wire:key="craft-{{ $craft['id'] }}" class="mb-3 break-inside-avoid rounded-xl border border-zinc-800 bg-[#14171f] p-4">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="rounded-full bg-zinc-800 px-2.5 py-0.5 text-xs font-semibold text-zinc-300">
                            {{ $craft['station']['name'] }} nv. {{ $craft['level'] }}
                        </span>
                        <span class="rounded-full bg-zinc-800 px-2.5 py-0.5 text-xs text-zinc-400">
                            ⏱ {{ $fmtTime($craft['duration']) }}
                        </span>
                    </div>
                    <div class="flex items-center gap-4 text-sm">
                        <span class="text-zinc-500">Custo: <x-price :value="$craft['cost']" class="text-zinc-300" /></span>
                        <span class="font-semibold {{ $craft['profit'] >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $craft['profit'] >= 0 ? '+' : '' }}<x-price :value="$craft['profit']" />
                            @unless ($craft['complete'])
                                <span class="text-zinc-500" title="Alguns itens não têm preço de flea — estimativa parcial">*</span>
                            @endunless
                        </span>
                        @if ($craft['profitPerHour'] !== null)
                            <span class="text-xs text-zinc-500">
                                (<x-price :value="$craft['profitPerHour']" class="{{ $craft['profitPerHour'] >= 0 ? 'text-emerald-500' : 'text-red-500' }}" />/h)
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach ($craft['requiredItems'] as $req)
                            <a href="{{ route('items.show', $req['item']['id']) }}" wire:navigate
                               class="flex items-center gap-1.5 rounded-lg border border-zinc-800 bg-[#181c26] px-2 py-1 text-xs text-zinc-300 hover:border-amber-500/40"
                               title="{{ $req['item']['name'] }}">
                                @if (! empty($req['item']['iconLink']))
                                    <img src="{{ $req['item']['iconLink'] }}" alt="" class="h-6 w-6 object-contain" loading="lazy">
                                @endif
                                {{ $req['count'] }}× {{ $req['item']['name'] }}
                            </a>
                        @endforeach
                    </div>
                    <span class="text-lg text-amber-400">→</span>
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach ($craft['rewardItems'] as $reward)
                            <a href="{{ route('items.show', $reward['item']['id']) }}" wire:navigate
                               class="flex items-center gap-1.5 rounded-lg border border-amber-500/30 bg-amber-400/5 px-2 py-1 text-xs text-amber-200 hover:border-amber-500/60"
                               title="{{ $reward['item']['name'] }}">
                                @if (! empty($reward['item']['iconLink']))
                                    <img src="{{ $reward['item']['iconLink'] }}" alt="" class="h-6 w-6 object-contain" loading="lazy">
                                @endif
                                {{ $reward['count'] }}× {{ $reward['item']['name'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if (! $error && $crafts->isEmpty())
        <p class="py-8 text-center text-zinc-500">Nenhum craft encontrado.</p>
    @endif

    @if ($total > $shown)
        <div class="mt-4 text-center">
            <button wire:click="loadMore" class="rounded-lg border border-zinc-700 bg-[#1a1d26] px-4 py-2 text-sm text-zinc-300 hover:border-amber-500/50 hover:text-amber-300">
                Carregar mais ({{ $total - $shown }} restantes)
            </button>
        </div>
    @endif
</div>
