<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DashboardMenuResource\Pages;
use App\Models\DashboardMenu;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DashboardMenuResource extends Resource
{
    protected static ?string $model = DashboardMenu::class;
    protected static ?string $navigationGroup = 'USER DASHBOARD';
    protected static ?string $navigationLabel = 'Sidebar Menus';
    protected static ?string $navigationIcon = 'heroicon-o-bars-3';
    protected static ?int $navigationSort = 2;


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
            Forms\Components\Section::make('Menu')->schema([
                Forms\Components\TextInput::make('label')->required(),
                Forms\Components\TextInput::make('url')->placeholder('/dashboard atau #'),
                Forms\Components\TextInput::make('icon_label')->maxLength(20)->helperText('Contoh: Aa, 文, ⚡'),
                Forms\Components\Select::make('menu_group')->options(['main' => 'Main', 'secondary' => 'Secondary'])->default('main')->required(),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                Forms\Components\Toggle::make('is_active')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort_order')->columns([
            Tables\Columns\TextColumn::make('label')->searchable()->sortable()->weight('bold'),
            Tables\Columns\TextColumn::make('url')->toggleable(),
            Tables\Columns\TextColumn::make('icon_label')->label('Icon'),
            Tables\Columns\TextColumn::make('menu_group')->badge(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
            Tables\Columns\TextColumn::make('sort_order')->sortable(),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDashboardMenus::route('/'),
            'create' => Pages\CreateDashboardMenu::route('/create'),
            'edit' => Pages\EditDashboardMenu::route('/{record}/edit'),
        ];
    }
}
