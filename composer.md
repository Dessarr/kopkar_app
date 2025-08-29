# 📦 Composer Packages Documentation

Dokumentasi lengkap tentang semua package Composer yang terinstall di project Kopkar App ini.

## 🚀 Cara Install Semua Dependencies

```bash
# Install semua dependencies
composer install

# Atau jika ingin update ke versi terbaru
composer update

# Install package baru
composer require nama-package

# Install package development
composer require --dev nama-package
```

## 📋 Daftar Package yang Terinstall

### 🔧 **Core Laravel Framework**
- **`laravel/framework`** (^12.0) - Framework utama Laravel
- **`laravel/tinker`** (^2.10.1) - REPL untuk debugging dan testing

### 📊 **Excel & Spreadsheet Processing**
- **`maatwebsite/excel`** (^3.1) - Package untuk import/export Excel
- **`phpoffice/phpspreadsheet`** (^1.29) - Library PHP untuk membaca/menulis file Excel

**Kegunaan:**
- Import data Excel untuk billing (seperti yang sudah diimplementasikan)
- Export laporan ke format Excel
- Manipulasi data spreadsheet

**Contoh Penggunaan:**
```php
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BillingUploadImport;

// Import Excel
$data = Excel::toArray(new BillingUploadImport(), $file);

// Export Excel
Excel::download(new BillingExport(), 'laporan.xlsx');
```

### 🖨️ **PDF Generation**
- **`barryvdh/laravel-dompdf`** (^3.1) - Wrapper Laravel untuk DOMPDF
- **`dompdf/dompdf`** (3.1.0) - Library untuk generate PDF dari HTML

**Kegunaan:**
- Generate laporan PDF
- Cetak tagihan dan kwitansi
- Export data ke format PDF

**Contoh Penggunaan:**
```php
use Barryvdh\DomPDF\Facade\Pdf;

$pdf = PDF::loadView('pdf.laporan', $data);
return $pdf->download('laporan.pdf');
```

### 🧪 **Development & Testing**
- **`fakerphp/faker`** (^1.23) - Generate fake data untuk testing
- **`laravel/pail`** (^1.2.2) - Tool untuk debugging dan monitoring
- **`laravel/pint`** (^1.13) - Code formatter untuk Laravel
- **`laravel/sail`** (^1.41) - Docker environment untuk development
- **`mockery/mockery`** (^1.6) - Mocking framework untuk testing
- **`nunomaduro/collision`** (^8.6) - Error handling untuk console
- **`phpunit/phpunit`** (^11.5.3) - Unit testing framework

### 🔌 **HTTP & API**
- **`guzzlehttp/guzzle`** (7.9.3) - HTTP client untuk API calls
- **`guzzlehttp/promises`** (2.2.0) - Promise library untuk async operations
- **`guzzlehttp/psr7`** (2.7.1) - PSR-7 HTTP message implementation

### 📅 **Date & Time**
- **`nesbot/carbon`** (3.10.1) - Extension untuk DateTime PHP
- **`carbonphp/carbon-doctrine-types`** (3.2.0) - Carbon types untuk Doctrine

**Kegunaan:**
- Manipulasi tanggal dan waktu
- Format tanggal Indonesia
- Perhitungan selisih tanggal

**Contoh Penggunaan:**
```php
use Carbon\Carbon;

$tanggal = Carbon::now();
$bulanIndonesia = $tanggal->locale('id')->monthName;
```

### 🗄️ **Database & ORM**
- **`doctrine/inflector`** (2.0.10) - Inflection library untuk ORM
- **`doctrine/lexer`** (3.0.1) - Lexer untuk parsing

### 🔐 **Security & Validation**
- **`egulias/email-validator`** (4.0.4) - Validasi email
- **`ezyang/htmlpurifier`** (4.18.0) - HTML filtering untuk security
- **`webmozart/assert`** (1.11.0) - Assertion library

### 📁 **File System**
- **`league/flysystem`** (3.30.0) - File storage abstraction
- **`league/flysystem-local`** (3.30.0) - Local filesystem adapter
- **`league/mime-type-detection`** (1.16.0) - MIME type detection

### 🎨 **Frontend & UI**
- **`nunomaduro/termwind`** (2.3.1) - Tailwind CSS untuk terminal
- **`laravel/prompts`** (0.3.6) - Beautiful prompts untuk console

### 📝 **Logging & Monitoring**
- **`monolog/monolog`** (3.9.0) - Logging library
- **`psr/log`** (3.0.2) - PSR-3 logging interface

### 🔄 **Utilities**
- **`ramsey/uuid`** (4.9.0) - UUID generation
- **`ramsey/collection`** (2.1.1) - Collection utilities
- **`myclabs/deep-copy`** (1.13.3) - Deep copy objects

## 🛠️ **Package yang Sering Digunakan di Project Ini**

### 1. **Maatwebsite Excel** - Import/Export Excel
```bash
composer require maatwebsite/excel
```

**Fitur:**
- Import Excel untuk billing upload
- Export laporan ke Excel
- Handle berbagai format Excel (.xlsx, .xls)

### 2. **Laravel DomPDF** - Generate PDF
```bash
composer require barryvdh/laravel-dompdf
```

**Fitur:**
- Generate PDF dari view Blade
- Custom styling dengan CSS
- Download atau preview PDF

### 3. **Carbon** - Date/Time Manipulation
```bash
# Sudah include dengan Laravel
use Carbon\Carbon;
```

**Fitur:**
- Format tanggal Indonesia
- Perhitungan selisih waktu
- Localization

## 📚 **Referensi Package**

- **Maatwebsite Excel**: https://docs.laravel-excel.com/
- **Laravel DomPDF**: https://github.com/barryvdh/laravel-dompdf
- **Carbon**: https://carbon.nesbot.com/
- **Laravel Framework**: https://laravel.com/docs

## ⚠️ **Catatan Penting**

1. **Versi PHP**: Project ini membutuhkan PHP ^8.2
2. **Laravel Version**: Menggunakan Laravel 12.x
3. **Composer**: Pastikan Composer terinstall dan up-to-date
4. **Extensions**: Beberapa package membutuhkan PHP extensions tertentu

## 🚀 **Quick Start untuk Developer Baru**

```bash
# Clone project
git clone [repository-url]
cd kopkar-app

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Setup database di .env
# Jalankan migration
php artisan migrate

# Install NPM dependencies (jika ada)
npm install

# Jalankan project
php artisan serve
```

---

**Last Updated**: 24 Agustus 2025  
**Project**: Kopkar App  
**Laravel Version**: 12.x  
**PHP Version**: 8.2+
