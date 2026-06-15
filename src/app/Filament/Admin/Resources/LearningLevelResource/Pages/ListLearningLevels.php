<?php

namespace App\Filament\Admin\Resources\LearningLevelResource\Pages;

use App\Filament\Admin\Resources\LearningLevelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLearningLevels extends ListRecords
{
    protected static string $resource = LearningLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
