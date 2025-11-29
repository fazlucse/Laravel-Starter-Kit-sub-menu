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
        // Disable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Drop table if exists (optional)
        Schema::dropIfExists('holiday_person');
        Schema::dropIfExists('holidays');

        // Create holidays table
        Schema::create('holidays', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('is_personwise')->nullable();
            $table->string('total_person_ids', 200)->nullable();
            $table->bigInteger('operational_office')->nullable();
            $table->string('operational_office_name')->nullable();
            $table->bigInteger('department')->nullable();
            $table->string('department_name')->nullable();
            $table->bigInteger('division')->nullable();
            $table->string('division_name')->nullable();
            $table->bigInteger('workgroup')->nullable();
            $table->string('workgroup_name')->nullable();
            $table->bigInteger('holiday_type')->nullable();
            $table->string('holiday_type_name')->nullable();
            $table->date('holiday_date')->nullable();
            $table->integer('date_range')->nullable();
            $table->boolean('notice_chk')->nullable();
            $table->bigInteger('notice_id')->nullable();
            $table->boolean('moon_setting')->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();
        });

        // Create pivot table
        Schema::create('holiday_person', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('holiday_id');
            $table->unsignedBigInteger('person_id');

            $table->foreign('holiday_id')
                ->references('id')->on('holidays');

            $table->foreign('person_id')
                ->references('id')->on('persons');

            $table->unique(['holiday_id', 'person_id']);
            $table->timestamps();
        });

        // Enable foreign key checks again
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
