<?php

namespace App\Filament\Admin\Resources\LearningLevelResource\Pages;

use App\Filament\Admin\Resources\LearningLevelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLearningLevel extends EditRecord
{
    protected static string $resource = LearningLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
