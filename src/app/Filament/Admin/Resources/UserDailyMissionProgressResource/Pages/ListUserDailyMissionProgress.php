<?php

namespace App\Filament\Admin\Resources\UserDailyMissionProgressResource\Pages;

use App\Filament\Admin\Resources\UserDailyMissionProgressResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserDailyMissionProgress extends ListRecords
{
    protected static string $resource = UserDailyMissionProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
