<?php

namespace App\Filament\Admin\Resources\DashboardSettingResource\Pages;

use App\Filament\Admin\Resources\DashboardSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDashboardSetting extends EditRecord
{
    protected static string $resource = DashboardSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
