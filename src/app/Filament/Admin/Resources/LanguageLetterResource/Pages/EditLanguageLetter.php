<?php

namespace App\Filament\Admin\Resources\LanguageLetterResource\Pages;

use App\Filament\Admin\Resources\LanguageLetterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLanguageLetter extends EditRecord
{
    protected static string $resource = LanguageLetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
