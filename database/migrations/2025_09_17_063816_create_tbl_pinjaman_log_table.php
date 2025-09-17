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
        Schema::create('tbl_pinjaman_log', function (Blueprint $table) {
            $table->id();
            $table->string('pinjaman_id', 50);
            $table->string('field_name', 100);
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('action', 50); // 'UPDATE', 'DELETE', 'CREATE'
            $table->string('user_name', 100);
            $table->timestamp('created_at');
            $table->text('notes')->nullable();
            
            $table->index(['pinjaman_id', 'created_at']);
            // Hapus foreign key constraint karena tipe data tidak kompatibel
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_pinjaman_log');
    }
};