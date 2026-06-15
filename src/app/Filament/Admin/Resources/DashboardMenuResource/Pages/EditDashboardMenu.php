<?php

namespace App\Filament\Admin\Resources\DashboardMenuResource\Pages;

use App\Filament\Admin\Resources\DashboardMenuResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDashboardMenu extends EditRecord
{
    protected static string $resource = DashboardMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
