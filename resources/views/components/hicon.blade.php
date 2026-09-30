@props(['name' => '', 'class' => 'w-6 h-6'])

@php
$icons = [
    'building' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>',
    'key' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>',
    'beach' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 18h18M5 18c0-5 3-9 7-9s7 4 7 9M12 9V4m0 0c2 0 4 1 5 3M12 4C10 4 8 5 7 7"/>',
    'home' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
    'trophy' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M8 21h8m-4-4v4m-5-9a5 5 0 0010 0V4H7v8zM5 8a2 2 0 01-2-2V5h4M19 8a2 2 0 002-2V5h-4"/>',
    'cash' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>',
    'sun' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>',
    'waves' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 16c2 0 2-2 4-2s2 2 4 2 2-2 4-2 2 2 4 2M3 11c2 0 2-2 4-2s2 2 4 2 2-2 4-2 2 2 4 2M3 6c2 0 2-2 4-2s2 2 4 2 2-2 4-2 2 2 4 2"/>',
    'document' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
    'chart' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>',
    'location' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>',
    'bed' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 12V7a1 1 0 011-1h16a1 1 0 011 1v5m-18 0h18m-18 0v5m18-5v5M3 17h18M7 12V9h4v3"/>',
    'bath' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 12h16a0 0 0 010 0v3a4 4 0 01-4 4H8a4 4 0 01-4-4v-3a0 0 0 010 0zM7 12V5a2 2 0 012-2 2 2 0 012 2M5 19l-1 2m16-2l1 2"/>',
    'area' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>',
    'car' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M5 13l1.5-4.5A2 2 0 018.4 7h7.2a2 2 0 011.9 1.5L19 13m-14 0h14m-14 0v4m14-4v4M7 17v1m10-1v1M6 13.5h.01M18 13.5h.01"/>',
    'pool' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 18c2 0 2-1.5 4-1.5s2 1.5 4 1.5 2-1.5 4-1.5 2 1.5 4 1.5M7 16V6a2 2 0 014 0M11 11h-4"/>',
    'gym' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M6.5 6.5l11 11m-12-2l-2 2m2-2l2 2m11-13l2-2m-2 2l-2-2M4 9l2-2 2 2-2 2zm14 6l-2-2 2-2 2 2z"/>',
    'check' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>',
    'arrow-right' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>',
    'phone' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>',
    'mail' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
    'calendar' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
    'play' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    'cube' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',
    'sea-view' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M2 16s2-1 4 0 4 1 6 0 4-1 6 0 2 1 2 1M4 12a8 8 0 0116 0M12 4v4"/>',
    'shield' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
    'star' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>',
    'sparkles' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>',
];
$svg = $icons[$name] ?? $icons['check'];
@endphp

<svg class="{{ $class }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" {{ $attributes }}>{!! $svg !!}</svg>
