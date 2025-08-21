<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordChangeController extends Controller
{
    public function show()
    {
        return Inertia::render('auth/ChangePassword');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => ['required','confirmed','min:8'],
        ]);

        $user = $request->user();
        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
            'password_expires_at' => null,
        ]);

        return redirect()->route('home')->with('success', 'Password changed successfully.');
    }
}
