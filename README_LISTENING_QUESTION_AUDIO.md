# Revisi Listening Question Audio

Perubahan ini menambahkan audio khusus untuk pembacaan pertanyaan pada tipe soal `listening`.

## Admin Panel

Buka:

```text
/admin → LEARNING CMS → Questions
```

Pilih jenis soal:

```text
Listening
```

Field baru:

```text
Audio Pembacaan Pertanyaan
```

Gunakan field ini untuk upload suara ketika pertanyaan dibacakan.

## Flow Frontend

Untuk tipe Listening:

```text
1. User mendengarkan audio listening utama.
2. Setelah audio utama selesai, audio pertanyaan diputar.
3. Setelah audio pertanyaan selesai, sistem menunggu sekitar 1 detik.
4. Pilihan jawaban baru muncul.
```

Kalau browser memblokir autoplay audio pertanyaan, tombol `Putar audio pertanyaan` akan muncul.

## Catatan

Tidak ada migration baru karena audio pertanyaan disimpan di kolom `settings` milik `learning_questions`.
