@props([
    'variant' => 'success',
    'title' => null,
])

@php
    $variants = [
        'success' => 'border-emerald-100 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-300',
        'error' => 'border-red-100 bg-red-50 text-red-700 dark:border-red-900 dark:bg-red-950/50 dark:text-red-300',
        'warning' => 'border-orange-100 bg-[#FC8505]/5 text-[#C96504] dark:border-[#FC8505]/30 dark:bg-[#FC8505]/10 dark:text-orange-200',
        'info' => 'border-blue-100 bg-blue-50 text-blue-700 dark:border-blue-900 dark:bg-blue-950/50 dark:text-blue-300',
        'neutral' => 'border-zinc-200 bg-white text-zinc-700 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300',
    ];

    $classes = 'rounded-2xl border p-4 text-sm font-bold shadow-sm ' . ($variants[$variant] ?? $variants['success']);
    $role = $variant === 'error' ? 'alert' : 'status';
@endphp

<div {{ $attributes->merge(['class' => $classes, 'role' => $role]) }}>
    @if ($title)
        <p class="font-black text-zinc-950 dark:text-zinc-50">{{ $title }}</p>
    @endif

    <div class="{{ $title ? 'mt-1.5' : '' }}">
        {{ $slot }}
    </div>
</div>
