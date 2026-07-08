<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            HomepageSeeder::class,
            AuthPageSeeder::class,
            DashboardSeeder::class,
            GameModeSeeder::class,
            LearningCmsSeeder::class,
            StarterLanguageQuestionBankSeeder::class,
            ExpandedLanguageContentSeeder::class,
            VariedLanguagePracticeSeeder::class,
            StoryLanguageQuestionSeeder::class,
            PremiumSeeder::class,
        ]);
    }
}
