<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class ViewKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:key';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show App Key';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->warn(env('APP_KEY'));
        $this->warn(config('app.key'));
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
