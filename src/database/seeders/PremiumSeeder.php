<?php

namespace Database\Seeders;

use App\Models\Ad;
use App\Models\PremiumPackage;
use Illuminate\Database\Seeder;

class PremiumSeeder extends Seeder
{
    public function run(): void
    {
        PremiumPackage::updateOrCreate([
            'slug' => 'premium-bulanan',
        ], [
            'name' => 'Premium Bulanan',
            'description' => 'Belajar lebih nyaman tanpa iklan saat masuk dan keluar level.',
            'price' => 25000,
            'duration_days' => 30,
            'benefits' => [
                'Bebas iklan 15 detik sebelum dan setelah level',
                'Akses level yang ditandai premium',
                'Pengalaman belajar lebih fokus',
                'Riwayat pembayaran tersimpan',
            ],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Ad::updateOrCreate([
            'placement' => 'level_entry',
            'title' => 'Mulai belajar tanpa jeda',
        ], [
            'description' => 'Upgrade ke Premium untuk membuka level tanpa iklan.',
            'duration_seconds' => 15,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Ad::updateOrCreate([
            'placement' => 'level_exit',
            'title' => 'Progress kamu sudah bagus',
        ], [
            'description' => 'Premium membuat sesi berikutnya lebih fokus tanpa iklan.',
            'duration_seconds' => 15,
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }
}
