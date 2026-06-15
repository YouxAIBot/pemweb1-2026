<?php

namespace App\Filament\Admin\Resources\UserLearningProfileResource\Pages;

use App\Filament\Admin\Resources\UserLearningProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserLearningProfiles extends ListRecords
{
    protected static string $resource = UserLearningProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
