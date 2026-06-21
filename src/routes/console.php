<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Artisan::command('yolearning:fresh {--force}', function () {
    if (! $this->option('force') && ! $this->confirm('Database akan di-reset total. Lanjutkan?')) {
        $this->warn('Reset dibatalkan.');
        return;
    }

    $this->call('migrate:fresh', [
        '--seed' => true,
        '--force' => true,
    ]);

    if (class_exists(\BezhanSalleh\FilamentShield\Commands\GenerateCommand::class)) {
        $this->call('shield:generate', [
            '--all' => true,
            '--panel' => 'admin',
        ]);
    }

    $this->call('storage:link');
    $this->call('optimize:clear');

    $this->info('Database YoLearning berhasil di-reset.');
})->purpose('Reset database YoLearning and reseed default data');
