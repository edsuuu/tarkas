<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-100">Mapas</h1>
        <p class="text-sm text-zinc-500">Chefes, extrações e duração das raids</p>
    </div>

    <x-api-error :message="$error" />

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @foreach ($maps as $map)
            <div class="rounded-xl border border-zinc-800 bg-[#14171f] p-5">
                <div class="mb-2 flex items-start justify-between gap-2">
                    <h2 class="text-lg font-bold text-zinc-100">{{ $map['name'] }}</h2>
                    @if (! empty($map['wiki']))
                        <a href="{{ $map['wiki'] }}" target="_blank" class="shrink-0 text-xs text-amber-400/80 hover:text-amber-300">Wiki ↗</a>
                    @endif
                </div>

                <div class="mb-3 flex flex-wrap gap-1.5 text-xs">
                    @if (! empty($map['players']))
                        <span class="rounded-full bg-zinc-800 px-2 py-0.5 text-zinc-300">👥 {{ $map['players'] }} jogadores</span>
                    @endif
                    @if (! empty($map['raidDuration']))
                        <span class="rounded-full bg-zinc-800 px-2 py-0.5 text-zinc-300">⏱ {{ $map['raidDuration'] }} min</span>
                    @endif
                    <span class="rounded-full bg-zinc-800 px-2 py-0.5 text-zinc-300">🚪 {{ count($map['extracts'] ?? []) }} extrações</span>
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
        @endforeach
    </div>
</div>
