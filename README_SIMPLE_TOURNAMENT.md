# Simple Tournament Update

Fitur baru:
- Route `/tournament`
- Menu dashboard `Turnamen`
- Halaman turnamen simpel tanpa sidebar/card berlebihan
- Ambil 5 soal acak dari bahasa aktif user
- User menjawab semua soal dalam satu halaman
- Skor dihitung otomatis
- Durasi pengerjaan disimpan
- Leaderboard sederhana berdasarkan bahasa aktif
- Progress misi harian `questions_answered` dan `study_minutes` ikut bertambah

File utama:
- `src/routes/web.php`
- `src/app/Http/Controllers/Frontend/LearningDashboardController.php`
- `src/app/Models/TournamentAttempt.php`
- `src/database/migrations/2026_06_17_000020_create_tournament_attempts_table.php`
- `src/resources/views/frontend/learning/tournament.blade.php`
- `src/database/seeders/DashboardSeeder.php`

Setelah extract:
```bash
docker compose exec php php artisan migrate
docker compose exec php php artisan db:seed --class=DashboardSeeder
docker compose exec php php artisan optimize:clear
```

Catatan:
- Turnamen membutuhkan soal yang punya opsi jawaban.
- Soal diambil dari bahasa aktif user.
- Jika belum ada soal, halaman akan menampilkan pesan kosong.
