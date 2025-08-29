# 📊 BAGIAN 8: MONITORING & REPORTING

## 🎯 **OVERVIEW MONITORING & REPORTING**

Bagian ini menjelaskan sistem monitoring dan reporting yang terintegrasi dalam aplikasi pinjaman dan billing. Sistem ini memungkinkan admin dan manajemen untuk memantau performa, menganalisis data, dan menghasilkan laporan yang komprehensif.

---

## 📈 **8.1 SISTEM MONITORING REAL-TIME**

### **Dashboard Monitoring Utama**:
```php
/**
 * Controller untuk monitoring real-time
 */
class MonitoringController extends Controller
{
    /**
     * Dashboard monitoring utama
     */
    public function dashboard()
    {
        $data = [
            'total_anggota' => $this->getTotalAnggota(),
            'total_pinjaman_aktif' => $this->getTotalPinjamanAktif(),
            'total_simpanan' => $this->getTotalSimpanan(),
            'total_billing_bulan_ini' => $this->getTotalBillingBulanIni(),
            'pinjaman_overdue' => $this->getPinjamanOverdue(),
            'billing_overdue' => $this->getBillingOverdue(),
            'chart_data' => $this->getChartData(),
            'recent_activities' => $this->getRecentActivities(),
            'system_health' => $this->getSystemHealth()
        ];
        
        return view('monitoring.dashboard', $data);
    }
    
    /**
     * Get total anggota aktif
     */
    private function getTotalAnggota()
    {
        return DataAnggota::where('aktif', 'Y')->count();
    }
    
    /**
     * Get total pinjaman aktif
     */
    private function getTotalPinjamanAktif()
    {
        return TblPinjamanH::where('status', '1')
            ->where('lunas', 'Belum')
            ->count();
    }
    
    /**
     * Get total simpanan
     */
    private function getTotalSimpanan()
    {
        return DataAnggota::where('aktif', 'Y')
            ->sum(DB::raw('simpanan_pokok + simpanan_wajib + simpanan_sukarela'));
    }
    
    /**
     * Get total billing bulan ini
     */
    private function getTotalBillingBulanIni()
    {
        $bulan = now()->month;
        $tahun = now()->year;
        
        return DB::table('tbl_trans_sp_bayar_temp')
            ->whereMonth('tgl_transaksi', $bulan)
            ->whereYear('tgl_transaksi', $tahun)
            ->sum('total_tagihan');
    }
    
    /**
     * Get pinjaman overdue
     */
    private function getPinjamanOverdue()
    {
        return DB::table('tempo_pinjaman as t')
            ->join('tbl_pinjaman_h as p', 't.pinjam_id', '=', 'p.id')
            ->where('t.tempo', '<', now())
            ->where('p.lunas', 'Belum')
            ->count();
    }
    
    /**
     * Get billing overdue
     */
    private function getBillingOverdue()
    {
        $bulan = now()->subMonth()->month;
        $tahun = now()->subMonth()->year;
        
        return DB::table('tbl_trans_sp_bayar_temp')
            ->whereMonth('tgl_transaksi', $bulan)
            ->whereYear('tgl_transaksi', $tahun)
            ->where('status_bayar', 'belum')
            ->count();
    }
    
    /**
     * Get data untuk chart
     */
    private function getChartData()
    {
        $data = [];
        
        // Data 12 bulan terakhir
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $bulan = $date->month;
            $tahun = $date->year;
            
            $data['labels'][] = $date->format('M Y');
            $data['pinjaman'][] = $this->getTotalPinjamanPerBulan($bulan, $tahun);
            $data['simpanan'][] = $this->getTotalSimpananPerBulan($bulan, $tahun);
            $data['billing'][] = $this->getTotalBillingPerBulan($bulan, $tahun);
        }
        
        return $data;
    }
    
    /**
     * Get recent activities
     */
    private function getRecentActivities()
    {
        return DB::table('activity_logs')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }
    
    /**
     * Get system health status
     */
    private function getSystemHealth()
    {
        return [
            'database' => $this->checkDatabaseHealth(),
            'cache' => $this->checkCacheHealth(),
            'storage' => $this->checkStorageHealth(),
            'queue' => $this->checkQueueHealth()
        ];
    }
}
```

### **Real-time Monitoring dengan WebSocket**:
```php
/**
 * WebSocket handler untuk real-time updates
 */
class RealTimeMonitoring
{
    /**
     * Broadcast monitoring data
     */
    public function broadcastMonitoringData()
    {
        $data = [
            'timestamp' => now(),
            'total_pinjaman' => $this->getTotalPinjamanAktif(),
            'total_billing' => $this->getTotalBillingBulanIni(),
            'overdue_count' => $this->getTotalOverdue(),
            'system_status' => $this->getSystemStatus()
        ];
        
        broadcast(new MonitoringDataUpdated($data));
    }
    
    /**
     * Get system status
     */
    private function getSystemStatus()
    {
        $status = 'healthy';
        
        // Cek database connection
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $status = 'database_error';
        }
        
        // Cek queue status
        if (Queue::size() > 1000) {
            $status = 'queue_backlog';
        }
        
        // Cek memory usage
        if (memory_get_usage(true) > 512 * 1024 * 1024) { // 512MB
            $status = 'high_memory';
        }
        
        return $status;
    }
}
```

---

## 📊 **8.2 SISTEM REPORTING**

### **Report Generator Controller**:
```php
/**
 * Controller untuk generate berbagai jenis laporan
 */
class ReportController extends Controller
{
    /**
     * Generate laporan pinjaman
     */
    public function generatePinjamanReport(Request $request)
    {
        $request->validate([
            'jenis_laporan' => 'required|in:aktif,overdue,lunas,summary',
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|min:2020',
            'format' => 'required|in:pdf,excel,html'
        ]);
        
        $data = $this->getPinjamanData($request->jenis_laporan, $request->bulan, $request->tahun);
        
        switch ($request->format) {
            case 'pdf':
                return $this->generatePDF($data, 'laporan_pinjaman');
            case 'excel':
                return $this->generateExcel($data, 'laporan_pinjaman');
            case 'html':
                return view('reports.pinjaman', compact('data'));
        }
    }
    
    /**
     * Generate laporan billing
     */
    public function generateBillingReport(Request $request)
    {
        $request->validate([
            'jenis_laporan' => 'required|in:bulanan,overdue,rekap',
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|min:2020',
            'format' => 'required|in:pdf,excel,html'
        ]);
        
        $data = $this->getBillingData($request->jenis_laporan, $request->bulan, $request->tahun);
        
        switch ($request->format) {
            case 'pdf':
                return $this->generatePDF($data, 'laporan_billing');
            case 'excel':
                return $this->generateExcel($data, 'laporan_billing');
            case 'html':
                return view('reports.billing', compact('data'));
        }
    }
    
    /**
     * Generate laporan keuangan
     */
    public function generateKeuanganReport(Request $request)
    {
        $request->validate([
            'periode' => 'required|in:bulanan,kuartalan,tahunan',
            'tahun' => 'required|integer|min:2020',
            'format' => 'required|in:pdf,excel,html'
        ]);
        
        $data = $this->getKeuanganData($request->periode, $request->tahun);
        
        switch ($request->format) {
            case 'pdf':
                return $this->generatePDF($data, 'laporan_keuangan');
            case 'excel':
                return $this->generateExcel($data, 'laporan_keuangan');
            case 'html':
                return view('reports.keuangan', compact('data'));
        }
    }
    
    /**
     * Get data pinjaman berdasarkan jenis laporan
     */
    private function getPinjamanData($jenis, $bulan, $tahun)
    {
        $query = DB::table('tbl_pinjaman_h as p')
            ->join('data_anggota as a', 'p.anggota_id', '=', 'a.id')
            ->select([
                'p.no_pinjaman',
                'a.nama',
                'a.no_ktp',
                'p.tgl_pinjam',
                'p.jumlah',
                'p.lama_angsuran',
                'p.bunga',
                'p.status',
                'p.lunas',
                'p.total_bayar',
                'p.sisa_pokok'
            ]);
        
        switch ($jenis) {
            case 'aktif':
                $query->where('p.status', '1')->where('p.lunas', 'Belum');
                break;
                
            case 'overdue':
                $query->whereExists(function ($subquery) {
                    $subquery->select(DB::raw(1))
                        ->from('tempo_pinjaman as t')
                        ->whereColumn('t.pinjam_id', 'p.id')
                        ->where('t.tempo', '<', now());
                });
                break;
                
            case 'lunas':
                $query->where('p.lunas', 'Sudah');
                break;
                
            case 'summary':
                // Return summary data
                return $this->getPinjamanSummary($bulan, $tahun);
        }
        
        return $query->get();
    }
    
    /**
     * Get data billing berdasarkan jenis laporan
     */
    private function getBillingData($jenis, $bulan, $tahun)
    {
        $query = DB::table('tbl_trans_sp_bayar_temp')
            ->join('data_anggota as a', 'tbl_trans_sp_bayar_temp.no_ktp', '=', 'a.no_ktp')
            ->whereMonth('tgl_transaksi', $bulan)
            ->whereYear('tgl_transaksi', $tahun);
        
        switch ($jenis) {
            case 'bulanan':
                return $query->get();
                
            case 'overdue':
                return $query->where('status_bayar', 'belum')->get();
                
            case 'rekap':
                return $this->getBillingSummary($bulan, $tahun);
        }
    }
    
    /**
     * Get data keuangan berdasarkan periode
     */
    private function getKeuanganData($periode, $tahun)
    {
        $data = [];
        
        switch ($periode) {
            case 'bulanan':
                for ($bulan = 1; $bulan <= 12; $bulan++) {
                    $data[$bulan] = $this->getKeuanganPerBulan($bulan, $tahun);
                }
                break;
                
            case 'kuartalan':
                for ($q = 1; $q <= 4; $q++) {
                    $data[$q] = $this->getKeuanganPerKuartal($q, $tahun);
                }
                break;
                
            case 'tahunan':
                $data = $this->getKeuanganPerTahun($tahun);
                break;
        }
        
        return $data;
    }
}
```

---

## 📋 **8.3 JENIS-JENIS LAPORAN**

### **Laporan Pinjaman**:
```php
/**
 * Laporan detail pinjaman
 */
class PinjamanReport
{
    /**
     * Laporan pinjaman aktif
     */
    public function laporanPinjamanAktif($bulan, $tahun)
    {
        $data = DB::table('tbl_pinjaman_h as p')
            ->join('data_anggota as a', 'p.anggota_id', '=', 'a.id')
            ->leftJoin('tempo_pinjaman as t', 'p.id', '=', 't.pinjam_id')
            ->select([
                'p.no_pinjaman',
                'a.nama',
                'a.no_ktp',
                'a.alamat',
                'p.tgl_pinjam',
                'p.jumlah',
                'p.lama_angsuran',
                'p.bunga',
                'p.jumlah_angsuran',
                'p.total_bayar',
                'p.sisa_pokok',
                DB::raw('COUNT(t.id) as total_angsuran_dibayar'),
                DB::raw('p.lama_angsuran - COUNT(t.id) as sisa_angsuran')
            ])
            ->where('p.status', '1')
            ->where('p.lunas', 'Belum')
            ->groupBy('p.id')
            ->get();
        
        $summary = [
            'total_pinjaman' => $data->count(),
            'total_nominal' => $data->sum('jumlah'),
            'total_sisa_pokok' => $data->sum('sisa_pokok'),
            'rata_rata_bunga' => $data->avg('bunga'),
            'pinjaman_terbesar' => $data->max('jumlah'),
            'pinjaman_terkecil' => $data->min('jumlah')
        ];
        
        return [
            'data' => $data,
            'summary' => $summary,
            'periode' => "Bulan {$bulan} Tahun {$tahun}"
        ];
    }
    
    /**
     * Laporan pinjaman overdue
     */
    public function laporanPinjamanOverdue()
    {
        $data = DB::table('tempo_pinjaman as t')
            ->join('tbl_pinjaman_h as p', 't.pinjam_id', '=', 'p.id')
            ->join('data_anggota as a', 'p.anggota_id', '=', 'a.id')
            ->select([
                'p.no_pinjaman',
                'a.nama',
                'a.no_ktp',
                'a.alamat',
                't.tempo',
                't.angsuran_ke',
                'p.jumlah_angsuran',
                'p.bunga',
                DB::raw('DATEDIFF(NOW(), t.tempo) as hari_terlambat'),
                DB::raw('p.jumlah_angsuran * (p.bunga / 100 / 12) as denda_bunga')
            ])
            ->where('t.tempo', '<', now())
            ->where('p.lunas', 'Belum')
            ->orderBy('t.tempo', 'asc')
            ->get();
        
        $summary = [
            'total_overdue' => $data->count(),
            'total_denda' => $data->sum('denda_bunga'),
            'terlambat_terlama' => $data->max('hari_terlambat'),
            'terlambat_terpendek' => $data->min('hari_terlambat'),
            'rata_rata_terlambat' => $data->avg('hari_terlambat')
        ];
        
        return [
            'data' => $data,
            'summary' => $summary,
            'tanggal_laporan' => now()->format('d/m/Y H:i:s')
        ];
    }
    
    /**
     * Laporan performa pinjaman
     */
    public function laporanPerformaPinjaman($tahun)
    {
        $data = [];
        
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $data[$bulan] = [
                'total_pinjaman_baru' => $this->getPinjamanBaru($bulan, $tahun),
                'total_pelunasan' => $this->getTotalPelunasan($bulan, $tahun),
                'total_bunga_diterima' => $this->getTotalBunga($bulan, $tahun),
                'pinjaman_overdue' => $this->getPinjamanOverdue($bulan, $tahun),
                'rata_rata_angsuran' => $this->getRataRataAngsuran($bulan, $tahun)
            ];
        }
        
        return [
            'data' => $data,
            'tahun' => $tahun,
            'summary_tahunan' => $this->getSummaryTahunan($data)
        ];
    }
}
```

### **Laporan Billing**:
```php
/**
 * Laporan detail billing
 */
class BillingReport
{
    /**
     * Laporan billing bulanan
     */
    public function laporanBillingBulanan($bulan, $tahun)
    {
        $data = DB::table('tbl_trans_sp_bayar_temp as b')
            ->join('data_anggota as a', 'b.no_ktp', '=', 'a.no_ktp')
            ->select([
                'a.nama',
                'b.no_ktp',
                'b.tagihan_simpanan_wajib',
                'b.tagihan_simpanan_sukarela',
                'b.tagihan_simpanan_khusus_2',
                'b.tagihan_pinjaman',
                'b.tagihan_pinjaman_jasa',
                'b.tagihan_toserda',
                'b.total_tagihan',
                'b.status_bayar',
                'b.tgl_bayar'
            ])
            ->whereMonth('b.tgl_transaksi', $bulan)
            ->whereYear('b.tgl_transaksi', $tahun)
            ->orderBy('a.nama', 'asc')
            ->get();
        
        $summary = [
            'total_anggota' => $data->count(),
            'total_tagihan' => $data->sum('total_tagihan'),
            'sudah_bayar' => $data->where('status_bayar', 'sudah')->count(),
            'belum_bayar' => $data->where('status_bayar', 'belum')->count(),
            'total_sudah_bayar' => $data->where('status_bayar', 'sudah')->sum('total_tagihan'),
            'total_belum_bayar' => $data->where('status_bayar', 'belum')->sum('total_tagihan')
        ];
        
        return [
            'data' => $data,
            'summary' => $summary,
            'periode' => "Bulan {$bulan} Tahun {$tahun}"
        ];
    }
    
    /**
     * Laporan arus kas
     */
    public function laporanArusKas($bulan, $tahun)
    {
        $penerimaan = DB::table('tbl_trans_sp_bayar_temp')
            ->whereMonth('tgl_transaksi', $bulan)
            ->whereYear('tgl_transaksi', $tahun)
            ->where('status_bayar', 'sudah')
            ->sum('total_tagihan');
        
        $pengeluaran = DB::table('tbl_pinjaman_h')
            ->whereMonth('tgl_pinjam', $bulan)
            ->whereYear('tgl_pinjam', $tahun)
            ->where('status', '1')
            ->sum('jumlah');
        
        $saldo_awal = $this->getSaldoAwalBulan($bulan, $tahun);
        $saldo_akhir = $saldo_awal + $penerimaan - $pengeluaran;
        
        return [
            'saldo_awal' => $saldo_awal,
            'penerimaan' => $penerimaan,
            'pengeluaran' => $pengeluaran,
            'saldo_akhir' => $saldo_akhir,
            'periode' => "Bulan {$bulan} Tahun {$tahun}"
        ];
    }
}
```

---

## 🔍 **8.4 SISTEM ALERT & NOTIFICATION**

### **Alert System**:
```php
/**
 * Sistem alert untuk monitoring
 */
class AlertSystem
{
    /**
     * Check dan generate alerts
     */
    public function checkAlerts()
    {
        $alerts = [];
        
        // Alert pinjaman overdue
        $overdueAlerts = $this->checkPinjamanOverdue();
        $alerts = array_merge($alerts, $overdueAlerts);
        
        // Alert billing overdue
        $billingAlerts = $this->checkBillingOverdue();
        $alerts = array_merge($alerts, $billingAlerts);
        
        // Alert saldo kas rendah
        $kasAlerts = $this->checkSaldoKas();
        $alerts = array_merge($alerts, $kasAlerts);
        
        // Alert sistem
        $systemAlerts = $this->checkSystemHealth();
        $alerts = array_merge($alerts, $systemAlerts);
        
        // Send alerts
        $this->sendAlerts($alerts);
        
        return $alerts;
    }
    
    /**
     * Check pinjaman overdue
     */
    private function checkPinjamanOverdue()
    {
        $alerts = [];
        
        $overduePinjaman = DB::table('tempo_pinjaman as t')
            ->join('tbl_pinjaman_h as p', 't.pinjam_id', '=', 'p.id')
            ->join('data_anggota as a', 'p.anggota_id', '=', 'a.id')
            ->where('t.tempo', '<', now())
            ->where('p.lunas', 'Belum')
            ->get();
        
        foreach ($overduePinjaman as $pinjaman) {
            $hariTerlambat = now()->diffInDays($pinjaman->tempo);
            
            if ($hariTerlambat >= 30) {
                $alerts[] = [
                    'type' => 'critical',
                    'title' => 'Pinjaman Overdue 30+ Hari',
                    'message' => "Pinjaman {$pinjaman->no_pinjaman} ({$pinjaman->nama}) terlambat {$hariTerlambat} hari",
                    'data' => $pinjaman
                ];
            } elseif ($hariTerlambat >= 7) {
                $alerts[] = [
                    'type' => 'warning',
                    'title' => 'Pinjaman Overdue 7+ Hari',
                    'message' => "Pinjaman {$pinjaman->no_pinjaman} ({$pinjaman->nama}) terlambat {$hariTerlambat} hari",
                    'data' => $pinjaman
                ];
            }
        }
        
        return $alerts;
    }
    
    /**
     * Check billing overdue
     */
    private function checkBillingOverdue()
    {
        $alerts = [];
        
        $bulanLalu = now()->subMonth();
        $overdueBilling = DB::table('tbl_trans_sp_bayar_temp')
            ->whereMonth('tgl_transaksi', $bulanLalu->month)
            ->whereYear('tgl_transaksi', $bulanLalu->year)
            ->where('status_bayar', 'belum')
            ->count();
        
        if ($overdueBilling > 0) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Billing Overdue',
                'message' => "Ada {$overdueBilling} tagihan bulan {$bulanLalu->format('M Y')} yang belum dibayar",
                'data' => ['count' => $overdueBilling, 'bulan' => $bulanLalu->format('M Y')]
            ];
        }
        
        return $alerts;
    }
    
    /**
     * Check saldo kas
     */
    private function checkSaldoKas()
    {
        $alerts = [];
        
        $kasList = DataKas::all();
        
        foreach ($kasList as $kas) {
            $saldoMinimum = $kas->saldo * 0.1; // 10% dari saldo
            
            if ($kas->saldo < $saldoMinimum) {
                $alerts[] = [
                    'type' => 'critical',
                    'title' => 'Saldo Kas Rendah',
                    'message' => "Saldo kas {$kas->nama} hanya Rp " . number_format($kas->saldo, 0, ',', '.'),
                    'data' => $kas
                ];
            }
        }
        
        return $alerts;
    }
    
    /**
     * Check system health
     */
    private function checkSystemHealth()
    {
        $alerts = [];
        
        // Check database connection
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $alerts[] = [
                'type' => 'critical',
                'title' => 'Database Error',
                'message' => 'Tidak dapat terhubung ke database',
                'data' => ['error' => $e->getMessage()]
            ];
        }
        
        // Check queue size
        $queueSize = Queue::size();
        if ($queueSize > 1000) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Queue Backlog',
                'message' => "Queue memiliki {$queueSize} job yang pending",
                'data' => ['queue_size' => $queueSize]
            ];
        }
        
        // Check memory usage
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        
        if ($memoryUsage > 512 * 1024 * 1024) { // 512MB
            $alerts[] = [
                'type' => 'warning',
                'title' => 'High Memory Usage',
                'message' => "Memory usage: " . number_format($memoryUsage / 1024 / 1024, 2) . " MB",
                'data' => ['memory_usage' => $memoryUsage, 'memory_limit' => $memoryLimit]
            ];
        }
        
        return $alerts;
    }
    
    /**
     * Send alerts
     */
    private function sendAlerts($alerts)
    {
        foreach ($alerts as $alert) {
            // Log alert
            Log::warning('System Alert', $alert);
            
            // Send email notification
            if (in_array($alert['type'], ['critical', 'warning'])) {
                $this->sendEmailAlert($alert);
            }
            
            // Send in-app notification
            $this->sendInAppNotification($alert);
            
            // Broadcast real-time notification
            broadcast(new AlertGenerated($alert));
        }
    }
}
```

---

## 📱 **8.5 NOTIFICATION SYSTEM**

### **Email Notifications**:
```php
/**
 * Email notification system
 */
class EmailNotificationService
{
    /**
     * Send billing reminder
     */
    public function sendBillingReminder($anggota, $billing)
    {
        $data = [
            'nama' => $anggota->nama,
            'no_ktp' => $anggota->no_ktp,
            'total_tagihan' => $billing->total_tagihan,
            'tgl_jatuh_tempo' => $billing->tgl_transaksi,
            'detail_tagihan' => $this->getDetailTagihan($billing)
        ];
        
        Mail::to($anggota->email)->send(new BillingReminder($data));
    }
    
    /**
     * Send overdue notification
     */
    public function sendOverdueNotification($anggota, $billing, $hariTerlambat)
    {
        $data = [
            'nama' => $anggota->nama,
            'no_ktp' => $anggota->no_ktp,
            'total_tagihan' => $billing->total_tagihan,
            'hari_terlambat' => $hariTerlambat,
            'denda' => $this->hitungDenda($billing, $hariTerlambat)
        ];
        
        Mail::to($anggota->email)->send(new OverdueNotification($data));
    }
    
    /**
     * Send payment confirmation
     */
    public function sendPaymentConfirmation($anggota, $payment)
    {
        $data = [
            'nama' => $anggota->nama,
            'no_ktp' => $anggota->no_ktp,
            'jumlah_bayar' => $payment->jumlah_bayar,
            'tgl_bayar' => $payment->tgl_bayar,
            'sisa_tagihan' => $payment->sisa_tagihan
        ];
        
        Mail::to($anggota->email)->send(new PaymentConfirmation($data));
    }
}
```

---

## 🚀 **KESIMPULAN BAGIAN 8**

Bagian 8 ini telah mencakup secara lengkap:

✅ **Sistem Monitoring Real-time** - Dashboard monitoring dan WebSocket updates
✅ **Sistem Reporting** - Generator laporan untuk berbagai jenis data
✅ **Jenis-jenis Laporan** - Pinjaman, Billing, dan Keuangan
✅ **Sistem Alert & Notification** - Monitoring otomatis dan alert system
✅ **Notification System** - Email dan in-app notifications

**Next Step**: Lanjut ke Bagian 9 untuk Security & Access Control.

