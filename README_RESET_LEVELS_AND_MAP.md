# Reset Levels and Map Revision

Perubahan:
- Peta level dikembalikan mengikuti posisi `position_x` dan `position_y` dari admin tanpa offset tambahan.
- Level sekarang diperlakukan sebagai wadah/kumpulan soal, bukan pemaksa jenis soal.
- Default mode level baru menjadi `Mix`, supaya 1 level bisa berisi banyak jenis soal.
- Kolom Type/Mode pada daftar Learning Levels disembunyikan secara default agar tidak mengganggu fokus pembuatan level.
- Seeder default `LearningCmsSeeder` sekarang hanya membuat level contoh untuk Bahasa Inggris.
- Bahasa lain tetap dibuat sebagai bahasa + bagian awal, tetapi tidak dipenuhi level dummy.
- Ditambahkan seeder khusus `ResetLearningLevelsSeeder` untuk membersihkan level dummy dan membuat ulang level Inggris yang rapi.

Cara reset data level dummy:
```bash
docker compose exec php php artisan db:seed --class=ResetLearningLevelsSeeder
docker compose exec php php artisan optimize:clear
```

Catatan:
- Seeder reset akan menghapus semua level yang ada lalu membuat ulang 5 level Bahasa Inggris yang rapi.
- Karena level dihapus, soal/progress yang menempel pada level lama juga ikut hilang melalui cascade database.
- Gunakan reset ini jika memang ingin memulai ulang penyusunan level dari awal.
