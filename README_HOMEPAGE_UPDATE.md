# YoLearning Homepage Update

Perubahan pada paket ini:

- Menambahkan landing page YoLearning berdasarkan wireframe.
- Menggunakan tema biru gelap dengan aksen cyan, indigo, violet, dan glassmorphism.
- Menambahkan custom cursor glow kecil seperti kunang-kunang di area gelap.
- Menggunakan struktur Laravel dari project asli.
- Menambahkan controller frontend `HomeController`.
- Mengarahkan route `/` ke halaman frontend baru.

File utama yang berubah/ditambahkan:

```text
src/routes/web.php
src/app/Http/Controllers/Frontend/HomeController.php
src/resources/views/layouts/frontend.blade.php
src/resources/views/frontend/home.blade.php
```

Cara menjalankan mengikuti workflow project Laravel/Docker yang sudah ada pada project asli.
