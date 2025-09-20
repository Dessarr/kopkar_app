<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ConvertPasswordsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'passwords:convert 
                            {--tbl-user : Konversi password tbl_user saja}
                            {--tbl-anggota : Konversi password tbl_anggota saja}
                            {--admin-password=admin123 : Password untuk admin}
                            {--default-password=password123 : Password default untuk user lain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Konversi password dari CodeIgniter hash ke Laravel bcrypt';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai konversi password...');

        $convertTblUser = $this->option('tbl-user') || (!$this->option('tbl-user') && !$this->option('tbl-anggota'));
        $convertTblAnggota = $this->option('tbl-anggota') || (!$this->option('tbl-user') && !$this->option('tbl-anggota'));

        if ($convertTblUser) {
            $this->convertTblUserPasswords();
        }

        if ($convertTblAnggota) {
            $this->convertTblAnggotaPasswords();
        }

        $this->info('Konversi password selesai!');
    }

    /**
     * Konversi password tbl_user ke bcrypt Laravel
     */
    private function convertTblUserPasswords(): void
    {
        $this->info('Mengkonversi password tbl_user...');

        $adminPassword = $this->option('admin-password');
        $defaultPassword = $this->option('default-password');

        $adminHashedPassword = Hash::make($adminPassword);
        $defaultHashedPassword = Hash::make($defaultPassword);

        $users = DB::table('tbl_user')->get();

        $this->withProgressBar($users, function ($user) use ($adminHashedPassword, $defaultHashedPassword, $adminPassword, $defaultPassword) {
            $newPassword = $user->u_name === 'admin' ? $adminHashedPassword : $defaultHashedPassword;
            
            DB::table('tbl_user')
                ->where('id', $user->id)
                ->update(['pass_word' => $newPassword]);
        });

        $this->newLine();
        $this->info('Password tbl_user berhasil dikonversi!');
        $this->warn("Password admin: {$adminPassword}");
        $this->warn("Password user lain: {$defaultPassword}");
    }

    /**
     * Konversi password tbl_anggota menggunakan no_ktp
     */
    private function convertTblAnggotaPasswords(): void
    {
        $this->info('Mengkonversi password tbl_anggota...');

        $anggotas = DB::table('tbl_anggota')
            ->whereNotNull('no_ktp')
            ->where('no_ktp', '!=', '')
            ->get();

        $this->withProgressBar($anggotas, function ($anggota) {
            // Hash no_ktp sebagai password
            $hashedPassword = Hash::make($anggota->no_ktp);
            
            DB::table('tbl_anggota')
                ->where('id', $anggota->id)
                ->update(['pass_word' => $hashedPassword]);
        });

        $this->newLine();
        $this->info('Password tbl_anggota berhasil dikonversi!');
        $this->warn('Password anggota menggunakan no_ktp mereka masing-masing');
    }
}