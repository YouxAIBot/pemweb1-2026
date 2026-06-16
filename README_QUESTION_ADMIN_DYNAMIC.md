# Update: Dynamic Question Admin

Revisi ini diterapkan ke basis `pemweb1-2026-daily-progress-fixed.zip`.

## Perubahan utama

Admin panel `LEARNING CMS → Questions` sekarang punya form dinamis berdasarkan jenis soal:

- Pilihan Ganda
- Listening
- Sambung Kata
- Soal Nyata
- Mix

Saat admin memilih jenis soal, field pembuatan soal akan berubah sesuai kebutuhan tipe tersebut.

## Reset soal

Seeder default `LearningCmsSeeder` tidak lagi membuat soal dummy.

Untuk mengosongkan soal lama tanpa menghapus bahasa, bagian, level, user, progress, homepage, atau auth pages, jalankan:

```bash
docker compose exec php php artisan db:seed --class=ResetLearningQuestionsSeeder
```

Atau dari admin panel:

`LEARNING CMS → Questions → Kosongkan Semua Soal`

## Setelah extract

```bash
docker compose exec php php artisan optimize:clear
```

Jika ingin reset soal:

```bash
docker compose exec php php artisan db:seed --class=ResetLearningQuestionsSeeder
docker compose exec php php artisan optimize:clear
```
