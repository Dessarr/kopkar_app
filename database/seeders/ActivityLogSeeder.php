<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ActivityLog;
use Carbon\Carbon;

class ActivityLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $actions = ['create', 'update', 'delete', 'approve', 'reject', 'cancel', 'view'];
        $modules = ['pengajuan_penarikan', 'pengajuan_pinjaman', 'simpanan', 'pinjaman', 'activity_logs'];
        $statuses = ['success', 'failed', 'pending'];
        $userTypes = ['admin', 'member'];
        $userNames = ['Admin Utama', 'Admin Cabang', 'John Doe', 'Jane Smith', 'Bob Johnson'];

        for ($i = 0; $i < 50; $i++) {
            $action = $actions[array_rand($actions)];
            $module = $modules[array_rand($modules)];
            $status = $statuses[array_rand($statuses)];
            $userType = $userTypes[array_rand($userTypes)];
            $userName = $userNames[array_rand($userNames)];

            $description = $this->generateDescription($action, $module, $status);
            
            ActivityLog::create([
                'user_id' => rand(1, 10),
                'user_type' => $userType,
                'user_name' => $userName,
                'action' => $action,
                'module' => $module,
                'description' => $description,
                'old_values' => $this->generateOldValues($module),
                'new_values' => $this->generateNewValues($module),
                'ip_address' => $this->generateIpAddress(),
                'user_agent' => $this->generateUserAgent(),
                'status' => $status,
                'error_message' => $status === 'failed' ? 'Sample error message for testing' : null,
                'affected_record_id' => rand(1, 100),
                'affected_record_type' => $this->getRecordType($module),
                'created_at' => Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59)),
            ]);
        }
    }

    private function generateDescription($action, $module, $status)
    {
        $descriptions = [
            'create' => [
                'pengajuan_penarikan' => 'Membuat pengajuan penarikan simpanan baru',
                'pengajuan_pinjaman' => 'Membuat pengajuan pinjaman baru',
                'simpanan' => 'Membuat transaksi simpanan baru',
                'pinjaman' => 'Membuat data pinjaman baru',
                'activity_logs' => 'Membuat log aktivitas baru'
            ],
            'update' => [
                'pengajuan_penarikan' => 'Mengupdate status pengajuan penarikan',
                'pengajuan_pinjaman' => 'Mengupdate data pengajuan pinjaman',
                'simpanan' => 'Mengupdate transaksi simpanan',
                'pinjaman' => 'Mengupdate data pinjaman',
                'activity_logs' => 'Mengupdate log aktivitas'
            ],
            'approve' => [
                'pengajuan_penarikan' => 'Menyetujui pengajuan penarikan simpanan',
                'pengajuan_pinjaman' => 'Menyetujui pengajuan pinjaman',
                'simpanan' => 'Menyetujui transaksi simpanan',
                'pinjaman' => 'Menyetujui pinjaman',
                'activity_logs' => 'Menyetujui log aktivitas'
            ],
            'reject' => [
                'pengajuan_penarikan' => 'Menolak pengajuan penarikan simpanan',
                'pengajuan_pinjaman' => 'Menolak pengajuan pinjaman',
                'simpanan' => 'Menolak transaksi simpanan',
                'pinjaman' => 'Menolak pinjaman',
                'activity_logs' => 'Menolak log aktivitas'
            ]
        ];

        if (isset($descriptions[$action][$module])) {
            return $descriptions[$action][$module];
        }

        return "Melakukan {$action} pada modul {$module}";
    }

    private function generateOldValues($module)
    {
        $values = [
            'pengajuan_penarikan' => [
                'status' => 'pending',
                'nominal' => 500000,
                'keterangan' => 'Untuk keperluan darurat'
            ],
            'pengajuan_pinjaman' => [
                'status' => 'pending',
                'nominal' => 1000000,
                'lama_angsuran' => 12
            ],
            'simpanan' => [
                'jumlah' => 100000,
                'jenis' => 'Simpanan Wajib'
            ],
            'pinjaman' => [
                'status' => 'aktif',
                'sisa_pinjaman' => 500000
            ]
        ];

        return $values[$module] ?? ['old_data' => 'Sample old data'];
    }

    private function generateNewValues($module)
    {
        $values = [
            'pengajuan_penarikan' => [
                'status' => 'approved',
                'nominal' => 500000,
                'keterangan' => 'Untuk keperluan darurat',
                'tgl_cair' => now()->toDateString()
            ],
            'pengajuan_pinjaman' => [
                'status' => 'approved',
                'nominal' => 1000000,
                'lama_angsuran' => 12,
                'tgl_cair' => now()->toDateString()
            ],
            'simpanan' => [
                'jumlah' => 100000,
                'jenis' => 'Simpanan Wajib',
                'status' => 'processed'
            ],
            'pinjaman' => [
                'status' => 'lunas',
                'sisa_pinjaman' => 0
            ]
        ];

        return $values[$module] ?? ['new_data' => 'Sample new data'];
    }

    private function generateIpAddress()
    {
        $ips = [
            '192.168.1.100',
            '192.168.1.101',
            '192.168.1.102',
            '10.0.0.1',
            '10.0.0.2',
            '172.16.0.1',
            '172.16.0.2'
        ];

        return $ips[array_rand($ips)];
    }

    private function generateUserAgent()
    {
        $agents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        ];

        return $agents[array_rand($agents)];
    }

    private function getRecordType($module)
    {
        $types = [
            'pengajuan_penarikan' => 'data_pengajuan_penarikan',
            'pengajuan_pinjaman' => 'data_pengajuan',
            'simpanan' => 'TblTransSp',
            'pinjaman' => 'TblPinjamanH',
            'activity_logs' => 'ActivityLog'
        ];

        return $types[$module] ?? 'Unknown';
    }
}
