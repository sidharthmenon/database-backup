<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use LaravelZero\Framework\Commands\Command;

class DumpFiles extends Command
{

    protected $signature = 'backup:files';

    protected $description = 'schedule file backup';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Artisan::call('backup-server:dispatch-backups');
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        $schedule->command(static::class)->everyMinute();
    }
}
