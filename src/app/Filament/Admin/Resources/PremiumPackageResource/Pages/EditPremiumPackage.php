<?php

namespace App\Filament\Admin\Resources\PremiumPackageResource\Pages;

use App\Filament\Admin\Resources\PremiumPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPremiumPackage extends EditRecord
{
    protected static string $resource = PremiumPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
