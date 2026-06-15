# Fix Homepage Group

Revisi ini mempertahankan dua group di Filament Admin:

- HOMEPAGE
- AUTH PAGES

Perbaikan tambahan:

- Resource HOMEPAGE dipastikan tetap ada.
- Resource HOMEPAGE diberi akses eksplisit agar tidak hilang karena cache/permission Shield saat development.
- Halaman login/register tetap versi compact sesuai revisi terakhir.

Jalankan setelah extract:

```bash
docker compose exec php php artisan optimize:clear
docker compose exec php php artisan migrate
docker compose exec php php artisan db:seed --class=HomepageSeeder
docker compose exec php php artisan db:seed --class=AuthPageSeeder
```
