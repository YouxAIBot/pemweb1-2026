<?php

namespace App\Services\Integrations;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class GoogleTextToSpeechService
{
    public function synthesize(
        string $text,
        ?string $languageCode = null,
        ?string $voiceName = null,
        float $speakingRate = 1.0,
        float $pitch = 0.0
    ): string {
        $apiKey = config('services.google_tts.api_key');

        if (blank($apiKey)) {
            throw new RuntimeException('GOOGLE_TTS_API_KEY belum diisi di .env.');
        }

        $languageCode = $languageCode ?: config('services.google_tts.default_language', 'en-US');
        $voiceName = $voiceName ?: config('services.google_tts.default_voice', 'en-US-Neural2-C');

        $response = Http::timeout(35)
            ->retry(2, 500)
            ->post(config('services.google_tts.endpoint') . '?key=' . $apiKey, [
                'input' => [
                    'text' => $text,
                ],
                'voice' => [
                    'languageCode' => $languageCode,
                    'name' => $voiceName,
                ],
                'audioConfig' => [
                    'audioEncoding' => 'MP3',
                    'speakingRate' => $speakingRate,
                    'pitch' => $pitch,
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Google Text-to-Speech gagal: ' . $response->body());
        }

        $audioContent = $response->json('audioContent');

        if (blank($audioContent)) {
            throw new RuntimeException('Google Text-to-Speech tidak mengembalikan audio.');
        }

        return base64_decode($audioContent);
    }

    public function synthesizeToPublicStorage(
        string $text,
        ?string $languageCode = null,
        ?string $voiceName = null,
        string $directory = 'learning/audio/generated/google-tts',
        float $speakingRate = 1.0,
        float $pitch = 0.0
    ): array {
        $audioBinary = $this->synthesize($text, $languageCode, $voiceName, $speakingRate, $pitch);
        $filename = trim($directory, '/') . '/' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.mp3';

        Storage::disk('public')->put($filename, $audioBinary);

        return [
            'path' => $filename,
            'url' => Storage::disk('public')->url($filename),
        ];
    }
}
