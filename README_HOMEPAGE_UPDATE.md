# YoLearning Homepage Revision

Revisi ini mengikuti koreksi desain terbaru:

- Navbar dibuat lebih simpel, modern, dan rapi.
- Tombol dibuat lebih polos/clean; tidak lagi memakai gradient yang terlalu ramai.
- Tombol `Daftar Sekarang` pada section bahasa dihapus.
- Kartu bahasa memiliki animasi scroll: saat section didekati kartu menyebar, saat dijauhi kartu kembali menyatu.
- Teks section bahasa muncul dari kiri saat masuk viewport.
- Setiap section memakai animasi reveal/hide yang reversible menggunakan IntersectionObserver.
- Visual tournament diganti menjadi preview podium yang lebih gelap, clean, dan tidak norak.
- Section CTA bawah dibuat lebih subtle dan menyatu dengan tema dark-blue.
- Font diganti ke Manrope agar terasa lebih modern, bersih, dan mudah dibaca.
- Custom cursor glow dibuat lebih kecil dan halus seperti kunang-kunang.

File utama yang berubah:

```text
src/resources/views/layouts/frontend.blade.php
src/resources/views/frontend/home.blade.php
```

File struktur Laravel lainnya tetap mengikuti project asli.

## Auth Page Compact Revision

Halaman `/login`, `/register`, dan `/forgot-password` sudah dibuat lebih minimal:

- Navbar dan footer disembunyikan khusus halaman auth.
- Panel kanan informasi dihapus agar fokus ke form.
- Background dibuat polos dark-blue.
- Ukuran card, judul, input, captcha, dan tombol diperkecil supaya nyaman di zoom 100%.
- Teks default login/register dibuat lebih singkat, tetap bisa diedit dari admin panel `AUTH PAGES`.
- Register tetap menyimpan user ke tabel `users`.
