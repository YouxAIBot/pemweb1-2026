<?php

namespace App\Filament\Admin\Resources\UserDailyMissionProgressResource\Pages;

use App\Filament\Admin\Resources\UserDailyMissionProgressResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserDailyMissionProgress extends EditRecord
{
    protected static string $resource = UserDailyMissionProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
