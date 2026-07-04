<?php

namespace App\Filament\Admin\Resources\PremiumPackageResource\Pages;

use App\Filament\Admin\Resources\PremiumPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPremiumPackages extends ListRecords
{
    protected static string $resource = PremiumPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
