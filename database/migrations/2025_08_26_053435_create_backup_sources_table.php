<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('backup_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');                 // human label
            $table->string('driver');               // mysql|pgsql|sqlsrv|mongodb
            // Connection options (relational + mongo)
            $table->string('host')->nullable()->default('127.0.0.1');
            $table->unsignedInteger('port')->nullable();            // default per driver if null
            $table->string('uri')->nullable();                      // mongodb+srv://... (optional)
            $table->string('database')->nullable();                 // DB name; null for "all" (mongo)
            $table->string('username')->nullable();
            $table->text('password')->nullable();                   // cast: encrypted
            $table->string('auth_db')->nullable();                  // Mongo auth DB (e.g. admin)

            // MySQL socket options (ignored for others)
            $table->boolean('use_socket')->default(false);
            $table->string('unix_socket')->nullable();

            // Dump options
            $table->boolean('gzip')->default(true);
            $table->json('exclude_tables')->nullable();             // for mongo = collections
            $table->json('only_tables')->nullable();                // for mongo = collections
            $table->unsignedInteger('timeout_seconds')->default(600);
            $table->string('dump_binary_path')->nullable();         // e.g. /usr/bin

            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_sources');
    }
};
