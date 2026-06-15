<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LearningQuestionResource\Pages;
use App\Models\LearningLevel;
use App\Models\LearningQuestion;
use Filament\Forms;
use Filament\Forms\Form;
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
        return $form->schema([
            Forms\Components\Section::make('Question')->schema([
                Forms\Components\Select::make('learning_level_id')->label('Level')->relationship('level', 'title')->required()->searchable()->preload(),
                Forms\Components\Select::make('type')->options(LearningLevel::TYPES)->required()->default('multiple_choice'),
                Forms\Components\TextInput::make('instruction')->maxLength(255),
                Forms\Components\Textarea::make('question_text')->required()->rows(3)->columnSpanFull(),
                Forms\Components\FileUpload::make('audio_path')->label('Audio Soal / Listening')->acceptedFileTypes(['audio/mpeg','audio/wav','audio/ogg','audio/mp4','audio/x-m4a'])->directory('learning/audio/questions'),
                Forms\Components\FileUpload::make('image_path')->label('Gambar Soal')->image()->directory('learning/images/questions')->imageEditor(),
                Forms\Components\Textarea::make('correct_answer')->rows(2)->columnSpanFull(),
                Forms\Components\Textarea::make('explanation')->rows(3)->columnSpanFull(),
                Forms\Components\TextInput::make('points')->numeric()->default(10),
                Forms\Components\TextInput::make('time_limit')->numeric()->suffix('detik'),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                Forms\Components\Toggle::make('is_active')->default(true),
            ])->columns(2),
            Forms\Components\Section::make('Options')->description('Untuk pilihan ganda, word match, atau listening option. Audio per pilihan juga bisa diupload.')->schema([
                Forms\Components\Repeater::make('options')
                    ->relationship()
                    ->schema([
                        Forms\Components\TextInput::make('option_text')->required(),
                        Forms\Components\FileUpload::make('audio_path')->label('Audio Option')->acceptedFileTypes(['audio/mpeg','audio/wav','audio/ogg','audio/mp4','audio/x-m4a'])->directory('learning/audio/options'),
                        Forms\Components\FileUpload::make('image_path')->label('Image Option')->image()->directory('learning/images/options')->imageEditor(),
                        Forms\Components\Toggle::make('is_correct')->default(false),
                        Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                    ])
                    ->columns(2)
                    ->orderColumn('sort_order')
                    ->defaultItems(0)
                    ->addActionLabel('Tambah Option'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort_order')->columns([
            Tables\Columns\TextColumn::make('level.part.language.name')->label('Language')->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('level.title')->label('Level')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('question_text')->limit(50)->searchable()->weight('bold'),
            Tables\Columns\TextColumn::make('type')->badge()->formatStateUsing(fn ($state) => LearningLevel::TYPES[$state] ?? $state),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
            Tables\Columns\TextColumn::make('sort_order')->sortable(),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])->bulkActions([
            Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
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
