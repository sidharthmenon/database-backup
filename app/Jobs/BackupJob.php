<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;

class BackupJob implements ShouldQueue
{
    use Queueable;

    public $source;

    /**
     * Create a new job instance.
     */
    public function __construct($source)
    {
        $this->source = $source;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Artisan::call('backup-server:backup '.$this->source->name);
    }
    
}
