<?php
// database/migrations/xxxx_xx_xx_create_people_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('designation')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->text('present_address')->nullable();
            $table->text('education')->nullable();
            $table->string('section')->nullable();
            $table->string('material_status')->nullable(); // e.g. Single, Married
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('company')->nullable();
            $table->string('nationality')->nullable();
            $table->string('national_id')->unique()->nullable();
            $table->string('tin')->unique()->nullable();
            $table->string('photo')->nullable();
            $table->string('gender')->nullable();
            $table->string('religion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};