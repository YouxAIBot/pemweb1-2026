<?php

namespace App\Filament\Admin\Resources\AuthPageSettingResource\Pages;

use App\Filament\Admin\Resources\AuthPageSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAuthPageSetting extends EditRecord
{
    protected static string $resource = AuthPageSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
