<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LearningLanguageResource\Pages;
use App\Models\LearningLanguage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LearningLanguageResource extends Resource
{
    protected static ?string $model = LearningLanguage::class;
    protected static ?string $navigationGroup = 'LEARNING CMS';
    protected static ?string $navigationLabel = 'Languages';
    protected static ?string $navigationIcon = 'heroicon-o-language';
    protected static ?int $navigationSort = 1;


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
            Forms\Components\Section::make('Language Content')->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(255),
                Forms\Components\TextInput::make('native_name')->label('Native Text')->maxLength(255),
                Forms\Components\TextInput::make('flag_label')->label('Flag / Code')->maxLength(30),
                Forms\Components\Textarea::make('description')->rows(3)->columnSpanFull(),
                Forms\Components\ColorPicker::make('accent_color'),
                Forms\Components\FileUpload::make('image_path')->label('Image')->image()->directory('learning/languages')->imageEditor(),
                Forms\Components\Toggle::make('is_active')->default(true),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort_order')->columns([
            Tables\Columns\TextColumn::make('name')->searchable()->sortable()->weight('bold'),
            Tables\Columns\TextColumn::make('native_name')->label('Native')->searchable(),
            Tables\Columns\TextColumn::make('slug')->searchable()->toggleable(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
            Tables\Columns\TextColumn::make('sort_order')->sortable(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListLearningLanguages::route('/'),
            'create' => Pages\CreateLearningLanguage::route('/create'),
            'edit' => Pages\EditLearningLanguage::route('/{record}/edit'),
        ];
    }
}
