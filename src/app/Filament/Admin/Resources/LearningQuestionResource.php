<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LearningQuestionResource\Pages;
use App\Models\LearningLanguage;
use App\Models\LearningLevel;
use App\Models\LearningQuestion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LearningQuestionResource extends Resource
{
    protected static ?string $model = LearningQuestion::class;

    protected static ?string $navigationGroup = 'LEARNING CMS';

    protected static ?string $navigationLabel = 'Questions';

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?int $navigationSort = 4;

    private static function allowAdmin(): bool
    {
        $user = auth()->user();

        return (bool) ($user && ($user->hasRole('super_admin') || $user->email === 'admin@admin.com'));
    }

    private static function audioAcceptedFileTypes(): array
    {
        return [
            'audio/mpeg',
            'audio/mp3',
            'audio/mpeg3',
            'audio/x-mpeg',
            'audio/x-mpeg-3',
            'audio/wav',
            'audio/x-wav',
            'audio/ogg',
            'audio/webm',
            'audio/mp4',
            'audio/m4a',
            'audio/x-m4a',
            'audio/aac',
        ];
    }

    public static function canViewAny(): bool
    {
        return static::allowAdmin();
    }

    public static function canCreate(): bool
    {
        return static::allowAdmin();
    }

    public static function canEdit($record): bool
    {
        return static::allowAdmin();
    }

    public static function canDelete($record): bool
    {
        return static::allowAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Pengaturan Dasar Soal')
                    ->description('Pilih level dan jenis soal terlebih dahulu. Field pembuatan soal akan berubah sesuai jenis yang dipilih.')
                    ->schema([
                        Forms\Components\Select::make('learning_level_id')
                            ->label('Level')
                            ->relationship(
                                name: 'level',
                                titleAttribute: 'title',
                                modifyQueryUsing: fn ($query) => $query
                                    ->with('part.language')
                                    ->orderBy('learning_part_id')
                                    ->orderBy('sort_order')
                            )
                            ->getOptionLabelFromRecordUsing(function (LearningLevel $record): string {
                                $language = $record->part?->language?->name ?? 'Tanpa Bahasa';
                                $part = $record->part?->title ?? 'Tanpa Bagian';

                                return "{$language} / {$part} / {$record->title}";
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->helperText('Satu level bisa berisi banyak soal dengan jenis berbeda. Pilih levelnya, lalu tentukan jenis soal pada field di bawah.'),

                        Forms\Components\Select::make('type')
                            ->label('Jenis Soal')
                            ->options(LearningLevel::TYPES)
                            ->required()
                            ->default('multiple_choice')
                            ->live()
                            ->helperText('Pilih bebas per soal. Dalam satu level boleh ada pilihan ganda, sambung kata, matching, listening, dan jenis lain secara campuran.'),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Urutan Soal')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        Forms\Components\TextInput::make('points')
                            ->label('Poin')
                            ->numeric()
                            ->default(10)
                            ->required(),

                        Forms\Components\TextInput::make('time_limit')
                            ->label('Batas Waktu')
                            ->numeric()
                            ->suffix('detik')
                            ->helperText('Kosongkan jika soal tidak memakai timer.'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ])
                    ->columns(2),

                static::multipleChoiceSection(),
                static::listeningSection(),
                static::wordMatchSection(),
                static::sentenceOrderSection(),
                static::readingStorySection(),
                static::mixedSection(),
                static::learningAidSection(),

                Forms\Components\Section::make('Pembahasan')
                    ->description('Bagian ini muncul untuk semua jenis soal.')
                    ->schema([
                        Forms\Components\Textarea::make('correct_answer')
                            ->label('Jawaban Benar / Kunci Jawaban')
                            ->rows(2)
                            ->columnSpanFull()
                            ->helperText('Boleh diisi teks jawaban benar. Untuk pilihan ganda, jawaban benar juga bisa ditentukan lewat opsi.'),

                        Forms\Components\Textarea::make('explanation')
                            ->label('Pembahasan')
                            ->rows(4)
                            ->columnSpanFull()
                            ->helperText('Tuliskan alasan kenapa jawaban benar. Ini akan dipakai saat review.'),
                    ]),
            ]);
    }

    private static function learningAidSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Bantuan Belajar Interaktif')
            ->description('Opsional. Data ini dipakai pada tampilan soal baru: teks bahasa asing bisa diklik/didengar dan saat diarahkan kursor akan menampilkan terjemahan.')
            ->visible(fn (Get $get): bool => in_array($get('type'), [
                'multiple_choice',
                'word_match',
                'sentence_order',
                'reading_story',
                'mixed',
            ], true))
            ->schema([
                Forms\Components\TextInput::make('settings.learning_phrase_text')
                    ->label('Teks Bahasa Asing')
                    ->maxLength(255)
                    ->helperText('Contoh: 火车站, Good morning, atau kalimat pendek yang sedang dipelajari.'),

                Forms\Components\Textarea::make('settings.learning_phrase_translation')
                    ->label('Terjemahan Saat Hover')
                    ->rows(2)
                    ->columnSpanFull()
                    ->helperText('Akan muncul saat user mengarahkan kursor ke teks bahasa asing.'),

                Forms\Components\FileUpload::make('settings.learning_phrase_audio_path')
                    ->label('Upload Audio Teks Bahasa Asing')
                    ->acceptedFileTypes(static::audioAcceptedFileTypes())
                    ->maxSize(51200)
                    ->directory('learning/audio/phrases')
                    ->helperText('Opsional. Audio ini diputar saat user klik tombol dengar pada frasa.'),

                Forms\Components\TextInput::make('settings.learning_phrase_audio_manual_path')
                    ->label('Path Audio Teks Bahasa Asing')
                    ->placeholder('learning/audio/generated/edge-tts/phrase.mp3')
                    ->helperText('Isi ini kalau audio dibuat dari API Tools/Edge TTS. Jika diisi, path ini diprioritaskan.'),
            ])
            ->columns(2);
    }

    private static function multipleChoiceSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Pembuat Soal Pilihan Ganda')
            ->description('Gunakan bagian ini untuk membuat soal dengan beberapa pilihan jawaban.')
            ->visible(fn (Get $get): bool => $get('type') === 'multiple_choice')
            ->schema([
                Forms\Components\TextInput::make('instruction')
                    ->label('Instruksi')
                    ->maxLength(255)
                    ->default('Pilih jawaban yang paling tepat.'),

                Forms\Components\Textarea::make('question_text')
                    ->label('Soal')
                    ->required(fn (Get $get): bool => $get('type') === 'multiple_choice')
                    ->rows(4)
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('image_path')
                    ->label('Gambar Soal')
                    ->image()
                    ->directory('learning/images/questions')
                    ->imageEditor()
                    ->columnSpanFull(),

                static::optionsRepeater(
                    minItems: 2,
                    helperText: 'Tandai minimal satu opsi sebagai jawaban benar.'
                ),
            ])
            ->columns(2);
    }

    private static function listeningSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Pembuat Soal Listening')
            ->description('Listening sederhana: user klik tombol mikrofon, mendengar audio, lalu menyusun kalimat sesuai yang diucapkan.')
            ->visible(fn (Get $get): bool => $get('type') === 'listening')
            ->schema([
                Forms\Components\TextInput::make('instruction')
                    ->label('Instruksi')
                    ->maxLength(255)
                    ->default('Dengarkan audio, lalu susun kalimat yang kamu dengar.'),

                Forms\Components\Placeholder::make('api_tools_hint')
                    ->label('Generate Audio via API')
                    ->content('Buka API INTEGRATION -> API Tools -> Edge TTS Gratis untuk generate audio otomatis. Copy path hasil generate ke field Path Audio.')
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('settings.question_audio_path')
                    ->label('Upload Audio')
                    ->acceptedFileTypes(static::audioAcceptedFileTypes())
                    ->maxSize(51200)
                    ->directory('learning/audio/listening/questions')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('settings.question_audio_manual_path')
                    ->label('Path Audio')
                    ->placeholder('learning/audio/generated/edge-tts/listening-1.mp3')
                    ->helperText('Isi ini jika audio dibuat dari Edge TTS/API Tools. Jika diisi, path ini diprioritaskan.')
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('question_text')
                    ->label('Arahan Soal')
                    ->rows(2)
                    ->default('Susun kalimat yang kamu dengar.')
                    ->required(fn (Get $get): bool => $get('type') === 'listening')
                    ->helperText('Teks ini hanya instruksi. Kalimat jawaban jangan ditampilkan di sini.')
                    ->columnSpanFull(),

                Forms\Components\Repeater::make('settings.sentence_tokens')
                    ->label('Token Kalimat Benar')
                    ->schema([
                        Forms\Components\TextInput::make('text')
                            ->label('Kata / Frasa')
                            ->required(),
                    ])
                    ->defaultItems(4)
                    ->minItems(2)
                    ->addActionLabel('Tambah Token')
                    ->helperText('Masukkan kata sesuai urutan audio. User akan menyusun token ini setelah mendengar suara.')
                    ->columnSpanFull(),

                Forms\Components\Placeholder::make('listening_answer_hint')
                    ->label('Kunci Jawaban')
                    ->content('Isi Jawaban Benar / Kunci Jawaban dengan kalimat lengkap. Jika kosong, sistem memakai gabungan token di atas.')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
    private static function wordMatchSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Pembuat Soal Sambung Kata')
            ->description('Gunakan bagian ini untuk membuat latihan cocokkan kata, arti, atau pasangan kalimat.')
            ->visible(fn (Get $get): bool => $get('type') === 'word_match')
            ->schema([
                Forms\Components\TextInput::make('instruction')
                    ->label('Instruksi')
                    ->maxLength(255)
                    ->default('Cocokkan kata dengan arti yang benar.'),

                Forms\Components\Textarea::make('question_text')
                    ->label('Judul / Pertanyaan')
                    ->required(fn (Get $get): bool => $get('type') === 'word_match')
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\Repeater::make('settings.word_pairs')
                    ->label('Pasangan Kata')
                    ->schema([
                        Forms\Components\TextInput::make('left')
                            ->label('Kata / Kalimat')
                            ->required(),

                        Forms\Components\TextInput::make('right')
                            ->label('Arti / Pasangan')
                            ->required(),

                        Forms\Components\FileUpload::make('audio_path')
                            ->label('Audio Kata')
                            ->acceptedFileTypes(static::audioAcceptedFileTypes())
                            ->maxSize(51200)
                            ->directory('learning/audio/vocabulary'),
                    ])
                    ->columns(3)
                    ->defaultItems(3)
                    ->minItems(2)
                    ->addActionLabel('Tambah Pasangan')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    private static function sentenceOrderSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Pembuat Soal Urutkan Kalimat')
            ->description('Gunakan bagian ini untuk membuat latihan menyusun kata atau frasa menjadi kalimat yang benar.')
            ->visible(fn (Get $get): bool => $get('type') === 'sentence_order')
            ->schema([
                Forms\Components\TextInput::make('instruction')
                    ->label('Instruksi')
                    ->maxLength(255)
                    ->default('Urutkan kata menjadi kalimat yang benar.'),

                Forms\Components\Textarea::make('question_text')
                    ->label('Pertanyaan / Arahan')
                    ->required(fn (Get $get): bool => $get('type') === 'sentence_order')
                    ->rows(3)
                    ->columnSpanFull()
                    ->helperText('Contoh: Susun kalimat sapaan berikut.'),

                Forms\Components\Repeater::make('settings.sentence_tokens')
                    ->label('Token Kata / Frasa')
                    ->schema([
                        Forms\Components\TextInput::make('text')
                            ->label('Kata / Frasa')
                            ->required(),
                    ])
                    ->defaultItems(3)
                    ->minItems(2)
                    ->addActionLabel('Tambah Token')
                    ->helperText('Masukkan token dalam urutan benar. Sistem akan mengacak tampilannya untuk user.')
                    ->columnSpanFull(),

                Forms\Components\Placeholder::make('sentence_order_answer_hint')
                    ->label('Kunci Jawaban')
                    ->content('Isi field Jawaban Benar / Kunci Jawaban di bagian Pembahasan dengan kalimat lengkap yang benar. Jika kosong, sistem memakai urutan token di atas sebagai kunci.')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    private static function readingStorySection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Pembuat Reading Story')
            ->description('Susun dialog dan pertanyaan dalam satu alur. User melihat dialog satu per satu, audio diputar, lalu pertanyaan muncul di titik yang admin tentukan.')
            ->visible(fn (Get $get): bool => $get('type') === 'reading_story')
            ->schema([
                Forms\Components\TextInput::make('instruction')
                    ->label('Instruksi')
                    ->maxLength(255)
                    ->default('Ikuti dialog, lalu jawab pertanyaan pemahaman.'),

                Forms\Components\TextInput::make('settings.story_button_label')
                    ->label('Label Tombol Mulai')
                    ->maxLength(120)
                    ->default('Mulai Reading'),

                Forms\Components\Textarea::make('question_text')
                    ->label('Judul Cerita / Tema')
                    ->required(fn (Get $get): bool => $get('type') === 'reading_story')
                    ->rows(3)
                    ->columnSpanFull()
                    ->helperText('Contoh: Dialog perkenalan di kelas.'),

                Forms\Components\Builder::make('settings.story_flow')
                    ->label('Alur Dialog dan Pertanyaan')
                    ->blocks([
                        Forms\Components\Builder\Block::make('dialogue')
                            ->label('Dialog Tokoh')
                            ->schema([
                                Forms\Components\TextInput::make('speaker')
                                    ->label('Nama Tokoh')
                                    ->placeholder('Tokoh A / Andra')
                                    ->required(),

                                Forms\Components\Select::make('side')
                                    ->label('Posisi Dialog')
                                    ->options([
                                        'left' => 'Kiri',
                                        'right' => 'Kanan',
                                    ])
                                    ->default('left')
                                    ->required(),

                                Forms\Components\Textarea::make('text')
                                    ->label('Teks Dialog')
                                    ->rows(3)
                                    ->required()
                                    ->columnSpanFull(),

                                Forms\Components\FileUpload::make('audio_path')
                                    ->label('Upload Audio Dialog')
                                    ->acceptedFileTypes(static::audioAcceptedFileTypes())
                                    ->maxSize(51200)
                                    ->directory('learning/audio/reading/story'),

                                Forms\Components\TextInput::make('audio_manual_path')
                                    ->label('Path Audio Dialog')
                                    ->placeholder('learning/audio/generated/edge-tts/dialog-1.mp3')
                                    ->helperText('Opsional jika audio dibuat dari API Tools/Edge TTS.'),
                            ])
                            ->columns(2),

                        Forms\Components\Builder\Block::make('question')
                            ->label('Pertanyaan Pemahaman')
                            ->schema([
                                Forms\Components\Textarea::make('question_text')
                                    ->label('Pertanyaan')
                                    ->rows(2)
                                    ->required()
                                    ->columnSpanFull(),

                                Forms\Components\Repeater::make('options')
                                    ->label('Pilihan Jawaban')
                                    ->schema([
                                        Forms\Components\TextInput::make('text')
                                            ->label('Teks Jawaban')
                                            ->required(),

                                        Forms\Components\Toggle::make('is_correct')
                                            ->label('Jawaban Benar')
                                            ->default(false),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(4)
                                    ->minItems(2)
                                    ->addActionLabel('Tambah Jawaban')
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('explanation')
                                    ->label('Catatan Internal / Pembahasan')
                                    ->rows(2)
                                    ->helperText('Tidak akan langsung membocorkan jawaban saat user salah.')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->addActionLabel('Tambah Dialog atau Pertanyaan')
                    ->collapsible()
                    ->cloneable()
                    ->reorderable()
                    ->columnSpanFull(),

                Forms\Components\Placeholder::make('reading_story_hint')
                    ->label('Catatan Mode Reading')
                    ->content('Mode ini tidak memakai nyawa. Jika user salah, sistem tidak memberi jawaban benar dan user harus mencoba lagi sampai paham.')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    private static function mixedSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Pembuat Soal Mix')
            ->description('Gunakan bagian ini untuk soal campuran. Bisa memakai teks, audio, gambar, dan opsi sekaligus.')
            ->visible(fn (Get $get): bool => $get('type') === 'mixed')
            ->schema([
                Forms\Components\TextInput::make('instruction')
                    ->label('Instruksi')
                    ->maxLength(255)
                    ->default('Kerjakan soal berikut dengan teliti.'),

                Forms\Components\Textarea::make('question_text')
                    ->label('Soal')
                    ->required(fn (Get $get): bool => $get('type') === 'mixed')
                    ->rows(4)
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('audio_path')
                    ->label('Audio Opsional')
                    ->acceptedFileTypes(static::audioAcceptedFileTypes())
                    ->maxSize(51200)
                    ->directory('learning/audio/questions'),

                Forms\Components\FileUpload::make('image_path')
                    ->label('Gambar Opsional')
                    ->image()
                    ->directory('learning/images/questions')
                    ->imageEditor(),

                Forms\Components\Textarea::make('settings.mix_note')
                    ->label('Catatan Mix')
                    ->rows(3)
                    ->columnSpanFull(),

                static::optionsRepeater(
                    minItems: 0,
                    helperText: 'Opsi boleh dikosongkan jika soal mix memakai jawaban teks.'
                ),
            ])
            ->columns(2);
    }

    private static function optionsRepeater(int $minItems = 0, ?string $helperText = null): Forms\Components\Repeater
    {
        return Forms\Components\Repeater::make('options')
            ->label('Opsi Jawaban')
            ->relationship()
            ->schema([
                Forms\Components\TextInput::make('option_text')
                    ->label('Teks Opsi')
                    ->required(),

                Forms\Components\FileUpload::make('audio_path')
                    ->label('Audio Opsi')
                    ->acceptedFileTypes(static::audioAcceptedFileTypes())
                    ->maxSize(51200)
                    ->directory('learning/audio/options'),

                Forms\Components\FileUpload::make('image_path')
                    ->label('Gambar Opsi')
                    ->image()
                    ->directory('learning/images/options')
                    ->imageEditor(),

                Forms\Components\Toggle::make('is_correct')
                    ->label('Jawaban Benar')
                    ->default(false),

                Forms\Components\TextInput::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->default(0),
            ])
            ->columns(2)
            ->orderColumn('sort_order')
            ->defaultItems($minItems > 0 ? $minItems : 0)
            ->minItems($minItems)
            ->helperText($helperText)
            ->addActionLabel('Tambah Opsi')
            ->columnSpanFull();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('level.part.language.name')
                    ->label('Language')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('level.part.title')
                    ->label('Bagian')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('level.title')
                    ->label('Level')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('question_text')
                    ->label('Soal')
                    ->limit(56)
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn ($state) => LearningLevel::TYPES[$state] ?? $state),

                Tables\Columns\IconColumn::make('audio_path')
                    ->label('Audio')
                    ->boolean()
                    ->state(fn (LearningQuestion $record): bool => filled($record->audio_path)),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis Soal')
                    ->options(LearningLevel::TYPES),

                Tables\Filters\SelectFilter::make('learning_language_id')
                    ->label('Language')
                    ->options(fn () => LearningLanguage::query()->orderBy('name')->pluck('name', 'id'))
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['value'] ?? null, fn (Builder $query, $languageId) => $query
                            ->whereHas('level.part', fn (Builder $query) => $query->where('learning_language_id', $languageId)))),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLearningQuestions::route('/'),
            'create' => Pages\CreateLearningQuestion::route('/create'),
            'edit' => Pages\EditLearningQuestion::route('/{record}/edit'),
        ];
    }
}
