# Dashboard Revision

Perubahan revisi:

- Menambahkan route `/welcome` setelah login/register.
- User yang sudah pernah onboarding tidak akan dipaksa memilih bahasa lagi.
- Setelah login, user melihat animasi teks “Selamat datang” dan “Ayo mulai petualanganmu”, lalu masuk dashboard milik akun tersebut.
- User baru tetap diarahkan ke onboarding setelah animasi welcome.
- Map level bagian dirapikan agar judul/deskripsi tidak bertabrakan dengan node level.
- Scrollbar visual di area tengah/panel dashboard disembunyikan agar tampilan lebih clean.

Route baru:

```text
GET /welcome -> learning.welcome
```

Setelah extract, jalankan:

```bash
docker compose exec php php artisan optimize:clear
```
