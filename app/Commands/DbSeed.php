<?php

namespace App\Commands;

use App\BackupSource;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Http;
use LaravelZero\Framework\Commands\Command;

class DbSeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'import from remote url';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response = Http::get(env('REMOTE_SEEDER_URL'))->throw();

        $data = $response->json();

        foreach($data as $item){
            $src = new BackupSource();
            $src->name = $item['name'];
            $src->driver = $item['driver'];
            $src->host = $item['host'];
            $src->database = $item['database'];
            $src->username = $item['username'];
            $src->password = $item['password'];

            if($item['driver'] == "mongodb"){
                $src->auth_db = "admin";
            }

            $src->save();

            $this->info(print_r($item));
        }

    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
