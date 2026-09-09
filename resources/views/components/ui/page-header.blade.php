@props([
    'eyebrow' => null,
    'title',
    'description' => null,
    'badgeVariant' => 'orange',
])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-3 sm:gap-4 xl:flex-row xl:items-center xl:justify-between']) }}>
    <div class="max-w-3xl">
        @if ($eyebrow)
            <x-ui.badge :variant="$badgeVariant">{{ $eyebrow }}</x-ui.badge>
        @endif

        <h1 class="{{ $eyebrow ? 'mt-3' : '' }} text-2xl font-black leading-tight text-zinc-950 dark:text-zinc-50 sm:text-3xl lg:text-4xl">
            {{ $title }}
        </h1>

        @if ($description)
            <p class="mt-2 max-w-2xl text-sm font-medium leading-6 text-zinc-600 dark:text-zinc-400 sm:text-base">
                {{ $description }}
            </p>
        @endif
    </div>

    @isset($actions)
        <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
            {{ $actions }}
        </div>
    @endisset
</div>
