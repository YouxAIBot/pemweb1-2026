# Daily Progress & Level Unlock Revision

Revisi ini menjaga fitur lama tetap ada dan menambahkan logika core learning berikut:

## Yang Ditambahkan

- Progress misi harian sekarang per user, bukan data global/static.
- Tabel baru `user_daily_mission_progress` menyimpan progres misi harian berdasarkan `user_id` dan tanggal.
- Template misi harian `dashboard_daily_missions` sekarang punya `mission_type`:
  - `questions_answered`
  - `study_minutes`
  - `levels_completed`
- Setelah user menyelesaikan level:
  - Level saat ini menjadi `completed`.
  - Level berikutnya otomatis menjadi `available`.
  - XP user bertambah sekali untuk level tersebut.
  - Progress misi harian user bertambah sesuai aktivitas.
- Toast notifikasi kanan bawah sekarang auto-hide.
- Welcome animation dibuat lebih ringan dengan mengurangi animasi blur/filter.

## Route Baru

```text
POST /dashboard/parts/{part}/levels/{level}/complete
```

Route ini dipakai tombol `Selesaikan Level` di halaman detail level.

## Jalankan Setelah Extract

```bash
docker compose exec php php artisan migrate
docker compose exec php php artisan db:seed --class=DashboardSeeder
docker compose exec php php artisan optimize:clear
```

Seeder `DashboardSeeder` diperbarui agar misi default dimulai dari `0`, bukan progress dummy.

## Catatan

Konten belajar tetap global dan dikontrol admin lewat Filament. Progress belajar tetap private per akun lewat `user_id`.
