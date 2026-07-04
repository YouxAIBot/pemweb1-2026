<?php

namespace App\Filament\Admin\Resources\LanguageLetterResource\Pages;

use App\Filament\Admin\Resources\LanguageLetterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLanguageLetters extends ListRecords
{
    protected static string $resource = LanguageLetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
