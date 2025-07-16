<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if the table already exists
        if (!Schema::hasTable('data_kas')) {
            Schema::create('data_kas', function (Blueprint $table) {
                $table->id();
                $table->string('nama')->nullable();
                $table->char('aktif', 1)->default('Y');
                $table->timestamps();
            });

            // Copy data from nama_kas_tbl if it exists
            if (Schema::hasTable('nama_kas_tbl')) {
                $kas = DB::table('nama_kas_tbl')->get();
                foreach ($kas as $item) {
                    DB::table('data_kas')->insert([
                        'id' => $item->id,
                        'nama' => $item->nama,
                        'aktif' => $item->aktif,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_kas');
    }
};
