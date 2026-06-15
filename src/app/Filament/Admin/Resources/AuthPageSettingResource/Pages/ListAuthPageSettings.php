<?php

namespace App\Filament\Admin\Resources\AuthPageSettingResource\Pages;

use App\Filament\Admin\Resources\AuthPageSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAuthPageSettings extends ListRecords
{
    protected static string $resource = AuthPageSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
