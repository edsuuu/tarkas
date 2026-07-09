@props(['value' => null, 'prefix' => '₽'])
@if ($value === null)
    <span class="text-zinc-600">—</span>
@else
    <span {{ $attributes }}>{{ $prefix }} {{ number_format($value, 0, ',', '.') }}</span>
@endif
