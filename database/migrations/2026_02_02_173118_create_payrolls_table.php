<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
            public function up()
            {
                Schema::create('payrolls', function (Blueprint $blueprint) {
                    $blueprint->id(); // Primary Key
                    $blueprint->foreignId('batch_id')->constrained('payroll_batches')->onDelete('cascade');

                    // Financial & HR Refs
                    $blueprint->unsignedBigInteger('fin_com_id')->default(0)->index();
                    $blueprint->unsignedBigInteger('hr_ref')->default(0)->index();
                    $blueprint->date('month')->index();

                    // Earnings (Calculated)
                    $blueprint->double('gross_salary_calculated')->default(0);
                    $blueprint->double('basic_salary')->default(0);
                    $blueprint->double('house_rent')->default(0);
                    $blueprint->double('medical_allowance')->default(0);
                    $blueprint->double('transport_allowance')->default(0);
                    $blueprint->double('other_benefits')->default(0);
                    $blueprint->double('bonus')->default(0);

                    // Deductions
                    $blueprint->double('tax_deduction')->default(0);
                    $blueprint->double('ips_deduction')->unsigned()->default(0);
                    $blueprint->double('ipd_deduction')->unsigned()->default(0);
                    $blueprint->double('loan_deduction')->default(0);
                    $blueprint->double('pf_loan_deduction')->unsigned()->default(0);
                    $blueprint->double('mobile_deduction')->default(0);
                    $blueprint->double('training_deduction')->default(0);
                    $blueprint->double('lwp_deduction')->default(0);
                    $blueprint->double('others_deduction')->default(0);
                    $blueprint->double('pf_deduction')->default(0)->index();
                    $blueprint->double('lb_deduction')->default(0);
                    $blueprint->double('canteen_bill_deduction')->default(0);
                    $blueprint->double('cafe_bill_deduction')->default(0);

                    // Final Payments
                    $blueprint->double('bank_amount')->default(0);
                    $blueprint->double('cash_amount')->default(0);

                    // Employee Snapshot (De-normalized for history)
                    $blueprint->string('emp_id', 50)->nullable();
                    $blueprint->string('emp_name', 50)->nullable();
                    $blueprint->unsignedBigInteger('com_id')->default(0)->index();
                    $blueprint->string('com_name', 100)->nullable();
                    $blueprint->unsignedBigInteger('division_id')->nullable()->index();
                    $blueprint->string('division_name', 50)->nullable()->index();
                    $blueprint->unsignedBigInteger('department_id')->default(0);
                    $blueprint->string('department_name', 50)->nullable();
                    $blueprint->unsignedBigInteger('designation_id')->default(0);
                    $blueprint->string('designation_name', 50)->nullable();
                    $blueprint->date('joining_date')->nullable();

                    // System Meta
                    $blueprint->unsignedBigInteger('entryby')->nullable();
                    $blueprint->datetime('entrytime')->nullable();
                    $blueprint->tinyInteger('locked')->unsigned()->default(0)->index();
                    $blueprint->date('exchange_date')->nullable();
                    $blueprint->unsignedBigInteger('entry_by_dept')->default(0);
                    $blueprint->double('other_advance')->unsigned()->default(0);
                    $blueprint->tinyInteger('gsc_flag')->unsigned()->default(0);

                    $blueprint->timestamps();
                    // indexing
                    $blueprint->index('id');
                });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
