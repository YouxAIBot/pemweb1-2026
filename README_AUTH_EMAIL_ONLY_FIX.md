# Auth Email Only Fix

Masalah:
- Login sebelumnya bisa memakai nama atau email.
- Karena nama user tidak unik, login memakai nama bisa mengambil akun yang salah.
- Ini bisa terlihat seperti akun baru tiba-tiba berubah menjadi super admin.

Perbaikan:
- Login sekarang wajib memakai email.
- Nama tidak lagi dipakai untuk autentikasi.
- Pesan login diubah menjadi Email.
- Jika user sudah login lalu membuka `/login` atau `/register`, akan diarahkan ke welcome.
- Saat login, session lama dibersihkan dulu sebelum attempt.
- Saat register, session lama dibersihkan dulu lalu login sebagai user baru.
- User baru tetap diberi role `user`.

Setelah extract:
```bash
docker compose exec php php artisan db:seed --class=AuthPageSeeder
docker compose exec php php artisan optimize:clear
```

Saran testing:
1. Logout total: buka `/logout`
2. Clear cookie pemweb1.test atau pakai incognito
3. Login selalu pakai email, bukan nama.
