<?php

namespace App\Filament\Admin\Pages;

use App\Services\Integrations\DeepLTranslationService;
use App\Services\Integrations\GoogleTextToSpeechService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Throwable;

class ApiIntegrationTools extends Page
{
    protected static ?string $navigationGroup = 'API INTEGRATION';

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationLabel = 'API Tools';

    protected static ?string $title = 'API Tools';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.admin.pages.api-integration-tools';

    public ?string $ttsText = null;

    public string $ttsLanguageCode = 'en-US';

    public ?string $ttsVoiceName = 'en-US-Neural2-C';

    public float $ttsSpeakingRate = 1.0;

    public ?string $ttsResultPath = null;

    public ?string $ttsResultUrl = null;

    public ?string $translateText = null;

    public string $translateTargetLang = 'EN';

    public ?string $translateSourceLang = null;

    public ?string $translatedText = null;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return (bool) ($user && ($user->hasRole('super_admin') || $user->email === 'admin@admin.com'));
    }

    public function generateTts(): void
    {
        $this->validate([
            'ttsText' => ['required', 'string', 'max:4500'],
            'ttsLanguageCode' => ['required', 'string', 'max:20'],
            'ttsVoiceName' => ['nullable', 'string', 'max:80'],
            'ttsSpeakingRate' => ['required', 'numeric', 'min:0.25', 'max:4'],
        ]);

        try {
            $result = app(GoogleTextToSpeechService::class)->synthesizeToPublicStorage(
                text: $this->ttsText,
                languageCode: $this->ttsLanguageCode,
                voiceName: $this->ttsVoiceName,
                speakingRate: $this->ttsSpeakingRate,
            );

            $this->ttsResultPath = $result['path'];
            $this->ttsResultUrl = $result['url'];

            Notification::make()
                ->title('Audio berhasil dibuat')
                ->body('Copy path audio lalu tempel ke field audio listening jika diperlukan.')
                ->success()
                ->send();
        } catch (Throwable $exception) {
            Notification::make()
                ->title('Gagal generate audio')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public function translate(): void
    {
        $this->validate([
            'translateText' => ['required', 'string', 'max:4500'],
            'translateTargetLang' => ['required', 'string', 'max:10'],
            'translateSourceLang' => ['nullable', 'string', 'max:10'],
        ]);

        try {
            $this->translatedText = app(DeepLTranslationService::class)->translate(
                text: $this->translateText,
                targetLanguage: $this->translateTargetLang,
                sourceLanguage: $this->translateSourceLang,
            );

            Notification::make()
                ->title('Terjemahan berhasil dibuat')
                ->success()
                ->send();
        } catch (Throwable $exception) {
            Notification::make()
                ->title('Gagal translate')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getTtsConfiguredProperty(): bool
    {
        return filled(config('services.google_tts.api_key'));
    }

    public function getDeeplConfiguredProperty(): bool
    {
        return filled(config('services.deepl.api_key'));
    }
}
