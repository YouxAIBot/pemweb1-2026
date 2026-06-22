# Duel 1v1 Update

Fitur baru:
- Halaman `Duel 1v1` di `/turnamen/duel`.
- Local matchmaking berbasis database, tanpa API berbayar dan tanpa VPS.
- User A bisa melawan User B selama keduanya membuka web yang sama dan database yang sama.
- Tampilan arena VS: profile kiri vs profile kanan.
- Countdown 3, 2, 1, Mulai.
- 10 soal mix, masing-masing 10 detik.
- Soal digenerate lokal dari template mix: translation, grammar, vocabulary, dialogue, reading, real case.
- Soal tidak mengambil soal yang pernah dikerjakan user.
- Kedua player mendapat soal yang sama.
- Sistem skor:
  - Benar: +100
  - Bonus cepat: +0 sampai +50
  - Salah / timeout: +0
- Hasil masuk ke history masing-masing user.
- Leaderboard duel berdasarkan rating.
- Statistik: match, win, lose, draw, best score, rating, rank.
- Rank otomatis:
  - Bronze
  - Silver
  - Gold
  - Platinum
  - Diamond

Route baru:
```text
GET  /turnamen/duel
POST /turnamen/duel/find-match
GET  /turnamen/duel/queue-status
POST /turnamen/duel/cancel-queue
GET  /turnamen/duel/{duelSession}
GET  /api/turnamen/duel/{duelSession}/state
POST /api/turnamen/duel/{duelSession}/answer
POST /api/turnamen/duel/{duelSession}/finish
```

Tabel baru:
```text
duel_sessions
duel_players
duel_questions
duel_answers
duel_matchmaking_queues
duel_player_stats
```

Setelah extract, jalankan:
```bash
docker compose exec php composer dump-autoload
docker compose exec php php artisan migrate
docker compose exec php php artisan db:seed --class=GameModeSeeder
docker compose exec php php artisan optimize:clear
```

Cara testing lokal:
1. Login akun A di Chrome.
2. Login akun B di Incognito / Firefox.
3. Keduanya buka `/turnamen/duel`.
4. Akun A klik `Cari Lawan`.
5. Akun B klik `Cari Lawan`.
6. Sistem membuat room duel yang sama.
