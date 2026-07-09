<div>
    @php
        $tierMeta = [
            6 => ['text' => 'text-purple-300', 'chip' => 'border-purple-500/50 text-purple-300', 'active' => 'bg-purple-400/15 border-purple-400', 'desc' => 'perfura classe 6 (pen 50+)'],
            5 => ['text' => 'text-red-400', 'chip' => 'border-red-500/50 text-red-400', 'active' => 'bg-red-400/15 border-red-400', 'desc' => 'perfura classe 5 (pen 40–49)'],
            4 => ['text' => 'text-orange-400', 'chip' => 'border-orange-500/50 text-orange-400', 'active' => 'bg-orange-400/15 border-orange-400', 'desc' => 'perfura classe 4 (pen 30–39)'],
            3 => ['text' => 'text-yellow-400', 'chip' => 'border-yellow-500/50 text-yellow-400', 'active' => 'bg-yellow-400/15 border-yellow-400', 'desc' => 'perfura classe 3 (pen 20–29)'],
            2 => ['text' => 'text-lime-400', 'chip' => 'border-lime-500/50 text-lime-400', 'active' => 'bg-lime-400/15 border-lime-400', 'desc' => 'perfura classe 2 (pen 10–19)'],
            1 => ['text' => 'text-zinc-400', 'chip' => 'border-zinc-600 text-zinc-400', 'active' => 'bg-zinc-400/15 border-zinc-400', 'desc' => 'só sem armadura (pen < 10)'],
        ];
    @endphp

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-100">Munição</h1>
        <p class="text-sm text-zinc-500">Penetração, dano e balística por calibre, separadas por tier de armadura</p>
    </div>

    <div class="mb-3 flex flex-wrap items-center gap-2">
        <span class="text-xs uppercase tracking-wide text-zinc-500">Tier:</span>
        <button wire:click="$set('tier', '')"
                class="rounded-full border px-3 py-1 text-xs font-semibold transition {{ $tier === '' ? 'border-amber-400 bg-amber-400/15 text-amber-300' : 'border-zinc-700 text-zinc-400 hover:border-zinc-500' }}">
            Todos
        </button>
        @foreach ([6, 5, 4, 3, 2, 1] as $t)
            <button wire:click="$set('tier', '{{ $t }}')" title="{{ $tierMeta[$t]['desc'] }}"
                    class="rounded-full border px-3 py-1 text-xs font-semibold transition {{ $tier === (string) $t ? $tierMeta[$t]['active'].' '.$tierMeta[$t]['text'] : $tierMeta[$t]['chip'].' opacity-70 hover:opacity-100' }}">
                Classe {{ $t }}
            </button>
        @endforeach
    </div>

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <select wire:model.live="caliber"
                class="rounded-lg border border-zinc-700 bg-[#1a1d26] px-3 py-2 text-sm text-zinc-200 focus:border-amber-500 focus:outline-none">
            <option value="">Todos os calibres</option>
            @foreach ($calibers as $cal)
                <option value="{{ $cal }}">{{ str_replace('Caliber', '', $cal) }}</option>
            @endforeach
        </select>
        <input type="search" wire:model.live.debounce.400ms="search" placeholder="Buscar munição…"
               class="w-full max-w-xs rounded-lg border border-zinc-700 bg-[#1a1d26] px-4 py-2 text-sm text-zinc-200 placeholder-zinc-500 focus:border-amber-500 focus:outline-none">
        <span class="text-sm text-zinc-500">{{ $rows->count() }} munições</span>
        <div wire:loading.delay class="text-sm text-amber-400">Carregando…</div>
    </div>

    <x-api-error :message="$error" />

    @if (! $error)
        <div class="overflow-x-auto rounded-xl border border-zinc-800 bg-[#14171f]">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-800 text-left text-xs uppercase tracking-wide text-zinc-500">
                        <th class="px-4 py-3">Munição</th>
                        <th class="px-4 py-3">Calibre</th>
                        <th class="px-4 py-3">Tier</th>
                        @foreach ([
                            'damage' => 'Dano',
                            'penetrationPower' => 'Penetração',
                            'armorDamage' => 'Dano armadura',
                            'fragmentationChance' => 'Frag.',
                            'initialSpeed' => 'Velocidade',
                        ] as $field => $label)
                            <th class="cursor-pointer select-none px-4 py-3 text-right hover:text-amber-300" wire:click="sort('{{ $field }}')">
                                {{ $label }}
                                @if ($sortBy === $field)
                                    <span class="text-amber-400">{{ $sortDir === 'desc' ? '↓' : '↑' }}</span>
                                @endif
                            </th>
                        @endforeach
                        <th class="px-4 py-3 text-center">Tracer</th>
                        <th class="px-4 py-3 text-right">Flea (24h)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $prevTier = null; @endphp
                    @forelse ($rows as $ammo)
                        @php
                            $pen = $ammo['penetrationPower'] ?? 0;
                            $rowTier = \App\Livewire\Ammo\Index::tierOf($pen);
                            $meta = $tierMeta[$rowTier];
                            $showDivider = $sortBy === 'penetrationPower' && $rowTier !== $prevTier;
                            $prevTier = $rowTier;
                            $dmg = ($ammo['damage'] ?? 0) * max($ammo['projectileCount'] ?? 1, 1);
                        @endphp

                        @if ($showDivider)
                            <tr class="bg-zinc-900/70">
                                <td colspan="10" class="px-4 py-1.5 text-xs font-bold uppercase tracking-wider {{ $meta['text'] }}">
                                    Classe {{ $rowTier }} — {{ $meta['desc'] }}
                                </td>
                            </tr>
                        @endif

                        <tr class="border-b border-zinc-800/60 transition hover:bg-zinc-800/30">
                            <td class="px-4 py-2">
                                <a href="{{ route('items.show', $ammo['item']['id']) }}" wire:navigate class="flex items-center gap-2">
                                    @if (! empty($ammo['item']['iconLink']))
                                        <img src="{{ $ammo['item']['iconLink'] }}" alt="" class="h-8 w-8 rounded bg-zinc-900 object-contain" loading="lazy">
                                    @endif
                                    <span class="font-medium text-zinc-200 hover:text-amber-300">{{ $ammo['item']['name'] }}</span>
                                </a>
                            </td>
                            <td class="px-4 py-2 text-zinc-400">{{ str_replace('Caliber', '', $ammo['caliber'] ?? '—') }}</td>
                            <td class="px-4 py-2">
                                <span class="rounded-full border px-2 py-0.5 text-xs font-semibold {{ $meta['chip'] }}">C{{ $rowTier }}</span>
                            </td>
                            <td class="px-4 py-2 text-right text-zinc-200">
                                {{ $dmg }}
                                @if (($ammo['projectileCount'] ?? 1) > 1)
                                    <span class="text-xs text-zinc-500">({{ $ammo['damage'] }}×{{ $ammo['projectileCount'] }})</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-right font-semibold {{ $meta['text'] }}">{{ $pen }}</td>
                            <td class="px-4 py-2 text-right text-zinc-300">{{ $ammo['armorDamage'] ?? '—' }}</td>
                            <td class="px-4 py-2 text-right text-zinc-300">{{ $ammo['fragmentationChance'] !== null ? number_format($ammo['fragmentationChance'] * 100, 0).'%' : '—' }}</td>
                            <td class="px-4 py-2 text-right text-zinc-300">{{ $ammo['initialSpeed'] ? $ammo['initialSpeed'].' m/s' : '—' }}</td>
                            <td class="px-4 py-2 text-center">{{ ($ammo['tracer'] ?? false) ? '🔴' : '—' }}</td>
                            <td class="px-4 py-2 text-right"><x-price :value="$ammo['item']['avg24hPrice']" class="text-zinc-300" /></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-zinc-500">Nenhuma munição encontrada com esses filtros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <p class="mt-3 text-xs text-zinc-600">
            Tier pela penetração: <span class="text-lime-400">C2 10+</span> ·
            <span class="text-yellow-400">C3 20+</span> ·
            <span class="text-orange-400">C4 30+</span> ·
            <span class="text-red-400">C5 40+</span> ·
            <span class="text-purple-300">C6 50+</span>
            — os separadores por classe aparecem ao ordenar por penetração.
        </p>
    @endif
</div>
