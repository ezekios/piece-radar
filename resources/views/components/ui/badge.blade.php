@props([
    'variant' => 'neutral',
])

@php
    $variants = [
        'neutral' => 'bg-zinc-100 text-zinc-700 ring-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:ring-zinc-700',
        'orange' => 'bg-[#FC8505]/10 text-[#C96504] ring-orange-100 dark:bg-[#FC8505]/15 dark:text-orange-200 dark:ring-[#FC8505]/30',
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-100 dark:bg-emerald-950/50 dark:text-emerald-300 dark:ring-emerald-900',
        'danger' => 'bg-red-50 text-red-700 ring-red-100 dark:bg-red-950/50 dark:text-red-300 dark:ring-red-900',
        'info' => 'bg-blue-50 text-blue-700 ring-blue-100 dark:bg-blue-950/50 dark:text-blue-300 dark:ring-blue-900',
        'dark' => 'bg-zinc-900 text-white ring-zinc-800 dark:bg-zinc-800 dark:ring-zinc-700',
    ];

    $classes = 'inline-flex w-fit items-center rounded-full px-3 py-1 text-xs font-black ring-1 ' . ($variants[$variant] ?? $variants['neutral']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
