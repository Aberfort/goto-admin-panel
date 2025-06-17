<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeUrlsToTextInWebsitesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->text('reg_url')->change();
            $table->text('front_url')->change();
            $table->text('app_url')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->string('reg_url', 255)->change();
            $table->string('front_url', 255)->change();
            $table->string('app_url', 255)->change();
        });
    }
}
