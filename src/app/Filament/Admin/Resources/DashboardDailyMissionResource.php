<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DashboardDailyMissionResource\Pages;
use App\Models\DashboardDailyMission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DashboardDailyMissionResource extends Resource
{
    protected static ?string $model = DashboardDailyMission::class;
    protected static ?string $navigationGroup = 'USER DASHBOARD';
    protected static ?string $navigationLabel = 'Daily Missions';
    protected static ?string $navigationIcon = 'heroicon-o-check-badge';
    protected static ?int $navigationSort = 3;


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
            Forms\Components\Section::make('Mission')->schema([
                Forms\Components\TextInput::make('title')->required(),
                Forms\Components\Select::make('mission_type')
                    ->label('Mission Type')
                    ->options([
                        'questions_answered' => 'Jumlah soal dikerjakan',
                        'study_minutes' => 'Menit belajar',
                        'levels_completed' => 'Level selesai',
                    ])
                    ->default('questions_answered')
                    ->required(),
                Forms\Components\TextInput::make('target')->numeric()->default(1)->required(),
                Forms\Components\TextInput::make('default_progress')->numeric()->default(0),
                Forms\Components\TextInput::make('unit_label')->default('soal'),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                Forms\Components\Toggle::make('is_active')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort_order')->columns([
            Tables\Columns\TextColumn::make('title')->searchable()->sortable()->weight('bold'),
            Tables\Columns\TextColumn::make('mission_type')->badge()->sortable(),
            Tables\Columns\TextColumn::make('target'),
            Tables\Columns\TextColumn::make('unit_label'),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
            Tables\Columns\TextColumn::make('sort_order')->sortable(),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDashboardDailyMissions::route('/'),
            'create' => Pages\CreateDashboardDailyMission::route('/create'),
            'edit' => Pages\EditDashboardDailyMission::route('/{record}/edit'),
        ];
    }
}
