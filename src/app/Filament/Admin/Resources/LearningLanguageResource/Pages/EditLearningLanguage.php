<?php

namespace App\Filament\Admin\Resources\LearningLanguageResource\Pages;

use App\Filament\Admin\Resources\LearningLanguageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLearningLanguage extends EditRecord
{
    protected static string $resource = LearningLanguageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
