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
            $table->string('level')->default('info');
            $table->unsignedBigInteger('record_id')->nullable()->default(0);    // ID of deleted record
            $table->text('comments')->nullable();        // Reason
            $table->unsignedBigInteger('user_id')->default(0);       // Who did it
            $table->timestamp('deleted_at')->useCurrent();
            $table->timestamps();

            $table->index(['module', 'action']);
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('action_logs');
    }
};
