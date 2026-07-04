<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LanguageLetterResource\Pages;
use App\Models\LanguageLetter;
use App\Models\LearningLanguage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LanguageLetterResource extends Resource
{
    protected static ?string $model = LanguageLetter::class;
    protected static ?string $navigationGroup = 'LEARNING CMS';
    protected static ?string $navigationLabel = 'Huruf Bahasa';
    protected static ?string $navigationIcon = 'heroicon-o-language';
    protected static ?int $navigationSort = 4;

    private static function allowAdmin(): bool
    {
        $user = auth()->user();

        return (bool) ($user && ($user->hasRole('super_admin') || $user->email === 'admin@admin.com'));
    }

    public static function canViewAny(): bool { return static::allowAdmin(); }
    public static function canCreate(): bool { return static::allowAdmin(); }
    public static function canEdit($record): bool { return static::allowAdmin(); }
    public static function canDelete($record): bool { return static::allowAdmin(); }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Data Huruf')->schema([
                Forms\Components\Select::make('learning_language_id')
                    ->label('Bahasa')
                    ->options(fn () => LearningLanguage::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('symbol')
                    ->label('Huruf/Karakter')
                    ->required()
                    ->maxLength(40),
                Forms\Components\TextInput::make('reading')
                    ->label('Cara Baca')
                    ->maxLength(160),
                Forms\Components\TextInput::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('example_word')
                    ->label('Contoh Kata')
                    ->maxLength(180),
                Forms\Components\TextInput::make('example_translation')
                    ->label('Arti Contoh')
                    ->maxLength(180),
                Forms\Components\FileUpload::make('audio_path')
                    ->label('Upload Suara')
                    ->acceptedFileTypes(['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg', 'audio/webm', 'audio/mp4'])
                    ->maxSize(5120)
                    ->directory('letters/audio'),
                Forms\Components\TextInput::make('audio_url')
                    ->label('Audio URL')
                    ->url()
                    ->maxLength(500),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ])->columns(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('language.name')->label('Bahasa')->badge()->sortable()->searchable(),
                Tables\Columns\TextColumn::make('symbol')->label('Huruf')->size('lg')->weight('bold')->searchable(),
                Tables\Columns\TextColumn::make('reading')->label('Cara Baca')->searchable(),
                Tables\Columns\TextColumn::make('example_word')->label('Contoh')->searchable(),
                Tables\Columns\TextColumn::make('sort_order')->label('Urutan')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('learning_language_id')
                    ->label('Bahasa')
                    ->options(fn () => LearningLanguage::query()->orderBy('name')->pluck('name', 'id')),
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
            'index' => Pages\ListLanguageLetters::route('/'),
            'create' => Pages\CreateLanguageLetter::route('/create'),
            'edit' => Pages\EditLanguageLetter::route('/{record}/edit'),
        ];
    }
}
