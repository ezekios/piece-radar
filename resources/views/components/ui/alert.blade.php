@props([
    'variant' => 'success',
    'title' => null,
])

@php
    $variants = [
        'success' => 'border-emerald-100 bg-emerald-50 text-emerald-700',
        'error' => 'border-red-100 bg-red-50 text-red-700',
        'warning' => 'border-orange-100 bg-[#FC8505]/5 text-[#C96504]',
        'info' => 'border-blue-100 bg-blue-50 text-blue-700',
        'neutral' => 'border-zinc-200 bg-white text-zinc-700',
    ];

    $classes = 'rounded-2xl border p-4 text-sm font-bold shadow-sm ' . ($variants[$variant] ?? $variants['success']);
    $role = $variant === 'error' ? 'alert' : 'status';
@endphp

<div {{ $attributes->merge(['class' => $classes, 'role' => $role]) }}>
    @if ($title)
        <p class="font-black text-zinc-950">{{ $title }}</p>
    @endif

    <div class="{{ $title ? 'mt-1.5' : '' }}">
        {{ $slot }}
    </div>
</div>
