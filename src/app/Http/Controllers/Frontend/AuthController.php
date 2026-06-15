<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\AuthPageSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(Request $request): View|RedirectResponse
    {
        $this->prepareCaptcha($request, 'login');

        return view('frontend.auth.login', [
            'authSetting' => $this->authSetting('login'),
            'captchaQuestion' => $request->session()->get('captcha_login_question'),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $this->validateCaptcha($request, 'login');

        $credentials = $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ], [
            'identifier.required' => 'Nama atau email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $identifier = trim($credentials['identifier']);
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        if (! Auth::attempt([$field => $identifier, 'password' => $credentials['password']])) {
            $this->prepareCaptcha($request, 'login', force: true);

            throw ValidationException::withMessages([
                'identifier' => 'Nama/email atau password tidak cocok.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->forget(['captcha_login_question', 'captcha_login_answer']);

        $setting = $this->authSetting('login');

        return redirect()
            ->route('learning.welcome')
            ->with('auth_success', $setting->success_message ?? 'Berhasil masuk.');
    }

    public function showRegister(Request $request): View|RedirectResponse
    {
        $this->prepareCaptcha($request, 'register');

        return view('frontend.auth.register', [
            'authSetting' => $this->authSetting('register'),
            'captchaQuestion' => $request->session()->get('captcha_register_question'),
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $this->validateCaptcha($request, 'register');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar. Silakan login.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        try {
            if (method_exists($user, 'assignRole')) {
                $user->assignRole('user');
            }
        } catch (\Throwable) {
            // Role seeding may not have run yet. The account is still stored safely.
        }

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget(['captcha_register_question', 'captcha_register_answer']);

        $setting = $this->authSetting('register');

        return redirect()
            ->route('learning.welcome')
            ->with('auth_success', $setting->success_message ?? 'Akun berhasil dibuat.');
    }

    public function showForgotPassword(): View
    {
        return view('frontend.auth.forgot-password', [
            'authSetting' => $this->authSetting('forgot'),
        ]);
    }

    public function forgotPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $setting = $this->authSetting('forgot');

        return back()->with('auth_success', $setting->success_message ?? 'Untuk tahap ini, fitur reset password belum mengirim email. Admin masih bisa mengubah password user dari panel admin.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function authSetting(string $pageKey): object
    {
        try {
            if (Schema::hasTable('auth_page_settings')) {
                $setting = AuthPageSetting::query()
                    ->where('page_key', $pageKey)
                    ->where('is_active', true)
                    ->first();

                if ($setting) {
                    return $setting;
                }
            }
        } catch (\Throwable) {
            // Keep frontend usable before migrations run.
        }

        return AuthPageSetting::fallback($pageKey);
    }

    private function prepareCaptcha(Request $request, string $type, bool $force = false): void
    {
        $questionKey = "captcha_{$type}_question";
        $answerKey = "captcha_{$type}_answer";

        if (! $force && $request->session()->has($questionKey) && $request->session()->has($answerKey)) {
            return;
        }

        $firstNumber = random_int(1, 5);
        $secondNumber = random_int(1, 5);

        $request->session()->put($questionKey, "{$firstNumber} + {$secondNumber}");
        $request->session()->put($answerKey, $firstNumber + $secondNumber);
    }

    private function validateCaptcha(Request $request, string $type): void
    {
        $answerKey = "captcha_{$type}_answer";
        $expected = (int) $request->session()->get($answerKey);

        $request->validate([
            'captcha_answer' => ['required', 'integer'],
        ], [
            'captcha_answer.required' => 'Jawaban verifikasi wajib diisi.',
            'captcha_answer.integer' => 'Jawaban verifikasi harus berupa angka.',
        ]);

        if ((int) $request->input('captcha_answer') !== $expected) {
            $this->prepareCaptcha($request, $type, force: true);

            throw ValidationException::withMessages([
                'captcha_answer' => 'Jawaban penjumlahan tidak sesuai. Coba lagi.',
            ]);
        }
    }
}
