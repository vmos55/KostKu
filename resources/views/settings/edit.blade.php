@extends('layouts.admin')

@section('title', 'Pengaturan')

@section('content')
@php($activeSection = request('section', $errors->hasAny(['current_password', 'password']) ? 'security' : 'profile'))

<div class="mb-6">
    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Pengaturan</h1>
    <p class="mt-2 text-sm text-slate-500">Kelola informasi profil, keamanan akun, dan preferensi dashboard Anda.</p>
</div>

<div class="grid gap-6 lg:grid-cols-[240px_minmax(0,1fr)]">
    <aside class="panel h-fit p-2">
        <div class="flex gap-1 overflow-x-auto lg:flex-col lg:overflow-visible" role="tablist" aria-label="Menu pengaturan">
            @foreach([
                ['id' => 'profile', 'label' => 'Profil Admin', 'description' => 'Identitas dan kontak', 'icon' => 'users'],
                ['id' => 'security', 'label' => 'Keamanan', 'description' => 'Ubah password akun', 'icon' => 'settings'],
                ['id' => 'preferences', 'label' => 'Preferensi', 'description' => 'Bahasa dan notifikasi', 'icon' => 'bell'],
            ] as $tab)
                <button type="button" data-settings-tab="{{ $tab['id'] }}" @class([
                    'flex min-w-max items-center gap-3 rounded-lg px-4 py-3 text-left transition lg:w-full',
                    'bg-[#12347f] text-white' => $activeSection === $tab['id'],
                    'text-slate-600 hover:bg-slate-50 hover:text-[#12347f]' => $activeSection !== $tab['id'],
                ]) role="tab" aria-selected="{{ $activeSection === $tab['id'] ? 'true' : 'false' }}">
                    <x-icon :name="$tab['icon']" class="h-5 w-5 shrink-0" />
                    <span>
                        <span class="block text-sm font-bold">{{ $tab['label'] }}</span>
                        <span class="mt-0.5 hidden text-[11px] opacity-70 lg:block">{{ $tab['description'] }}</span>
                    </span>
                </button>
            @endforeach
        </div>
    </aside>

    <div>
        <section data-settings-panel="profile" @class(['panel overflow-hidden', 'hidden' => $activeSection !== 'profile']) role="tabpanel">
            <div class="border-b border-slate-200 px-5 py-5 sm:px-7">
                <h2 class="text-lg font-extrabold text-slate-900">Profil Admin</h2>
                <p class="mt-1 text-sm text-slate-500">Informasi ini digunakan untuk identitas akun pengelola KostKu.</p>
            </div>

            <form method="POST" action="{{ route('settings.profile') }}" enctype="multipart/form-data" class="p-5 sm:p-7">
                @csrf
                @method('PUT')

                <div class="mb-7 flex flex-col gap-5 rounded-xl bg-[#f7f9fe] p-4 sm:flex-row sm:items-center sm:p-5">
                    <div class="grid h-20 w-20 shrink-0 place-items-center overflow-hidden rounded-full bg-[#dbe7ff] text-xl font-extrabold text-[#12347f] ring-4 ring-white" data-avatar-preview>
                        @if($user->avatar)
                            <img src="{{ Storage::url($user->avatar) }}" alt="Foto profil {{ $user->name }}" class="h-full w-full object-cover">
                        @else
                            {{ collect(explode(' ', $user->name))->map(fn($word) => mb_substr($word, 0, 1))->take(2)->join('') }}
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-bold text-slate-900">Foto Profil</p>
                        <p class="mt-1 text-xs leading-5 text-slate-500">Gunakan JPG, PNG, atau WEBP. Ukuran maksimal 3 MB.</p>
                        <label class="btn-secondary mt-3 w-fit">
                            Pilih Foto
                            <input type="file" name="avatar" accept="image/png,image/jpeg,image/webp" class="sr-only" data-avatar-input>
                        </label>
                    </div>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="name" class="form-label">Nama Lengkap</label>
                        <input id="name" name="name" value="{{ old('name', $user->name) }}" class="form-input" required autocomplete="name">
                        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email" class="form-label">Alamat Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" class="form-input" required autocomplete="email">
                        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="phone" class="form-label">Nomor Telepon</label>
                        <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="form-input" placeholder="Contoh: 0812 3456 7890" autocomplete="tel">
                        @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mt-7 flex justify-end border-t border-slate-200 pt-5">
                    <button class="btn-primary w-full sm:w-auto"><x-icon name="check" /> Simpan Perubahan</button>
                </div>
            </form>
        </section>

        <section data-settings-panel="security" @class(['panel overflow-hidden', 'hidden' => $activeSection !== 'security']) role="tabpanel">
            <div class="border-b border-slate-200 px-5 py-5 sm:px-7">
                <h2 class="text-lg font-extrabold text-slate-900">Keamanan Akun</h2>
                <p class="mt-1 text-sm text-slate-500">Perbarui password secara berkala untuk menjaga keamanan data.</p>
            </div>

            <form method="POST" action="{{ route('settings.password') }}" class="p-5 sm:p-7">
                @csrf
                @method('PUT')
                <div class="max-w-2xl space-y-5">
                    <div>
                        <label for="current_password" class="form-label">Password Saat Ini</label>
                        <div class="relative">
                            <input id="current_password" type="password" name="current_password" class="form-input pr-14" required autocomplete="current-password">
                            <button type="button" data-password-toggle class="absolute inset-y-0 right-0 px-4 text-xs font-bold text-[#12347f]">Lihat</button>
                        </div>
                        @error('current_password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="password" class="form-label">Password Baru</label>
                            <div class="relative">
                                <input id="password" type="password" name="password" class="form-input pr-14" minlength="8" required autocomplete="new-password">
                                <button type="button" data-password-toggle class="absolute inset-y-0 right-0 px-4 text-xs font-bold text-[#12347f]">Lihat</button>
                            </div>
                            @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                            <div class="relative">
                                <input id="password_confirmation" type="password" name="password_confirmation" class="form-input pr-14" minlength="8" required autocomplete="new-password">
                                <button type="button" data-password-toggle class="absolute inset-y-0 right-0 px-4 text-xs font-bold text-[#12347f]">Lihat</button>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-xs leading-5 text-blue-800">Password minimal 8 karakter. Hindari menggunakan password yang sama dengan akun lain.</div>
                </div>

                <div class="mt-7 flex justify-end border-t border-slate-200 pt-5">
                    <button class="btn-primary w-full sm:w-auto">Perbarui Password</button>
                </div>
            </form>
        </section>

        <section data-settings-panel="preferences" @class(['panel overflow-hidden', 'hidden' => $activeSection !== 'preferences']) role="tabpanel">
            <div class="border-b border-slate-200 px-5 py-5 sm:px-7">
                <h2 class="text-lg font-extrabold text-slate-900">Preferensi Dashboard</h2>
                <p class="mt-1 text-sm text-slate-500">Sesuaikan bahasa, zona waktu, dan notifikasi yang ingin diterima.</p>
            </div>

            <form method="POST" action="{{ route('settings.preferences') }}" class="p-5 sm:p-7">
                @csrf
                @method('PUT')
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="locale" class="form-label">Bahasa</label>
                        <select id="locale" name="locale" class="form-select">
                            <option value="id" @selected(old('locale', $preferences['locale']) === 'id')>Bahasa Indonesia</option>
                            <option value="en" @selected(old('locale', $preferences['locale']) === 'en')>English</option>
                        </select>
                    </div>
                    <div>
                        <label for="timezone" class="form-label">Zona Waktu</label>
                        <select id="timezone" name="timezone" class="form-select">
                            <option value="Asia/Jakarta" @selected(old('timezone', $preferences['timezone']) === 'Asia/Jakarta')>WIB — Jakarta</option>
                            <option value="Asia/Makassar" @selected(old('timezone', $preferences['timezone']) === 'Asia/Makassar')>WITA — Makassar</option>
                            <option value="Asia/Jayapura" @selected(old('timezone', $preferences['timezone']) === 'Asia/Jayapura')>WIT — Jayapura</option>
                        </select>
                    </div>
                </div>

                <div class="mt-7 border-t border-slate-200 pt-6">
                    <h3 class="font-extrabold text-slate-900">Notifikasi</h3>
                    <p class="mt-1 text-sm text-slate-500">Pilih aktivitas yang perlu memberi pemberitahuan kepada Anda.</p>
                    <div class="mt-4 divide-y divide-slate-200 rounded-xl border border-slate-200">
                        @foreach([
                            ['key' => 'email_notifications', 'title' => 'Notifikasi Email', 'text' => 'Kirim ringkasan aktivitas penting ke email admin.'],
                            ['key' => 'booking_notifications', 'title' => 'Booking Baru', 'text' => 'Beritahu saat ada permintaan booking baru.'],
                            ['key' => 'payment_notifications', 'title' => 'Pembayaran Masuk', 'text' => 'Beritahu saat bukti pembayaran perlu diverifikasi.'],
                        ] as $option)
                            <label class="flex cursor-pointer items-center justify-between gap-5 px-4 py-4 sm:px-5">
                                <span>
                                    <span class="block text-sm font-bold text-slate-800">{{ $option['title'] }}</span>
                                    <span class="mt-1 block text-xs leading-5 text-slate-500">{{ $option['text'] }}</span>
                                </span>
                                <input type="hidden" name="{{ $option['key'] }}" value="0">
                                <input type="checkbox" name="{{ $option['key'] }}" value="1" @checked((bool) old($option['key'], $preferences[$option['key']])) class="peer sr-only">
                                <span class="relative h-6 w-11 shrink-0 rounded-full bg-slate-300 transition peer-checked:bg-[#12347f] after:absolute after:left-1 after:top-1 after:h-4 after:w-4 after:rounded-full after:bg-white after:shadow after:transition peer-checked:after:translate-x-5"></span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mt-7 flex justify-end border-t border-slate-200 pt-5">
                    <button class="btn-primary w-full sm:w-auto"><x-icon name="check" /> Simpan Preferensi</button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection
