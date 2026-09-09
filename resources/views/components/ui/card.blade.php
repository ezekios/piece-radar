@props([
    'as' => 'div',
    'padding' => 'p-5',
    'interactive' => false,
    'variant' => 'white',
])

@php
    $variants = [
        'white' => 'border-zinc-200 bg-white text-zinc-950',
        'subtle' => 'border-zinc-200 bg-zinc-50 text-zinc-950',
        'orange' => 'border-[#FC8505] bg-[#FC8505] text-white',
        'dark' => 'border-zinc-800 bg-zinc-950 text-white',
    ];

    $classes = trim(
        'rounded-2xl border shadow-sm ' .
        ($variants[$variant] ?? $variants['white']) . ' ' .
        $padding . ' ' .
        ($interactive ? 'transition hover:-translate-y-0.5 hover:border-orange-200 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2' : '')
    );
@endphp

<{{ $as }} {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</{{ $as }}>
