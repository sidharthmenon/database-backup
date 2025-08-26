<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BackupSource extends Model
{
	protected $table = 'backup_sources';

	// ---- Drivers ----
	public const DRIVER_MYSQL   = 'mysql';
	public const DRIVER_PGSQL   = 'pgsql';
	public const DRIVER_SQLITE  = 'sqlite';
	public const DRIVER_MONGODB = 'mongodb';

	public const SUPPORTED_DRIVERS = [
		self::DRIVER_MYSQL,
		self::DRIVER_PGSQL,
		self::DRIVER_SQLITE,
		self::DRIVER_MONGODB,
	];

	protected $fillable = [
        'name',
        'driver',

        // connection / auth
        'host',
        'port',
        'uri',          // for MongoDB (e.g., mongodb+srv://...)
        'database',     // null for "all" (Mongo)
        'username',
        'password',
        'auth_db',      // Mongo auth DB (e.g. admin)

        // MySQL socket
        'use_socket',
        'unix_socket',

        // dump options
        'gzip',
        'exclude_tables',   // for Mongo = collections
        'only_tables',      // for Mongo = collections
        'timeout_seconds',
        'dump_binary_path',

        'active',
    ];

    protected $casts = [
        // secrets (at-rest encryption)
        'password'        => 'encrypted',
        'uri'             => 'encrypted', // often contains credentials in SRV URIs

        // booleans / arrays / ints
        'use_socket'      => 'boolean',
        'gzip'            => 'boolean',
        'active'          => 'boolean',
        'exclude_tables'  => 'array',
        'only_tables'     => 'array',
        'timeout_seconds' => 'integer',
        'port'            => 'integer',
    ];

    /* ------------------------------------------------------------
     |  Scopes
     | ------------------------------------------------------------ */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /* ------------------------------------------------------------
     |  Helpers
     | ------------------------------------------------------------ */

    /** Slug used for folder naming: storage/app/db-dumps/{slug} */
    public function slug(): string
    {
        $base = $this->name ?: "{$this->driver}-" . ($this->database ?: 'all');
        return Str::slug($base);
    }

    /** Default port per driver (used when port is null). */
    public function defaultPort(): int
    {
        return match ($this->driver) {
            self::DRIVER_MYSQL   => 3306,
            self::DRIVER_PGSQL   => 5432,
            self::DRIVER_SQLITE  => 1433,
            self::DRIVER_MONGODB => 27017,
            default              => 0,
        };
    }

    /** Returns the port to use (explicit port or driver default). */
    public function effectivePort(): int
    {
        return $this->port ?: $this->defaultPort();
    }

    public function isMongo(): bool
    {
        return $this->driver === self::DRIVER_MONGODB;
    }

    public function isRelational(): bool
    {
        return in_array($this->driver, [
            self::DRIVER_MYSQL, self::DRIVER_PGSQL, self::DRIVER_SQLITE,
        ], true);
    }

    /**
     * Suggested dump file extension for this source based on gzip preference.
     * Relational: .sql / .sql.gz
     * MongoDB:    .archive / .archive.gz (when using --archive)
     */
    public function suggestedFileExtension(?bool $gzip = null): string
    {
        $gzip = $gzip ?? (bool)$this->gzip;

        if ($this->isMongo()) {
            return $gzip ? 'archive.gz' : 'archive';
        }

        return $gzip ? 'sql.gz' : 'sql';
    }

    /**
     * Build a filename (without directory) for a given timestamp.
     * Example: mydb_20250826_020000.sql.gz  or  mydb_20250826_020000.archive.gz
     */
    public function buildDumpFilename(string $timestamp): string
    {
        $name = ($this->database ?: 'all');
        return "{$name}_{$timestamp}." . $this->suggestedFileExtension();
    }

}
