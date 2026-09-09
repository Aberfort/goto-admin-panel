<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('links', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('is_active');
            // Hashed, never stored or returned in plain text - see
            // Link::$hidden and LinkController.
            $table->string('password')->nullable()->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('links', function (Blueprint $table) {
            $table->dropColumn(['expires_at', 'password']);
        });
    }
};
