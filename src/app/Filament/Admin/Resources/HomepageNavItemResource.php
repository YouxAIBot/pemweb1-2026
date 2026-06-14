<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\HomepageNavItemResource\Pages;
use App\Models\HomepageNavItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HomepageNavItemResource extends Resource
{
    protected static ?string $model = HomepageNavItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-3-bottom-left';

    protected static ?string $navigationGroup = 'HOMEPAGE';

    protected static ?string $navigationLabel = 'Navbar Menu';

    protected static ?string $modelLabel = 'Menu Navbar';

    protected static ?string $pluralModelLabel = 'Menu Navbar';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Menu Navbar')
                    ->description('Kontrol label, URL, style tombol, urutan, dan status menu di navbar frontend.')
                    ->schema([
                        Forms\Components\TextInput::make('label')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('url')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Contoh: #languages, #tournament, /admin, /login'),
                        Forms\Components\Select::make('style')
                            ->required()
                            ->options([
                                'link' => 'Link biasa',
                                'soft' => 'Tombol soft',
                                'primary' => 'Tombol primary',
                                'ghost' => 'Tombol ghost',
                            ])
                            ->default('link'),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
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
                Tables\Columns\TextColumn::make('label')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('url')
                    ->searchable(),
                Tables\Columns\TextColumn::make('style')
                    ->badge(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
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
        return parent::getEloquentQuery()->orderBy('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHomepageNavItems::route('/'),
            'create' => Pages\CreateHomepageNavItem::route('/create'),
            'edit' => Pages\EditHomepageNavItem::route('/{record}/edit'),
        ];
    }
}
