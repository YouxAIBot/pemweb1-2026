# Update Login & Register YoLearning

Update ini menambahkan halaman autentikasi frontend dengan tema yang sama seperti homepage YoLearning.

## Route baru

- `GET /login` menampilkan halaman login frontend
- `POST /login` memproses login dengan nama/email + password + verifikasi penjumlahan
- `GET /register` menampilkan halaman register frontend
- `POST /register` menyimpan akun baru ke table `users`
- `GET /forgot-password` menampilkan halaman forgot password sederhana
- `POST /forgot-password` validasi email awal
- `POST /logout` logout user

## Admin Panel

Di `/admin` ditambahkan group baru:

```text
AUTH PAGES
└── Login & Register Text
```

Admin dapat mengubah teks halaman login dan register:

- judul
- deskripsi
- label input
- label captcha penjumlahan
- teks tombol
- teks link forgot password/register/login
- teks card informasi samping
- pesan sukses

## Database

Akun register masuk ke table Laravel bawaan:

```text
users
```

Teks login/register masuk ke table:

```text
auth_page_settings
```

## Command setelah extract

```bash
docker compose exec php php artisan migrate

docker compose exec php php artisan db:seed --class=AuthPageSeeder

docker compose exec php php artisan db:seed --class=HomepageSeeder

docker compose exec php php artisan optimize:clear

cd src
npm run build
```

`HomepageSeeder` dijalankan ulang supaya tombol navbar default berubah menjadi:

- Daftar -> `/register`
- Login -> `/login`
