@props(['name', 'class' => 'h-5 w-5'])

<svg {{ $attributes->merge(['class' => $class, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.8', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'aria-hidden' => 'true']) }}>
    @switch($name)
        @case('dashboard') <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/> @break
        @case('building') <path d="M3 21h18M6 21V4h9v17M15 9h4v12M9 7h2M9 11h2M9 15h2M17 12h1M17 16h1"/> @break
        @case('bed') <path d="M3 20v-8h18v8M3 16h18M6 12V7h5a3 3 0 0 1 3 3v2M3 12V6M21 12V9a2 2 0 0 0-2-2h-5"/> @break
        @case('calendar') <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01"/> @break
        @case('users') <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/> @break
        @case('payment') <rect x="2" y="6" width="20" height="13" rx="2"/><path d="M16 10h4M6 15h4M7 6V4h10v2"/> @break
        @case('chart') <path d="M4 19V9M10 19V5M16 19v-7M22 19V3M2 21h21"/> @break
        @case('settings') <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1.03 1.55V21h-4v-.08A1.7 1.7 0 0 0 8.97 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 4.6 15 1.7 1.7 0 0 0 3.08 14H3v-4h.08A1.7 1.7 0 0 0 4.6 8.97a1.7 1.7 0 0 0-.34-1.88L4.2 7.03 7.03 4.2l.06.06A1.7 1.7 0 0 0 8.97 4.6 1.7 1.7 0 0 0 10 3.08V3h4v.08A1.7 1.7 0 0 0 15.03 4.6a1.7 1.7 0 0 0 1.88-.34l.06-.06 2.83 2.83-.06.06a1.7 1.7 0 0 0-.34 1.88A1.7 1.7 0 0 0 20.92 10H21v4h-.08A1.7 1.7 0 0 0 19.4 15Z"/> @break
        @case('logout') <path d="M10 17l5-5-5-5M15 12H3M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/> @break
        @case('search') <circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/> @break
        @case('bell') <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/> @break
        @case('plus') <path d="M12 5v14M5 12h14"/> @break
        @case('filter') <path d="M4 5h16M7 12h10M10 19h4"/> @break
        @case('edit') <path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/> @break
        @case('trash') <path d="M3 6h18M8 6V4h8v2M19 6l-1 15H6L5 6M10 11v6M14 11v6"/> @break
        @case('eye') <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/> @break
        @case('check') <path d="m5 12 4 4L19 6"/> @break
        @case('x') <path d="M18 6 6 18M6 6l12 12"/> @break
        @case('menu') <path d="M4 6h16M4 12h16M4 18h16"/> @break
        @case('money') <rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M7 9H6v1M17 15h1v-1"/> @break
        @case('download') <path d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14"/> @break
        @case('home') <path d="m3 11 9-8 9 8v10h-6v-6H9v6H3Z"/> @break
        @case('chevron-down') <path d="m6 9 6 6 6-6"/> @break
        @default <circle cx="12" cy="12" r="9"/> @break
    @endswitch
</svg>
