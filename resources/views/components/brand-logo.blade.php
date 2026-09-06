@props([
    'href' => null,
    'imageClass' => 'h-10 w-auto max-w-[160px] object-contain',
    'ariaLabel' => "Retour à l'accueil Pièce Radar",
])

@if ($href)
    <a href="{{ $href }}" aria-label="{{ $ariaLabel }}" {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center']) }}>
        <img src="{{ asset('images/logo-piece-radar.png') }}" alt="Pièce Radar" class="{{ $imageClass }}">
    </a>
@else
    <span {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center']) }}>
        <img src="{{ asset('images/logo-piece-radar.png') }}" alt="Pièce Radar" class="{{ $imageClass }}">
    </span>
@endif
