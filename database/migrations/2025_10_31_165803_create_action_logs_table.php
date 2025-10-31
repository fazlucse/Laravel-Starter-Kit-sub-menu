<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('action_logs', function (Blueprint $table) {
            $table->id();
            $table->string('module');                    // e.g., "Person"
            $table->string('action');                    // e.g., "deleted"
            $table->unsignedBigInteger('record_id');     // ID of deleted record
            $table->text('comments')->nullable();        // Reason
            $table->unsignedBigInteger('user_id');       // Who did it
            $table->timestamp('deleted_at')->useCurrent();
            $table->timestamps();

            $table->index(['module', 'action']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('action_logs');
    }
};