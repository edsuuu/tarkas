<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-100">Mapas</h1>
        <p class="text-sm text-zinc-500">Chefes, extrações e duração das raids</p>
    </div>

    <x-api-error :message="$error" />

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <input type="search" wire:model.live.debounce.400ms="search" placeholder="Buscar mapa, chefe, extração ou chave…"
               class="w-full max-w-xs rounded-lg border border-zinc-700 bg-[#1a1d26] px-4 py-2 text-sm text-zinc-200 placeholder-zinc-500 focus:border-amber-500 focus:outline-none">
        <select wire:model.live="boss"
                class="rounded-lg border border-zinc-700 bg-[#1a1d26] px-3 py-2 text-sm text-zinc-200 focus:border-amber-500 focus:outline-none">
            <option value="">Todos os chefes</option>
            @foreach ($bosses as $b)
                <option value="{{ $b }}">{{ $b }}</option>
            @endforeach
        </select>
        <select wire:model.live="faction"
                class="rounded-lg border border-zinc-700 bg-[#1a1d26] px-3 py-2 text-sm text-zinc-200 focus:border-amber-500 focus:outline-none">
            <option value="">Todas as extrações</option>
            <option value="pmc">PMC</option>
            <option value="scav">Scav</option>
            <option value="shared">Compartilhadas</option>
        </select>
        <span class="text-sm text-zinc-500">{{ $total }} mapas</span>
        <div wire:loading.delay class="text-sm text-amber-400">Carregando…</div>
    </div>

    <div class="grid grid-cols-1 items-start gap-3 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($maps as $map)
            <div class="min-w-0 overflow-hidden rounded-xl border border-zinc-800 bg-[#14171f]">
                @if (! empty($map['image']))
                    <a href="{{ $map['interactiveUrl'] ?? $map['image'] }}" target="_blank"
                       class="group relative block aspect-video overflow-hidden border-b border-zinc-800 bg-[#0c0e12]">
                        <img src="{{ $map['image'] }}" alt="Mapa de {{ $map['name'] }}"
                             class="h-full w-full object-contain transition group-hover:scale-105" loading="lazy">
                        <span class="absolute bottom-2 right-2 rounded bg-black/70 px-2 py-0.5 text-xs text-amber-300 opacity-0 transition group-hover:opacity-100">Abrir mapa interativo ↗</span>
                    </a>
                @endif

                <div class="p-4">
                <div class="mb-3 flex min-w-0 items-start justify-between gap-3">
                    <h2 class="min-w-0 break-words text-lg font-bold leading-snug text-zinc-100">{{ $map['name'] }}</h2>
                    @if (! empty($map['wiki']))
                        <a href="{{ $map['wiki'] }}" target="_blank" class="shrink-0 text-xs text-amber-400/80 hover:text-amber-300">Wiki ↗</a>
                    @endif
                </div>

                <div class="mb-3 grid grid-cols-2 gap-1.5 text-xs">
                    @if (! empty($map['players']))
                        <span class="min-w-0 rounded bg-zinc-800 px-2 py-1 text-zinc-300">👥 {{ $map['players'] }}</span>
                    @endif
                    @if (! empty($map['raidDuration']))
                        <span class="min-w-0 rounded bg-zinc-800 px-2 py-1 text-zinc-300">⏱ {{ $map['raidDuration'] }} min</span>
                    @endif
                    <span class="min-w-0 rounded bg-zinc-800 px-2 py-1 text-zinc-300">🚪 {{ count($map['extracts'] ?? []) }} extrações</span>
                </div>

                @if (! empty($map['description']))
                    <details class="mb-3">
                        <summary class="cursor-pointer text-xs text-amber-400/80 hover:text-amber-300">Descrição</summary>
                        <p class="mt-1 text-sm leading-relaxed text-zinc-400">{{ $map['description'] }}</p>
                    </details>
                @endif

                @if (! empty($map['bosses']))
                    <p class="mb-1 text-xs uppercase tracking-wide text-zinc-500">Chefes</p>
                    <ul class="mb-3 space-y-1">
                        @foreach (collect($map['bosses'])->unique(fn ($b) => $b['boss']['name'])->values() as $boss)
                            <li class="flex items-center gap-2 text-sm text-zinc-300">
                                @if (! empty($boss['boss']['imagePortraitLink']))
                                    <img src="{{ $boss['boss']['imagePortraitLink'] }}" alt="" class="h-7 w-7 rounded object-cover" loading="lazy">
                                @endif
                                <span class="flex-1">{{ $boss['boss']['name'] }}</span>
                                @if ($boss['spawnChance'] !== null)
                                    <span class="text-xs text-amber-400/90">{{ number_format($boss['spawnChance'] * 100, 0) }}% spawn</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if (! empty($map['extracts']))
                    <details>
                        <summary class="cursor-pointer text-xs text-amber-400/80 hover:text-amber-300">Extrações ({{ count($map['extracts']) }})</summary>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            @foreach ($map['extracts'] as $extract)
                                @php
                                    $faction = $extract['faction'] ?? 'shared';
                                    $factionClass = match ($faction) {
                                        'pmc' => 'bg-sky-400/10 text-sky-300',
                                        'scav' => 'bg-orange-400/10 text-orange-300',
                                        default => 'bg-zinc-800 text-zinc-300',
                                    };
                                @endphp
                                <span class="rounded-full px-2 py-0.5 text-xs {{ $factionClass }}" title="{{ $faction }}">
                                    {{ $extract['name'] }}
                                </span>
                            @endforeach
                        </div>
                    </details>
                @endif

                @if (! empty($map['accessKeys']))
                    <p class="mt-3 text-xs text-zinc-500">
                        🔑 Chaves: {{ collect($map['accessKeys'])->pluck('name')->implode(', ') }}
                        @if (! empty($map['accessKeysMinPlayerLevel']))
                            (a partir do nível {{ $map['accessKeysMinPlayerLevel'] }})
                        @endif
                    </p>
                @endif
                </div>
            </div>
        @endforeach
    </div>

    @if (! $error && $maps->isEmpty())
        <p class="py-8 text-center text-zinc-500">Nenhum mapa encontrado com esses filtros.</p>
    @endif
</div>
