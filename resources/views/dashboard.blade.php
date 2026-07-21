@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="mb-7 flex flex-col justify-between gap-5 sm:mb-10 md:flex-row md:items-center">
    <div><h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl lg:text-4xl">Selamat Datang, Admin KostKu</h1><p class="mt-3 flex items-center gap-2 text-sm text-slate-500 sm:text-base"><x-icon name="calendar" class="h-5 w-5 shrink-0"/> {{ $today }}</p></div>
    <a href="{{ route('kosts.create') }}" class="btn-primary w-full sm:w-auto"><x-icon name="plus" class="h-5 w-5"/> Tambah Kost</a>
</div>

<div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
    @foreach([
        ['label' => 'Total Kost', 'value' => $totalKosts.' Properti', 'tag' => 'TOTAL', 'icon' => 'building', 'green' => false],
        ['label' => 'Total Kamar', 'value' => $totalRooms, 'tag' => 'KAPASITAS', 'icon' => 'bed', 'green' => false],
        ['label' => 'Kamar Tersedia', 'value' => $availableRooms, 'tag' => 'AVAILABLE', 'icon' => 'home', 'green' => true],
        ['label' => 'Pendapatan Bulan Ini', 'value' => 'Rp'.number_format($revenueThisMonth, 0, ',', '.'), 'tag' => '+12%', 'icon' => 'money', 'green' => false],
    ] as $card)
        <div class="panel p-6">
            <div class="flex items-center justify-between"><span @class(['grid h-11 w-11 place-items-center rounded-lg', 'bg-blue-100 text-[#12347f]' => !$card['green'], 'bg-emerald-300 text-emerald-800' => $card['green']])><x-icon :name="$card['icon']" /></span><span @class(['rounded px-2 py-1 text-[10px] font-extrabold tracking-wider', 'bg-blue-50 text-[#12347f]' => !$card['green'], 'bg-emerald-100 text-emerald-700' => $card['green']])>{{ $card['tag'] }}</span></div>
            <p class="mt-7 text-sm text-slate-500">{{ $card['label'] }}</p><p class="mt-1 text-2xl font-extrabold text-slate-900">{{ $card['value'] }}</p>
        </div>
    @endforeach
</div>

<div class="mt-8 grid gap-6 xl:grid-cols-[1fr_295px]">
    <section class="panel min-w-0 p-4 sm:p-6"><div class="flex items-center justify-between"><h2 class="text-base font-extrabold sm:text-lg">Grafik Pendapatan Bulanan</h2><span class="text-xl text-slate-400">•••</span></div>
        @php $maxRevenue = max(1, $revenueSeries->max('value')); @endphp
        <div class="overflow-x-auto"><div class="mt-7 flex h-64 min-w-[460px] items-end gap-3 border-b border-l border-slate-200 px-4 pt-4 sm:h-72 sm:gap-6 sm:px-5">
            @foreach($revenueSeries as $item)
                <div class="flex h-full flex-1 flex-col items-center justify-end gap-3"><div class="group relative w-full max-w-16 rounded-t bg-[#244393] transition hover:bg-[#102f7d]" style="height: {{ max(8, ($item['value'] / $maxRevenue) * 88) }}%"><span class="absolute -top-7 left-1/2 hidden -translate-x-1/2 whitespace-nowrap rounded bg-slate-900 px-2 py-1 text-[10px] text-white group-hover:block">Rp{{ number_format($item['value']/1000000, 1) }}M</span></div><span class="text-xs text-slate-500">{{ $item['label'] }}</span></div>
            @endforeach
        </div></div>
    </section>
    <section class="panel p-4 sm:p-6"><div class="flex items-center justify-between"><h2 class="text-base font-extrabold sm:text-lg">Okupansi Kamar</h2><span class="text-xl text-slate-400">•••</span></div>
        <div class="mx-auto mt-10 grid h-48 w-48 place-items-center rounded-full" style="background: conic-gradient(#244393 {{ $occupancyRate }}%, #dbe7ff 0)"><div class="grid h-36 w-36 place-items-center rounded-full bg-white text-center"><div><strong class="text-3xl">{{ $occupancyRate }}%</strong><p class="mt-1 text-xs font-semibold text-slate-500">Terisi</p></div></div></div>
        <div class="mt-8 flex justify-center gap-6 text-xs text-slate-600"><span class="flex items-center gap-2"><i class="h-3 w-3 rounded-full bg-[#244393]"></i>Terisi ({{ $occupiedRooms }})</span><span class="flex items-center gap-2"><i class="h-3 w-3 rounded-full bg-blue-100"></i>Kosong ({{ $availableRooms }})</span></div>
    </section>
</div>

<section class="panel mt-8 overflow-hidden"><div class="flex items-center justify-between px-6 py-5"><h2 class="text-lg font-extrabold">Aktivitas Terbaru</h2><a href="{{ route('payments.index') }}" class="text-xs font-bold text-[#12347f]">Lihat Semua</a></div>
    <div class="overflow-x-auto"><table class="w-full"><thead><tr><th class="table-heading">Nama Penyewa</th><th class="table-heading">Kost</th><th class="table-heading">Kamar</th><th class="table-heading">Status</th><th class="table-heading text-right">Tanggal</th></tr></thead><tbody>
    @forelse($recentPayments as $payment)<tr><td class="table-cell font-semibold text-slate-900">{{ $payment->booking->user->name }}</td><td class="table-cell">{{ $payment->booking->room->kost->name }}</td><td class="table-cell">{{ $payment->booking->room->room_number }}</td><td class="table-cell"><span @class(['rounded-md px-2.5 py-1 text-xs font-medium', 'bg-emerald-100 text-emerald-700' => $payment->status->value === 'approved', 'bg-amber-100 text-amber-700' => $payment->status->value === 'pending', 'bg-red-100 text-red-700' => $payment->status->value === 'rejected'])>{{ ucfirst($payment->status->value) }}</span></td><td class="table-cell text-right">{{ optional($payment->submitted_at)->format('d M Y') }}</td></tr>
    @empty<tr><td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500">Belum ada aktivitas pembayaran.</td></tr>@endforelse
    </tbody></table></div>
</section>
@endsection
