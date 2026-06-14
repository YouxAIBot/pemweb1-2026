<?php

namespace App\Filament\Admin\Resources\HomepageSettingResource\Pages;

use App\Filament\Admin\Resources\HomepageSettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHomepageSetting extends CreateRecord
{
    protected static string $resource = HomepageSettingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
