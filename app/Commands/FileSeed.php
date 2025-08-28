<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Http;
use LaravelZero\Framework\Commands\Command;
use Spatie\BackupServer\Models\Source;

class FileSeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:seed-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed file source';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response = Http::get(env('REMOTE_FILE_SEEDER_URL'))->throw();

        $data = $response->json();

        foreach($data as $item){
            $src = new Source();
            $src->name = $item['name'];
            $src->host = $item['host'];
            $src->ssh_user = $item['ssh_user'];
            $src->ssh_private_key_file = '/home/ubuntu/ssh_key.crt';
            $src->includes = explode(',', $item['includes']);
            $src->destination_id = env("DEFAULT_DESTINATION");

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
