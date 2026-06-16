<?php

namespace Database\Seeders;

use App\Models\LearningQuestion;
use App\Models\LearningQuestionOption;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResetLearningQuestionsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            LearningQuestionOption::query()->delete();
            LearningQuestion::query()->delete();
        });
    }
}
