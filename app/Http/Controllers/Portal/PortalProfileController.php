<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PortalProfileController extends Controller
{
    public function show(): View
    {
        return view('portal.profile.index', ['client' => Auth::guard('client')->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        // Email is intentionally NOT updatable from the portal.
        Auth::guard('client')->user()->update($validated);

        return back()->with('success', 'Profile updated successfully!');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'min:8', 'confirmed'],
        ]);

        $client = Auth::guard('client')->user();

        if (! Hash::check($request->current_password, $client->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $client->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password changed successfully!');
    }
}
