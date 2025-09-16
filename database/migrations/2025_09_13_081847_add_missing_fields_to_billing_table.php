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
        Schema::table('billing', function (Blueprint $table) {
            $table->decimal('simpanan_khusus_1', 15, 2)->nullable()->default(0)->after('simpanan_sukarela');
            $table->decimal('tab_perumahan', 15, 2)->nullable()->default(0)->after('simpanan_khusus_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billing', function (Blueprint $table) {
            $table->dropColumn(['simpanan_khusus_1', 'tab_perumahan']);
        });
    }
};
