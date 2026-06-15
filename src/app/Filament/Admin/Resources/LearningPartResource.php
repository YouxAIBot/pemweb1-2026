<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LearningPartResource\Pages;
use App\Models\LearningPart;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LearningPartResource extends Resource
{
    protected static ?string $model = LearningPart::class;
    protected static ?string $navigationGroup = 'LEARNING CMS';
    protected static ?string $navigationLabel = 'Parts / Bagian';
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?int $navigationSort = 2;


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
            Forms\Components\Section::make('Bagian')->schema([
                Forms\Components\Select::make('learning_language_id')->label('Language')->relationship('language', 'name')->required()->searchable()->preload(),
                Forms\Components\TextInput::make('title')->required()->maxLength(255),
                Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(255),
                Forms\Components\TextInput::make('subtitle')->maxLength(255),
                Forms\Components\TextInput::make('badge_text')->maxLength(255),
                Forms\Components\Textarea::make('description')->rows(3)->columnSpanFull(),
                Forms\Components\FileUpload::make('image_path')->image()->directory('learning/parts')->imageEditor(),
                Forms\Components\TextInput::make('level_number')->label('Level Group Number')->numeric()->default(1),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                Forms\Components\Toggle::make('is_active')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort_order')->columns([
            Tables\Columns\TextColumn::make('language.name')->label('Language')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('title')->searchable()->sortable()->weight('bold'),
            Tables\Columns\TextColumn::make('levels_count')->counts('levels')->label('Levels'),
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
            'index' => Pages\ListLearningParts::route('/'),
            'create' => Pages\CreateLearningPart::route('/create'),
            'edit' => Pages\EditLearningPart::route('/{record}/edit'),
        ];
    }
}
