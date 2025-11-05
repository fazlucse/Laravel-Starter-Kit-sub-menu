<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('info_master', function (Blueprint $table) {
            $table->id();
            $table->string('type')->comment('e.g. country, city, etc.');
            $table->string('name');
            $table->string('code')->nullable()->comment('Short code for reference, e.g. BD, IN, DEL');
            $table->text('description')->nullable()->comment('Optional description of the entry');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('Used if city belongs to a country');
            $table->unsignedBigInteger('created_by')->nullable()->comment('User who created this record');
            $table->timestamps(); // created_at + updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('info_master');
    }
};
