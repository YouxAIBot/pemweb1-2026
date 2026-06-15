<?php

namespace App\Filament\Admin\Resources\LearningQuestionResource\Pages;

use App\Filament\Admin\Resources\LearningQuestionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLearningQuestions extends ListRecords
{
    protected static string $resource = LearningQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
