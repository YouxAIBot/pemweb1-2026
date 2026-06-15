<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\HomepageSectionResource\Pages;
use App\Models\HomepageSection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;

class HomepageSectionResource extends Resource
{
    protected static ?string $model = HomepageSection::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'HOMEPAGE';

    protected static ?string $navigationLabel = 'Sections';

    protected static ?string $modelLabel = 'Section Homepage';

    protected static ?string $pluralModelLabel = 'Sections Homepage';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identitas Section')
                    ->description('Gunakan Section 1, Section 2, dan seterusnya untuk mengontrol homepage dari admin panel.')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('section_key')
                                    ->label('Kode Section')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Contoh: hero, languages, tournament, cta. Jangan diubah kalau sudah dipakai frontend.'),
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Section')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Contoh: Section 1 - Hero'),
                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Urutan')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),
                            ]),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Tampilkan section di frontend')
                            ->default(true),
                    ]),
                Forms\Components\Section::make('Konten Teks')
                    ->description('Kontrol teks besar, teks kecil, dan deskripsi section.')
                    ->schema([
                        Forms\Components\TextInput::make('kicker')
                            ->label('Label Kecil / Kicker')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('title')
                            ->label('Judul Utama')
                            ->rows(2)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Tombol & Gambar')
                    ->description('Kontrol CTA dan gambar utama section. Jika gambar kosong, frontend memakai visual bawaan.')
                    ->schema([
                        Forms\Components\TextInput::make('primary_button_label')
                            ->label('Label Tombol Utama')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('primary_button_url')
                            ->label('URL Tombol Utama')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('secondary_button_label')
                            ->label('Label Tombol Kedua')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('secondary_button_url')
                            ->label('URL Tombol Kedua')
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('image_path')
                            ->label('Gambar Section')
                            ->image()
                            ->disk('public')
                            ->directory('homepage/sections')
                            ->imageEditor()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Item di Dalam Section')
                    ->description('Dipakai untuk kartu bahasa di Section 2, daftar fitur di Section 3, atau item custom lain.')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->label('Items')
                            ->relationship()
                            ->orderColumn('sort_order')
                            ->defaultItems(0)
                            ->collapsed()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? $state['label'] ?? $state['item_key'] ?? 'Item')
                            ->schema([
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('item_key')
                                            ->label('Kode Item')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('label')
                                            ->label('Label / Nomor')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('sort_order')
                                            ->label('Urutan')
                                            ->numeric()
                                            ->default(0),
                                    ]),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('accent_text')
                                            ->label('Aksen Teks')
                                            ->maxLength(255)
                                            ->helperText('Contoh untuk kartu bahasa: 你好, 안녕, Hello.'),
                                        Forms\Components\TextInput::make('badge_text')
                                            ->label('Badge / Status')
                                            ->maxLength(255)
                                            ->helperText('Contoh: Tersedia, Segera.'),
                                    ]),
                                Forms\Components\TextInput::make('title')
                                    ->label('Judul Item')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('subtitle')
                                    ->label('Subtitle')
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('description')
                                    ->label('Deskripsi Item')
                                    ->rows(3),
                                Forms\Components\TextInput::make('url')
                                    ->label('URL Item')
                                    ->maxLength(255),
                                Forms\Components\FileUpload::make('image_path')
                                    ->label('Gambar Item')
                                    ->image()
                                    ->disk('public')
                                    ->directory('homepage/items')
                                    ->imageEditor(),
                                Forms\Components\Textarea::make('icon_svg')
                                    ->label('Icon SVG Opsional')
                                    ->rows(3)
                                    ->helperText('Opsional. Bisa dikosongkan.'),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true),
                            ])
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Advanced')
                    ->collapsed()
                    ->schema([
                        Forms\Components\KeyValue::make('settings')
                            ->label('Custom Settings JSON')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->columnSpanFull(),
                    ]),
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Section')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('section_key')
                    ->label('Kode')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Update')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('items')->orderBy('sort_order');
    }



    public static function canCreate(): bool
    {
        return true;
    }

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function canEdit($record): bool
    {
        return true;
    }

    public static function canDelete($record): bool
    {
        return true;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHomepageSections::route('/'),
            'create' => Pages\CreateHomepageSection::route('/create'),
            'edit' => Pages\EditHomepageSection::route('/{record}/edit'),
        ];
    }
}
