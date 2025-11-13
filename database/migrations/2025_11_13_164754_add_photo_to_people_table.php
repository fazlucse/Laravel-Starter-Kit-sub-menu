<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   // database/migrations/xxxx_add_photo_to_people_table.php
public function up()
{
    Schema::table('people', function (Blueprint $table) {
        $table->string('photo')->nullable()->after('name');
    });
}

public function down()
{
    Schema::table('people', function (Blueprint $table) {
        $table->dropColumn('photo');
    });
}
};
