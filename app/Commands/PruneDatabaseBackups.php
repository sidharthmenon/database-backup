<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Illuminate\Support\Facades\Storage;

class PruneDatabaseBackups extends Command
{
    protected $signature = 'backup:db-clean
        {--keep=7 : Number of most recent backups to keep per DB}
        {--disk=local : Filesystem disk to use}
        {--root=db-dumps : Root path under the disk containing DB dump folders}
        {--dry-run : Show what would be deleted without deleting}';

    protected $description = 'Delete old DB dump files, keeping only the most recent N per database folder';

    private array $allowedExtensions = [
        '.sql', '.sql.gz',           // relational plain + gzip
        '.dump', '.dump.gz',         // pg custom dumps etc.
        '.archive', '.archive.gz',   // MongoDB --archive (+ gzip)
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $diskName = (string)$this->option('disk');
        $keep     = max(0, (int)$this->option('keep'));
        $root     = trim((string)$this->option('root'), '/');
        $dryRun   = (bool)$this->option('dry-run');

        $disk = Storage::disk($diskName);

        if (!$disk->exists($root)) {
            $this->warn("Root '{$root}' not found on disk '{$diskName}'. Nothing to prune.");
            return self::SUCCESS;
        }

        $dbDirs = $disk->directories($root);
        if (empty($dbDirs)) {
            $this->info("No database directories under '{$root}'.");
            return self::SUCCESS;
        }

        $totalDeleted = 0;

        foreach ($dbDirs as $dbDir) {
            // list files only in the top of that dir (adjust to recursive if you ever nest)
            $files = $disk->files($dbDir);

            // filter to known dump extensions
            $files = array_values(array_filter($files, function ($path) {
                $lower = strtolower($path);
                foreach ($this->allowedExtensions as $ext) {
                    if (str_ends_with($lower, $ext)) return true;
                }
                return false;
            }));

            $count = count($files);
            if ($count <= $keep) {
                $this->line("✔ {$dbDir}: {$count} file(s), nothing to prune.");
                continue;
            }

            // sort by last modified DESC (newest first)
            usort($files, fn ($a, $b) => $disk->lastModified($b) <=> $disk->lastModified($a));

            $toKeep   = array_slice($files, 0, $keep);
            $toDelete = array_slice($files, $keep);

            $this->info("Pruning '{$dbDir}': keeping " . count($toKeep) . ", deleting " . count($toDelete) . ".");

            foreach ($toDelete as $path) {
                if ($dryRun) {
                    $this->line("  - (dry-run) delete {$path}");
                    continue;
                }

                try {
                    if ($disk->delete($path)) {
                        $this->line("  - deleted {$path}");
                        $totalDeleted++;
                    } else {
                        $this->warn("  - failed to delete {$path}");
                    }
                } catch (\Throwable $e) {
                    $this->error("  - error deleting {$path}: {$e->getMessage()}");
                    report($e);
                }
            }
        }

        $this->info($dryRun ? "Dry run complete." : "Done. Deleted {$totalDeleted} file(s).");
        
        return self::SUCCESS;

    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        $schedule->command(static::class)->dailyAt("01:00");
    }
}
