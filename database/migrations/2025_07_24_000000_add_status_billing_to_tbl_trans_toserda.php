<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tbl_trans_toserda', function (Blueprint $table) {
            $table->enum('status_billing', ['Y', 'N'])->default('N')->after('user_name');
        });
    }

    public function down()
    {
        Schema::table('tbl_trans_toserda', function (Blueprint $table) {
            $table->dropColumn('status_billing');
        });
    }
};