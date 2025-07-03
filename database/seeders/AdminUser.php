<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUser extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tbl_user')->insert([
            'u_name' => 'prakerinadmin', // username bebas kamu tentukan
            'pass_word' => Hash::make('admin2025@'), // password yang bisa kamu pakai saat login
            'id_cabang' =>'CB0001',
            'aktif' =>'Y',
            'level' =>'admin',
        ]);
    }
}