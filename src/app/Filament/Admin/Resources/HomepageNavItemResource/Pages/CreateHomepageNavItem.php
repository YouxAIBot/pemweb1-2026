<?php

namespace App\Filament\Admin\Resources\HomepageNavItemResource\Pages;

use App\Filament\Admin\Resources\HomepageNavItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHomepageNavItem extends CreateRecord
{
    protected static string $resource = HomepageNavItemResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
