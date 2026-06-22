<?php

namespace Database\Seeders;

use App\Models\AuthPageSetting;
use Illuminate\Database\Seeder;

class AuthPageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            'login' => [
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
                'is_active' => true,
            ],
            'register' => [
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
                'is_active' => true,
            ],
            'forgot' => [
                'page_name' => 'Forgot Password Page',
                'kicker' => 'Reset password',
                'title' => 'Lupa password',
                'description' => 'Masukkan email akun kamu.',
                'email_label' => 'Email',
                'submit_label' => 'Cek Email',
                'login_link_label' => 'Kembali ke login',
                'back_home_label' => 'Kembali ke homepage',
                'success_message' => 'Untuk tahap ini, fitur reset password belum mengirim email. Admin masih bisa mengubah password user dari panel admin.',
                'is_active' => true,
            ],
        ];

        foreach ($pages as $pageKey => $data) {
            AuthPageSetting::updateOrCreate([
                'page_key' => $pageKey,
            ], $data);
        }
    }
}
