<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LearningQuestionResource\Pages;
use App\Models\LearningLevel;
use App\Models\LearningQuestion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

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
                static::realCaseSection(),
                static::mixedSection(),

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
            ->description('Listening dibuat sebagai alur bebas: admin bisa menambah Kalimat + Audio beberapa kali, lalu menambah Soal + Jawaban, lalu lanjut cerita lagi sesuka kebutuhan.')
            ->visible(fn (Get $get): bool => $get('type') === 'listening')
            ->schema([
                Forms\Components\TextInput::make('instruction')
                    ->label('Instruksi')
                    ->maxLength(255)
                    ->default('Dengarkan cerita, lalu jawab pertanyaan sampai benar.'),

                Forms\Components\TextInput::make('settings.story_button_label')
                    ->label('Label Tombol Mulai')
                    ->maxLength(120)
                    ->default('Mulai'),

                Forms\Components\Placeholder::make('api_tools_hint')
                    ->label('Generate Audio via API')
                    ->content('Buka menu API INTEGRATION → API Tools untuk generate audio otomatis dari teks. Copy storage path hasil generate ke field Audio Cerita atau Audio Pertanyaan jika dibutuhkan.')
                    ->columnSpanFull(),

                Forms\Components\Builder::make('settings.listening_flow')
                    ->label('Alur Listening')
                    ->helperText('Tambahkan blok Kalimat + Audio untuk cerita, lalu blok Soal + Jawaban saat ingin memunculkan pertanyaan. Urutannya bebas: cerita, cerita, soal, cerita, soal, dan seterusnya.')
                    ->blocks([
                        Forms\Components\Builder\Block::make('story')
                            ->label('Kalimat + Audio')
                            ->schema([
                                Forms\Components\Textarea::make('story_text')
                                    ->label('Kalimat Cerita')
                                    ->rows(4)
                                    ->required()
                                    ->columnSpanFull()
                                    ->helperText('Kalimat ini akan tampil di layar. Setelah user klik Mulai, audio diputar otomatis sampai selesai.'),

                                Forms\Components\FileUpload::make('story_audio_path')
                                    ->label('Audio Cerita')
                                    ->acceptedFileTypes([
                                        'audio/mpeg',
                                        'audio/wav',
                                        'audio/ogg',
                                        'audio/mp4',
                                        'audio/x-m4a',
                                    ])
                                    ->directory('learning/audio/listening/story')
                                    ->columnSpanFull(),
                            ])
                            ->columns(1),

                        Forms\Components\Builder\Block::make('question')
                            ->label('Soal + Jawaban')
                            ->schema([
                                Forms\Components\Textarea::make('question_text')
                                    ->label('Soal')
                                    ->rows(3)
                                    ->required()
                                    ->columnSpanFull(),

                                Forms\Components\FileUpload::make('question_audio_path')
                                    ->label('Audio Pertanyaan')
                                    ->acceptedFileTypes([
                                        'audio/mpeg',
                                        'audio/wav',
                                        'audio/ogg',
                                        'audio/mp4',
                                        'audio/x-m4a',
                                    ])
                                    ->directory('learning/audio/listening/questions')
                                    ->columnSpanFull()
                                    ->helperText('Opsional. Jika diupload, pilihan jawaban baru muncul setelah audio pertanyaan selesai dan delay sekitar 1 detik.'),

                                Forms\Components\Repeater::make('options')
                                    ->label('Pilihan Jawaban')
                                    ->schema([
                                        Forms\Components\TextInput::make('text')
                                            ->label('Teks Opsi')
                                            ->required(),

                                        Forms\Components\Toggle::make('is_correct')
                                            ->label('Jawaban Benar')
                                            ->default(false),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(2)
                                    ->minItems(2)
                                    ->addActionLabel('Tambah Pilihan Jawaban')
                                    ->columnSpanFull()
                                    ->helperText('Pilihan jawaban bebas sebanyak kebutuhan admin. Tandai minimal satu sebagai benar.'),

                                Forms\Components\Textarea::make('explanation')
                                    ->label('Pembahasan')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->columns(1),
                    ])
                    ->addActionLabel('Tambah Kalimat / Soal')
                    ->collapsible()
                    ->cloneable()
                    ->reorderable()
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('question_text')
                    ->label('Judul Internal / Fallback')
                    ->rows(2)
                    ->default('Listening flow')
                    ->required(fn (Get $get): bool => $get('type') === 'listening')
                    ->helperText('Dipakai sebagai judul internal/fallback. Soal utama bisa dibuat lewat blok Soal + Jawaban pada Alur Listening.')
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
                            ->acceptedFileTypes([
                                'audio/mpeg',
                                'audio/wav',
                                'audio/ogg',
                                'audio/mp4',
                                'audio/x-m4a',
                            ])
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

    private static function realCaseSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Pembuat Soal Situasi Nyata')
            ->description('Gunakan bagian ini untuk membuat soal berbasis konteks kehidupan nyata.')
            ->visible(fn (Get $get): bool => $get('type') === 'real_case')
            ->schema([
                Forms\Components\TextInput::make('instruction')
                    ->label('Instruksi')
                    ->maxLength(255)
                    ->default('Baca situasi lalu pilih respons yang paling natural.'),

                Forms\Components\Textarea::make('settings.scenario_context')
                    ->label('Konteks Skenario')
                    ->required(fn (Get $get): bool => $get('type') === 'real_case')
                    ->rows(4)
                    ->columnSpanFull()
                    ->helperText('Contoh: Kamu sedang di restoran dan ingin memesan makanan.'),

                Forms\Components\Textarea::make('question_text')
                    ->label('Pertanyaan')
                    ->required(fn (Get $get): bool => $get('type') === 'real_case')
                    ->rows(4)
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('settings.ideal_response')
                    ->label('Respons Ideal')
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('image_path')
                    ->label('Gambar Skenario')
                    ->image()
                    ->directory('learning/images/questions')
                    ->imageEditor()
                    ->columnSpanFull(),

                static::optionsRepeater(
                    minItems: 2,
                    helperText: 'Isi pilihan respons yang mungkin dipilih user.'
                ),
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
                    ->acceptedFileTypes([
                        'audio/mpeg',
                        'audio/wav',
                        'audio/ogg',
                        'audio/mp4',
                        'audio/x-m4a',
                    ])
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
                    ->acceptedFileTypes([
                        'audio/mpeg',
                        'audio/wav',
                        'audio/ogg',
                        'audio/mp4',
                        'audio/x-m4a',
                    ])
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
