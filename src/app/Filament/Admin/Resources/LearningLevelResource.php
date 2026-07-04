<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LearningLevelResource\Pages;
use App\Models\LearningLanguage;
use App\Models\LearningLevel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LearningLevelResource extends Resource
{
    protected static ?string $model = LearningLevel::class;
    protected static ?string $navigationGroup = 'LEARNING CMS';
    protected static ?string $navigationLabel = 'Levels';
    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?int $navigationSort = 3;


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
            Forms\Components\Section::make('Level')->schema([
                Forms\Components\Select::make('learning_part_id')->label('Bagian')->relationship('part', 'title')->required()->searchable()->preload(),
                Forms\Components\TextInput::make('title')->required()->maxLength(255),
                Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(255),
                Forms\Components\Select::make('type')->label('Mode Level')->options(LearningLevel::TYPES)->required()->default('mixed')->helperText('Level sekarang adalah wadah soal. Gunakan Mix agar dalam satu level bisa berisi pilihan ganda, listening, sambung kata, dan jenis soal lain.'),
                Forms\Components\TextInput::make('short_label')->helperText('Label pendek di node map, contoh: 1, A1, L3.'),
                Forms\Components\Textarea::make('description')->rows(3)->columnSpanFull(),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                Forms\Components\TextInput::make('xp_reward')->numeric()->default(10),
                Forms\Components\TextInput::make('passing_score')->numeric()->default(70),
                Forms\Components\Toggle::make('is_premium')
                    ->label('Level Premium')
                    ->helperText('Jika aktif, hanya user premium yang bisa membuka level ini.')
                    ->default(false),
                Forms\Components\TextInput::make('position_x')->numeric()->minValue(5)->maxValue(95)->default(50)->helperText('Posisi node map 5-95.'),
                Forms\Components\TextInput::make('position_y')->numeric()->minValue(10)->maxValue(92)->default(50)->helperText('Posisi node map 10-92.'),
                Forms\Components\Toggle::make('is_active')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort_order')->columns([
            Tables\Columns\TextColumn::make('part.language.name')->label('Language')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('part.title')->label('Bagian')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('title')->searchable()->sortable()->weight('bold'),
            Tables\Columns\TextColumn::make('type')->label('Mode')->badge()->formatStateUsing(fn ($state) => LearningLevel::TYPES[$state] ?? $state)->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('questions_count')->counts('questions')->label('Questions'),
            Tables\Columns\IconColumn::make('is_premium')->label('Premium')->boolean(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
            Tables\Columns\TextColumn::make('sort_order')->sortable(),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('learning_language_id')
                ->label('Language')
                ->options(fn () => LearningLanguage::query()->orderBy('name')->pluck('name', 'id'))
                ->query(fn (Builder $query, array $data): Builder => $query
                    ->when($data['value'] ?? null, fn (Builder $query, $languageId) => $query
                        ->whereHas('part', fn (Builder $query) => $query->where('learning_language_id', $languageId)))),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])->bulkActions([
            Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLearningLevels::route('/'),
            'create' => Pages\CreateLearningLevel::route('/create'),
            'edit' => Pages\EditLearningLevel::route('/{record}/edit'),
        ];
    }
}
