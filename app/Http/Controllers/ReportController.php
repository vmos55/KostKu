<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Enums\RoomStatus;
use App\Enums\TenantStatus;
use App\Models\Booking;
use App\Models\Kost;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Tenant;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        return view('reports.index', $this->reportData($request));
    }

    public function pdf(Request $request): View
    {
        return view('reports.pdf', $this->reportData($request));
    }

    public function excel(Request $request): StreamedResponse
    {
        $data = $this->reportData($request);

        return response()->streamDownload(function () use ($data): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Laporan KostKu', $data['start']->format('d/m/Y').' - '.$data['end']->format('d/m/Y')]);
            fputcsv($handle, []);
            fputcsv($handle, ['Properti', 'Lokasi', 'Jumlah Kamar', 'Pendapatan', 'Okupansi']);

            foreach ($data['properties'] as $property) {
                fputcsv($handle, [$property->name, $property->city, $property->rooms_count, $property->revenue, $property->occupancy.'%']);
            }

            fclose($handle);
        }, 'laporan-kostku-'.$data['end']->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    /** @return array<string, mixed> */
    private function reportData(Request $request): array
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $start = isset($validated['start_date']) ? Carbon::parse($validated['start_date'])->startOfDay() : now()->subMonths(5)->startOfMonth();
        $end = isset($validated['end_date']) ? Carbon::parse($validated['end_date'])->endOfDay() : now()->endOfMonth();
        $totalRooms = Room::count();
        $occupiedRooms = Room::where('status', RoomStatus::Occupied)->count();

        $monthlyRevenue = collect(CarbonPeriod::create($start->copy()->startOfMonth(), '1 month', $end->copy()->startOfMonth()))
            ->map(fn (Carbon $month): array => [
                'label' => $month->translatedFormat('M Y'),
                'value' => (float) Payment::where('status', PaymentStatus::Approved)
                    ->whereBetween('verified_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                    ->sum('amount'),
            ])->values();

        $properties = Kost::withCount(['rooms', 'rooms as occupied_rooms_count' => fn ($query) => $query->where('status', RoomStatus::Occupied)])
            ->orderBy('name')->get()->map(function (Kost $kost) use ($start, $end): Kost {
                $kost->revenue = (float) Payment::where('status', PaymentStatus::Approved)
                    ->whereBetween('verified_at', [$start, $end])
                    ->whereHas('booking.room', fn ($query) => $query->where('kost_id', $kost->id))->sum('amount');
                $kost->occupancy = $kost->rooms_count > 0 ? round(($kost->occupied_rooms_count / $kost->rooms_count) * 100) : 0;

                return $kost;
            });

        return [
            'start' => $start,
            'end' => $end,
            'totalRevenue' => Payment::where('status', PaymentStatus::Approved)->whereBetween('verified_at', [$start, $end])->sum('amount'),
            'totalBookings' => Booking::whereBetween('booking_date', [$start, $end])->count(),
            'activeTenants' => Tenant::where('status', TenantStatus::Active)->count(),
            'occupancyRate' => $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100) : 0,
            'monthlyRevenue' => $monthlyRevenue,
            'properties' => $properties,
        ];
    }
}
