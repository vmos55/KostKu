<?php

namespace App\Http\Controllers;

use App\Enums\TenantStatus;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Models\Kost;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $rooms = Room::with(['kost', 'currentTenant.user'])
            ->when($request->string('search')->toString(), fn ($query, $search) => $query->where(fn ($inner) => $inner
                ->where('room_number', 'like', "%{$search}%")
                ->orWhereHas('kost', fn ($kost) => $kost->where('name', 'like', "%{$search}%"))
                ->orWhereHas('currentTenant.user', fn ($user) => $user->where('name', 'like', "%{$search}%"))))
            ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))
            ->when($request->integer('kost_id'), fn ($query, $kostId) => $query->where('kost_id', $kostId))
            ->orderBy('kost_id')->orderBy('room_number')->paginate(12)->withQueryString();

        return view('rooms.index', ['rooms' => $rooms, 'kosts' => Kost::orderBy('name')->get()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('rooms.form', ['kosts' => Kost::orderBy('name')->get()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoomRequest $request): RedirectResponse
    {
        Room::create($request->validated());

        return redirect()->route('rooms.index')->with('success', 'Kamar berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Room $room)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Room $room): View
    {
        return view('rooms.form', ['room' => $room, 'kosts' => Kost::orderBy('name')->get()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoomRequest $request, Room $room): RedirectResponse
    {
        $room->update($request->validated());

        return redirect()->route('rooms.index')->with('success', 'Kamar berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room): RedirectResponse
    {
        if ($room->tenants()->where('status', TenantStatus::Active)->exists()) {
            return back()->with('error', 'Kamar yang sedang dihuni tidak dapat dihapus.');
        }

        $room->delete();

        return back()->with('success', 'Kamar berhasil dihapus.');
    }
}
