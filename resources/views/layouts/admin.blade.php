<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · KostKu</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f6f8ff] text-slate-900 antialiased">
@php
    $navigation = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'dashboard'],
        ['label' => 'Data Kost', 'route' => 'kosts.index', 'active' => 'kosts.*', 'icon' => 'building'],
        ['label' => 'Data Kamar', 'route' => 'rooms.index', 'active' => 'rooms.*', 'icon' => 'bed'],
        ['label' => 'Booking', 'route' => 'bookings.index', 'active' => 'bookings.*', 'icon' => 'calendar'],
        ['label' => 'Penyewa', 'route' => 'tenants.index', 'active' => 'tenants.*', 'icon' => 'users'],
        ['label' => 'Pembayaran', 'route' => 'payments.index', 'active' => 'payments.*', 'icon' => 'payment'],
        ['label' => 'Laporan', 'route' => 'reports.index', 'active' => 'reports.*', 'icon' => 'chart'],
    ];
@endphp

<div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-slate-950/40 lg:hidden"></div>
<aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-[min(280px,86vw)] -translate-x-full flex-col overflow-y-auto border-r border-slate-200 bg-white px-5 py-7 shadow-2xl transition-transform duration-200 sm:px-7 sm:py-8 lg:w-[280px] lg:translate-x-0 lg:shadow-none">
    <a href="{{ route('dashboard') }}" class="mb-12 block px-2">
        <span class="block text-[38px] font-extrabold leading-none tracking-tight text-[#082d72]">KostKu</span>
        <span class="mt-2 block text-xs font-semibold tracking-wide text-slate-500">Management Suite</span>
    </a>

    <nav class="space-y-1.5">
        @foreach($navigation as $item)
            <a href="{{ route($item['route']) }}" @class([
                'relative flex items-center gap-4 rounded-lg px-4 py-3 text-[13px] font-semibold tracking-wide transition',
                'bg-[#edf2ff] text-[#082d72] after:absolute after:inset-y-0 after:right-0 after:w-1 after:rounded-l after:bg-[#082d72]' => request()->routeIs($item['active']),
                'text-slate-600 hover:bg-slate-50 hover:text-[#082d72]' => !request()->routeIs($item['active']),
            ])>
                <x-icon :name="$item['icon']" class="h-5 w-5" />
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>

    <div class="mt-auto border-t border-slate-200 pt-5">
        <a href="#" class="flex items-center gap-4 px-4 py-3 text-[13px] font-semibold text-slate-600"><x-icon name="settings" class="h-5 w-5" /> Settings</a>
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button class="flex w-full items-center gap-4 px-4 py-3 text-[13px] font-semibold text-red-600"><x-icon name="logout" class="h-5 w-5" /> Logout</button>
        </form>
    </div>
</aside>

<header class="fixed inset-x-0 top-0 z-20 flex h-16 items-center gap-2 border-b border-slate-200 bg-white/95 px-3 backdrop-blur sm:px-5 lg:left-[280px] lg:px-8">
    <button id="sidebar-toggle" class="mr-3 rounded-lg p-2 text-slate-600 lg:hidden"><x-icon name="menu" /></button>
    <a href="{{ route('dashboard') }}" class="text-lg font-extrabold text-[#082d72] md:hidden">KostKu</a>
    <form action="{{ url()->current() }}" method="GET" class="relative hidden w-full max-w-md md:block">
        <x-icon name="search" class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
        <input name="search" value="{{ request('search') }}" class="h-10 w-full rounded-xl border border-slate-300 bg-white pl-12 pr-4 text-sm outline-none transition focus:border-blue-700 focus:ring-2 focus:ring-blue-100" placeholder="Search globally...">
    </form>
    <div class="ml-auto flex items-center gap-5">
        <button class="relative text-slate-600"><x-icon name="bell" class="h-5 w-5" /><span class="absolute right-0 top-0 h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span></button>
        <span class="hidden h-9 w-px bg-slate-200 md:block"></span>
        <div class="hidden text-right md:block">
            <p class="text-sm font-bold text-[#082d72]">{{ auth()->user()->name }}</p>
            <p class="text-xs text-slate-500">Property Manager</p>
        </div>
        <div class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-[#dbe7ff] text-xs font-bold text-[#082d72]">{{ collect(explode(' ', auth()->user()->name))->map(fn($word) => mb_substr($word, 0, 1))->take(2)->join('') }}</div>
    </div>
</header>

<main class="min-h-screen pt-16 lg:pl-[280px]">
    <div class="mx-auto w-full max-w-[1440px] px-4 py-6 sm:px-6 sm:py-8 lg:px-8 lg:py-9 xl:px-10">
        @if(session('success'))
            <div class="mb-5 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800"><x-icon name="check" class="h-5 w-5" />{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-5 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700"><x-icon name="x" class="h-5 w-5" />{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif
        @yield('content')
    </div>
</main>
@stack('scripts')
</body>
</html>
