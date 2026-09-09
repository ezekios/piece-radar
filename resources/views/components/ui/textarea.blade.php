@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'value' => null,
    'rows' => 5,
    'help' => null,
    'error' => null,
])

@php
    $fieldId = $id ?? $name;
    $resolvedValue = $name ? old($name, $value) : $value;
    $errorMessage = $error ?? ($name ? $errors->first($name) : null);
    $describedBy = trim(($help && $fieldId ? $fieldId . '-help ' : '') . ($errorMessage && $fieldId ? $fieldId . '-error' : ''));
    $classes = 'w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-3 text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:border-[#FC8505] focus:outline-none focus:ring-2 focus:ring-[#FC8505]/20 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-50 dark:placeholder:text-zinc-500';
@endphp

<div>
    @if ($label && $fieldId)
        <label for="{{ $fieldId }}" class="text-sm font-black text-zinc-900 dark:text-zinc-100">{{ $label }}</label>
    @endif

    <textarea
        @if ($fieldId) id="{{ $fieldId }}" @endif
        @if ($name) name="{{ $name }}" @endif
        rows="{{ $rows }}"
        @if ($errorMessage) aria-invalid="true" @endif
        @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes->merge(['class' => trim(($label ? 'mt-2 ' : '') . $classes)]) }}
    >{{ $resolvedValue }}</textarea>

    @if ($help && $fieldId)
        <p id="{{ $fieldId }}-help" class="mt-1.5 text-xs font-medium leading-5 text-zinc-500 dark:text-zinc-400">{{ $help }}</p>
    @endif

    @if ($errorMessage && $fieldId)
        <p id="{{ $fieldId }}-error" class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-300">{{ $errorMessage }}</p>
    @endif
</div>
