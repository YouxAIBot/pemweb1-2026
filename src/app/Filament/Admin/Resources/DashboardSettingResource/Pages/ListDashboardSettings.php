<?php

namespace App\Filament\Admin\Resources\DashboardSettingResource\Pages;

use App\Filament\Admin\Resources\DashboardSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDashboardSettings extends ListRecords
{
    protected static string $resource = DashboardSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
