<?php

namespace App\Filament\Admin\Resources\LearningPartResource\Pages;

use App\Filament\Admin\Resources\LearningPartResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLearningParts extends ListRecords
{
    protected static string $resource = LearningPartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
