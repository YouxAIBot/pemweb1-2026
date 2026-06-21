<?php

namespace App\Filament\Admin\Resources\GameModeResource\Pages;

use App\Filament\Admin\Resources\GameModeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGameModes extends ListRecords
{
    protected static string $resource = GameModeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
