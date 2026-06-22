<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthPageSetting extends Model
{
    protected $fillable = [
        'page_key',
        'page_name',
        'kicker',
        'title',
        'description',
        'side_badge',
        'side_title',
        'side_description',
        'side_points',
        'identifier_label',
        'name_label',
        'email_label',
        'password_label',
        'captcha_label',
        'submit_label',
        'forgot_password_label',
        'register_prompt',
        'register_link_label',
        'login_prompt',
        'login_link_label',
        'back_home_label',
        'success_message',
        'is_active',
    ];

    protected $casts = [
        'side_points' => 'array',
        'is_active' => 'boolean',
    ];

    public static function fallback(string $pageKey): object
    {
        $fallbacks = [
            'login' => [
                'page_key' => 'login',
                'page_name' => 'Login Page',
                'kicker' => 'Masuk akun',
                'title' => 'Login',
                'description' => 'Masuk dengan akun yang sudah terdaftar.',
                'identifier_label' => 'Email',
                'password_label' => 'Password',
                'captcha_label' => 'Verifikasi',
                'submit_label' => 'Masuk',
                'forgot_password_label' => 'Lupa password?',
                'register_prompt' => 'Belum punya akun?',
                'register_link_label' => 'Daftar',
                'back_home_label' => 'Kembali ke homepage',
                'success_message' => 'Berhasil masuk.',
            ],
            'register' => [
                'page_key' => 'register',
                'page_name' => 'Register Page',
                'kicker' => 'Buat akun',
                'title' => 'Daftar',
                'description' => 'Buat akun baru untuk mulai belajar.',
                'name_label' => 'Nama',
                'email_label' => 'Email',
                'password_label' => 'Password',
                'captcha_label' => 'Verifikasi',
                'submit_label' => 'Daftar',
                'login_prompt' => 'Sudah punya akun?',
                'login_link_label' => 'Login',
                'back_home_label' => 'Kembali ke homepage',
                'success_message' => 'Akun berhasil dibuat dan tersimpan.',
            ],
            'forgot' => [
                'page_key' => 'forgot',
                'page_name' => 'Forgot Password Page',
                'kicker' => 'Reset password',
                'title' => 'Lupa password',
                'description' => 'Masukkan email akun kamu.',
                'email_label' => 'Email',
                'submit_label' => 'Cek Email',
                'login_link_label' => 'Kembali ke login',
                'back_home_label' => 'Kembali ke homepage',
                'success_message' => 'Untuk tahap ini, fitur reset password belum mengirim email. Admin masih bisa mengubah password user dari panel admin.',
            ],
        ];

        return (object) ($fallbacks[$pageKey] ?? $fallbacks['login']);
    }
}
