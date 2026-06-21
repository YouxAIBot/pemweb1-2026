# Games Dashboard CMS Update

Fitur baru:
- Halaman `/games` sebagai dashboard pemilihan mode game.
- Menu dashboard diubah menjadi `Games` dan diarahkan ke `/games`.
- Daftar game tidak statis: semua mode game diambil dari tabel `game_modes`.
- Admin panel baru: `GAME CMS -> Game Modes`.
- Admin bisa mengatur:
  - Judul game
  - Subtitle
  - Deskripsi
  - Icon
  - Route name
  - Label tombol
  - Status: Aktif / Segera Hadir / Terkunci
  - Urutan
  - Tampil atau tidak
- Mode default:
  - Turnamen
  - Duel 1 vs 1
  - Quiz Room seperti Kahoot
  - Video Question
  - Daily Boss
- Turnamen yang sudah ada tetap dipertahankan.
- Tombol kembali di halaman turnamen diarahkan ke Games.

File utama:
- `src/database/migrations/2026_06_17_000021_create_game_modes_table.php`
- `src/app/Models/GameMode.php`
- `src/database/seeders/GameModeSeeder.php`
- `src/app/Filament/Admin/Resources/GameModeResource.php`
- `src/resources/views/frontend/learning/games.blade.php`
- `src/routes/web.php`
- `src/app/Http/Controllers/Frontend/LearningDashboardController.php`
- `src/database/seeders/DashboardSeeder.php`

Setelah extract:
```bash
docker compose exec php php artisan migrate
docker compose exec php php artisan db:seed --class=DashboardSeeder
docker compose exec php php artisan db:seed --class=GameModeSeeder
docker compose exec php php artisan optimize:clear
```

Catatan:
- Hanya Turnamen yang sudah playable saat ini.
- Duel 1 vs 1, Quiz Room, Video Question, dan Daily Boss disiapkan sebagai mode dinamis berstatus Segera Hadir.
- Untuk membuat mode baru tampil di frontend, admin cukup tambah data di GAME CMS -> Game Modes.
