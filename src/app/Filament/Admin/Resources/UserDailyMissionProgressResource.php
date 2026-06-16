<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserDailyMissionProgressResource\Pages;
use App\Models\UserDailyMissionProgress;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserDailyMissionProgressResource extends Resource
{
    protected static ?string $model = UserDailyMissionProgress::class;

    protected static ?string $navigationGroup = 'USER DASHBOARD';

    protected static ?string $navigationLabel = 'Daily Mission Progress';

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?int $navigationSort = 5;

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
            Forms\Components\Section::make('Per-User Daily Mission Progress')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'email')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Select::make('dashboard_daily_mission_id')
                        ->relationship('mission', 'title')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\DatePicker::make('mission_date')
                        ->default(now())
                        ->required(),

                    Forms\Components\TextInput::make('progress_value')
                        ->numeric()
                        ->default(0)
                        ->required(),

                    Forms\Components\DateTimePicker::make('completed_at'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('mission_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('mission.title')
                    ->label('Mission')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('mission.mission_type')
                    ->label('Type')
                    ->badge(),

                Tables\Columns\TextColumn::make('progress_value')
                    ->label('Progress')
                    ->sortable(),

                Tables\Columns\TextColumn::make('mission.target')
                    ->label('Target'),

                Tables\Columns\TextColumn::make('mission_date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('completed_at')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Belum selesai'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('dashboard_daily_mission_id')
                    ->label('Mission')
                    ->relationship('mission', 'title')
                    ->preload(),
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
            'index' => Pages\ListUserDailyMissionProgress::route('/'),
            'create' => Pages\CreateUserDailyMissionProgress::route('/create'),
            'edit' => Pages\EditUserDailyMissionProgress::route('/{record}/edit'),
        ];
    }
}
