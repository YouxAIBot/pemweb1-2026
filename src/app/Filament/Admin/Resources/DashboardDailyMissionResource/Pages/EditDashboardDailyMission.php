<?php

namespace App\Filament\Admin\Resources\DashboardDailyMissionResource\Pages;

use App\Filament\Admin\Resources\DashboardDailyMissionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDashboardDailyMission extends EditRecord
{
    protected static string $resource = DashboardDailyMissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
