# Midtrans Sandbox Payment

Integrasi Midtrans yang dipakai YoLearning adalah Snap Redirect. User klik tombol bayar, sistem membuat transaksi Snap, lalu user diarahkan ke halaman pembayaran Midtrans. Premium aktif otomatis setelah Midtrans mengirim notifikasi sukses ke endpoint YoLearning.

## ENV

Isi key sandbox dari dashboard Midtrans:

```env
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxx
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true
```

Setelah mengubah `.env`, jalankan:

```bash
docker compose exec php php artisan optimize:clear
```

Pastikan `APP_URL` di VPS memakai domain publik yang benar, misalnya:

```env
APP_URL=https://domain-kamu.com
```

## URL Notifikasi

Di dashboard Midtrans sandbox, set Payment Notification URL ke:

```text
https://domain-kamu.com/api/midtrans/premium/notification
```

Kalau masih lokal, `pemweb1.test` atau `localhost` tidak bisa dipanggil oleh Midtrans. Untuk test webhook lokal, gunakan URL publik sementara seperti ngrok/localtunnel, atau test manual dengan request palsu yang signature-nya valid.

## Alur

1. User buka `Toko Premium`.
2. User pilih paket lalu klik `Bayar Otomatis via Midtrans`.
3. Sistem membuat baris `premium_payments` dengan metode `midtrans_snap`.
4. Midtrans mengembalikan `snap_redirect_url`.
5. User menyelesaikan pembayaran di halaman Midtrans.
6. Midtrans mengirim webhook ke `/api/midtrans/premium/notification`.
7. Sistem memverifikasi signature, nominal, dan status transaksi.
8. Jika status `settlement` atau `capture` valid, sistem mengaktifkan premium otomatis.

## Keamanan Yang Sudah Ditangani

- Signature webhook dicek dengan Server Key.
- Nominal `gross_amount` harus sama dengan nominal paket di database.
- Status sukses hanya diterima untuk `settlement` atau `capture`.
- Jika `fraud_status` ada, nilainya harus `accept`.
- Webhook duplikat tidak akan menggandakan masa premium.
- Status gagal seperti `cancel`, `deny`, `failure`, dan `expire` tidak mengaktifkan premium.

## Testing

Jalankan test utama:

```bash
docker compose exec php php artisan test --filter=YoLearningPremiumCompetitionTest
```

Untuk test manual:

1. Login sebagai user biasa.
2. Buka `/toko`.
3. Klik `Bayar Otomatis via Midtrans`.
4. Selesaikan pembayaran di sandbox Midtrans.
5. Pastikan riwayat pembayaran berubah menjadi disetujui.
6. Pastikan status akun menjadi premium dan iklan level tidak tampil.
