@props(['href', 'active' => false])
<a href="{{ $href }}" wire:navigate {{ $attributes->class([
    'whitespace-nowrap rounded-md px-3 py-1.5 text-sm font-medium transition',
    'bg-amber-400/10 text-amber-300' => $active,
    'text-zinc-400 hover:bg-zinc-800 hover:text-zinc-200' => ! $active,
]) }}>{{ $slot }}</a>
