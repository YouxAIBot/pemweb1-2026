<?php

namespace App\Filament\Admin\Resources\LearningLanguageResource\Pages;

use App\Filament\Admin\Resources\LearningLanguageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLearningLanguages extends ListRecords
{
    protected static string $resource = LearningLanguageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
