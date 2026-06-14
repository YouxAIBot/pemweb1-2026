<?php

namespace App\Filament\Admin\Resources\HomepageNavItemResource\Pages;

use App\Filament\Admin\Resources\HomepageNavItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHomepageNavItem extends EditRecord
{
    protected static string $resource = HomepageNavItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
