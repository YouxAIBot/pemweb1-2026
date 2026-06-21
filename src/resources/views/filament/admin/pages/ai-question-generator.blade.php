<x-filament-panels::page>
    <div class="grid gap-6 lg:grid-cols-2">
        <x-filament::section>
            <x-slot name="heading">
                Generate Soal Otomatis
            </x-slot>

            <x-slot name="description">
                Pakai OpenAI Structured Outputs agar hasil AI mengikuti struktur soal YoLearning.
            </x-slot>

            @if (! $this->openaiConfigured)
                <div class="mb-4 rounded-xl border border-warning-200 bg-warning-50 p-4 text-sm text-warning-700">
                    OPENAI_API_KEY belum diisi di .env.
                </div>
            @endif

            <form wire:submit.prevent="generate" class="space-y-4">
                <div>
                    <label class="text-sm font-semibold">Level Tujuan</label>
                    <select wire:model.defer="learningLevelId" class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                        <option value="">Pilih level</option>
                        @foreach ($this->levels as $level)
                            <option value="{{ $level->id }}">
                                {{ $level->part?->language?->name ?? 'Tanpa Bahasa' }} / {{ $level->part?->title ?? 'Tanpa Bagian' }} / {{ $level->title }}
                            </option>
                        @endforeach
                    </select>
                    @error('learningLevelId') <p class="mt-1 text-sm text-danger-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <div>
                        <label class="text-sm font-semibold">Jenis Soal</label>
                        <select wire:model.defer="questionType" class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                            <option value="multiple_choice">Pilihan Ganda</option>
                            <option value="word_match">Sambung Kata</option>
                            <option value="listening">Listening Flow</option>
                            <option value="real_case">Soal Nyata</option>
                            <option value="mixed">Mix</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-sm font-semibold">Jumlah Soal</label>
                        <input type="number" min="1" max="10" wire:model.defer="questionCount" class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    </div>
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <div>
                        <label class="text-sm font-semibold">Bahasa Target</label>
                        <input wire:model.defer="targetLanguage" class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900" placeholder="English">
                    </div>

                    <div>
                        <label class="text-sm font-semibold">Kesulitan</label>
                        <select wire:model.defer="difficulty" class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold">Topik</label>
                    <input wire:model.defer="topic" class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900" placeholder="Daily greeting, food, school, travel...">
                    @error('topic') <p class="mt-1 text-sm text-danger-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-sm font-semibold">Catatan Tambahan</label>
                    <textarea wire:model.defer="notes" rows="3" class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900" placeholder="Contoh: gunakan Bahasa Indonesia untuk instruksi, jangan terlalu panjang."></textarea>
                </div>

                <x-filament::button type="submit">
                    Generate Soal
                </x-filament::button>
            </form>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                Preview Hasil
            </x-slot>

            <x-slot name="description">
                Review dulu. Jika sudah cocok, simpan ke database.
            </x-slot>

            @if ($generatedJson)
                <pre class="max-h-[520px] overflow-auto rounded-xl bg-gray-950 p-4 text-xs text-gray-100">{{ $generatedJson }}</pre>

                <div class="mt-4">
                    <x-filament::button wire:click="saveGenerated" color="success">
                        Simpan ke Database
                    </x-filament::button>
                </div>
            @else
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">
                    Belum ada hasil generate.
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
