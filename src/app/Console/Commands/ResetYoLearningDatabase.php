<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ResetYoLearningDatabase extends Command
{
    protected $signature = 'yolearning:reset-database {--force : Run without confirmation}';

    protected $description = 'Reset database YoLearning for development and reseed default data.';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('Database akan di-reset total. Lanjutkan?')) {
            $this->warn('Reset dibatalkan.');

            return self::SUCCESS;
        }

        $this->call('migrate:fresh', [
            '--seed' => true,
            '--force' => true,
        ]);

        if ($this->hasCommand('shield:generate')) {
            $this->call('shield:generate', [
                '--all' => true,
                '--panel' => 'admin',
            ]);
        }

        $this->call('storage:link');
        $this->call('optimize:clear');

        $this->info('Database YoLearning berhasil di-reset.');

        return self::SUCCESS;
    }
}
