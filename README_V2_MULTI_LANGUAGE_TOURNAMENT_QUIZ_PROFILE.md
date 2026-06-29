# YoLearning V2 Update

Update ini menambahkan:

1. Dashboard user bisa mengganti bahasa aktif.
2. Turnamen berjalan berdasarkan bahasa aktif user.
3. Duel 1v1 matchmaking dan rank dipisahkan berdasarkan `learning_language_id`.
4. Arena duel menampilkan "Lawan ditemukan" selama 2 detik, lalu countdown 3, 2, 1, Mulai.
5. Leaderboard pada menu utama turnamen dihapus agar tampilan lebih simpel.
6. Quiz Room/Kahoot aktif tanpa leaderboard global.
7. Owner Quiz Room bisa membuat soal sendiri dengan teks/gambar dan pilihan jawaban teks/gambar.
8. Peserta melihat progress skor sementara setelah menjawab.
9. Owner bisa menyelesaikan room untuk menyimpan history posisi.
10. Page profil pengguna ditambahkan untuk edit nama, email, password, dan foto profil.
11. UI turnamen, quiz, dan profil dibuat lebih simpel.

## Setelah Upload ke VPS

Jalankan:

```bash
cd /var/www/yolearning
docker compose exec php composer dump-autoload
docker compose exec php php artisan migrate
docker compose exec php php artisan db:seed --class=GameModeSeeder
docker compose exec php php artisan storage:link
docker compose exec php php artisan optimize:clear
docker compose restart php nginx
```

## Route Baru

- `POST /dashboard/switch-language`
- `GET /profile`
- `POST /profile`
- `GET /turnamen/quiz`
- `POST /turnamen/quiz`
- `POST /turnamen/quiz/join`
- `GET /turnamen/quiz/{room}`
- `POST /turnamen/quiz/{room}/questions`
- `POST /turnamen/quiz/{room}/start`
- `POST /turnamen/quiz/{room}/finish`
- `POST /api/turnamen/quiz/{room}/answer`

