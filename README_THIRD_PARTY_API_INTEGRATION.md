# Third-Party API Integration

API pihak ketiga yang diterapkan:

1. Google Cloud Text-to-Speech
   - Untuk generate audio MP3 dari teks.
   - Cocok untuk soal Listening.
   - Admin tidak harus upload audio manual setiap kali membuat cerita listening.

2. DeepL Translation
   - Untuk translate teks.
   - Cocok untuk hint, pembahasan, bahan soal, atau validasi terjemahan manual.

File yang ditambahkan:
- `src/app/Services/Integrations/GoogleTextToSpeechService.php`
- `src/app/Services/Integrations/DeepLTranslationService.php`
- `src/app/Filament/Admin/Pages/ApiIntegrationTools.php`
- `src/resources/views/filament/admin/pages/api-integration-tools.blade.php`

File yang diubah:
- `src/config/services.php`
- `src/.env.example`
- `src/app/Providers/Filament/AdminPanelProvider.php`
- `src/app/Filament/Admin/Resources/LearningQuestionResource.php`

Admin panel:
- Menu baru: `API INTEGRATION -> API Tools`
- Tool 1: Generate audio dari teks memakai Google Text-to-Speech.
- Tool 2: Translate teks memakai DeepL.

ENV yang perlu diisi:
```env
GOOGLE_TTS_API_KEY=
GOOGLE_TTS_ENDPOINT=https://texttospeech.googleapis.com/v1/text:synthesize
GOOGLE_TTS_DEFAULT_LANGUAGE=en-US
GOOGLE_TTS_DEFAULT_VOICE=en-US-Neural2-C

DEEPL_API_KEY=
DEEPL_ENDPOINT=https://api-free.deepl.com/v2/translate
DEEPL_DEFAULT_TARGET_LANG=EN
```

Setelah extract:
```bash
docker compose exec php php artisan optimize:clear
docker compose exec php php artisan storage:link
```

Catatan:
- API key tidak di-hardcode di project.
- Jika API key kosong, halaman API Tools tetap tampil, tetapi akan memberi peringatan.
- Audio hasil Google TTS disimpan ke disk public pada folder:
  `learning/audio/generated/google-tts`
- Copy storage path hasil generate lalu pakai untuk field audio listening jika diperlukan.
