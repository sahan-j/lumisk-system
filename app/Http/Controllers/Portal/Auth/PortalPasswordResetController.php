<?php

namespace App\Http\Controllers\Portal\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PortalPasswordResetMail;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PortalPasswordResetController extends Controller
{
    public function showForgotForm(): View
    {
        return view('portal.auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email']);

        $client = Client::where('email', $request->email)
            ->where('portal_enabled', true)
            ->first();

        $message = 'If this email exists in our system, you will receive a reset link shortly.';

        if ($client) {
            $token = Str::random(64);

            DB::table('portal_password_resets')->where('email', $client->email)->delete();
            DB::table('portal_password_resets')->insert([
                'email'      => $client->email,
                'token'      => $token,
                'created_at' => now(),
            ]);

            Mail::to($client->email)->send(new PortalPasswordResetMail($client, $token));
        }

        return back()->with('status', $message);
    }

    public function showResetForm(Request $request, string $token): View
    {
        return view('portal.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $record = DB::table('portal_password_resets')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (! $record) {
            return back()->withErrors(['email' => 'Invalid or expired reset link.']);
        }

        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('portal_password_resets')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'Reset link has expired. Please request a new one.']);
        }

        $client = Client::where('email', $request->email)->first();

        if ($client) {
            $client->update(['password' => Hash::make($request->password)]);
        }

        DB::table('portal_password_resets')->where('email', $request->email)->delete();

        return redirect()->route('portal.login')->with('status', 'Password reset successfully! Please login.');
    }
}
