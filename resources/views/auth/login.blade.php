@extends('layouts.app')

@section('title', 'Entrar — Tarkas')

@section('content')
    <div class="mx-auto max-w-md">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-zinc-100">Entrar</h1>
            <p class="text-sm text-zinc-500">Acesso administrativo criado apenas via seeder.</p>
        </div>

        <form method="POST" action="{{ route('login.store') }}" class="rounded-xl border border-zinc-800 bg-[#14171f] p-5">
            @csrf

            <div class="space-y-4">
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-zinc-300">E-mail</span>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        required
                        autofocus
                        class="w-full rounded-lg border border-zinc-700 bg-[#1a1d26] px-4 py-2 text-sm text-zinc-200 placeholder-zinc-500 focus:border-amber-500 focus:outline-none"
                    >
                    @error('email')
                        <span class="mt-1 block text-sm text-red-400">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-zinc-300">Senha</span>
                    <input
                        type="password"
                        name="password"
                        autocomplete="current-password"
                        required
                        class="w-full rounded-lg border border-zinc-700 bg-[#1a1d26] px-4 py-2 text-sm text-zinc-200 placeholder-zinc-500 focus:border-amber-500 focus:outline-none"
                    >
                    @error('password')
                        <span class="mt-1 block text-sm text-red-400">{{ $message }}</span>
                    @enderror
                </label>

                <label class="flex items-center gap-2 text-sm text-zinc-400">
                    <input type="checkbox" name="remember" value="1" class="rounded border-zinc-700 bg-[#1a1d26] text-amber-500 focus:ring-amber-500">
                    Lembrar sessão
                </label>
            </div>

            <button type="submit" class="mt-5 w-full rounded-lg bg-amber-400 px-4 py-2 text-sm font-semibold text-zinc-950 transition hover:bg-amber-300">
                Entrar
            </button>
        </form>
    </div>
@endsection
