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
    Schema::create('appointments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The Patient
        $table->foreignId('service_id')->constrained(); // The Service
        
        // Appointment Details
        $table->date('appointment_date');
        $table->time('appointment_time');
        
        // Status: pending, confirmed, completed, cancelled, rescheduled
        $table->string('status')->default('pending');
        
        // "Void" / Cancellation Audit
        $table->text('cancellation_reason')->nullable();
        $table->foreignId('cancelled_by')->nullable()->constrained('users'); // Who cancelled it?
        $table->timestamp('cancelled_at')->nullable();

        $table->timestamps();
        $table->softDeletes(); // If you want to "Archive" old appointments
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
