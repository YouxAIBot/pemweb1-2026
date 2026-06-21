<?php

namespace App\Services\Integrations;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class DeepLTranslationService
{
    public function translate(string $text, ?string $targetLanguage = null, ?string $sourceLanguage = null): string
    {
        $apiKey = config('services.deepl.api_key');

        if (blank($apiKey)) {
            throw new RuntimeException('DEEPL_API_KEY belum diisi di .env.');
        }

        $targetLanguage = $targetLanguage ?: config('services.deepl.default_target_lang', 'EN');

        $payload = [
            'text' => $text,
            'target_lang' => strtoupper($targetLanguage),
        ];

        if (filled($sourceLanguage)) {
            $payload['source_lang'] = strtoupper($sourceLanguage);
        }

        $response = Http::timeout(30)
            ->retry(2, 500)
            ->withHeaders([
                'Authorization' => 'DeepL-Auth-Key ' . $apiKey,
            ])
            ->asForm()
            ->post(config('services.deepl.endpoint'), $payload);

        if (! $response->successful()) {
            throw new RuntimeException('DeepL translate gagal: ' . $response->body());
        }

        return (string) ($response->json('translations.0.text') ?? '');
    }
}
