<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_allotments', function (Blueprint $table) {
            $table->id();
            $table->string('year', 20)->nullable();
            $table->unsignedBigInteger('office')->nullable();
            $table->string('office_name', 255)->nullable();
            $table->string('employee_id', 50)->nullable();
            $table->unsignedBigInteger('person_id')->nullable();
            $table->string('name', 255)->nullable();
            $table->unsignedBigInteger('gender')->nullable();
            $table->unsignedBigInteger('designation')->nullable();
            $table->string('designation_name', 255)->nullable();
            $table->unsignedBigInteger('division')->nullable();
            $table->string('division_name', 255)->nullable();
            $table->unsignedBigInteger('department')->nullable();
            $table->string('department_name', 255)->nullable();
            $table->double('annual_allotment')->nullable();
            $table->double('annual_utilized')->nullable();
            $table->double('casual_allotment')->nullable();
            $table->double('casual_utilized')->nullable();
            $table->double('sick_allotment')->nullable();
            $table->double('sick_utilized')->nullable();
            $table->double('maternal_allotment')->nullable();
            $table->double('maternal_utilized')->nullable();
            $table->double('paternal_allotment')->nullable();
            $table->double('paternal_utilized')->nullable();
            $table->double('earn_allotment')->nullable();
            $table->double('earn_utilized')->nullable();
            $table->double('earn_utilized_encash')->nullable();
            $table->unsignedBigInteger('el_total')->nullable();
            $table->unsignedBigInteger('total_working_days')->nullable();
            $table->unsignedBigInteger('previous_earn_utilized')->nullable();
            $table->double('lwp_allotment')->nullable();
            $table->double('lwp_utilized')->nullable();
            $table->double('replacement_leave_allotment')->nullable();
            $table->double('replacement_leave_utilized')->nullable();
            $table->integer('replacement_leave_encash')->nullable();
            $table->text('replacement_leave_encash_date')->nullable();
            $table->date('rl_start_date')->nullable();
            $table->date('rl_end_date')->nullable();
            $table->longText('replacement_leave_dates');
            $table->longText('replacement_leave_utl_dates');
            $table->string('remarks', 255)->nullable();
            $table->string('reason', 255)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_allotments');
    }
};
