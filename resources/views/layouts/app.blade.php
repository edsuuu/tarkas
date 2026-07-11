<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $yieldTitle = trim($__env->yieldContent('title'));
        $pageTitle = $title ?? ($yieldTitle !== '' ? $yieldTitle : 'Tarkas — Escape from Tarkov');
        $mainClass = trim($__env->yieldContent('main_class')) ?: 'mx-auto max-w-7xl px-4 py-6';
        $navControls = trim($__env->yieldContent('nav_controls'));
    @endphp
    <title>{{ $pageTitle }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #3f3f46; border-radius: 4px; }
        details > summary { list-style: none; }
        details > summary::-webkit-details-marker { display: none; }
        [x-cloak] { display: none !important; }
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
            @if ($navControls !== '')
                {!! $navControls !!}
            @endif
            <div class="ml-auto flex items-center gap-2">
                @auth
                    @can('viewLogViewer')
                        <x-nav-link :href="route('log-viewer.index')" :active="request()->is('log-viewer*')" :navigate="false">Logs</x-nav-link>
                    @endcan
                    <span class="hidden items-center gap-2 whitespace-nowrap text-sm text-zinc-400 sm:flex">
                        {{ auth()->user()->name }}
                        @php($userRole = auth()->user()->getRoleNames()->first())
                        @if ($userRole)
                            <span class="rounded-full border px-2 py-0.5 text-xs font-semibold {{ $userRole === 'Tecnologia' ? 'border-amber-500/40 bg-amber-500/10 text-amber-400' : 'border-zinc-700 bg-zinc-800 text-zinc-300' }}">
                                {{ $userRole }}
                            </span>
                        @endif
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="whitespace-nowrap rounded-md px-3 py-1.5 text-sm font-medium text-zinc-400 transition hover:bg-zinc-800 hover:text-zinc-200">
                            Sair
                        </button>
                    </form>
                @else
                    <x-nav-link :href="route('login')" :active="request()->routeIs('login')" :navigate="false">Entrar</x-nav-link>
                    <x-nav-link :href="route('register')" :active="request()->routeIs('register')" :navigate="false">Cadastrar</x-nav-link>
                @endauth
            </div>
        </div>
    </nav>

    <main class="{{ $mainClass }}">
        @isset($slot)
            {{ $slot }}
        @else
            @yield('content')
        @endisset
    </main>

</body>
</html>
