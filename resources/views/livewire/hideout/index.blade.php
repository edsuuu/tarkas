<div x-data="{
    open: null,
    expanded: null,
    toggle(id) {
        if (this.open === id) {
            this.open = null;
            this.$nextTick(() => {
                if (this.open === null && this.expanded === id) {
                    this.expanded = null;
                }
            });

            return;
        }

        this.open = null;
        this.$nextTick(() => {
            this.expanded = id;
            this.$nextTick(() => {
                if (this.expanded === id) {
                    this.open = id;
                }
            });
        });
    },
}">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-100">Hideout</h1>
        <p class="text-sm text-zinc-500">Estações, níveis e requisitos de upgrade</p>
    </div>

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <input type="search" wire:model.live.debounce.400ms="search" placeholder="Buscar item necessário (ex.: parafusos, GPU)…"
               class="w-full max-w-md rounded-lg border border-zinc-700 bg-[#1a1d26] px-4 py-2 text-sm text-zinc-200 placeholder-zinc-500 focus:border-amber-500 focus:outline-none">
        <select wire:model.live="level"
                class="rounded-lg border border-zinc-700 bg-[#1a1d26] px-3 py-2 text-sm text-zinc-200 focus:border-amber-500 focus:outline-none">
            <option value="0">Todos os níveis</option>
            @foreach ($levels as $lvl)
                <option value="{{ $lvl }}">Nível {{ $lvl }}</option>
            @endforeach
        </select>
        @if ($searching)
            <span class="text-sm text-zinc-500">Mostrando só os níveis que precisam de "{{ trim($search) }}"</span>
        @elseif ($level > 0)
            <span class="text-sm text-zinc-500">Mostrando o nível {{ $level }} de cada estação</span>
        @endif
        <div wire:loading.delay class="text-sm text-amber-400">Carregando…</div>
    </div>

    <x-api-error :message="$error" />

    @php
        $fmtTime = function (?int $seconds) {
            if (! $seconds) {
                return 'instantâneo';
            }
            $d = intdiv($seconds, 86400);
            $h = intdiv($seconds % 86400, 3600);
            $m = intdiv($seconds % 3600, 60);
            $parts = array_filter([$d ? "{$d}d" : null, $h ? "{$h}h" : null, $m ? "{$m}min" : null]);
            return $parts ? implode(' ', $parts) : "{$seconds}s";
        };
        $needle = mb_strtolower(trim($search));
    @endphp

    <div class="grid grid-cols-1 items-start gap-3 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($stations as $station)
            <div wire:key="station-{{ $station['id'] }}"
                 x-data="{ id: @js($station['id']) }"
                 class="min-w-0 overflow-hidden rounded-xl border bg-[#14171f]"
                 :class="({{ $filtering ? 'true' : 'expanded === id' }}) ? 'border-amber-500/30 md:col-span-2 xl:col-span-3' : 'border-zinc-800'">
                <button type="button"
                        @if (! $filtering) @click="toggle(id)" @endif
                        class="flex min-w-0 w-full items-center gap-3 p-4 text-left">
                    @if (! empty($station['imageLink']))
                        <img src="{{ $station['imageLink'] }}" alt="" class="h-10 w-10 shrink-0 object-contain" loading="lazy">
                    @endif
                    <span class="min-w-0 flex-1 break-words font-semibold"
                          :class="({{ $filtering ? 'true' : 'expanded === id' }}) ? 'text-amber-300' : 'text-zinc-100'">
                        {{ $station['name'] }}
                    </span>
                    <span class="shrink-0 text-xs text-zinc-500">{{ count($station['levels'] ?? []) }} {{ $searching ? 'nível(is) com o item' : ($filtering ? 'nível(is)' : 'níveis') }}</span>
                    <span class="text-zinc-600 transition"
                          :class="({{ $filtering ? 'true' : 'expanded === id' }}) ? 'rotate-90' : ''">▸</span>
                </button>

                <div x-cloak
                     x-show="{{ $filtering ? 'true' : 'open === id' }}"
                     class="grid grid-cols-1 gap-3 border-t border-zinc-800 p-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($station['levels'] ?? [] as $level)
                        <div wire:key="level-{{ $station['id'] }}-{{ $level['level'] }}" class="min-w-0 rounded-lg border border-zinc-800/80 bg-[#181c26] p-3">
                            <div class="mb-2 flex items-center justify-between">
                                <p class="font-semibold text-amber-300">Nível {{ $level['level'] }}</p>
                                <p class="text-xs text-zinc-500">Construção: {{ $fmtTime($level['constructionTime'] ?? 0) }}</p>
                            </div>

                            @if (! empty($level['itemRequirements']))
                                <p class="mb-1 text-xs uppercase tracking-wide text-zinc-500">Itens</p>
                                <ul class="mb-2 space-y-1">
                                    @foreach ($level['itemRequirements'] as $req)
                                        @php
                                            $price = $req['item']['lastLowPrice'] ?? $req['item']['avg24hPrice'];
                                            $isMatch = $searching && str_contains(mb_strtolower($req['item']['name'] ?? ''), $needle);
                                        @endphp
                                        <li class="flex min-w-0 items-center gap-2 rounded text-sm {{ $isMatch ? 'bg-amber-400/10 px-1 py-0.5 font-medium text-amber-300' : 'text-zinc-300' }}">
                                            @if (! empty($req['item']['iconLink']))
                                                <img src="{{ $req['item']['iconLink'] }}" alt="" class="h-10 w-10 rounded bg-zinc-900 object-contain" loading="lazy">
                                            @endif
                                            <span class="min-w-0 flex-1 break-words">
                                                {{ number_format($req['count'], 0, ',', '.') }}× {{ $req['item']['name'] }}
                                            </span>
                                            @if ($price)
                                                <x-price :value="$price * $req['count']" class="shrink-0 text-xs text-zinc-500" />
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            @php
                                $extras = collect()
                                    ->concat(collect($level['stationLevelRequirements'] ?? [])->map(fn ($r) => $r['station']['name'].' nv. '.$r['level']))
                                    ->concat(collect($level['traderRequirements'] ?? [])->map(fn ($r) => $r['trader']['name'].' LL'.$r['level']))
                                    ->concat(collect($level['skillRequirements'] ?? [])->map(fn ($r) => 'Skill '.$r['name'].' '.$r['level']));
                            @endphp
                            @if ($extras->isNotEmpty())
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($extras as $extra)
                                        <span class="rounded-full bg-zinc-800 px-2 py-0.5 text-xs text-zinc-400">{{ $extra }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    @if (! $error && count($stations) === 0 && $searching)
        <p class="py-8 text-center text-zinc-500">Nenhuma estação precisa de "{{ trim($search) }}". Dica: a busca usa os nomes em português (ex.: "parafusos", "placa de vídeo").</p>
    @elseif (! $error && count($stations) === 0 && $level > 0)
        <p class="py-8 text-center text-zinc-500">Nenhuma estação tem nível {{ $level }}.</p>
    @endif
</div>
