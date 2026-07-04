<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                Pilih Bahasa Kerja
            </x-slot>

            <x-slot name="description">
                Semua ringkasan dan shortcut di bawah mengikuti bahasa yang dipilih agar Part, Level, Soal, dan Huruf tidak bercampur.
            </x-slot>

            <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_auto] md:items-end">
                <label class="space-y-2">
                    <span class="text-sm font-semibold">Bahasa</span>
                    <select wire:model.live="languageId" class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                        @foreach ($this->languages as $language)
                            <option value="{{ $language->id }}">{{ $language->name }}{{ $language->native_name ? ' - ' . $language->native_name : '' }}</option>
                        @endforeach
                    </select>
                </label>

                @if ($this->selectedLanguage)
                    <div class="rounded-xl border border-primary-200 bg-primary-50 px-4 py-3 text-sm font-bold text-primary-800 dark:border-primary-700 dark:bg-primary-950 dark:text-primary-100">
                        Workspace: {{ $this->selectedLanguage->name }}
                    </div>
                @endif
            </div>
        </x-filament::section>

        <div class="grid gap-4 md:grid-cols-4">
            @foreach ([
                'parts' => 'Parts',
                'levels' => 'Levels',
                'questions' => 'Questions',
                'letters' => 'Huruf',
            ] as $key => $label)
                <x-filament::section>
                    <div class="space-y-3">
                        <p class="text-sm font-semibold text-gray-500">{{ $label }}</p>
                        <p class="text-3xl font-black">{{ $this->stats[$key] ?? 0 }}</p>
                        <x-filament::button tag="a" :href="$this->resourceUrl($key)" size="sm">
                            Kelola {{ $label }}
                        </x-filament::button>
                    </div>
                </x-filament::section>
            @endforeach
        </div>

        <x-filament::section>
            <x-slot name="heading">
                Struktur Bagian
            </x-slot>

            <x-slot name="description">
                Ringkasan cepat bagian dalam bahasa aktif.
            </x-slot>

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($this->parts as $part)
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-primary-600">Bagian {{ $part->level_number }}</p>
                                <h3 class="mt-1 font-bold">{{ $part->title }}</h3>
                            </div>
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                {{ $part->levels_count }} level
                            </span>
                        </div>
                        <p class="mt-2 line-clamp-2 text-sm text-gray-500">{{ $part->description ?: 'Belum ada deskripsi.' }}</p>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-gray-300 p-6 text-sm text-gray-500 dark:border-gray-700">
                        Belum ada bagian untuk bahasa ini.
                    </div>
                @endforelse
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
