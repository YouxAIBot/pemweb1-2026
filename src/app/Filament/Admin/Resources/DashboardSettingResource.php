<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DashboardSettingResource\Pages;
use App\Models\DashboardSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DashboardSettingResource extends Resource
{
    protected static ?string $model = DashboardSetting::class;
    protected static ?string $navigationGroup = 'USER DASHBOARD';
    protected static ?string $navigationLabel = 'Dashboard Settings';
    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';
    protected static ?int $navigationSort = 1;


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
            Forms\Components\Section::make('Texts')->schema([
                Forms\Components\TextInput::make('brand_text')->required(),
                Forms\Components\TextInput::make('welcome_text')->required(),
                Forms\Components\TextInput::make('adventure_text')->required(),
                Forms\Components\TextInput::make('language_title')->required(),
                Forms\Components\TextInput::make('ability_title')->required(),
                Forms\Components\TextInput::make('dashboard_title')->required(),
                Forms\Components\Textarea::make('dashboard_subtitle')->rows(3)->columnSpanFull(),
                Forms\Components\TextInput::make('part_button_label')->required(),
                Forms\Components\Select::make('theme')->options(['discord_dark' => 'Discord Dark'])->default('discord_dark'),
                Forms\Components\Toggle::make('animations_enabled')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('brand_text')->weight('bold'),
            Tables\Columns\TextColumn::make('dashboard_title'),
            Tables\Columns\IconColumn::make('animations_enabled')->boolean(),
            Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDashboardSettings::route('/'),
            'create' => Pages\CreateDashboardSetting::route('/create'),
            'edit' => Pages\EditDashboardSetting::route('/{record}/edit'),
        ];
    }
}
