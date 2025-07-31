<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatedAtToTblTransToserda extends Migration
{
    public function up()
    {
        Schema::table('tbl_trans_toserda', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        // Update existing records to set created_at to current timestamp
        DB::table('tbl_trans_toserda')
            ->whereNull('created_at')
            ->update(['created_at' => now(), 'updated_at' => now()]);
    }

    public function down()
    {
        Schema::table('tbl_trans_toserda', function (Blueprint $table) {
            $table->dropColumn(['created_at', 'updated_at']);
        });
    }
}