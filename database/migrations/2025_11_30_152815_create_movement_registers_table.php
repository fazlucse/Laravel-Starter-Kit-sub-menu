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
        Schema::create('movement_registers', function (Blueprint $table) {
            $table->id();

            // Location details
            $table->string('origin_location_type')->nullable();
            $table->string('destination_location_type')->nullable();
            $table->string('origin_location_name')->nullable();
            $table->unsignedBigInteger('origin_location_id')->default(0)->index();
            $table->string('destination_location_name')->nullable();
            $table->unsignedBigInteger('destination_location_id')->default(0)->index();

            // Purpose & transport
            $table->text('purpose')->nullable();
            $table->string('transport_mode')->nullable();
            $table->double('conveyance_amount', 15, 2)->default(0);

            // GPS
            $table->float('origin_lat')->nullable();
            $table->float('origin_lng')->nullable();
            $table->float('destination_lat')->nullable();
            $table->float('destination_lng')->nullable();

            // Times
            $table->timestamp('movement_started_at')->nullable();
            $table->timestamp('movement_ended_at')->nullable();

            // Entry details
            $table->string('entry_from')->default('system');
            $table->unsignedBigInteger('created_by')->default(0)->index();
            $table->string('created_by_name')->nullable();

            // Division
            $table->unsignedBigInteger('division_id')->default(0)->index();
            $table->string('division_name')->nullable();

            // Updates
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->string('update_from')->nullable();

            // Customer
            $table->string('customer_id')->nullable();
            $table->text('customer_name')->nullable();

            // Billing
            $table->double('night_allowance', 18, 2)->nullable();
            $table->double('holiday_amount', 12, 2)->default(0);
            $table->double('other_amount', 12, 2)->default(0);
            $table->double('night_amount', 12, 2)->default(0);
            $table->double('budgeted_amount', 18, 2)->default(0);
            $table->text('bill_remarks')->nullable();

            // Currency
            $table->unsignedBigInteger('currency_id')->default(89)->index();
            $table->string('currency_name')->default('BDT');
            $table->unsignedBigInteger('base_currency_id')->default(90);
            $table->string('base_currency_name')->default('USD');
            $table->double('conversion_rate')->default(0);
            $table->double('conversion_amount')->default(0);

            // Approval
            $table->unsignedBigInteger('approved_by')->default(0)->index();
            $table->string('approved_by_name')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approved_remarks')->nullable();

            // Accounts confirmation
            $table->unsignedBigInteger('accounts_confirmed_by')->default(0);
            $table->integer('accounts_confirm_status')->default(0);
            $table->string('accounts_confirmed_by_name')->nullable();
            $table->timestamp('accounts_confirmed_at')->nullable();
            $table->text('accounts_confirm_remarks')->nullable();

            // Misc
            $table->tinyInteger('off_day')->default(0);
            $table->string('visit_type')->nullable();
            $table->unsignedBigInteger('module_type')->default(0);
            $table->tinyInteger('is_sent')->default(0);
            $table->unsignedBigInteger('email_sent_by')->default(0);
            $table->string('email_sent_by_name')->nullable();
            $table->timestamp('email_sent_date')->nullable();

            // Laravel timestamps
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movement_registers');
    }
};
