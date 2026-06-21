# Listening Sequence Update

Perubahan utama:
- Halaman level/quiz sekarang focus mode tanpa sidebar kiri dan panel kanan.
- Scroll halaman quiz diperbaiki agar konten panjang tidak kepotong.
- Tampilan quiz dibuat lebih polos dan simpel.
- Jawaban salah tidak membuat user lanjut; user harus menjawab sampai benar.
- Level baru selesai setelah semua soal selesai.
- Durasi belajar dikirim dari frontend melalui `study_seconds`, bukan angka dummy.

Khusus Listening:
- Satu soal Listening sekarang bisa berisi banyak bagian cerita.
- Di admin: LEARNING CMS -> Questions -> Listening -> Bagian Cerita & Soal Listening.
- Setiap bagian listening memiliki:
  - Teks Cerita
  - Audio Cerita
  - Pertanyaan
  - Audio Pertanyaan opsional
  - Pilihan Jawaban sendiri
  - Pembahasan bagian
- Flow frontend:
  1. Instruksi tampil
  2. User klik Mulai
  3. Delay 2 detik
  4. Teks cerita tampil dan audio cerita auto-play
  5. Pertanyaan tampil
  6. Audio pertanyaan diputar jika ada
  7. Delay sekitar 1 detik
  8. Pilihan jawaban muncul
  9. Jika salah, tetap di bagian yang sama
  10. Jika benar, lanjut ke bagian berikutnya
