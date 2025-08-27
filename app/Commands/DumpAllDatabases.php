<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use App\BackupSource;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\DbDumper\Compressors\GzipCompressor;
use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Databases\PostgreSql;
use Spatie\DbDumper\Databases\MongoDb;
use Spatie\DbDumper\Databases\Sqlite;

class DumpAllDatabases extends Command
{
    protected $signature = 'backup:dump
        {--id=* : Only dump specific backup_sources IDs}
        {--disk=local : Filesystem disk for dumps}
        {--root=db-dumps : Root folder under disk for dumps}';

    protected $description = 'Dump databases listed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $diskName = (string)$this->option('disk');
        $root     = trim((string)$this->option('root'), '/');

        $query = BackupSource::query()->active();

        if ($ids = $this->option('id')) {
            $query->whereIn('id', (array)$ids);
        }

        $sources = $query->get();

        if ($sources->isEmpty()) {
            $this->warn('No active backup sources found.');
            Log::warn('No active backup sources found.');
            return self::SUCCESS;
        }

        $ts = CarbonImmutable::now()->format('Ymd_His');
        $disk = Storage::disk($diskName);

        foreach ($sources as $src) {
            $slug = $src->slug();
            $dir  = "{$root}/{$slug}";
            $disk->makeDirectory($dir);

            $filename = $src->buildDumpFilename($ts); // uses suggested extension per driver/gzip
            $relPath  = "{$dir}/{$filename}";
            $absPath  = $disk->path($relPath);

            $this->info("▶ Dumping [{$src->name}] ({$src->driver}:" . ($src->database ?: 'all') . ") → {$relPath}");
            Log::info()("▶ Dumping [{$src->name}] ({$src->driver}:" . ($src->database ?: 'all') . ") → {$relPath}");

            try {
                $dumper = match ($src->driver) {
                    BackupSource::DRIVER_MYSQL   => MySql::create(),
                    BackupSource::DRIVER_PGSQL   => PostgreSql::create(),
                    BackupSource::DRIVER_SQLITE  => Sqlite::create(),
                    BackupSource::DRIVER_MONGODB => MongoDb::create(),
                    default => throw new \RuntimeException("Unsupported driver: {$src->driver}"),
                };

                // ---------- Common ----------
                if ($src->host)       { $dumper->setHost($src->host); }
                if ($src->port)       { $dumper->setPort($src->effectivePort()); }
                if ($src->database)   { $dumper->setDbName($src->database); }
                if ($src->username)   { $dumper->setUserName($src->username); }
                if ($src->password)   { $dumper->setPassword($src->password); }
                if ($src->dump_binary_path) {
                    $dumper->setDumpBinaryPath(rtrim($src->dump_binary_path, '/'));
                }
                if (method_exists($dumper, 'setTimeout') && $src->timeout_seconds) {
                    $dumper->setTimeout((int)$src->timeout_seconds);
                }

                // ---------- MySQL niceties ----------
                if ($src->driver === BackupSource::DRIVER_MYSQL) {
                    if ($src->use_socket && $src->unix_socket && method_exists($dumper, 'setSocket')) {
                        $dumper->setSocket($src->unix_socket);
                    }
                    if (method_exists($dumper, 'useSingleTransaction')) {
                        $dumper->useSingleTransaction();
                    }
                }

                // ---------- Mongo specifics ----------
                if ($src->driver === BackupSource::DRIVER_MONGODB) {
                    if ($src->auth_db && method_exists($dumper, 'setAuthenticationDatabase')) {
                        $dumper->setAuthenticationDatabase($src->auth_db);
                    }
                    // If your package version supports URIs:
                    if ($src->uri && method_exists($dumper, 'setUri')) {
                        $dumper->setUri($src->uri);
                    }
                }

                // ---------- Include / Exclude ----------
                // Relational tables
                if ($src->isRelational()) {
                    if (!empty($src->only_tables) && method_exists($dumper, 'includeTables')) {
                        $dumper->includeTables($src->only_tables);
                    }
                    if (!empty($src->exclude_tables) && method_exists($dumper, 'excludeTables')) {
                        $dumper->excludeTables($src->exclude_tables);
                    }
                }
                // Mongo collections (newer versions expose these)
                if ($src->isMongo()) {
                    if (!empty($src->only_tables) && method_exists($dumper, 'includeCollections')) {
                        $dumper->includeCollections($src->only_tables);
                    }
                    if (!empty($src->exclude_tables) && method_exists($dumper, 'excludeCollections')) {
                        $dumper->excludeCollections($src->exclude_tables);
                    }
                }

                // ---------- Compression ----------
                if ($src->gzip && method_exists($dumper, 'useCompressor')) {
                   $dumper->useCompressor(new GzipCompressor()); // produces .sql.gz (relational) or gzipped output for Mongo
                }

                // ---------- Execute ----------
                $dumper->dumpToFile($absPath);

                $this->line("   ✔ Dumped to: storage/app/{$relPath}");
            } catch (\Throwable $e) {
                $this->error("   ✖ Failed: {$e->getMessage()}");
                Log::error("   ✖ Failed: {$e->getMessage()}");

                report($e);
            }
        }

        $this->info('Done.');
        Log::info('Done.');

        return self::SUCCESS;
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        $schedule->command(static::class)->dailyAt("05:00");
    }
}
