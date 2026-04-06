<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\FirstPasswordChangeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PasswordFirstChangeController extends Controller
{
    public function show(): View|RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if (! Auth::user()->must_change_password) {
            return redirect()->intended(route('admin.dashboard.index'));
        }

        return view('auth.first-password');
    }

    public function update(FirstPasswordChangeRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $user->password = $request->input('password');
        $user->must_change_password = false;
        $user->save();

        $request->session()->regenerate();

        return redirect()->route('admin.dashboard.index')->with('success', 'Your password has been updated.');
    }
}
