<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id(); // Primary key 'id'

            // Basic Company Info
            $table->string('company_name', 150);
            $table->string('company_code', 50)->unique()->nullable();
            $table->string('registration_no', 100)->nullable();
            $table->string('tax_identification_no', 100)->nullable();

            // Contact Info
            $table->string('email', 150)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('website', 150)->nullable();

            // Address Info
            $table->string('address_line1', 255)->nullable();
            $table->string('address_line2', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('postal_code', 20)->nullable();

            // Additional Details
            $table->string('industry_type', 100)->nullable();
            $table->string('ownership_type', 100)->nullable();
            $table->string('logo_path', 255)->nullable(); // company logo

            // Status and Audit
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            // Foreign Keys
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
