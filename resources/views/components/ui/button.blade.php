@props([
    'as' => 'a',
    'variant' => 'primary',
    'size' => 'md',
    'full' => false,
])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-xl text-sm font-black transition focus:outline-none focus:ring-2 focus:ring-offset-2';

    $variants = [
        'primary' => 'bg-[#FC8505] text-white shadow-sm hover:bg-[#E87804] focus:ring-[#FC8505]',
        'secondary' => 'border border-zinc-200 bg-white text-zinc-800 shadow-sm hover:border-orange-200 hover:text-[#FC8505] focus:ring-[#FC8505] dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:border-[#FC8505]/40 dark:hover:text-[#FC8505]',
        'ghost' => 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950 focus:ring-[#FC8505] dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-50',
        'danger' => 'border border-red-200 bg-white text-red-700 shadow-sm hover:bg-red-50 focus:ring-red-200 dark:border-red-900/60 dark:bg-zinc-900 dark:text-red-300 dark:hover:bg-red-950/40 dark:focus:ring-red-900',
    ];

    $sizes = [
        'sm' => 'h-10 px-3',
        'md' => 'h-11 px-4',
        'lg' => 'h-12 px-5',
    ];

    $classes = trim(implode(' ', [
        $base,
        $variants[$variant] ?? $variants['primary'],
        $sizes[$size] ?? $sizes['md'],
        $full ? 'w-full' : '',
    ]));
@endphp

<{{ $as }}
    @if ($as === 'button' && ! $attributes->has('type'))
        type="button"
    @endif
    {{ $attributes->merge(['class' => $classes]) }}
>
    {{ $slot }}
</{{ $as }}>
