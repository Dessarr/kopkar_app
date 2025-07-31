<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# Kopkar App

Aplikasi Koperasi Karyawan dengan sistem billing dan simpanan terintegrasi.

## Fitur Utama

### 1. Sistem Billing Terintegrasi
- Generate billing otomatis untuk simpanan dan toserda
- Proses pembayaran dengan generate record di tbl_trans_sp
- Dashboard member menampilkan simpanan dari data transaksi aktual

### 2. Logika Billing Baru
- **Ketika billing disimpan**: Menambah ke data member/simpanan di tbl_anggota
- **Ketika billing diproses**: Generate record di tbl_trans_sp sesuai simpanan member
- **Dashboard member**: Menampilkan simpanan berdasarkan data tbl_trans_sp

## Script Maintenance

### Script Update Simpanan Pokok untuk Anggota Lama

#### 1. update_simpanan_pokok.php
**Fungsi**: Menambahkan simpanan pokok (100.000) untuk anggota aktif yang belum punya.

**Cara pakai**:
```bash
php update_simpanan_pokok.php
```

**Output**:
- Mengecek anggota yang sudah dan belum memiliki simpanan pokok
- Menambahkan simpanan pokok untuk anggota yang belum punya
- Menampilkan hasil update

#### 2. fix_simpanan_pokok.php
**Fungsi**: Memperbaiki data simpanan pokok yang bernilai 0 menjadi 100.000.

**Cara pakai**:
```bash
php fix_simpanan_pokok.php
```

**Output**:
- Mengecek record simpanan pokok dengan nilai 0
- Update nilai 0 menjadi 100.000
- Menampilkan hasil perbaikan

#### 3. test_dashboard_member.php
**Fungsi**: Test perhitungan simpanan member seperti di dashboard.

**Cara pakai**:
```bash
php test_dashboard_member.php
```

**Output**:
- Menampilkan data simpanan member dari tbl_trans_sp
- Menampilkan jenis simpanan dari master data
- Menampilkan total simpanan dan status tampil

## Struktur Data

### Tabel Utama
- `tbl_anggota`: Data anggota
- `tbl_trans_sp`: Transaksi simpanan
- `jns_simpan`: Master data jenis simpanan
- `billing`: Data billing aktif
- `billing_process`: Data billing yang sudah diproses

### Alur Kerja
1. **Generate Billing** → Billing disimpan di tabel billing
2. **Proses Pembayaran** → Generate record di tbl_trans_sp
3. **Dashboard Member** → Tampilkan data dari tbl_trans_sp

## Cara Menggunakan

### 1. Setup Awal
```bash
# Install dependencies
composer install

# Setup database
php artisan migrate

# Jalankan script update simpanan pokok
php update_simpanan_pokok.php
php fix_simpanan_pokok.php
```

### 2. Generate Billing
- Akses menu Billing di admin panel
- Pilih bulan dan tahun
- Sistem akan generate billing otomatis

### 3. Proses Pembayaran
- Klik tombol "Proses" pada billing
- Sistem akan generate record di tbl_trans_sp
- Billing dipindah ke billing_process

### 4. Dashboard Member
- Login sebagai member
- Dashboard menampilkan simpanan dari tbl_trans_sp
- Semua jenis simpanan ditampilkan sesuai master data

## Troubleshooting

### Masalah Login Member
Jika member tidak bisa login:
1. Cek password di database (harus bcrypt)
2. Pastikan kolom aktif = 'Y'
3. Cek log Laravel untuk error detail

### Simpanan Tidak Muncul
Jika simpanan tidak muncul di dashboard:
1. Jalankan script update_simpanan_pokok.php
2. Jalankan script fix_simpanan_pokok.php
3. Cek data di tbl_trans_sp

### Billing Error
Jika billing error:
1. Cek log Laravel di storage/logs/laravel.log
2. Pastikan master data jns_simpan lengkap
3. Cek koneksi database

## Catatan Penting

- Semua script di atas dijalankan dari root project
- Pastikan environment sudah ter-load (autoload Laravel, dsb)
- Script ini hanya untuk maintenance/debug, tidak untuk production secara reguler
- Backup database sebelum menjalankan script update

## Kontribusi

Untuk menambah script baru:
1. Buat file PHP di root project
2. Load Laravel dengan require_once
3. Tambahkan dokumentasi di README
4. Test script sebelum digunakan
