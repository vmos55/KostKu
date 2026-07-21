<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin · KostKu</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#eef3ff] antialiased">
<main class="flex min-h-screen items-center justify-center p-3 sm:p-5 lg:p-10">
    <div class="grid min-w-0 w-[calc(100vw-1.5rem)] max-w-full overflow-hidden rounded-2xl border border-blue-200 bg-white shadow-[0_18px_60px_rgba(30,58,138,.10)] sm:w-[calc(100vw-2.5rem)] lg:w-full lg:max-w-[1024px] lg:grid-cols-2">
        <section class="relative hidden min-h-[620px] overflow-hidden lg:block">
            <img src="{{ asset('images/kostku-login-building.png') }}" alt="Bangunan KostKu" class="absolute inset-0 h-full w-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-[#082d72]/95 via-[#082d72]/25 to-transparent"></div>
            <div class="absolute inset-x-0 bottom-0 p-10 text-white">
                <h2 class="text-4xl font-extrabold tracking-tight">Kelola dengan Mudah</h2>
                <p class="mt-3 max-w-md text-sm leading-6 text-blue-100">Sistem manajemen properti terpadu untuk efisiensi dan visibilitas finansial yang optimal.</p>
            </div>
        </section>
        <section class="flex min-h-[560px] items-center px-5 py-10 sm:min-h-[620px] sm:px-14 sm:py-12">
            <div class="w-full">
                <div class="mb-10 text-center">
                    <div class="mx-auto mb-8 flex items-center justify-center gap-2 text-[#082d72]"><x-icon name="home" class="h-9 w-9 text-emerald-500"/><span class="text-2xl font-extrabold">Kost<span class="text-emerald-500">Ku</span></span></div>
                    <h1 class="text-xl font-extrabold tracking-tight text-slate-900 sm:text-2xl">Selamat Datang di KostKu Admin</h1>
                    <p class="mt-2 text-sm text-slate-500">Silakan masuk ke akun Anda</p>
                </div>
                @if($errors->any())<div class="mb-5 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif
                <form method="POST" action="{{ route('login.store') }}" class="space-y-5">@csrf
                    <div><label class="mb-2 block text-xs font-bold text-slate-700">Email</label><div class="relative"><x-icon name="search" class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-300"/><input type="email" name="email" value="{{ old('email', 'admin@kostku.com') }}" required autofocus class="h-12 w-full border border-slate-300 pl-12 pr-4 text-sm outline-none focus:border-[#1e3a8a]" placeholder="admin@kostku.com"></div></div>
                    <div><div class="mb-2 flex items-center justify-between"><label class="text-xs font-bold text-slate-700">Password</label><span class="text-xs font-semibold text-[#1e3a8a]">Lupa Password?</span></div><div class="relative"><x-icon name="settings" class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-300"/><input id="password" type="password" name="password" required class="h-12 w-full border border-slate-300 pl-12 pr-12 text-sm outline-none focus:border-[#1e3a8a]" placeholder="••••••••"><button type="button" data-password-toggle class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400"><x-icon name="eye" class="h-5 w-5"/></button></div></div>
                    <label class="flex items-center gap-3 text-sm text-slate-600"><input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-[#1e3a8a]"> Remember me</label>
                    <button class="flex h-12 w-full items-center justify-center gap-2 bg-[#244393] font-bold text-white transition hover:bg-[#173477]">Login <x-icon name="logout" class="h-5 w-5"/></button>
                </form>
            </div>
        </section>
    </div>
</main>
</body>
</html>
