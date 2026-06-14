<?php

namespace App\Filament\Admin\Resources\HomepageSettingResource\Pages;

use App\Filament\Admin\Resources\HomepageSettingResource;
use Filament\Resources\Pages\EditRecord;

class EditHomepageSetting extends EditRecord
{
    protected static string $resource = HomepageSettingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
