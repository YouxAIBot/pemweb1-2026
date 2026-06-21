# Listening History Update

Perubahan:
- Pada soal Listening, cerita yang sudah diputar tidak dihilangkan.
- Soal dan jawaban yang sudah dikerjakan tetap tampil di halaman.
- Alur listening sekarang memanjang ke bawah sehingga user bisa scroll dan membaca ulang cerita sebelumnya.
- Setelah semua blok listening selesai, sistem tidak langsung lanjut/progress selesai.
- Muncul tombol `Selesai` terlebih dahulu.
- Setelah user klik `Selesai`, baru sistem lanjut ke soal berikutnya atau panel progress selesai jika itu soal terakhir.

Flow:
1. User klik Mulai.
2. Cerita/audio muncul dan tetap tersimpan di halaman.
3. Jika ada cerita berikutnya, cerita berikutnya ditambahkan di bawah.
4. Jika ada soal, soal ditambahkan di bawah.
5. Jawaban salah tetap harus coba lagi.
6. Jawaban benar mengunci soal tersebut lalu lanjut ke blok berikutnya.
7. Semua history tetap terlihat dan bisa discroll.
8. Setelah semua selesai, muncul tombol Selesai.
