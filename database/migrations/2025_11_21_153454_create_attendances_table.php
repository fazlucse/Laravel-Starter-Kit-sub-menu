<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->string('device_id', 20)->nullable();
            $table->string('shift', 100)->nullable();
            $table->unsignedBigInteger('operational_office')->nullable();
            $table->string('operational_office_name', 255)->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->string('employee_id', 50)->nullable();
            $table->string('emp_name', 255)->nullable();
            $table->unsignedBigInteger('designation')->nullable();
            $table->string('designation_name', 200)->nullable();
            $table->unsignedBigInteger('department')->nullable();
            $table->string('department_name', 200)->nullable();
            $table->unsignedBigInteger('division')->nullable();
            $table->string('division_name', 200)->nullable();
            $table->string('serving_division_ids', 255)->nullable();
            $table->string('work_place', 200)->nullable();
            $table->string('work_place_xl', 100)->nullable();
            $table->string('office_in_time', 20)->nullable();
            $table->string('office_out_time', 20)->nullable();
            $table->string('emp_in_time', 20)->nullable();
            $table->string('emp_out_time', 20)->nullable();
            $table->string('work_time', 20)->nullable();
            $table->integer('in_time_late')->nullable();
            $table->integer('out_time_late')->nullable();
            $table->string('office_lunch_start', 20)->nullable();
            $table->string('office_lunch_end', 20)->nullable();
            $table->string('lunch_start', 20)->nullable();
            $table->string('lunch_end', 20)->nullable();
            $table->string('current_location_xl', 100)->nullable();
            $table->string('current_location', 255)->nullable();
            $table->string('reason', 200)->nullable();
            $table->unsignedBigInteger('addby')->nullable();
            $table->dateTime('add_time')->nullable();
            $table->unsignedBigInteger('editby')->nullable();
            $table->dateTime('edit_time')->nullable();
            $table->string('entry_from', 50)->nullable();
            $table->string('out_from', 50)->nullable();
            $table->string('remarks', 200)->nullable();
            $table->string('remarks_out', 255)->nullable();
            $table->boolean('is_holiday')->default(0);
            $table->string('holiday_name', 100)->nullable();
            $table->boolean('is_leave_applied')->nullable();
            $table->boolean('is_leave')->default(0);
            $table->double('leave_count')->nullable();
            $table->string('leave_type', 200)->nullable();
            $table->boolean('is_offday')->default(0);
            $table->boolean('business_travel_applied')->nullable();
            $table->boolean('in_business_travel')->nullable();
            $table->unsignedBigInteger('manpower_type')->nullable();
            $table->string('manpower_type_name', 100)->nullable();
            $table->integer('is_manual')->nullable();
            $table->double('night_allowance', 12, 2)->nullable();
            $table->integer('approved_by')->default(0);
            $table->string('approved_by_name', 255)->nullable();
            $table->dateTime('approved_date')->nullable();
            $table->text('approved_remarks')->nullable();
            $table->boolean('is_email_sent')->default(0);
            $table->integer('email_sent_by')->default(0);
            $table->string('email_sent_by_name', 255)->nullable();
            $table->dateTime('email_sent_date')->nullable();
            $table->double('convertion_amount')->nullable();
            $table->string('latitude_in', 200)->nullable();
            $table->string('longitude_in', 200)->nullable();
            $table->string('latitude_out', 200)->nullable();
            $table->string('longitude_out', 200)->nullable();
            $table->string('login_ip', 200)->nullable();
            $table->string('connected_mac', 200)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
