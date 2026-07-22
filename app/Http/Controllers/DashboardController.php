<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Enums\RoomStatus;
use App\Models\Kost;
use App\Models\Payment;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): View
    {
        $totalRooms = Room::count();
        $occupiedRooms = Room::where('status', RoomStatus::Occupied)->count();
        $availableRooms = Room::where('status', RoomStatus::Available)->count();

        $revenueThisMonth = Payment::where('status', PaymentStatus::Approved)
            ->whereBetween('verified_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount');

        $revenueSeries = collect(range(5, 0))->map(function (int $monthsAgo): array {
            $month = now()->subMonths($monthsAgo);

            return [
                'label' => $month->translatedFormat('M'),
                'value' => (float) Payment::where('status', PaymentStatus::Approved)
                    ->whereBetween('verified_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                    ->sum('amount'),
            ];
        });

        $recentPayments = Payment::with(['booking.user', 'booking.room.kost'])
            ->latest('submitted_at')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'totalKosts' => Kost::count(),
            'totalRooms' => $totalRooms,
            'availableRooms' => $availableRooms,
            'occupiedRooms' => $occupiedRooms,
            'occupancyRate' => $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100) : 0,
            'revenueThisMonth' => $revenueThisMonth,
            'revenueSeries' => $revenueSeries,
            'recentPayments' => $recentPayments,
            'today' => Carbon::now()->locale('id')->translatedFormat('l, d F Y'),
        ]);
    }
}
