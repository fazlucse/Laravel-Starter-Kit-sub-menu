<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bonuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('payroll_id')->nullable();
            $table->date('bonus_date')->nullable(); // e.g., 'Festival Bonus', 'Performance'
            $table->string('bonus_type')->nullable(); // e.g., 'Festival Bonus', 'Performance'
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('payroll_month', 7); // Format: Y-m (e.g., 2026-03)
            $table->boolean('is_paid')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();
            $table->index('employee_id');
            $table->index('payroll_id');
            $table->index('payroll_month');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bonuses');
    }
};
