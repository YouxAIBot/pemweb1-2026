<?php

namespace App\Filament\Admin\Resources\HomepageNavItemResource\Pages;

use App\Filament\Admin\Resources\HomepageNavItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHomepageNavItems extends ListRecords
{
    protected static string $resource = HomepageNavItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
