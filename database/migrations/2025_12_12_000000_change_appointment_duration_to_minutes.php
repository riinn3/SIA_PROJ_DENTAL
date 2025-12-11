<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Add minutes column
            $table->integer('duration_minutes')->default(60)->after('appointment_time');
            // Drop the old hours column
            $table->dropColumn('duration_hours');
        });
    }

    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->integer('duration_hours')->default(1);
            $table->dropColumn('duration_minutes');
        });
    }
};