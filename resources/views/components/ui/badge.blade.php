@props([
    'variant' => 'neutral',
])

@php
    $variants = [
        'neutral' => 'bg-zinc-100 text-zinc-700 ring-zinc-200',
        'orange' => 'bg-[#FC8505]/10 text-[#C96504] ring-orange-100',
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'danger' => 'bg-red-50 text-red-700 ring-red-100',
        'info' => 'bg-blue-50 text-blue-700 ring-blue-100',
        'dark' => 'bg-zinc-900 text-white ring-zinc-800',
    ];

    $classes = 'inline-flex w-fit items-center rounded-full px-3 py-1 text-xs font-black ring-1 ' . ($variants[$variant] ?? $variants['neutral']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
