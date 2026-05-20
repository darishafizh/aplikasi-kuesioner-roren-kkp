<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Maximum login attempts before lockout.
     */
    protected int $maxAttempts = 5;

    /**
     * Lockout duration in seconds (60 = 1 menit).
     */
    protected int $decaySeconds = 60;

    public function showLoginForm(Request $request)
    {
        if ($request->has('tahun')) {
            session(['selected_tahun' => $request->query('tahun')]);
        }
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        session(['captcha_answer' => $num1 + $num2]);
        $captcha_question = "$num1 + $num2 = ?";
        return view('auth.login', compact('captcha_question'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_.-]+$/'],
            'password' => ['required', 'string', 'min:6'],
            'captcha'  => ['required', 'numeric'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.regex' => 'Format username tidak valid. Gunakan huruf, angka, titik, atau underscore.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal terdiri dari 6 karakter.',
            'captcha.required' => 'Jawaban CAPTCHA wajib diisi.',
            'captcha.numeric' => 'Jawaban CAPTCHA harus berupa angka.',
        ]);

        if ($request->input('captcha') != session('captcha_answer')) {
            return back()->withErrors(['captcha' => 'Jawaban CAPTCHA salah.'])->onlyInput('username');
        }

        // Rate limiting: check if too many attempts
        $throttleKey = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, $this->maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'username' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ])->onlyInput('username');
        }

        // Cari user dengan username yang case-sensitive (menggunakan BINARY comparison)
        $user = User::where('username', $credentials['username'])->first();

        // Jika username tidak ditemukan
        if (!$user) {
            RateLimiter::hit($throttleKey, $this->decaySeconds);
            $attemptsLeft = RateLimiter::remaining($throttleKey, $this->maxAttempts);

            return back()->withErrors([
                'username' => "Username tidak ditemukan. Sisa percobaan: {$attemptsLeft}.",
            ])->onlyInput('username');
        }

        // Verifikasi password cocok
        if (Hash::check($credentials['password'], $user->password)) {
            RateLimiter::clear($throttleKey);
            Auth::login($user);
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard.index'));
        }

        // Password salah
        RateLimiter::hit($throttleKey, $this->decaySeconds);
        $attemptsLeft = RateLimiter::remaining($throttleKey, $this->maxAttempts);

        return back()->withErrors([
            'password' => "Password yang Anda masukkan salah. Sisa percobaan: {$attemptsLeft}.",
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        $selectedTahun = session('selected_tahun');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($selectedTahun) {
            return redirect()->route('login', ['tahun' => $selectedTahun]);
        }
        return redirect()->route('login');
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(
            Str::lower($request->input('username')) . '|' . $request->ip()
        );
    }
}
