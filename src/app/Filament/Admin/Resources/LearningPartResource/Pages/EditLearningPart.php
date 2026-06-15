<?php

namespace App\Filament\Admin\Resources\LearningPartResource\Pages;

use App\Filament\Admin\Resources\LearningPartResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLearningPart extends EditRecord
{
    protected static string $resource = LearningPartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
