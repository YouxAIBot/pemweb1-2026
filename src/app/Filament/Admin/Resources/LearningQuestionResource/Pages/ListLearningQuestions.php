<?php

namespace App\Filament\Admin\Resources\LearningQuestionResource\Pages;

use App\Filament\Admin\Resources\LearningQuestionResource;
use App\Models\LearningQuestion;
use App\Models\LearningQuestionOption;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class ListLearningQuestions extends ListRecords
{
    protected static string $resource = LearningQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('resetQuestions')
                ->label('Kosongkan Semua Soal')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Kosongkan semua soal?')
                ->modalDescription('Aksi ini akan menghapus semua soal dan opsi jawaban. Bahasa, bagian, level, user, progress, homepage, dan auth pages tidak akan dihapus.')
                ->modalSubmitActionLabel('Ya, kosongkan soal')
                ->action(function (): void {
                    DB::transaction(function (): void {
                        LearningQuestionOption::query()->delete();
                        LearningQuestion::query()->delete();
                    });

                    Notification::make()
                        ->title('Semua soal berhasil dikosongkan')
                        ->body('Anda bisa mulai membuat soal baru dari awal untuk setiap bahasa dan level.')
                        ->success()
                        ->send();
                }),

            Actions\CreateAction::make()
                ->label('Buat Soal Baru'),
        ];
    }
}
