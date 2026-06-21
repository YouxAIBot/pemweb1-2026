# Quiz Engine Update

Perubahan utama:

- Halaman level sekarang menjadi quiz engine interaktif.
- Satu level bisa berisi banyak soal.
- Pilihan jawaban bisa diklik.
- Tidak ada lagi tombol auto selesai level di awal.
- Level baru selesai setelah semua soal dalam level dikerjakan.
- Jawaban benar/salah punya animasi dan feedback.
- Setelah semua soal selesai, user menekan tombol simpan progress untuk membuka level berikutnya.
- Progress misi `questions_answered` bertambah berdasarkan jumlah soal aktif dalam level.
- Progress misi `study_minutes` dihitung dari durasi user berada di halaman level.
- Ditambahkan jenis soal baru `reading_story`.

## Reading Story

Di admin panel `LEARNING CMS → Questions`, pilih jenis soal `Reading Story`.

Admin bisa mengisi:

- Segmen cerita 1, 2, 3, dan seterusnya.
- Audio bacaan untuk setiap segmen.
- Pertanyaan setelah cerita selesai.
- Pilihan jawaban tanpa audio.

Di frontend:

- User menekan tombol mulai cerita.
- Segmen cerita pertama tampil dan audio diputar.
- Setelah audio selesai, lanjut ke segmen berikutnya.
- Setelah semua segmen selesai, soal dan pilihan jawaban baru muncul.

## Catatan

Perubahan ini tidak menghapus fitur sebelumnya: homepage CMS, auth pages, user management, onboarding, dashboard, learning CMS, daily mission, dan progress user tetap dipertahankan.
