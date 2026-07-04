<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PremiumPaymentResource\Pages;
use App\Models\PremiumPayment;
use App\Services\PremiumActivationService;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PremiumPaymentResource extends Resource
{
    protected static ?string $model = PremiumPayment::class;
    protected static ?string $navigationGroup = 'MONETIZATION';
    protected static ?string $navigationLabel = 'Premium Payments';
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?int $navigationSort = 2;

    private static function allowAdmin(): bool
    {
        $user = auth()->user();

        return (bool) ($user && ($user->hasRole('super_admin') || $user->email === 'admin@admin.com'));
    }

    public static function canViewAny(): bool { return static::allowAdmin(); }
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return static::allowAdmin(); }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')->columns([
            Tables\Columns\TextColumn::make('payment_code')->label('Kode')->searchable()->copyable()->weight('bold'),
            Tables\Columns\TextColumn::make('user.name')->label('User')->searchable(),
            Tables\Columns\TextColumn::make('package.name')->label('Paket')->placeholder('-'),
            Tables\Columns\TextColumn::make('amount')->label('Nominal')->money('IDR')->sortable(),
            Tables\Columns\TextColumn::make('payment_status')
                ->label('Status')
                ->badge()
                ->formatStateUsing(fn ($state) => PremiumPayment::STATUSES[$state] ?? $state)
                ->color(fn ($state) => match ($state) {
                    PremiumPayment::STATUS_APPROVED, PremiumPayment::STATUS_PAID => 'success',
                    PremiumPayment::STATUS_REJECTED, PremiumPayment::STATUS_EXPIRED => 'danger',
                    default => 'warning',
                }),
            Tables\Columns\TextColumn::make('payment_method')->label('Metode')->badge(),
            Tables\Columns\TextColumn::make('created_at')->label('Dikirim')->dateTime('d M Y H:i')->sortable(),
            Tables\Columns\TextColumn::make('verified_at')->label('Diverifikasi')->dateTime('d M Y H:i')->placeholder('-')->toggleable(),
        ])->filters([
            Tables\Filters\SelectFilter::make('payment_status')
                ->label('Status')
                ->options(PremiumPayment::STATUSES),
        ])->actions([
            Tables\Actions\Action::make('proof')
                ->label('Bukti')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn (PremiumPayment $record): ?string => $record->payment_proof ? asset('storage/' . $record->payment_proof) : null)
                ->openUrlInNewTab()
                ->visible(fn (PremiumPayment $record): bool => filled($record->payment_proof)),

            Tables\Actions\Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (PremiumPayment $record): bool => $record->isPending())
                ->action(fn (PremiumPayment $record) => app(PremiumActivationService::class)->approve($record, auth()->user())),

            Tables\Actions\Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (PremiumPayment $record): bool => $record->isPending())
                ->form([
                    Forms\Components\Textarea::make('note')
                        ->label('Alasan Penolakan')
                        ->required()
                        ->maxLength(500),
                ])
                ->action(fn (PremiumPayment $record, array $data) => app(PremiumActivationService::class)->reject($record, auth()->user(), $data['note'])),

            Tables\Actions\DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPremiumPayments::route('/'),
        ];
    }
}
