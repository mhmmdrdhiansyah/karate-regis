<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\UpdateContingentProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('profile.edit', [
            'user' => $user,
            'contingent' => $user->contingent,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('users/avatars', 'public');
        } elseif ($request->boolean('remove_avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = null;
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update kontingen profile information.
     */
    public function updateKontingen(UpdateContingentProfileRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $contingent = $request->user()->contingent;

        if (!$contingent) {
            return Redirect::route('profile.edit')
                ->with('error', 'Data kontingen tidak ditemukan');
        }

        if ($request->hasFile('photo')) {
            if ($contingent->photo) {
                Storage::disk('public')->delete($contingent->photo);
            }
            $validated['photo'] = $request->file('photo')->store('contingents/photos', 'public');
        } elseif ($request->boolean('remove_photo')) {
            if ($contingent->photo) {
                Storage::disk('public')->delete($contingent->photo);
            }
            $validated['photo'] = null;
        }

        $contingent->update($validated);

        return Redirect::route('profile.edit')
            ->with('status', 'kontingen-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
