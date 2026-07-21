<?php

namespace App\Http\Controllers\Api;

use App\Enums\KostStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\KostResource;
use App\Models\Kost;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class KostController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $kosts = Kost::query()
            ->where('status', KostStatus::Active)
            ->with(['rooms' => fn ($query) => $query->orderBy('room_number')])
            ->orderBy('name')
            ->get();

        return KostResource::collection($kosts);
    }

    public function show(Kost $kost): JsonResource
    {
        abort_unless($kost->status === KostStatus::Active, 404);

        $kost->load(['rooms' => fn ($query) => $query->orderBy('room_number')]);

        return new KostResource($kost);
    }
}
