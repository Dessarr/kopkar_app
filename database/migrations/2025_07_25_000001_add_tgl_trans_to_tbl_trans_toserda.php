<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTglTransToTblTransToserda extends Migration
{
    public function up()
    {
        Schema::table('tbl_trans_toserda', function (Blueprint $table) {
            $table->date('tgl_trans')->nullable();
        });

        // Update existing records to set tgl_trans to created_at date
        DB::table('tbl_trans_toserda')
            ->whereNull('tgl_trans')
            ->update(['tgl_trans' => DB::raw('DATE(created_at)')]);
    }

    public function down()
    {
        Schema::table('tbl_trans_toserda', function (Blueprint $table) {
            $table->dropColumn('tgl_trans');
        });
    }
}