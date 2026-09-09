@props([
    'title',
    'description' => null,
])

<section {{ $attributes->merge(['class' => 'rounded-2xl border border-dashed border-orange-200 bg-white p-6 text-center shadow-sm sm:p-8']) }}>
    <h2 class="text-base font-black text-zinc-950">{{ $title }}</h2>

    @if ($description)
        <p class="mt-1.5 text-sm font-medium leading-6 text-zinc-600">{{ $description }}</p>
    @endif

    @isset($actions)
        <div class="mt-4 flex flex-col items-center justify-center gap-2 sm:flex-row">
            {{ $actions }}
        </div>
    @endisset

    {{ $slot }}
</section>
