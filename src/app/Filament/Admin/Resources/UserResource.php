<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'USER MANAGEMENT';

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $modelLabel = 'User';

    protected static ?string $pluralModelLabel = 'Users';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    /**
     * Keep this explicit so Users appears for super_admin even when
     * Filament Shield permissions have not been regenerated yet.
     */
    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user?->hasRole('super_admin') || $user?->can('view_any_user') || false;
    }

    public static function canView(Model $record): bool
    {
        $user = auth()->user();

        return $user?->hasRole('super_admin') || $user?->can('view_user') || false;
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();

        return $user?->hasRole('super_admin') || $user?->can('create_user') || false;
    }

    public static function canEdit(Model $record): bool
    {
        $user = auth()->user();

        return $user?->hasRole('super_admin') || $user?->can('update_user') || false;
    }

    public static function canDelete(Model $record): bool
    {
        $user = auth()->user();

        return $user?->hasRole('super_admin') || $user?->can('delete_user') || false;
    }

    public static function canDeleteAny(): bool
    {
        $user = auth()->user();

        return $user?->hasRole('super_admin') || $user?->can('delete_any_user') || false;
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name',
            'email',
            'roles.name',
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Role' => $record->roles->pluck('name')->implode(', ') ?: '-',
            'Email' => $record->email,
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi User')
                    ->description('Kelola akun user yang mendaftar dari frontend atau dibuat oleh admin.')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama')
                                    ->minLength(2)
                                    ->maxLength(255)
                                    ->required()
                                    ->columnSpan('full'),

                                Forms\Components\FileUpload::make('avatar_url')
                                    ->label('Avatar')
                                    ->image()
                                    ->optimize('webp')
                                    ->imageEditor()
                                    ->imagePreviewHeight('180')
                                    ->panelAspectRatio('1:1')
                                    ->panelLayout('integrated')
                                    ->directory('avatars')
                                    ->columnSpan('full'),

                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->required()
                                    ->unique(table: User::class, column: 'email', ignoreRecord: true)
                                    ->prefixIcon('heroicon-m-envelope')
                                    ->email()
                                    ->columnSpan('full'),

                                Forms\Components\TextInput::make('password')
                                    ->label('Password')
                                    ->password()
                                    ->confirmed()
                                    ->revealable()
                                    ->minLength(8)
                                    ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->required(fn (string $context): bool => $context === 'create')
                                    ->helperText('Kosongkan jika tidak ingin mengubah password.')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('password_confirmation')
                                    ->label('Konfirmasi Password')
                                    ->password()
                                    ->revealable()
                                    ->required(fn (string $context): bool => $context === 'create')
                                    ->dehydrated(false)
                                    ->columnSpan(1),
                            ]),
                    ]),

                Forms\Components\Section::make('Roles')
                    ->description('User baru dari halaman register otomatis memakai role user.')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->label('Role')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label('Avatar')
                    ->defaultImageUrl(url('https://www.gravatar.com/avatar/64e1b8d34f425d19e1ee2ea7236d3028?d=mp&r=g&s=250'))
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->sortable()
                    ->searchable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->sortable()
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->separator(',')
                    ->sortable()
                    ->searchable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label('Verifikasi Email')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Belum verified')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Daftar')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Update Terakhir')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Filter Role')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),

                Tables\Actions\EditAction::make()
                    ->label('Edit'),

                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->visible(fn (User $record): bool => auth()->id() !== $record->id),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),
            ])
            ->emptyStateHeading('Belum ada user')
            ->emptyStateDescription('User yang register dari halaman frontend akan muncul di sini.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah User'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
