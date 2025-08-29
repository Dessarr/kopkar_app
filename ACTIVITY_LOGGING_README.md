# Sistem Activity Logging - Koperasi App

## Overview

Sistem ini telah ditambahkan fitur logging yang komprehensif untuk melacak semua aktivitas dalam aplikasi koperasi, khususnya pada modul pengajuan penarikan simpanan.

## Fitur Utama

### 1. **Activity Log Model**

-   **File**: `app/Models/ActivityLog.php`
-   **Tabel**: `activity_logs`
-   **Fitur**:
    -   Tracking user ID, user type (admin/member), dan user name
    -   Action yang dilakukan (create, update, delete, approve, reject, cancel, view)
    -   Module yang diakses (pengajuan_penarikan, pinjaman, simpanan, dll)
    -   Description detail aktivitas
    -   Old values dan new values untuk tracking perubahan
    -   IP address dan user agent untuk security
    -   Status aktivitas (success, failed, pending)
    -   Error message jika gagal
    -   Affected record ID dan type

### 2. **Activity Log Service**

-   **File**: `app/Services/ActivityLogService.php`
-   **Method**:
    -   `logSuccess()`: Log aktivitas berhasil
    -   `logFailed()`: Log aktivitas gagal
    -   `logPending()`: Log aktivitas pending
    -   `getLogs()`: Ambil logs dengan filter
    -   `getSummary()`: Statistik summary logs

### 3. **Activity Log Controller (Admin)**

-   **File**: `app/Http/Controllers/ActivityLogController.php`
-   **Fitur**:
    -   Index: Tampilkan semua logs dengan filter
    -   Show: Detail log spesifik
    -   Export Excel/PDF
    -   Clear old logs (pembersihan otomatis)

## Implementasi pada Sistem Pengajuan Penarikan

### Member Controller

-   **Pengajuan Baru**: Log status pending → success/failed
-   **Validasi**: Log setiap validasi yang gagal
-   **Pembatalan**: Log perubahan status dari pending ke batal

### Admin Controller

-   **Approval**: Log proses approval dengan detail lengkap
-   **Rejection**: Log penolakan dengan alasan
-   **Processing**: Log proses penarikan simpanan

## Database Schema

```sql
CREATE TABLE `activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `user_type` enum('admin','member') DEFAULT 'admin',
  `user_name` varchar(255) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `module` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `status` enum('success','failed','pending') DEFAULT 'success',
  `error_message` text DEFAULT NULL,
  `affected_record_id` bigint(20) unsigned DEFAULT NULL,
  `affected_record_type` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_logs_user_id_user_type_index` (`user_id`,`user_type`),
  KEY `activity_logs_module_action_index` (`module`,`action`),
  KEY `activity_logs_status_created_at_index` (`status`,`created_at`),
  KEY `activity_logs_affected_record_id_index` (`affected_record_id`)
);
```

## Routes

```php
// Activity Logs Routes
Route::prefix('activity-logs')->group(function () {
    Route::get('/', [ActivityLogController::class, 'index'])->name('admin.activity_logs.index');
    Route::get('/{id}', [ActivityLogController::class, 'show'])->name('admin.activity_logs.show');
    Route::get('/export/excel', [ActivityLogController::class, 'exportExcel'])->name('admin.activity_logs.export.excel');
    Route::get('/export/pdf', [ActivityLogController::class, 'exportPdf'])->name('admin.activity_logs.export.pdf');
    Route::post('/clear', [ActivityLogController::class, 'clearOldLogs'])->name('admin.activity_logs.clear');
});
```

## Cara Penggunaan

### 1. **Logging Aktivitas Berhasil**

```php
use App\Services\ActivityLogService;

ActivityLogService::logSuccess(
    'approve',                    // Action
    'pengajuan_penarikan',       // Module
    'Berhasil menyetujui pengajuan', // Description
    $oldValues,                   // Data sebelum perubahan
    $newValues,                   // Data setelah perubahan
    $recordId,                    // ID record yang terpengaruh
    'data_pengajuan_penarikan'   // Type record
);
```

### 2. **Logging Aktivitas Gagal**

```php
ActivityLogService::logFailed(
    'create',                     // Action
    'pengajuan_penarikan',       // Module
    'Gagal membuat pengajuan',    // Description
    'Saldo tidak mencukupi',      // Error message
    null,                         // Old values
    $requestData,                 // New values
    null,                         // Record ID
    'data_pengajuan_penarikan'   // Record type
);
```

### 3. **Logging Aktivitas Pending**

```php
ActivityLogService::logPending(
    'approve',                    // Action
    'pengajuan_penarikan',       // Module
    'Memulai proses approval',    // Description
    $currentData,                 // Current values
    $requestData,                 // Request data
    $recordId,                    // Record ID
    'data_pengajuan_penarikan'   // Record type
);
```

## Filter dan Pencarian

### Filter yang Tersedia:

-   **Module**: pengajuan_penarikan, pinjaman, simpanan, dll
-   **Action**: create, update, delete, approve, reject, cancel, view
-   **Status**: success, failed, pending
-   **User Type**: admin, member
-   **Date Range**: Tanggal dari - sampai
-   **Search**: Pencarian berdasarkan description atau user name

### Contoh Filter:

```php
$filters = [
    'module' => 'pengajuan_penarikan',
    'action' => 'approve',
    'status' => 'success',
    'date_from' => '2025-01-01',
    'date_to' => '2025-01-31'
];

$logs = ActivityLogService::getLogs($filters);
```

## Dashboard dan Statistik

### Summary Cards:

-   **Total Logs**: Jumlah total aktivitas
-   **Success**: Aktivitas berhasil
-   **Failed**: Aktivitas gagal
-   **Pending**: Aktivitas pending
-   **Today**: Aktivitas hari ini
-   **This Week**: Aktivitas minggu ini
-   **This Month**: Aktivitas bulan ini

## Security Features

### 1. **IP Address Tracking**

-   Mencatat IP address setiap aktivitas
-   Berguna untuk audit trail dan security monitoring

### 2. **User Agent Tracking**

-   Mencatat browser/device yang digunakan
-   Berguna untuk deteksi anomali

### 3. **User Authentication**

-   Otomatis detect user yang sedang login
-   Support multi-guard (admin dan member)

## Maintenance

### 1. **Clear Old Logs**

-   Fitur pembersihan log lama otomatis
-   Konfigurasi periode retensi (30, 60, 90, 180, 365 hari)
-   Konfirmasi sebelum penghapusan

### 2. **Export Data**

-   Export ke Excel (akan diimplementasikan)
-   Export ke PDF (akan diimplementasikan)

### 3. **Backup Logs**

-   Logs juga tersimpan di Laravel default log
-   Fallback jika database logging gagal

## Testing

### Seeder

```bash
php artisan db:seed --class=ActivityLogSeeder
```

### Sample Data

-   50 sample logs dengan data variatif
-   Cover semua action, module, dan status
-   Data realistic untuk testing

## Monitoring dan Alerting

### 1. **Failed Activities**

-   Monitor aktivitas yang gagal
-   Alert admin untuk investigasi

### 2. **Suspicious Activities**

-   Monitor IP address yang tidak biasa
-   Monitor user yang melakukan banyak action

### 3. **Performance Monitoring**

-   Track response time setiap action
-   Monitor database performance

## Best Practices

### 1. **Log Level**

-   **Success**: Aktivitas normal yang berhasil
-   **Failed**: Aktivitas yang gagal (perlu investigasi)
-   **Pending**: Aktivitas yang sedang diproses

### 2. **Description Format**

-   Gunakan bahasa yang jelas dan deskriptif
-   Include detail penting (ID, nominal, status)
-   Format: "Action + Detail + Result"

### 3. **Data Privacy**

-   Jangan log sensitive data (password, token)
-   Gunakan masking untuk data personal
-   Comply dengan regulasi privacy

## Troubleshooting

### 1. **Logs Tidak Muncul**

-   Check database connection
-   Verify table `activity_logs` exists
-   Check permissions

### 2. **Performance Issues**

-   Monitor query performance
-   Add database indexes jika perlu
-   Implement log rotation

### 3. **Storage Issues**

-   Monitor database size
-   Implement log retention policy
-   Regular cleanup old logs

## Future Enhancements

### 1. **Real-time Notifications**

-   WebSocket untuk real-time updates
-   Email notifications untuk failed activities

### 2. **Advanced Analytics**

-   Machine learning untuk anomaly detection
-   Predictive analytics untuk performance

### 3. **Integration**

-   SIEM system integration
-   External monitoring tools
-   API untuk third-party tools

## Conclusion

Sistem activity logging ini memberikan visibilitas lengkap atas semua aktivitas dalam aplikasi koperasi, memungkinkan admin untuk:

1. **Monitor** semua aktivitas real-time
2. **Audit** perubahan data dengan detail lengkap
3. **Troubleshoot** masalah dengan cepat
4. **Comply** dengan regulasi audit dan compliance
5. **Improve** user experience dan system performance

Dengan implementasi yang komprehensif ini, admin dapat dengan mudah melacak setiap aktivitas dan memastikan sistem berjalan dengan aman dan efisien.
