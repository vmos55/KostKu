<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdatePreferencesRequest;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(Request $request): View
    {
        return view('settings.edit', [
            'user' => $request->user(),
            'preferences' => array_merge([
                'locale' => 'id',
                'timezone' => 'Asia/Jakarta',
                'email_notifications' => true,
                'booking_notifications' => true,
                'payment_notifications' => true,
            ], $request->user()->preferences ?? []),
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->safe()->except('avatar');

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return to_route('settings.edit', ['section' => 'profile'])
            ->with('success', 'Profil admin berhasil diperbarui.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->validated()['password'],
        ]);

        return to_route('settings.edit', ['section' => 'security'])
            ->with('success', 'Password berhasil diperbarui.');
    }

    public function updatePreferences(UpdatePreferencesRequest $request): RedirectResponse
    {
        $preferences = $request->validated();

        foreach (['email_notifications', 'booking_notifications', 'payment_notifications'] as $key) {
            $preferences[$key] = $request->boolean($key);
        }

        $request->user()->update(['preferences' => $preferences]);

        return to_route('settings.edit', ['section' => 'preferences'])
            ->with('success', 'Preferensi berhasil disimpan.');
    }
}
