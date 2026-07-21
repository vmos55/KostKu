<?php

namespace App\Http\Controllers;

use App\Enums\RoomStatus;
use App\Enums\TenantStatus;
use App\Enums\UserRole;
use App\Http\Requests\StoreTenantRequest;
use App\Http\Requests\UpdateTenantRequest;
use App\Models\Kost;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $tenants = Tenant::with(['user', 'room.kost'])
            ->when($request->string('search')->toString(), fn ($query, $search) => $query->whereHas('user', fn ($user) => $user
                ->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%")))
            ->when($request->integer('kost_id'), fn ($query, $kostId) => $query->whereHas('room', fn ($room) => $room->where('kost_id', $kostId)))
            ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))
            ->latest('check_in')->paginate(10)->withQueryString();

        return view('tenants.index', ['tenants' => $tenants, 'kosts' => Kost::orderBy('name')->get()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('tenants.form', $this->formData());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTenantRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $tenant = Tenant::create($request->validated());
            $tenant->room->update(['status' => $tenant->status === TenantStatus::Active ? RoomStatus::Occupied : RoomStatus::Available]);
        });

        return redirect()->route('tenants.index')->with('success', 'Penyewa berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Tenant $tenant): View
    {
        $tenant->load(['user', 'room.kost', 'booking.payments']);

        return view('tenants.show', compact('tenant'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tenant $tenant): View
    {
        return view('tenants.form', [...$this->formData(), 'tenant' => $tenant]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTenantRequest $request, Tenant $tenant): RedirectResponse
    {
        DB::transaction(function () use ($request, $tenant): void {
            $oldRoom = $tenant->room;
            $tenant->update($request->validated());

            if ($oldRoom->isNot($tenant->room)) {
                $oldRoom->update(['status' => RoomStatus::Available]);
            }

            $tenant->room->update(['status' => $tenant->status === TenantStatus::Active ? RoomStatus::Occupied : RoomStatus::Available]);
        });

        return redirect()->route('tenants.index')->with('success', 'Data penyewa berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tenant $tenant): RedirectResponse
    {
        DB::transaction(function () use ($tenant): void {
            $tenant->room->update(['status' => RoomStatus::Available]);
            $tenant->delete();
        });

        return back()->with('success', 'Data penyewa berhasil dihapus.');
    }

    private function formData(): array
    {
        return [
            'users' => User::where('role', UserRole::Tenant)->orderBy('name')->get(),
            'rooms' => Room::with('kost')->orderBy('kost_id')->orderBy('room_number')->get(),
        ];
    }
}
