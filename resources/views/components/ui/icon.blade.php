@props([
    'name',
])

@php
    $icons = [
        'dashboard' => '<path d="M4 5.5A1.5 1.5 0 0 1 5.5 4h3A1.5 1.5 0 0 1 10 5.5v3A1.5 1.5 0 0 1 8.5 10h-3A1.5 1.5 0 0 1 4 8.5v-3Z" /><path d="M14 5.5A1.5 1.5 0 0 1 15.5 4h3A1.5 1.5 0 0 1 20 5.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 14 8.5v-3Z" /><path d="M4 15.5A1.5 1.5 0 0 1 5.5 14h3a1.5 1.5 0 0 1 1.5 1.5v3A1.5 1.5 0 0 1 8.5 20h-3A1.5 1.5 0 0 1 4 18.5v-3Z" /><path d="M14 15.5a1.5 1.5 0 0 1 1.5-1.5h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3a1.5 1.5 0 0 1-1.5-1.5v-3Z" />',
        'vehicles' => '<path d="M5 16h14" /><path d="M7 16l1.4-5.2A2.5 2.5 0 0 1 10.8 9h2.4a2.5 2.5 0 0 1 2.4 1.8L17 16" /><path d="M7.5 16.5a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3Z" /><path d="M16.5 16.5a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3Z" /><path d="M9 13h6" />',
        'parts' => '<path d="M12 8.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Z" /><path d="M12 3v2" /><path d="M12 19v2" /><path d="M4.2 7.5l1.7 1" /><path d="M18.1 15.5l1.7 1" /><path d="M4.2 16.5l1.7-1" /><path d="M18.1 8.5l1.7-1" />',
        'requests' => '<path d="M4 6.5A2.5 2.5 0 0 1 6.5 4h11A2.5 2.5 0 0 1 20 6.5v11a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 17.5v-11Z" /><path d="M4 13h4l1.5 2h5l1.5-2h4" />',
        'matches' => '<path d="M13 3l-1.3 4.2L8 8.5l3.7 1.3L13 14l1.3-4.2L18 8.5l-3.7-1.3L13 3Z" /><path d="M6 14l-.8 2.2L3 17l2.2.8L6 20l.8-2.2L9 17l-2.2-.8L6 14Z" />',
        'account' => '<path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" /><path d="M4 20a8 8 0 0 1 16 0" />',
        'users' => '<path d="M16 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" /><path d="M8 12a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" /><path d="M3 20a5 5 0 0 1 10 0" /><path d="M13 18a5 5 0 0 1 8 2" />',
        'building' => '<path d="M4 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16" /><path d="M20 21v-9a2 2 0 0 0-2-2h-2" /><path d="M8 7h4" /><path d="M8 11h4" /><path d="M8 15h4" /><path d="M3 21h18" />',
        'shield' => '<path d="M12 3 5 6v5c0 4.8 3 8.3 7 10 4-1.7 7-5.2 7-10V6l-7-3Z" /><path d="m9 12 2 2 4-4" />',
        'bell' => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Z" /><path d="M10 21h4" />',
        'logout' => '<path d="M10 6H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h4" /><path d="M14 16l4-4-4-4" /><path d="M18 12H9" />',
        'plus' => '<path d="M12 5v14" /><path d="M5 12h14" />',
        'clock' => '<path d="M12 6v6l4 2" /><path d="M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Z" />',
        'check' => '<path d="M20 6 9 17l-5-5" />',
        'package' => '<path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z" /><path d="m4 7.5 8 4.5 8-4.5" /><path d="M12 12v9" />',
        'arrow' => '<path d="M5 12h14" /><path d="m13 6 6 6-6 6" />',
    ];
@endphp

<svg {{ $attributes->merge(['class' => 'h-5 w-5']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    {!! $icons[$name] ?? $icons['dashboard'] !!}
</svg>
