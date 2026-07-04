<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PremiumPackageResource\Pages;
use App\Models\PremiumPackage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PremiumPackageResource extends Resource
{
    protected static ?string $model = PremiumPackage::class;
    protected static ?string $navigationGroup = 'MONETIZATION';
    protected static ?string $navigationLabel = 'Premium Packages';
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
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
            Forms\Components\Section::make('Paket Premium')->schema([
                Forms\Components\TextInput::make('name')->label('Nama Paket')->required()->maxLength(160),
                Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(180),
                Forms\Components\TextInput::make('price')->label('Harga')->numeric()->prefix('Rp')->required()->default(25000),
                Forms\Components\TextInput::make('duration_days')->label('Durasi')->numeric()->suffix('hari')->required()->default(30),
                Forms\Components\Textarea::make('description')->label('Deskripsi')->rows(3)->columnSpanFull(),
                Forms\Components\TagsInput::make('benefits')->label('Benefit')->placeholder('Tambah benefit')->columnSpanFull(),
                Forms\Components\TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
                Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort_order')->columns([
            Tables\Columns\TextColumn::make('name')->label('Paket')->searchable()->sortable()->weight('bold'),
            Tables\Columns\TextColumn::make('price')->label('Harga')->money('IDR')->sortable(),
            Tables\Columns\TextColumn::make('duration_days')->label('Durasi')->suffix(' hari')->sortable(),
            Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            Tables\Columns\TextColumn::make('sort_order')->label('Urutan')->sortable(),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPremiumPackages::route('/'),
            'create' => Pages\CreatePremiumPackage::route('/create'),
            'edit' => Pages\EditPremiumPackage::route('/{record}/edit'),
        ];
    }
}
