<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            // One domain per site: a site is a brand, a brand is a domain.
            $table->foreignId('site_id')->unique()->constrained()->cascadeOnDelete();
            // Globally unique - two accounts can't both claim the same host,
            // and ownership is settled by the DNS TXT check below.
            $table->string('host')->unique();
            $table->string('verification_token', 64);
            // Null until the TXT record has been seen. Only verified domains
            // ever resolve a link.
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
