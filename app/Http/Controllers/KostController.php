<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKostRequest;
use App\Http\Requests\UpdateKostRequest;
use App\Models\Kost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class KostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $kosts = Kost::query()->withCount('rooms')
            ->when($request->string('search')->toString(), fn ($query, $search) => $query
                ->where(fn ($inner) => $inner->where('name', 'like', "%{$search}%")->orWhere('city', 'like', "%{$search}%")))
            ->latest()->paginate(8)->withQueryString();

        return view('kosts.index', compact('kosts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('kosts.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreKostRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['code'] = 'KST-'.str_pad((string) ((Kost::withTrashed()->max('id') ?? 0) + 1), 3, '0', STR_PAD_LEFT);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('kosts', 'public');
        }

        Kost::create($data);

        return redirect()->route('kosts.index')->with('success', 'Data kost berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Kost $kost): View
    {
        $kost->load(['rooms.currentTenant.user']);

        return view('kosts.show', compact('kost'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kost $kost): View
    {
        return view('kosts.form', compact('kost'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateKostRequest $request, Kost $kost): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($kost->image);
            $data['image'] = $request->file('image')->store('kosts', 'public');
        }

        $kost->update($data);

        return redirect()->route('kosts.index')->with('success', 'Data kost berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kost $kost): RedirectResponse
    {
        if ($kost->rooms()->exists()) {
            return back()->with('error', 'Kost tidak dapat dihapus karena masih memiliki kamar.');
        }

        Storage::disk('public')->delete($kost->image);
        $kost->delete();

        return back()->with('success', 'Data kost berhasil dihapus.');
    }
}
