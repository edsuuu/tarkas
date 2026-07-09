<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Tarkas — Escape from Tarkov' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #3f3f46; border-radius: 4px; }
        details > summary { list-style: none; }
        details > summary::-webkit-details-marker { display: none; }
    </style>
</head>
<body class="min-h-screen bg-[#0c0e12] text-zinc-200 antialiased">
    <nav class="sticky top-0 z-50 border-b border-zinc-800 bg-[#12141a]/95 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center gap-1 overflow-x-auto px-4 py-3">
            <a href="{{ route('dashboard') }}" wire:navigate class="mr-4 flex items-center gap-2 whitespace-nowrap text-lg font-bold tracking-wide text-amber-400">
                <span>⚡</span> TARKAS
            </a>
            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-nav-link>
            <x-nav-link :href="route('items.index')" :active="request()->routeIs('items.*')">Itens</x-nav-link>
            <x-nav-link :href="route('ammo.index')" :active="request()->routeIs('ammo.*')">Munição</x-nav-link>
            <x-nav-link :href="route('tasks.index')" :active="request()->routeIs('tasks.*')">Quests</x-nav-link>
            <x-nav-link :href="route('hideout.index')" :active="request()->routeIs('hideout.*')">Hideout</x-nav-link>
            <x-nav-link :href="route('traders.index')" :active="request()->routeIs('traders.*')">Traders</x-nav-link>
            <x-nav-link :href="route('barters.index')" :active="request()->routeIs('barters.*')">Trocas</x-nav-link>
            <x-nav-link :href="route('crafts.index')" :active="request()->routeIs('crafts.*')">Crafts</x-nav-link>
            <x-nav-link :href="route('maps.index')" :active="request()->routeIs('maps.*')">Mapas</x-nav-link>
        </div>
    </nav>

    <main class="mx-auto max-w-7xl px-4 py-6">
        {{ $slot }}
    </main>

    <footer class="mx-auto max-w-7xl border-t border-zinc-900 px-4 pb-8 pt-4 text-center text-xs text-zinc-600">
        Dados da API GraphQL pública de <a href="https://tarkov.dev" target="_blank" class="underline hover:text-zinc-400">tarkov.dev</a> — sem autenticação. Este site não é afiliado à Battlestate Games.
    </footer>
</body>
</html>
