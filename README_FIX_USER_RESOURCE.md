# Fix UserResource Tidak Muncul di Admin Panel

Perbaikan ini menjaga fitur HOMEPAGE dan AUTH PAGES tetap ada, lalu memperbaiki menu Users di Filament Admin.

## Masalah

`UserResource.php` sudah ada, tetapi menu Users tidak muncul karena project memakai Filament Shield/Spatie Permission. Policy mengecek permission seperti `view_any_user`, sementara role `super_admin` belum otomatis dianggap boleh untuk semua permission.

## Perbaikan

File yang diubah:

- `src/app/Providers/AppServiceProvider.php`
  - Menambahkan `Gate::before()` agar role `super_admin` lolos semua Gate/Policy.

- `src/app/Models/User.php`
  - Membatasi akses `/admin` hanya untuk role `super_admin`.

- `src/app/Policies/UserPolicy.php`
  - Menambahkan `before()` untuk super admin.
  - Merapikan signature method policy.

- `src/app/Filament/Admin/Resources/UserResource.php`
  - Menambahkan fallback authorization eksplisit.
  - Mengubah group menjadi `USER MANAGEMENT`.
  - Menambahkan kolom list user: nama, email, role, tanggal daftar.

- `src/app/Filament/Admin/Resources/UserResource/Pages/ViewUser.php`
  - Menambahkan halaman view detail user.

- `src/app/Providers/Filament/AdminPanelProvider.php`
  - Mendaftarkan `UserResource` secara eksplisit.

## Jalankan Setelah Extract

```bash
docker compose exec php php artisan optimize:clear
```

Pastikan akun admin punya role `super_admin`:

```bash
docker compose exec php php artisan tinker
```

```php
$user = App\Models\User::where('email', 'admin@admin.com')->first();
$user->assignRole('super_admin');
exit
```

Lalu logout dari `/admin`, login ulang dengan:

```text
admin@admin.com
password
```

Menu harus muncul di:

```text
USER MANAGEMENT → Users
```
