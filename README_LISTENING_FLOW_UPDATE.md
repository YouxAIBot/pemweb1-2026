# Listening Flow Update

Perubahan utama:

- Satu level sekarang bisa berisi banyak soal dengan jenis berbeda karena pemilihan jenis soal tidak lagi dipaksa mengikuti tipe level.
- Halaman quiz tetap focus mode tanpa sidebar kiri/kanan dan bisa discroll.
- Jawaban salah tetap menahan user di soal/bagian yang sama sampai benar.
- Listening sekarang menjadi satu tipe soal dengan alur bebas berbasis timeline.

## Admin Panel Listening

Menu: `LEARNING CMS -> Questions -> Listening`

Field utama:

- Instruksi
- Label tombol mulai
- Alur Listening
- Judul internal / fallback

Di `Alur Listening`, admin bisa menyusun blok secara bebas:

1. `Kalimat + Audio`
   - Kalimat cerita
   - Audio cerita

2. `Soal + Jawaban`
   - Soal
   - Audio pertanyaan opsional
   - Pilihan jawaban sebanyak kebutuhan
   - Jawaban benar
   - Pembahasan

Contoh urutan yang bisa dibuat:

- Kalimat + Audio
- Kalimat + Audio
- Kalimat + Audio
- Soal + Jawaban
- Kalimat + Audio
- Soal + Jawaban

## Flow Frontend Listening

1. Instruksi tampil.
2. User klik Mulai.
3. Delay sekitar 2 detik.
4. Kalimat cerita tampil.
5. Audio cerita autoplay sampai selesai.
6. Jika blok berikutnya masih cerita, lanjut ke cerita berikutnya.
7. Jika blok berikutnya soal, soal muncul.
8. User menjawab sampai benar.
9. Setelah benar, delay sekitar 1 detik lalu lanjut ke blok berikutnya.
10. Setelah semua blok selesai, listening dianggap selesai dan lanjut ke soal berikutnya di level.
