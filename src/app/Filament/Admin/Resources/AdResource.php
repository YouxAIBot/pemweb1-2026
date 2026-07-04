<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AdResource\Pages;
use App\Models\Ad;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AdResource extends Resource
{
    protected static ?string $model = Ad::class;
    protected static ?string $navigationGroup = 'MONETIZATION';
    protected static ?string $navigationLabel = 'Ads';
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
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
            Forms\Components\Section::make('Konten Iklan')->schema([
                Forms\Components\TextInput::make('title')->label('Judul')->required()->maxLength(160),
                Forms\Components\Select::make('placement')->label('Placement')->options(Ad::PLACEMENTS)->required()->default('level_entry'),
                Forms\Components\Textarea::make('description')->label('Deskripsi')->rows(3)->columnSpanFull(),
                Forms\Components\FileUpload::make('video_path')
                    ->label('Upload Video')
                    ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime', 'video/x-m4v'])
                    ->maxSize(20480)
                    ->directory('ads/videos')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('video_url')->label('Video URL')->url()->maxLength(500)->columnSpanFull(),
                Forms\Components\TextInput::make('target_url')->label('Target URL')->url()->maxLength(500)->columnSpanFull(),
                Forms\Components\TextInput::make('duration_seconds')->label('Durasi')->numeric()->suffix('detik')->default(15)->required(),
                Forms\Components\DateTimePicker::make('starts_at')->label('Mulai Tayang'),
                Forms\Components\DateTimePicker::make('ends_at')->label('Selesai Tayang'),
                Forms\Components\TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
                Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort_order')->columns([
            Tables\Columns\TextColumn::make('title')->label('Iklan')->searchable()->sortable()->weight('bold'),
            Tables\Columns\TextColumn::make('placement')->label('Placement')->badge()->formatStateUsing(fn ($state) => Ad::PLACEMENTS[$state] ?? $state),
            Tables\Columns\TextColumn::make('duration_seconds')->label('Durasi')->suffix(' detik'),
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
            'index' => Pages\ListAds::route('/'),
            'create' => Pages\CreateAd::route('/create'),
            'edit' => Pages\EditAd::route('/{record}/edit'),
        ];
    }
}
