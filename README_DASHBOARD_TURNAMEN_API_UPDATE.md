# Dashboard, Turnamen Menu, dan API Update

Perubahan:
- Dashboard utama diubah menjadi lebih mirip halaman saat akun baru memilih bahasa:
  - layout lebih fokus dan centered
  - card bagian dibuat seperti pilihan bahasa
  - ada ringkasan XP, streak, level awal
  - tombol cepat ke Turnamen
- Menu game disederhanakan:
  - Menu `Games` / `Turnamen & Games` dinonaktifkan dari seeder
  - Menu aktif sekarang hanya `Turnamen`
  - Halaman pemilihan mode ada di `/turnamen`
  - `/games` tetap diarahkan ke `/turnamen` agar link lama tidak error
- Halaman `Games` diganti nama/tampilan menjadi `Turnamen`
- Challenge turnamen cepat ada di `/turnamen/cepat`
- Tombol kembali dari turnamen cepat kembali ke halaman Turnamen
- Ditambahkan API internal:
  - `GET /api/turnamen/modes`
  - `GET /api/turnamen/leaderboard`
- Halaman Turnamen memakai API internal tersebut untuk memperbarui mode dan leaderboard.
- Admin panel `GAME CMS -> Game Modes` tetap dipertahankan agar daftar mode tidak statis.

Setelah extract jalankan:
```bash
docker compose exec php php artisan migrate
docker compose exec php php artisan db:seed --class=DashboardSeeder
docker compose exec php php artisan db:seed --class=GameModeSeeder
docker compose exec php php artisan optimize:clear
```

Catatan:
- Jika sebelumnya sudah ada menu `Games` atau `Turnamen & Games`, seeder akan menonaktifkannya.
- Menu yang tampil untuk fitur game sekarang adalah `Turnamen`.
- API yang ditambahkan adalah API internal Laravel, bukan API eksternal, supaya aman dan relevan dengan kebutuhan aplikasi saat ini.
