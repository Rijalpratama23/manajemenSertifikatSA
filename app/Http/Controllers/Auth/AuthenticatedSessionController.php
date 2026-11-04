<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->onlyInput('username')
                ->with('auth_notice', [
                    'type' => 'error',
                    'message' => 'Lengkapi username dan password dengan benar.',
                ]);
        }

        $credentials = $validator->validated();

        if (! Auth::guard('web')->attempt($credentials)) {
            return back()
                ->withErrors(['username' => 'Username atau password yang Anda masukkan salah.'])
                ->onlyInput('username')
                ->with('auth_notice', [
                    'type' => 'error',
                    'message' => 'Login gagal. Periksa kembali username dan password Anda.',
                ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))
            ->with('auth_notice', [
                'type' => 'success',
                'message' => 'Login berhasil. Selamat datang kembali.',
            ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('auth_notice', [
                'type' => 'success',
                'message' => 'Anda berhasil keluar dari akun.',
            ]);
    }
}
