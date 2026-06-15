# YoLearning Learning Dashboard Update

Update ini menambahkan halaman utama setelah login dengan konsep Discord-like dashboard + map level ala learning path.

## Route baru

- `/onboarding` — intro animasi, pilih bahasa, pilih kemampuan.
- `/dashboard` — dashboard utama setelah login.
- `/dashboard/parts/{part}` — peta level/bagian.
- `/dashboard/parts/{part}/levels/{level}` — preview level dan soal.

## Admin panel group baru

Di Filament admin akan muncul:

```text
USER DASHBOARD
├── Dashboard Settings
├── Sidebar Menus
├── Daily Missions
└── User Progress

LEARNING CMS
├── Languages
├── Parts / Bagian
├── Levels
└── Questions
```

## Per-user data

Progress tidak dibuat global. Data belajar tersimpan per akun lewat:

```text
user_learning_profiles
user_level_progress
```

Jadi akun A dan akun B dapat memiliki bahasa, level, XP, streak, dan progress yang berbeda.

## Listening audio

Admin bisa upload audio di:

```text
LEARNING CMS → Questions → Audio Soal / Listening
```

Admin juga bisa upload audio per pilihan jawaban lewat repeater Options.

## Jalankan setelah extract

```bash
docker compose exec php php artisan migrate
docker compose exec php php artisan db:seed --class=DashboardSeeder
docker compose exec php php artisan db:seed --class=LearningCmsSeeder
docker compose exec php php artisan storage:link
docker compose exec php php artisan optimize:clear
docker compose exec php php artisan permission:cache-reset
```

Kalau ingin seed semua default:

```bash
docker compose exec php php artisan db:seed
```

## Catatan

Fitur lama tetap dipertahankan:

- HOMEPAGE CMS
- AUTH PAGES CMS
- Login/Register/Forgot Password
- User Management
- Logout flow
