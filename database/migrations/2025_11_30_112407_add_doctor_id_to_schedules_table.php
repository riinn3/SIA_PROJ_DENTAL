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
        Schema::table('schedules', function (Blueprint $table) {
            // This links the schedule to a specific User (Doctor)
            // 'after' places it at the top of the table for easy reading
            $table->foreignId('doctor_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
        });
    }

public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
            $table->dropColumn('doctor_id');
        });
    }
};
