# AI Question Generator + Database Reset

Fitur baru:
- Admin panel: `LEARNING CMS -> AI Question Generator`
- Generate soal otomatis memakai OpenAI API dengan Structured Outputs.
- Admin memilih:
  - Level tujuan
  - Jenis soal
  - Jumlah soal
  - Bahasa target
  - Kesulitan
  - Topik
  - Catatan tambahan
- Hasil generate tampil sebagai preview JSON.
- Admin klik `Simpan ke Database` untuk memasukkan soal ke tabel learning questions.
- Mendukung jenis:
  - Pilihan Ganda
  - Sambung Kata
  - Listening Flow
  - Soal Nyata
  - Mix

File baru:
- `src/app/Services/Integrations/OpenAIQuestionGeneratorService.php`
- `src/app/Filament/Admin/Pages/AiQuestionGenerator.php`
- `src/resources/views/filament/admin/pages/ai-question-generator.blade.php`
- `src/app/Console/Commands/ResetYoLearningDatabase.php`

ENV baru:
```env
OPENAI_API_KEY=
OPENAI_RESPONSES_ENDPOINT=https://api.openai.com/v1/responses
OPENAI_QUESTION_MODEL=gpt-4.1-mini
```

Reset database:
```bash
docker compose exec php php artisan yolearning:reset-database --force
```

Fallback reset jika command class belum terdeteksi:
```bash
docker compose exec php php artisan yolearning:fresh --force
```

Atau manual:
```bash
docker compose exec php php artisan migrate:fresh --seed --force
docker compose exec php php artisan storage:link
docker compose exec php php artisan optimize:clear
```

Setelah extract:
```bash
docker compose exec php composer dump-autoload
docker compose exec php php artisan optimize:clear
docker compose exec php php artisan storage:link
docker compose exec php php artisan yolearning:reset-database --force
```

Catatan:
- OPENAI_API_KEY tidak di-hardcode.
- AI hanya membuat draft soal. Admin tetap review dulu sebelum menyimpan.
- Untuk Listening, AI membuat teks story/question. Audio bisa dibuat lewat `API INTEGRATION -> API Tools` dengan Google Text-to-Speech.
