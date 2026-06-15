<?php

namespace App\Filament\Admin\Resources\UserLearningProfileResource\Pages;

use App\Filament\Admin\Resources\UserLearningProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserLearningProfile extends EditRecord
{
    protected static string $resource = UserLearningProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
