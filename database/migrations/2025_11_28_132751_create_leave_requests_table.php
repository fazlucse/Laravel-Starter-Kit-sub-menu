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
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->unsignedBigInteger('office')->default(0);

            $table->unsignedBigInteger('person_id')->default(0);
            $table->string('person_name',255)->nullable();
            $table->string('department_name',255)->nullable();
            $table->string('division_name',255)->nullable();
            $table->string('employee_id',255)->nullable();
            $table->string('leave_type',255)->nullable();
            $table->string('on_behalf_request',50)->nullable();
            $table->unsignedBigInteger('division_id');
            $table->unsignedBigInteger('department_id');
            $table->double('al_leave', 20, 2)->default(0);
            $table->double('cl_leave', 20, 2)->default(0);
            $table->double('sl_leave', 20, 2)->default(0);
            $table->double('pat_leave', 20, 2)->default(0);
            $table->double('mat_leave', 20, 2)->default(0);

            $table->tinyText('purpose_leave')->nullable();
            $table->text('contact_address')->nullable();
            $table->text('remarks')->nullable();
            $table->double('total_leave', 20, 2)->default(0);
            $table->string('office_text', 100)->nullable();
            $table->string('division_text', 100)->nullable();
            $table->string('employee_text', 100)->nullable();

            $table->double('balance_al', 20, 2)->default(0);
            $table->double('balance_cl', 20, 2)->default(0);
            $table->double('balance_sl', 20, 2)->default(0);
            $table->double('balance_pat', 20, 2)->default(0);
            $table->double('balance_mat', 20, 2)->default(0);
            $table->double('balance_total', 20, 2)->default(0);

            // START + END dates fields (AL, CL, SL, MAT, PAT)
            $table->date('al_start_date')->nullable();
            $table->string('al_start_time', 20)->nullable();
            $table->date('al_end_date')->nullable();
            $table->string('al_end_time', 20)->nullable();

            $table->date('cl_start_date')->nullable();
            $table->string('cl_start_time', 20)->nullable();
            $table->date('cl_end_date')->nullable();
            $table->string('cl_end_time', 20)->nullable();

            $table->date('sl_start_date')->nullable();
            $table->string('sl_start_time', 20)->nullable();
            $table->date('sl_end_date')->nullable();
            $table->string('sl_end_time', 20)->nullable();

            $table->date('mat_start_date')->nullable();
            $table->string('mat_start_time', 20)->nullable();
            $table->date('mat_end_date')->nullable();
            $table->string('mat_end_time', 20)->nullable();

            $table->date('pat_start_date')->nullable();
            $table->string('pat_start_time', 20)->nullable();
            $table->date('pat_end_date')->nullable();
            $table->string('pat_end_time', 20)->nullable();

            $table->unsignedBigInteger('reliver_employee')->nullable();
            $table->unsignedBigInteger('leave_year')->nullable();
            $table->double('p_cl', 20, 2)->default(0);
            $table->double('p_sl', 20, 2)->default(0);
            $table->double('others_emp_leave', 20, 2);
            $table->date('others_start_date')->nullable();
            $table->date('others_end_date')->nullable();
            $table->string('others_start_time', 25)->nullable();
            $table->string('others_end_time', 25)->nullable();
            $table->unsignedBigInteger('compensatory_leave')->nullable();
            $table->date('compensatory_start_date')->nullable();
            $table->date('compensatory_end_date')->nullable();
            $table->string('compensatory_start_time', 30)->nullable();
            $table->string('compensatory_end_time', 30)->nullable();
            $table->text('notes')->nullable();
            $table->double('lwp_leave', 20, 2)->nullable();
            $table->date('lwp_start_date')->nullable();
            $table->date('lwp_end_date')->nullable();
            $table->string('lwp_start_time', 30)->nullable();
            $table->string('lwp_end_time', 30)->nullable();
            $table->tinyInteger('status')->default(0);
            $table->unsignedBigInteger('request_by')->default(0);
            $table->dateTime('request_date')->nullable();
            $table->unsignedBigInteger('pass_by')->default(0);
            $table->text('pass_note')->nullable();
            $table->dateTime('pass_date')->nullable();
            $table->unsignedBigInteger('approved_by')->default(0);
            $table->string('approved_by_name', 70);
            $table->string('approved_by_designation', 70);
            $table->text('approved_note')->nullable();
            $table->dateTime('approved_date')->nullable();
            $table->string('entry_from', 20)->default('System');
            $table->unsignedBigInteger('inline_supervisor_id');
            $table->string('inline_supervisor_name', 50)->nullable();
            $table->string('inline_supervisor_designation', 70)->nullable();

            $table->unsignedBigInteger('request_for_approved')->nullable();
            $table->tinyInteger('request_for')->default(0);

            $table->unsignedBigInteger('other_person')->default(0);
            $table->string('other_person_text', 200);

            $table->integer('is_other_leave')->default(0);
            $table->integer('is_cancel')->default(0);

            $table->unsignedBigInteger('hr_app_by')->default(0);
            $table->dateTime('hr_date')->nullable();
            $table->text('hr_remarks')->nullable();
            $table->integer('hr_status')->default(0);
            $table->timestamps();
            // indexing
            $table->index('request_for_approved');
            $table->index('entry_from');
            $table->index('request_by');
            $table->index('inline_supervisor_id');
            $table->index('status');
            $table->index('al_start_date');
            $table->index('division_id');
            $table->index('department_id');
            $table->index('division_name');
            $table->index('department_name');
            $table->index('person_id');
            $table->index('person_name');
//            $table->index('office');
//            $table->index('office_text');
            $table->index('pass_by');
            $table->index('request_for');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
