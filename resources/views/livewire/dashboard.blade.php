<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-100">Dashboard</h1>
            <p class="text-sm text-zinc-500">Visão geral do Escape from Tarkov — dados de tarkov.dev</p>
        </div>
        <div wire:loading.delay class="text-sm text-amber-400">Carregando…</div>
    </div>

    <x-api-error :message="$error" />

    @if ($status)
        @php
            $general = $status['generalStatus'] ?? null;
            $ok = ($general['status'] ?? 1) === 0;
        @endphp

        <div class="mb-6 rounded-xl border p-4 {{ $ok ? 'border-emerald-900 bg-emerald-950/30' : 'border-red-900 bg-red-950/30' }}">
            <div class="flex items-center gap-3">
                <span class="h-3 w-3 shrink-0 animate-pulse rounded-full {{ $ok ? 'bg-emerald-400' : 'bg-red-400' }}"></span>
                <div>
                    <p class="font-semibold {{ $ok ? 'text-emerald-300' : 'text-red-300' }}">
                        Status geral do jogo: {{ $ok ? 'operacional' : 'com problemas' }}
                    </p>
                    @if (! empty($general['message']))
                        <p class="text-sm text-zinc-400">{{ $general['message'] }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ($status['currentStatuses'] ?? [] as $svc)
                @php $svcOk = ($svc['status'] ?? 1) === 0; @endphp
                <div class="rounded-xl border border-zinc-800 bg-[#14171f] p-3">
                    <div class="flex items-center justify-between gap-2">
                        <span class="truncate text-sm text-zinc-300">{{ $svc['name'] }}</span>
                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $svcOk ? 'bg-emerald-400/10 text-emerald-300' : 'bg-red-400/10 text-red-300' }}">
                            {{ $svc['statusCode'] ?? ($svcOk ? 'OK' : 'Problema') }}
                        </span>
                    </div>
                    @if (! empty($svc['message']))
                        <p class="mt-1 truncate text-xs text-zinc-500" title="{{ $svc['message'] }}">{{ $svc['message'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        @php $serverMessages = collect($status['messages'] ?? [])->filter(fn ($m) => ! empty($m['content']))->take(5); @endphp
        @if ($serverMessages->isNotEmpty())
            <div class="mb-6 rounded-xl border border-zinc-800 bg-[#14171f] p-4">
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-zinc-400">Avisos recentes do servidor</h2>
                <ul class="space-y-2">
                    @foreach ($serverMessages as $msg)
                        <li class="text-sm text-zinc-300">
                            <span class="text-zinc-500">{{ \Carbon\Carbon::parse($msg['time'])->diffForHumans() }}:</span>
                            {{ $msg['content'] }}
                            @if (! empty($msg['solveTime']))
                                <span class="text-emerald-400">(resolvido)</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endif

    @if ($traders)
        <h2 class="mb-3 text-lg font-bold text-zinc-100">Reset de estoque dos traders</h2>
        <div class="mb-8 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-8">
            @foreach ($traders as $trader)
                <div class="rounded-xl border border-zinc-800 bg-[#14171f] p-3 text-center">
                    @if (! empty($trader['imageLink']))
                        <img src="{{ $trader['imageLink'] }}" alt="{{ $trader['name'] }}" class="mx-auto mb-2 h-14 w-14 rounded-lg object-cover" loading="lazy">
                    @endif
                    <p class="truncate text-sm font-semibold text-zinc-200">{{ $trader['name'] }}</p>
                    <p class="text-xs text-amber-400/80">
                        @if (! empty($trader['resetTime']))
                            {{ \Carbon\Carbon::parse($trader['resetTime'])->diffForHumans(short: true) }}
                        @else
                            —
                        @endif
                    </p>
                </div>
            @endforeach
        </div>
    @endif

    <h2 class="mb-3 text-lg font-bold text-zinc-100">Ferramentas</h2>
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['route' => 'items.index',   'icon' => '📦', 'title' => 'Itens & Flea Market', 'desc' => 'Preços da flea, venda a traders, busca de itens'],
            ['route' => 'ammo.index',    'icon' => '🔫', 'title' => 'Munição',             'desc' => 'Penetração, dano e velocidade por calibre'],
            ['route' => 'tasks.index',   'icon' => '📜', 'title' => 'Quests',              'desc' => 'Missões por trader, requisitos e recompensas'],
            ['route' => 'hideout.index', 'icon' => '🏠', 'title' => 'Hideout',             'desc' => 'Estações, upgrades e itens necessários'],
            ['route' => 'traders.index', 'icon' => '🤝', 'title' => 'Traders',             'desc' => 'Níveis de lealdade e reset de estoque'],
            ['route' => 'barters.index', 'icon' => '🔄', 'title' => 'Trocas (Barters)',    'desc' => 'Lucro estimado de cada troca com traders'],
            ['route' => 'crafts.index',  'icon' => '⚗️', 'title' => 'Crafts',              'desc' => 'Lucro por hora das produções do hideout'],
            ['route' => 'maps.index',    'icon' => '🗺️', 'title' => 'Mapas',               'desc' => 'Chefes, extrações e duração das raids'],
        ] as $card)
            <a href="{{ route($card['route']) }}" wire:navigate
               class="group rounded-xl border border-zinc-800 bg-[#14171f] p-4 transition hover:border-amber-500/40 hover:bg-[#181c26]">
                <div class="mb-2 text-2xl">{{ $card['icon'] }}</div>
                <p class="font-semibold text-zinc-100 group-hover:text-amber-300">{{ $card['title'] }}</p>
                <p class="mt-1 text-sm text-zinc-500">{{ $card['desc'] }}</p>
            </a>
        @endforeach
    </div>
</div>
