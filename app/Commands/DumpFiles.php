<?php

namespace App\Commands;

use App\Jobs\BackupJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use LaravelZero\Framework\Commands\Command;
use Spatie\BackupServer\Models\Source;

class DumpFiles extends Command
{

    protected $signature = 'backup:files';

    protected $description = 'schedule file backup';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Artisan::call('backup-server:dispatch-backups');

        $sources = Source::where('status', 'active')->get();

        foreach($sources as $item){

            $this->info("▶ Dumping [{$item->name}] ({$item->host}:) → {$item->id}");

            dispatch(new BackupJob($item));

        }

    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        $schedule->command(static::class)->dailyAt("01:00");
    }
}
