<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetAdminPasswordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Mereset password admin...');

        // Reset password admin ke 'admin123'
        $adminPassword = 'admin123';
        $hashedPassword = Hash::make($adminPassword);

        $updated = DB::table('tbl_user')
            ->where('u_name', 'admin')
            ->update(['pass_word' => $hashedPassword]);

        if ($updated) {
            $this->command->info('Password admin berhasil direset!');
            $this->command->warn("Username: admin");
            $this->command->warn("Password: {$adminPassword}");
        } else {
            $this->command->error('Gagal mereset password admin. User admin tidak ditemukan.');
        }
    }
}
