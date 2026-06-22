# Edge TTS + Manual Audio Path Fix

Perubahan:
- Admin panel `API INTEGRATION -> API Tools` sekarang punya tool `Edge TTS Gratis`.
- Edge TTS tidak butuh API key dan tidak butuh credit card.
- Hasil Edge TTS disimpan ke:
  `storage/app/public/learning/audio/generated/edge-tts`
- Output tool menampilkan storage path yang bisa dicopy.

Perubahan di Learning Questions:
- Pada blok Listening `Kalimat + Audio`, field upload diganti label menjadi `Upload Audio Cerita`.
- Ditambahkan field `Path Audio Cerita`.
- Pada blok Listening `Soal + Jawaban`, field upload diganti label menjadi `Upload Audio Pertanyaan`.
- Ditambahkan field `Path Audio Pertanyaan`.
- Jika field path manual diisi, frontend akan memakai path manual itu.
- Jika field path manual kosong, frontend tetap memakai file upload seperti biasa.

Contoh path:
```text
learning/audio/generated/edge-tts/anna-intro.mp3
```

Perintah yang perlu dijalankan kalau edge-tts belum ada:
```bash
docker compose exec -u root php bash -lc "apt-get update && apt-get install -y python3-pip"
docker compose exec -u root php bash -lc "python3 -m pip install --break-system-packages edge-tts"
docker compose exec php php artisan storage:link
docker compose exec php php artisan optimize:clear
```

Cara pakai:
1. Buka `Admin Panel -> API INTEGRATION -> API Tools`.
2. Isi teks di `Edge TTS Gratis`.
3. Pilih voice, contoh `en-US-AriaNeural` atau `id-ID-GadisNeural`.
4. Klik Generate.
5. Copy path hasil generate.
6. Buka `LEARNING CMS -> Questions`.
7. Pada Listening, tempel path ke `Path Audio Cerita` atau `Path Audio Pertanyaan`.
