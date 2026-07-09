@extends('layouts.app')

@section('title', 'Criar conta — Tarkas')

@section('content')
    <div class="mx-auto max-w-md">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-zinc-100">Criar conta</h1>
            <p class="text-sm text-zinc-500">Crie sua conta PMC para salvar o progresso das suas quests.</p>
        </div>

        <form method="POST" action="{{ route('register.store') }}" class="rounded-xl border border-zinc-800 bg-[#14171f] p-5">
            @csrf

            <div class="space-y-4">
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-zinc-300">Nome</span>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        autocomplete="name"
                        required
                        autofocus
                        class="w-full rounded-lg border border-zinc-700 bg-[#1a1d26] px-4 py-2 text-sm text-zinc-200 placeholder-zinc-500 focus:border-amber-500 focus:outline-none"
                    >
                    @error('name')
                        <span class="mt-1 block text-sm text-red-400">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-zinc-300">E-mail</span>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        required
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
                        autocomplete="new-password"
                        required
                        class="w-full rounded-lg border border-zinc-700 bg-[#1a1d26] px-4 py-2 text-sm text-zinc-200 placeholder-zinc-500 focus:border-amber-500 focus:outline-none"
                    >
                    @error('password')
                        <span class="mt-1 block text-sm text-red-400">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-zinc-300">Confirmar senha</span>
                    <input
                        type="password"
                        name="password_confirmation"
                        autocomplete="new-password"
                        required
                        class="w-full rounded-lg border border-zinc-700 bg-[#1a1d26] px-4 py-2 text-sm text-zinc-200 placeholder-zinc-500 focus:border-amber-500 focus:outline-none"
                    >
                </label>
            </div>

            <button type="submit" class="mt-5 w-full rounded-lg bg-amber-400 px-4 py-2 text-sm font-semibold text-zinc-950 transition hover:bg-amber-300">
                Cadastrar
            </button>

            <p class="mt-4 text-center text-sm text-zinc-500">
                Já tem conta?
                <a href="{{ route('login') }}" class="text-amber-400 hover:text-amber-300">Entrar</a>
            </p>
        </form>
    </div>
@endsection
