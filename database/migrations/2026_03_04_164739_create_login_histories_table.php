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
        Schema::create('login_histories', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->default(0);
            $table->ipAddress('ip_address');
            $table->text('user_agent')->nullable();
            $table->string('location')->default('Dhaka, Bangladesh'); // Manual or GeoIP location
            $table->string('timezone')->nullable()->default(''); // Manual or GeoIP location
            $table->timestamp('login_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_histories');
    }
};
