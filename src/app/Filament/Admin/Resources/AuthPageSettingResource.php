<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AuthPageSettingResource\Pages;
use App\Models\AuthPageSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AuthPageSettingResource extends Resource
{
    protected static ?string $model = AuthPageSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationGroup = 'AUTH PAGES';

    protected static ?string $navigationLabel = 'Login, Register & Forgot Text';

    protected static ?string $modelLabel = 'Teks Auth Page';

    protected static ?string $pluralModelLabel = 'Teks Login, Register & Forgot';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identitas Page')
                    ->description('Kontrol teks untuk halaman login, register, dan forgot password. Tidak mengubah fitur HOMEPAGE.')
                    ->schema([
                        Forms\Components\Select::make('page_key')
                            ->label('Page')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->options([
                                'login' => 'Login Page',
                                'register' => 'Register Page',
                                'forgot' => 'Forgot Password Page',
                            ]),
                        Forms\Components\TextInput::make('page_name')
                            ->label('Nama Page di Admin')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Teks Utama')
                    ->description('Teks yang tampil di bagian atas form. Buat singkat supaya halaman tetap simple.')
                    ->schema([
                        Forms\Components\TextInput::make('kicker')
                            ->label('Kicker / Label Kecil')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi Singkat')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Label Input & Tombol')
                    ->description('Kontrol label field, label captcha penjumlahan, dan teks tombol submit.')
                    ->schema([
                        Forms\Components\TextInput::make('identifier_label')
                            ->label('Label Nama/Email Login')
                            ->maxLength(255)
                            ->helperText('Dipakai di Login Page.'),
                        Forms\Components\TextInput::make('name_label')
                            ->label('Label Nama Register')
                            ->maxLength(255)
                            ->helperText('Dipakai di Register Page.'),
                        Forms\Components\TextInput::make('email_label')
                            ->label('Label Email')
                            ->maxLength(255)
                            ->helperText('Dipakai di Register dan Forgot Password.'),
                        Forms\Components\TextInput::make('password_label')
                            ->label('Label Password')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('captcha_label')
                            ->label('Label Verifikasi Penjumlahan')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('submit_label')
                            ->label('Teks Tombol Submit')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Link Bawah Form')
                    ->description('Kontrol teks link lupa password, register, login, dan kembali ke homepage.')
                    ->schema([
                        Forms\Components\TextInput::make('forgot_password_label')
                            ->label('Teks Forgot Password')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('register_prompt')
                            ->label('Prompt Register')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('register_link_label')
                            ->label('Label Link Register')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('login_prompt')
                            ->label('Prompt Login')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('login_link_label')
                            ->label('Label Link Login')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('back_home_label')
                            ->label('Label Kembali ke Homepage')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Pesan Sistem')
                    ->schema([
                        Forms\Components\TextInput::make('success_message')
                            ->label('Pesan setelah berhasil')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('page_key')
                    ->label('Page')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('page_name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->limit(45)
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
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
            'index' => Pages\ListAuthPageSettings::route('/'),
            'create' => Pages\CreateAuthPageSetting::route('/create'),
            'edit' => Pages\EditAuthPageSetting::route('/{record}/edit'),
        ];
    }
}
