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
        Schema::create('leave_request_details', function (Blueprint $table) {
            $table->id();
            // Link to the parent table
            $table->integer('leave_request_id')->default(0)->nullable();
            $table->string('leave_reason')->nullable();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->integer('employee_id')->default(0); // Person taking the leave
            $table->string('request_for')->nullable(); // self or other
            $table->string('leave_duration')->nullable(); // Full Day, Half Day, etc.
            $table->decimal('total_days', 8, 1)->nullable()->default(0.0);
            $table->integer('remarks')->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_request_details');
    }
};
