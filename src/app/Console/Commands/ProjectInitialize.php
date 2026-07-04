<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProjectInitialize extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:init {--fresh : Reset database before initializing} {--seed : Seed default data after migrating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Project Initialization';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('fresh')) {
            $this->warn('Reset database berjalan karena opsi --fresh dipakai.');
            $this->call('migrate:fresh', [
                '--force' => true,
            ]);
        } else {
            $this->call('migrate', [
                '--force' => true,
            ]);
        }

        $this->call('shield:generate', [
            '--all' => true,
            '--panel' => 'admin',
        ]);

        if ($this->option('fresh') || $this->option('seed')) {
            $this->call('db:seed', [
                '--force' => true,
            ]);
        }

        $this->call('filament:optimize-clear');
        $this->call('optimize:clear');
    }
}
