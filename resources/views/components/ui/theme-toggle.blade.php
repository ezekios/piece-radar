@props([
    'label' => 'Changer de thème',
    'class' => '',
])

<button
    type="button"
    data-theme-toggle
    aria-label="{{ $label }}"
    {{ $attributes->merge(['class' => trim('inline-flex h-11 w-11 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-700 shadow-sm transition hover:border-orange-200 hover:text-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505] focus:ring-offset-2 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:border-[#FC8505]/40 dark:hover:text-[#FC8505] dark:focus:ring-offset-zinc-950 ' . $class)]) }}
>
    <span class="sr-only">{{ $label }}</span>
    <x-ui.icon name="sun" class="hidden h-5 w-5 dark:block" />
    <x-ui.icon name="moon" class="h-5 w-5 dark:hidden" />
</button>
