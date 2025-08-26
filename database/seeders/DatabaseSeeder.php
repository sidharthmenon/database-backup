<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\BackupSource;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
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

            $this->command->info(print_r($item));
        }

    }
}
