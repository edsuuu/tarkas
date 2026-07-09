<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-100">Quests</h1>
        <p class="text-sm text-zinc-500">Missões, requisitos e recompensas por trader</p>
    </div>

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <input type="search" wire:model.live.debounce.400ms="search" placeholder="Buscar quest…"
               class="w-full max-w-xs rounded-lg border border-zinc-700 bg-[#1a1d26] px-4 py-2 text-sm text-zinc-200 placeholder-zinc-500 focus:border-amber-500 focus:outline-none">
        <select wire:model.live="trader"
                class="rounded-lg border border-zinc-700 bg-[#1a1d26] px-3 py-2 text-sm text-zinc-200 focus:border-amber-500 focus:outline-none">
            <option value="">Todos os traders</option>
            @foreach ($traders as $t)
                <option value="{{ $t }}">{{ $t }}</option>
            @endforeach
        </select>
        <select wire:model.live="map"
                class="rounded-lg border border-zinc-700 bg-[#1a1d26] px-3 py-2 text-sm text-zinc-200 focus:border-amber-500 focus:outline-none">
            <option value="">Todos os mapas</option>
            @foreach ($maps as $m)
                <option value="{{ $m }}">{{ $m }}</option>
            @endforeach
        </select>
        <label class="flex cursor-pointer items-center gap-2 text-sm text-zinc-300">
            <input type="checkbox" wire:model.live="kappa" class="h-4 w-4 rounded border-zinc-600 bg-[#1a1d26] accent-amber-500">
            Só Kappa
        </label>
        <label class="flex cursor-pointer items-center gap-2 text-sm text-zinc-300">
            <input type="checkbox" wire:model.live="lightkeeper" class="h-4 w-4 rounded border-zinc-600 bg-[#1a1d26] accent-amber-500">
            Só Lightkeeper
        </label>
        @auth
            <label class="flex cursor-pointer items-center gap-2 text-sm text-zinc-300">
                <input type="checkbox" wire:model.live="hideCompleted" class="h-4 w-4 rounded border-zinc-600 bg-[#1a1d26] accent-emerald-500">
                Esconder concluídas
            </label>
            <span class="rounded-full bg-emerald-400/10 px-2.5 py-0.5 text-sm font-medium text-emerald-300">✓ {{ $doneCount }} concluídas</span>
        @else
            <span class="text-sm text-zinc-500">
                <a href="{{ route('login') }}" class="text-amber-400 hover:text-amber-300">Entre</a>
                para salvar suas quests concluídas
            </span>
        @endauth
        <span class="text-sm text-zinc-500">{{ $total }} quests</span>
        <div wire:loading.delay class="text-sm text-amber-400">Carregando…</div>
    </div>

    <x-api-error :message="$error" />

    <div class="grid grid-cols-1 items-start gap-3 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($tasks as $task)
            @php $isDone = isset($completedIds[$task['id']]); @endphp
            <details wire:key="task-{{ $task['id'] }}" class="group rounded-xl border bg-[#14171f] {{ $isDone ? 'border-emerald-900/60 opacity-60' : 'border-zinc-800' }} open:border-zinc-700">
                <summary class="flex cursor-pointer items-center gap-3 p-4">
                    @auth
                        <button type="button"
                                wire:click.stop.prevent="toggleCompleted('{{ $task['id'] }}')"
                                title="{{ $isDone ? 'Desmarcar quest concluída' : 'Marcar como concluída' }}"
                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border text-sm transition {{ $isDone ? 'border-emerald-500 bg-emerald-500/20 text-emerald-300' : 'border-zinc-700 text-zinc-600 hover:border-emerald-500/60 hover:text-emerald-400' }}">
                            ✓
                        </button>
                    @endauth
                    @if (! empty($task['trader']['imageLink']))
                        <img src="{{ $task['trader']['imageLink'] }}" alt="{{ $task['trader']['name'] ?? '' }}"
                             class="h-10 w-10 shrink-0 rounded-lg object-cover" loading="lazy">
                    @endif
                    <span class="min-w-0 flex-1">
                        <span class="block truncate font-semibold text-zinc-100">{{ $task['name'] }}</span>
                        <span class="block text-xs text-zinc-500">
                            {{ $task['trader']['name'] ?? '' }}
                            @if (! empty($task['map']['name'])) · {{ $task['map']['name'] }} @endif
                            @if (! empty($task['minPlayerLevel'])) · nível {{ $task['minPlayerLevel'] }}+ @endif
                            @if (! empty($task['experience'])) · {{ number_format($task['experience'], 0, ',', '.') }} XP @endif
                        </span>
                    </span>
                    <span class="flex shrink-0 gap-1.5">
                        @if ($task['kappaRequired'])
                            <span class="rounded-full bg-amber-400/10 px-2 py-0.5 text-xs font-semibold text-amber-300" title="Necessária para o contêiner Kappa">Kappa</span>
                        @endif
                        @if ($task['lightkeeperRequired'])
                            <span class="rounded-full bg-sky-400/10 px-2 py-0.5 text-xs font-semibold text-sky-300" title="Necessária para o Lightkeeper">LK</span>
                        @endif
                    </span>
                    <span class="shrink-0 text-zinc-600 transition group-open:rotate-90">▸</span>
                </summary>
                <div class="border-t border-zinc-800 p-4 text-sm">
                    @if (! empty($task['taskRequirements']))
                        <p class="mb-3 text-zinc-400">
                            <span class="text-xs uppercase tracking-wide text-zinc-500">Requer:</span>
                            {{ collect($task['taskRequirements'])->pluck('task.name')->filter()->implode(', ') }}
                        </p>
                    @endif

                    @if (! empty($task['objectives']))
                        <p class="mb-1 text-xs uppercase tracking-wide text-zinc-500">Objetivos</p>
                        <ul class="mb-3 list-inside list-disc space-y-1 text-zinc-300">
                            @foreach ($task['objectives'] as $obj)
                                <li>
                                    {{ $obj['description'] }}
                                    @php $objMaps = collect($obj['maps'] ?? [])->pluck('name')->filter(); @endphp
                                    @if ($objMaps->isNotEmpty())
                                        <span class="text-xs text-zinc-500">({{ $objMaps->implode(', ') }})</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @php
                        $rewardItems = $task['finishRewards']['items'] ?? [];
                        $standings = $task['finishRewards']['traderStanding'] ?? [];
                        $unlocks = $task['finishRewards']['offerUnlock'] ?? [];
                    @endphp
                    @if ($rewardItems || $standings || $unlocks)
                        <p class="mb-1 text-xs uppercase tracking-wide text-zinc-500">Recompensas</p>
                        <div class="flex flex-wrap items-center gap-2">
                            @foreach ($standings as $st)
                                <span class="rounded-full bg-emerald-400/10 px-2 py-0.5 text-xs text-emerald-300">
                                    {{ $st['trader']['name'] }} {{ $st['standing'] >= 0 ? '+' : '' }}{{ $st['standing'] }} rep
                                </span>
                            @endforeach
                            @foreach ($rewardItems as $reward)
                                <span class="flex items-center gap-1 rounded-full bg-zinc-800 px-2 py-0.5 text-xs text-zinc-300">
                                    @if (! empty($reward['item']['iconLink']))
                                        <img src="{{ $reward['item']['iconLink'] }}" alt="" class="h-4 w-4 object-contain" loading="lazy">
                                    @endif
                                    {{ $reward['count'] > 1 ? number_format($reward['count'], 0, ',', '.').'× ' : '' }}{{ $reward['item']['name'] }}
                                </span>
                            @endforeach
                            @if ($unlocks)
                                <span class="rounded-full bg-sky-400/10 px-2 py-0.5 text-xs text-sky-300">
                                    Desbloqueia {{ count($unlocks) }} oferta(s)
                                </span>
                            @endif
                        </div>
                    @endif

                    @if (! empty($task['wikiLink']))
                        <a href="{{ $task['wikiLink'] }}" target="_blank" class="mt-3 inline-block text-xs text-amber-400/80 hover:text-amber-300">Ver na wiki ↗</a>
                    @endif
                </div>
            </details>
        @endforeach
    </div>

    @if (! $error && $tasks->isEmpty())
        <p class="py-8 text-center text-zinc-500">Nenhuma quest encontrada com esses filtros.</p>
    @endif

    @if ($total > $shown)
        <div class="mt-4 text-center">
            <button wire:click="loadMore" class="rounded-lg border border-zinc-700 bg-[#1a1d26] px-4 py-2 text-sm text-zinc-300 hover:border-amber-500/50 hover:text-amber-300">
                Carregar mais ({{ $total - $shown }} restantes)
            </button>
        </div>
    @endif
</div>
