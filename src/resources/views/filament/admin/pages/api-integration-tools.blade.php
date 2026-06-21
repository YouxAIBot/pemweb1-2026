<x-filament-panels::page>
    <div class="grid gap-6 lg:grid-cols-2">
        <x-filament::section>
            <x-slot name="heading">
                Google Text-to-Speech
            </x-slot>

            <x-slot name="description">
                Generate audio MP3 untuk soal Listening dari teks. API key disimpan di .env.
            </x-slot>

            @if (! $this->ttsConfigured)
                <div class="rounded-xl border border-warning-200 bg-warning-50 p-4 text-sm text-warning-700">
                    GOOGLE_TTS_API_KEY belum diisi di .env.
                </div>
            @endif

            <form wire:submit.prevent="generateTts" class="space-y-4">
                <div>
                    <label class="text-sm font-semibold">Teks audio</label>
                    <textarea wire:model.defer="ttsText" rows="7" class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900"></textarea>
                    @error('ttsText') <p class="mt-1 text-sm text-danger-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-3 md:grid-cols-3">
                    <div>
                        <label class="text-sm font-semibold">Language Code</label>
                        <input wire:model.defer="ttsLanguageCode" class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900" placeholder="en-US">
                    </div>

                    <div>
                        <label class="text-sm font-semibold">Voice Name</label>
                        <input wire:model.defer="ttsVoiceName" class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900" placeholder="en-US-Neural2-C">
                    </div>

                    <div>
                        <label class="text-sm font-semibold">Speed</label>
                        <input type="number" step="0.05" wire:model.defer="ttsSpeakingRate" class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    </div>
                </div>

                <x-filament::button type="submit">
                    Generate Audio
                </x-filament::button>
            </form>

            @if ($ttsResultPath)
                <div class="mt-5 rounded-xl border border-success-200 bg-success-50 p-4 text-sm text-success-800">
                    <p class="font-bold">Audio berhasil dibuat</p>
                    <p class="mt-2">Storage path:</p>
                    <code class="block break-all rounded bg-white/70 p-2">{{ $ttsResultPath }}</code>

                    @if ($ttsResultUrl)
                        <a class="mt-3 inline-flex font-bold text-primary-600" href="{{ $ttsResultUrl }}" target="_blank">
                            Buka audio
                        </a>
                    @endif
                </div>
            @endif
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                DeepL Translation
            </x-slot>

            <x-slot name="description">
                Translate teks untuk hint, pembahasan, atau bahan soal.
            </x-slot>

            @if (! $this->deeplConfigured)
                <div class="rounded-xl border border-warning-200 bg-warning-50 p-4 text-sm text-warning-700">
                    DEEPL_API_KEY belum diisi di .env.
                </div>
            @endif

            <form wire:submit.prevent="translate" class="space-y-4">
                <div>
                    <label class="text-sm font-semibold">Teks</label>
                    <textarea wire:model.defer="translateText" rows="7" class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900"></textarea>
                    @error('translateText') <p class="mt-1 text-sm text-danger-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <div>
                        <label class="text-sm font-semibold">Source Lang</label>
                        <input wire:model.defer="translateSourceLang" class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900" placeholder="AUTO / ID / EN">
                    </div>

                    <div>
                        <label class="text-sm font-semibold">Target Lang</label>
                        <input wire:model.defer="translateTargetLang" class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900" placeholder="EN">
                    </div>
                </div>

                <x-filament::button type="submit">
                    Translate
                </x-filament::button>
            </form>

            @if ($translatedText)
                <div class="mt-5 rounded-xl border border-primary-200 bg-primary-50 p-4 text-sm text-primary-800">
                    <p class="font-bold">Hasil Translate</p>
                    <div class="mt-2 whitespace-pre-line rounded bg-white/70 p-3">{{ $translatedText }}</div>
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
