<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class PasswordConverterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Memulai konversi password...');

        // Konversi password untuk tbl_user
        $this->convertTblUserPasswords();
        
        // Konversi password untuk tbl_anggota
        $this->convertTblAnggotaPasswords();

        $this->command->info('Konversi password selesai!');
    }

    /**
     * Konversi password tbl_user ke bcrypt Laravel
     */
    private function convertTblUserPasswords(): void
    {
        $this->command->info('Mengkonversi password tbl_user...');

        // Reset password admin ke 'admin123'
        $adminPassword = Hash::make('admin123');
        
        // Reset password untuk user lain ke 'password123'
        $defaultPassword = Hash::make('password123');

        $users = DB::table('tbl_user')->get();

        foreach ($users as $user) {
            $newPassword = $user->u_name === 'admin' ? $adminPassword : $defaultPassword;
            
            DB::table('tbl_user')
                ->where('id', $user->id)
                ->update(['pass_word' => $newPassword]);

            $this->command->info("Password untuk user '{$user->u_name}' telah direset.");
        }

        $this->command->info('Password tbl_user berhasil dikonversi!');
        $this->command->warn('Password admin: admin123');
        $this->command->warn('Password user lain: password123');
    }

    /**
     * Konversi password tbl_anggota menggunakan no_ktp
     */
    private function convertTblAnggotaPasswords(): void
    {
        $this->command->info('Mengkonversi password tbl_anggota...');

        $anggotas = DB::table('tbl_anggota')
            ->whereNotNull('no_ktp')
            ->where('no_ktp', '!=', '')
            ->get();

        foreach ($anggotas as $anggota) {
            // Hash no_ktp sebagai password
            $hashedPassword = Hash::make($anggota->no_ktp);
            
            DB::table('tbl_anggota')
                ->where('id', $anggota->id)
                ->update(['pass_word' => $hashedPassword]);

            $this->command->info("Password untuk anggota '{$anggota->nama}' (KTP: {$anggota->no_ktp}) telah direset.");
        }

        $this->command->info('Password tbl_anggota berhasil dikonversi!');
        $this->command->warn('Password anggota menggunakan no_ktp mereka masing-masing');
    }
}