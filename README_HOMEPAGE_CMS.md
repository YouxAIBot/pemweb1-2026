# YoLearning Homepage CMS Update

Update ini menghubungkan homepage frontend dengan Filament Admin Panel agar konten bisa dikelola secara dinamis.

## Menu Admin Baru

Di `/admin` akan muncul navigation group:

```text
HOMEPAGE
├── Pengaturan Umum
├── Navbar Menu
└── Sections
```

## Fungsi Menu

### Pengaturan Umum
Mengatur:

- nama website
- teks brand navbar
- inisial/logo brand
- meta title
- meta description
- footer kiri
- footer kanan
- cursor glow aktif/nonaktif
- ukuran cursor glow

### Navbar Menu
Mengatur:

- label menu
- URL menu
- style menu: link, soft, primary, ghost
- urutan menu
- status aktif/tidak aktif

### Sections
Mengatur section homepage:

- Section 1 - Hero
- Section 2 - Pilih Bahasa
- Section 3 - Tournament
- Section 4 - CTA

Setiap section bisa mengatur:

- label kecil/kicker
- judul utama
- deskripsi
- tombol utama
- tombol kedua
- gambar section
- status aktif/tidak aktif
- item section seperti kartu bahasa dan fitur tournament

## File Baru / Diubah

```text
src/app/Models/HomepageSetting.php
src/app/Models/HomepageNavItem.php
src/app/Models/HomepageSection.php
src/app/Models/HomepageSectionItem.php

src/database/migrations/2026_06_15_000001_create_homepage_settings_table.php
src/database/migrations/2026_06_15_000002_create_homepage_nav_items_table.php
src/database/migrations/2026_06_15_000003_create_homepage_sections_table.php
src/database/migrations/2026_06_15_000004_create_homepage_section_items_table.php

src/database/seeders/HomepageSeeder.php
src/database/seeders/DatabaseSeeder.php

src/app/Filament/Admin/Resources/HomepageSettingResource.php
src/app/Filament/Admin/Resources/HomepageNavItemResource.php
src/app/Filament/Admin/Resources/HomepageSectionResource.php

src/app/Http/Controllers/Frontend/HomeController.php
src/app/Providers/AppServiceProvider.php
src/app/Providers/Filament/AdminPanelProvider.php
src/resources/views/layouts/frontend.blade.php
src/resources/views/frontend/home.blade.php
```

## Cara Menjalankan

Dari root project:

```bash
docker compose up -d --build
```

Jalankan migration dan seeder dari container PHP:

```bash
docker compose exec php php artisan migrate
docker compose exec php php artisan db:seed --class=HomepageSeeder
```

Buat storage link untuk upload gambar:

```bash
docker compose exec php php artisan storage:link
```

Clear cache:

```bash
docker compose exec php php artisan optimize:clear
```

Build asset:

```bash
cd src
npm run build
```

Buka:

```text
http://pemweb1.test
```

Admin:

```text
http://pemweb1.test/admin
```

Default login dari starter:

```text
admin@admin.com
password
```
