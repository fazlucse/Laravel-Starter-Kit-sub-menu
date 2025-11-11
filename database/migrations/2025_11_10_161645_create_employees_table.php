<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            // Primary Key
            $table->id();
            // Person Info
            $table->unsignedBigInteger('person_id');
            $table->string('person_name', 150);

            // Company Info
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('company_name', 150)->nullable();
            $table->string('fin_com_name', 150)->nullable();
            $table->unsignedBigInteger('fin_com_id')->default(0);
            // Division
            $table->unsignedBigInteger('division_id')->nullable();
            $table->string('division_name', 150)->nullable();

            // Department
            $table->unsignedBigInteger('department_id');
            $table->string('department_name', 100);

            // Designation
            $table->unsignedBigInteger('designation_id');
            $table->string('designation_name', 100);
            $table->string('employee_code', 20)->unique();
            $table->string('employee_id', 20)->unique();
            // Joining & Employment
            $table->date('joining_date');
            $table->date('confirmation_date')->nullable();
            $table->date('probation_end_date')->nullable();
            $table->date('effective_date')->nullable();
            $table->enum('employment_type', ['Permanent','Contract','Intern','Probation','Freelancer']);
            $table->string('work_location', 100)->nullable();
            $table->string('shift', 50)->nullable();
            $table->string('official_email', 100)->nullable();
            $table->string('official_phone', 20)->nullable();

            // Office Timings
            $table->time('office_in_time')->nullable();
            $table->time('office_out_time')->nullable();
            $table->integer('late_time')->default(0)->comment('Late time in minutes');
            $table->enum('employee_status', ['Active','Inactive','Terminated','Resigned','Retired','On Leave'])->default('Active');
            // Salary Components
            $table->decimal('gross_salary', 15, 2)->default(0)->comment('Sum of all fixed components');
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('house_rent_allowance', 15, 2)->default(0);
            $table->decimal('medical_allowance', 15, 2)->default(0);
            $table->decimal('transport_allowance', 15, 2)->default(0);
            $table->decimal('other_allowances', 15, 2)->default(0);
            $table->decimal('overtime_rate', 15, 2)->default(0);
            $table->decimal('total_salary', 15, 2)->default(0)->comment('Gross salary + overtime');
            $table->string('currency', 10)->default('USD');

            // Banking & Tax
            $table->string('bank_name', 255)->nullable();
            $table->string('bank_account_no', 50)->nullable();
            $table->string('bank_ifsc_code', 20)->nullable();
            $table->string('pan_number', 20)->nullable();
            $table->enum('tax_status', ['Resident','Non-Resident'])->nullable();
            $table->string('social_security_no', 50)->nullable();
            $table->string('passport_number', 20)->nullable();
            $table->integer('is_tax_dedction')->default(0);
             $table->integer('is_salary_stop')->default(0);
            // Emergency & Personal Info
            $table->string('emergency_contact_name', 100)->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->enum('marital_status', ['Single','Married','Divorced','Widowed'])->nullable();
            $table->enum('gender', ['Male','Female','Other'])->nullable();
            $table->string('blood_group', 5)->nullable();
            $table->integer('work_experience')->nullable();
            $table->text('skills')->nullable();

                        // Reporting Manager
            $table->unsignedBigInteger('reporting_manager_id')->nullable();
            $table->string('reporting_manager_name', 150)->nullable();
            // Reporting Manager
            $table->unsignedBigInteger('second_reporting_manager_id')->nullable();
            $table->string('second_reporting_manager_name', 150)->nullable();
            $table->unsignedBigInteger('deparment_head')->nullable();
            $table->string('deparment_head_name', 150)->nullable();

            // Performance & Promotions
            $table->date('last_appraisal_date')->nullable();
            $table->date('next_appraisal_date')->nullable();
            $table->date('last_promotion_date')->nullable();
            $table->date('next_promotion_due')->nullable();

            // Status & Admin
            $table->text('office_time')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // Foreign Key Constraints (without onDelete)
            // $table->foreign('company_id')->references('id')->on('companies');
            // $table->foreign('division_id')->references('id')->on('divisions');
            // $table->foreign('department_id')->references('department_id')->on('departments');
            // $table->foreign('designation_id')->references('designation_id')->on('designations');
            // $table->foreign('reporting_manager_id')->references('id')->on('employees');
            // $table->foreign('created_by')->references('id')->on('users');
            // $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
