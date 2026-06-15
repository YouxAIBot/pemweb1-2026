<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserLearningProfileResource\Pages;
use App\Models\UserLearningProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserLearningProfileResource extends Resource
{
    protected static ?string $model = UserLearningProfile::class;
    protected static ?string $navigationGroup = 'USER DASHBOARD';
    protected static ?string $navigationLabel = 'User Progress';
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
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
            Forms\Components\Section::make('Per-User Learning Data')->schema([
                Forms\Components\Select::make('user_id')->relationship('user', 'email')->required()->searchable()->preload(),
                Forms\Components\Select::make('learning_language_id')->relationship('language', 'name')->searchable()->preload(),
                Forms\Components\Select::make('current_part_id')->relationship('currentPart', 'title')->searchable()->preload(),
                Forms\Components\Select::make('current_level_id')->relationship('currentLevel', 'title')->searchable()->preload(),
                Forms\Components\Select::make('ability_level')->options(['beginner' => 'Pemula', 'intermediate' => 'Paham', 'master' => 'Master']),
                Forms\Components\TextInput::make('total_xp')->numeric()->default(0),
                Forms\Components\TextInput::make('streak')->numeric()->default(0),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('updated_at', 'desc')->columns([
            Tables\Columns\TextColumn::make('user.name')->label('User')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('user.email')->label('Email')->searchable(),
            Tables\Columns\TextColumn::make('language.name')->label('Language')->badge(),
            Tables\Columns\TextColumn::make('currentPart.title')->label('Current Part')->limit(28),
            Tables\Columns\TextColumn::make('currentLevel.title')->label('Current Level')->limit(28),
            Tables\Columns\TextColumn::make('ability_level')->badge(),
            Tables\Columns\TextColumn::make('total_xp')->sortable(),
            Tables\Columns\TextColumn::make('streak')->sortable(),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserLearningProfiles::route('/'),
            'create' => Pages\CreateUserLearningProfile::route('/create'),
            'edit' => Pages\EditUserLearningProfile::route('/{record}/edit'),
        ];
    }
}
