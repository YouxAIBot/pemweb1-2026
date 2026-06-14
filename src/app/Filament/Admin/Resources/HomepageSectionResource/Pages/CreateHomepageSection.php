<?php

namespace App\Filament\Admin\Resources\HomepageSectionResource\Pages;

use App\Filament\Admin\Resources\HomepageSectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHomepageSection extends CreateRecord
{
    protected static string $resource = HomepageSectionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
