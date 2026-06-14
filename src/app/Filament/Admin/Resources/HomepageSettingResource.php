<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\HomepageSettingResource\Pages;
use App\Models\HomepageSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HomepageSettingResource extends Resource
{
    protected static ?string $model = HomepageSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'HOMEPAGE';

    protected static ?string $navigationLabel = 'Pengaturan Umum';

    protected static ?string $modelLabel = 'Pengaturan Homepage';

    protected static ?string $pluralModelLabel = 'Pengaturan Homepage';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Brand & SEO')
                    ->description('Kontrol nama brand, logo, title browser, dan deskripsi halaman.')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('site_name')
                                    ->label('Nama Website')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('brand_text')
                                    ->label('Teks Brand Navbar')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('brand_initial')
                                    ->label('Inisial Logo')
                                    ->required()
                                    ->maxLength(5),
                            ]),
                        Forms\Components\FileUpload::make('brand_logo_path')
                            ->label('Logo Brand')
                            ->image()
                            ->disk('public')
                            ->directory('homepage/brand')
                            ->imageEditor()
                            ->helperText('Opsional. Jika kosong, navbar memakai inisial logo.'),
                        Forms\Components\TextInput::make('meta_title')
                            ->label('Meta Title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('meta_description')
                            ->label('Meta Description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Footer & Cursor')
                    ->description('Kontrol teks footer dan efek cursor glow pada frontend.')
                    ->schema([
                        Forms\Components\TextInput::make('footer_left')
                            ->label('Footer Kiri')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('footer_right')
                            ->label('Footer Kanan')
                            ->maxLength(255),
                        Forms\Components\Toggle::make('cursor_glow_enabled')
                            ->label('Aktifkan Cursor Glow')
                            ->default(true),
                        Forms\Components\TextInput::make('cursor_glow_size')
                            ->label('Ukuran Glow Cursor')
                            ->numeric()
                            ->minValue(8)
                            ->maxValue(40)
                            ->default(18)
                            ->suffix('px'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('site_name')
                    ->label('Website')
                    ->searchable(),
                Tables\Columns\TextColumn::make('brand_text')
                    ->label('Brand')
                    ->searchable(),
                Tables\Columns\TextColumn::make('meta_title')
                    ->label('Meta Title')
                    ->limit(40),
                Tables\Columns\IconColumn::make('cursor_glow_enabled')
                    ->label('Cursor Glow')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Update')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function canCreate(): bool
    {
        return HomepageSetting::query()->count() === 0;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHomepageSettings::route('/'),
            'create' => Pages\CreateHomepageSetting::route('/create'),
            'edit' => Pages\EditHomepageSetting::route('/{record}/edit'),
        ];
    }
}
