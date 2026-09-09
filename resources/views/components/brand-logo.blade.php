@props([
    'href' => null,
    'imageClass' => 'h-10 w-auto max-w-[160px] object-contain',
    'ariaLabel' => "Retour à l'accueil Pièce Radar",
    'themeAware' => false,
])

@php
    $lightLogo = asset('images/logo-piece-radar-transparent.png');
    $darkLogo = asset('images/logo-piece-radar-transparent-dark.png');
    $fixedLogo = asset('images/logo-piece-radar.png');
@endphp

@if ($href)
    <a href="{{ $href }}" aria-label="{{ $ariaLabel }}" {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center']) }}>
        @if ($themeAware)
            <span class="grid shrink-0 items-center">
                <img src="{{ $darkLogo }}" alt="" aria-hidden="true" class="invisible col-start-1 row-start-1 {{ $imageClass }}">
                <img src="{{ $lightLogo }}" alt="Pièce Radar" class="col-start-1 row-start-1 dark:hidden {{ $imageClass }}">
                <img src="{{ $darkLogo }}" alt="Pièce Radar" class="col-start-1 row-start-1 hidden dark:block {{ $imageClass }}">
            </span>
        @else
            <img src="{{ $fixedLogo }}" alt="Pièce Radar" class="{{ $imageClass }}">
        @endif
    </a>
@else
    <span {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center']) }}>
        @if ($themeAware)
            <span class="grid shrink-0 items-center">
                <img src="{{ $darkLogo }}" alt="" aria-hidden="true" class="invisible col-start-1 row-start-1 {{ $imageClass }}">
                <img src="{{ $lightLogo }}" alt="Pièce Radar" class="col-start-1 row-start-1 dark:hidden {{ $imageClass }}">
                <img src="{{ $darkLogo }}" alt="Pièce Radar" class="col-start-1 row-start-1 hidden dark:block {{ $imageClass }}">
            </span>
        @else
            <img src="{{ $fixedLogo }}" alt="Pièce Radar" class="{{ $imageClass }}">
        @endif
    </span>
@endif
