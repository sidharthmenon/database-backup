<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use LaravelZero\Framework\Commands\Command;

class PruneFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:files-clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'clean file backups';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Artisan::call('backup-server:cleanup');
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        $schedule->command(static::class)->dailyAt('02:00');
    }
}
