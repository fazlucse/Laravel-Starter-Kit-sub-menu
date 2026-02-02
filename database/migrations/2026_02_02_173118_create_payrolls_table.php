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
                    $blueprint->unsignedBigInteger('fin_com_id')->index();
                    $blueprint->unsignedBigInteger('hr_ref')->index();
                    $blueprint->date('month')->index();

                    // Earnings (Calculated)
                    $blueprint->double('gross_salary_calculated');
                    $blueprint->double('basic_salary')->default(0);
                    $blueprint->double('house_rent')->default(0);
                    $blueprint->double('medical_allowance')->default(0);
                    $blueprint->double('transport_allowance')->default(0);
                    $blueprint->double('other_benefits')->default(0);
                    $blueprint->double('bonus')->default(0);

                    // Deductions
                    $blueprint->double('tax_deduction')->default(0);
                    $blueprint->double('ips_deduction')->unsigned();
                    $blueprint->double('ipd_deduction')->unsigned();
                    $blueprint->double('loan_deduction')->default(0);
                    $blueprint->double('pf_loan_deduction')->unsigned();
                    $blueprint->double('mobile_deduction')->default(0);
                    $blueprint->double('training_deduction')->default(0);
                    $blueprint->double('lwp_deduction');
                    $blueprint->double('others_deduction');
                    $blueprint->double('pf_deduction')->index();
                    $blueprint->double('lb_deduction');
                    $blueprint->double('canteen_bill_deduction');
                    $blueprint->double('cafe_bill_deduction');

                    // Final Payments
                    $blueprint->double('bank_amount')->default(0);
                    $blueprint->double('cash_amount')->default(0);

                    // Employee Snapshot (De-normalized for history)
                    $blueprint->string('emp_id', 50);
                    $blueprint->string('emp_name', 50);
                    $blueprint->unsignedBigInteger('com_id')->index();
                    $blueprint->string('com_name', 100);
                    $blueprint->unsignedBigInteger('division_id')->index();
                    $blueprint->string('division_name', 50)->index();
                    $blueprint->unsignedBigInteger('department_id');
                    $blueprint->string('department_name', 50);
                    $blueprint->unsignedBigInteger('designation_id');
                    $blueprint->string('designation_name', 50);
                    $blueprint->date('joining_date');

                    // System Meta
                    $blueprint->unsignedBigInteger('entryby');
                    $blueprint->datetime('entrytime');
                    $blueprint->tinyInteger('locked')->unsigned()->default(0)->index();
                    $blueprint->date('exchange_date');
                    $blueprint->unsignedBigInteger('entry_by_dept');
                    $blueprint->double('other_advance')->unsigned();
                    $blueprint->tinyInteger('gsc_flag')->unsigned();

                    $blueprint->timestamps();
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
