<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\GameModeResource\Pages;
use App\Models\GameMode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GameModeResource extends Resource
{
    protected static ?string $model = GameMode::class;

    protected static ?string $navigationGroup = 'GAME CMS';

    protected static ?string $navigationLabel = 'Game Modes';

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

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
        return $form
            ->schema([
                Forms\Components\Section::make('Game Mode')
                    ->description('Atur daftar mode game yang tampil di halaman Games. Ini membuat halaman game tidak statis.')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('key')
                            ->label('Key')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Contoh: tournament, duel_1v1, kahoot_quiz.'),

                        Forms\Components\TextInput::make('subtitle')
                            ->label('Subtitle')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('icon_label')
                            ->label('Icon')
                            ->maxLength(16)
                            ->helperText('Boleh emoji atau teks pendek.'),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('route_name')
                            ->label('Route Name')
                            ->helperText('Contoh: learning.tournament. Kosongkan jika mode belum punya halaman.')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('button_label')
                            ->label('Label Tombol')
                            ->default('Buka')
                            ->maxLength(255),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(GameMode::STATUSES)
                            ->default('coming_soon')
                            ->required(),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Tampil di Games')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),

                Tables\Columns\TextColumn::make('icon_label')
                    ->label('Icon'),

                Tables\Columns\TextColumn::make('title')
                    ->label('Game')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('subtitle')
                    ->label('Subtitle')
                    ->limit(36),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => GameMode::STATUSES[$state] ?? $state),

                Tables\Columns\TextColumn::make('route_name')
                    ->label('Route')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Tampil')
                    ->boolean(),
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
            'index' => Pages\ListGameModes::route('/'),
            'create' => Pages\CreateGameMode::route('/create'),
            'edit' => Pages\EditGameMode::route('/{record}/edit'),
        ];
    }
}
