<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-100">Traders</h1>
        <p class="text-sm text-zinc-500">Níveis de lealdade, reset de estoque e moedas</p>
    </div>

    <x-api-error :message="$error" />

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @foreach ($traders as $trader)
            <div class="rounded-xl border border-zinc-800 bg-[#14171f] p-4">
                <div class="flex items-start gap-4">
                    @if (! empty($trader['imageLink']))
                        <img src="{{ $trader['imageLink'] }}" alt="{{ $trader['name'] }}" class="h-16 w-16 shrink-0 rounded-lg object-cover" loading="lazy">
                    @endif
                    <div class="min-w-0 flex-1">
                        <h2 class="text-lg font-bold text-zinc-100">{{ $trader['name'] }}</h2>
                        <p class="text-xs text-zinc-500">
                            Moeda: {{ $trader['currency']['name'] ?? '—' }}
                            @if (! empty($trader['resetTime']))
                                · Reset {{ \Carbon\Carbon::parse($trader['resetTime'])->diffForHumans() }}
                            @endif
                        </p>
                        @if (! empty($trader['description']))
                            <details class="mt-1">
                                <summary class="cursor-pointer text-xs text-amber-400/80 hover:text-amber-300">Sobre</summary>
                                <p class="mt-1 text-xs leading-relaxed text-zinc-400">{{ $trader['description'] }}</p>
                            </details>
                        @endif
                    </div>
                </div>

                @if (! empty($trader['levels']))
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="border-b border-zinc-800 text-left uppercase tracking-wide text-zinc-500">
                                    <th class="py-2 pr-2">LL</th>
                                    <th class="py-2 pr-2 text-right">Nível jogador</th>
                                    <th class="py-2 pr-2 text-right">Reputação</th>
                                    <th class="py-2 pr-2 text-right">Volume de compras</th>
                                    <th class="py-2 text-right">Pagamento</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($trader['levels'] as $level)
                                    <tr class="border-b border-zinc-800/60 last:border-0">
                                        <td class="py-1.5 pr-2 font-semibold text-amber-300">LL{{ $level['level'] }}</td>
                                        <td class="py-1.5 pr-2 text-right text-zinc-300">{{ $level['requiredPlayerLevel'] ?? '—' }}</td>
                                        <td class="py-1.5 pr-2 text-right text-zinc-300">{{ $level['requiredReputation'] ?? '0' }}</td>
                                        <td class="py-1.5 pr-2 text-right text-zinc-300">
                                            {{ $level['requiredCommerce'] ? number_format($level['requiredCommerce'], 0, ',', '.') : '—' }}
                                        </td>
                                        <td class="py-1.5 text-right text-zinc-300">
                                            {{ $level['payRate'] !== null ? number_format($level['payRate'] * 100, 0).'%' : '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
