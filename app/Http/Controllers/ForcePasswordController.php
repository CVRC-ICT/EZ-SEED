<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ForcePasswordController extends Controller
{
    public function edit(Request $request)
    {
        // Nothing to do if this account isn't actually flagged.
        if (! $request->user()->must_change_password) {
            return redirect()->route('dashboard');
        }

        return view('auth.force-password-change');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'          => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password'              => Hash::make($validated['password']),
            'must_change_password'  => false,
        ]);

        return redirect()->route('dashboard')->with('success', 'Password updated. Welcome to EZ-Seed.');
    }
}
