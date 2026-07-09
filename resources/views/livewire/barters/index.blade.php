<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-100">Trocas (Barters)</h1>
        <p class="text-sm text-zinc-500">Lucro estimado: valor de flea dos itens recebidos menos o custo de comprar os exigidos na flea</p>
    </div>

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <input type="search" wire:model.live.debounce.400ms="search" placeholder="Buscar item, recompensa ou quest…"
               class="w-full max-w-xs rounded-lg border border-zinc-700 bg-[#1a1d26] px-4 py-2 text-sm text-zinc-200 placeholder-zinc-500 focus:border-amber-500 focus:outline-none">
        <select wire:model.live="trader"
                class="rounded-lg border border-zinc-700 bg-[#1a1d26] px-3 py-2 text-sm text-zinc-200 focus:border-amber-500 focus:outline-none">
            <option value="">Todos os traders</option>
            @foreach ($traders as $t)
                <option value="{{ $t }}">{{ $t }}</option>
            @endforeach
        </select>
        <label class="flex cursor-pointer items-center gap-2 text-sm text-zinc-300">
            <input type="checkbox" wire:model.live="profitable" class="h-4 w-4 rounded border-zinc-600 bg-[#1a1d26] accent-amber-500">
            Só lucrativas
        </label>
        <span class="text-sm text-zinc-500">{{ $total }} trocas, ordenadas por lucro</span>
        <div wire:loading.delay class="text-sm text-amber-400">Carregando…</div>
    </div>

    <x-api-error :message="$error" />

    <div class="space-y-3">
        @foreach ($barters as $barter)
            <div class="rounded-xl border border-zinc-800 bg-[#14171f] p-4">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="rounded-full bg-zinc-800 px-2.5 py-0.5 text-xs font-semibold text-zinc-300">
                            {{ $barter['trader']['name'] }} LL{{ $barter['level'] }}
                        </span>
                        @if (! empty($barter['taskUnlock']['name']))
                            <span class="rounded-full bg-sky-400/10 px-2.5 py-0.5 text-xs text-sky-300" title="Precisa completar a quest">
                                🔒 {{ $barter['taskUnlock']['name'] }}
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-4 text-sm">
                        <span class="text-zinc-500">Custo: <x-price :value="$barter['cost']" class="text-zinc-300" /></span>
                        <span class="text-zinc-500">Valor: <x-price :value="$barter['value']" class="text-zinc-300" /></span>
                        <span class="font-semibold {{ $barter['profit'] >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $barter['profit'] >= 0 ? '+' : '' }}<x-price :value="$barter['profit']" />
                            @unless ($barter['complete'])
                                <span class="text-zinc-500" title="Alguns itens não têm preço de flea — estimativa parcial">*</span>
                            @endunless
                        </span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach ($barter['requiredItems'] as $req)
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
                        @foreach ($barter['rewardItems'] as $reward)
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

        @if (! $error && $barters->isEmpty())
            <p class="py-8 text-center text-zinc-500">Nenhuma troca encontrada.</p>
        @endif
    </div>

    @if ($total > $shown)
        <div class="mt-4 text-center">
            <button wire:click="loadMore" class="rounded-lg border border-zinc-700 bg-[#1a1d26] px-4 py-2 text-sm text-zinc-300 hover:border-amber-500/50 hover:text-amber-300">
                Carregar mais ({{ $total - $shown }} restantes)
            </button>
        </div>
    @endif
</div>
