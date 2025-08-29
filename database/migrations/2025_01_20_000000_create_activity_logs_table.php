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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('user_type', ['admin', 'member'])->default('admin');
            $table->string('user_name')->nullable();
            $table->string('action'); // create, update, delete, approve, reject, etc.
            $table->string('module'); // pengajuan_penarikan, pinjaman, simpanan, etc.
            $table->text('description');
            $table->json('old_values')->nullable(); // Data sebelum perubahan
            $table->json('new_values')->nullable(); // Data setelah perubahan
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->enum('status', ['success', 'failed', 'pending'])->default('success');
            $table->text('error_message')->nullable(); // Pesan error jika gagal
            $table->unsignedBigInteger('affected_record_id')->nullable(); // ID record yang terpengaruh
            $table->string('affected_record_type')->nullable(); // Class/model yang terpengaruh
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'user_type']);
            $table->index(['module', 'action']);
            $table->index(['status', 'created_at']);
            $table->index('affected_record_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
