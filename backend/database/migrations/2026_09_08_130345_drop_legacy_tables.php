<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Cleans up the abandoned pre-LinkFleet schema before the new sites/links
 * tables below get created. websites/sites/urls were created by migrations
 * that no longer exist in this repo, so the migrator would never drop them
 * on its own - without this, create_sites_table below would fail with a
 * "table already exists" error against a database still carrying the old
 * (differently-shaped) "sites" table. Safe to run against a fresh database
 * too, where none of these tables exist yet.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('websites');
        Schema::dropIfExists('urls');
        Schema::dropIfExists('sites');
    }

    public function down(): void
    {
        // Irreversible on purpose - only ever deletes data from a schema
        // that no longer has code behind it.
    }
};
