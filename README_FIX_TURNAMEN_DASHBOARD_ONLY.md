# Fix Turnamen Dashboard Only

Perbaikan:
- Dashboard utama belajar dikembalikan ke tampilan semula.
- Yang diubah menjadi mirip halaman pilih bahasa/onboarding adalah halaman menu Turnamen saja.
- Halaman menu Turnamen tetap berada di `/turnamen`.
- `/games` tetap redirect ke `/turnamen` agar link lama aman.
- Menu tetap satu nama: `Turnamen`.
- API internal tetap dipertahankan:
  - `GET /api/turnamen/modes`
  - `GET /api/turnamen/leaderboard`
- Admin panel `GAME CMS -> Game Modes` tetap terhubung.

Setelah extract:
```bash
docker compose exec php php artisan migrate
docker compose exec php php artisan db:seed --class=DashboardSeeder
docker compose exec php php artisan db:seed --class=GameModeSeeder
docker compose exec php php artisan optimize:clear
```
