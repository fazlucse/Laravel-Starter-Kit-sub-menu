<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('payroll_batches', function (Blueprint $table) {
            $table->id();
            $table->date('payroll_month')->index();
            $table->unsignedBigInteger('com_id')->index();
            $table->string('com_name')->nullable();
            $table->string('status')->default('draft'); // draft, posted, approved
            $table->boolean('is_locked')->default(false);
            // Staff Count
            $table->integer('total_staff')->default(0);
            // Financial Summaries
            $table->double('total_gross_amount', 15, 2)->default(0);  // Total before deductions
            $table->double('total_payable_amount', 15, 2)->default(0); // Amount company is liable for
            $table->double('total_net_disbursement', 15, 2)->default(0); // Actual take-home pay
            // Tracking & Dates
            $table->unsignedBigInteger('prepared_by');
            $table->timestamp('prepared_date')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_date')->nullable();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('payroll_batches');
    }
};
