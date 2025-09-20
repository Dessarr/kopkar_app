# Konversi Password dari CodeIgniter ke Laravel Bcrypt

## Masalah
Password di database menggunakan hash CodeIgniter, sedangkan aplikasi Laravel memerlukan bcrypt hash. Ini menyebabkan error "This password does not use the Bcrypt algorithm."

## Solusi

### 1. Menggunakan Command Artisan (Recommended)

#### Konversi semua password:
```bash
php artisan passwords:convert
```

#### Konversi hanya tbl_user:
```bash
php artisan passwords:convert --tbl-user
```

#### Konversi hanya tbl_anggota:
```bash
php artisan passwords:convert --tbl-anggota
```

#### Custom password untuk admin:
```bash
php artisan passwords:convert --admin-password=passwordbaru123
```

#### Custom password untuk user lain:
```bash
php artisan passwords:convert --default-password=passworddefault123
```

### 2. Menggunakan Seeder

#### Reset password admin saja:
```bash
php artisan db:seed --class=ResetAdminPasswordSeeder
```

#### Konversi semua password:
```bash
php artisan db:seed --class=PasswordConverterSeeder
```

## Password Default

### tbl_user
- **Admin**: `admin123`
- **User lain**: `password123`

### tbl_anggota
- **Password**: Menggunakan `no_ktp` masing-masing anggota

## Catatan Penting

1. **Backup Database**: Selalu backup database sebelum menjalankan konversi
2. **Test Login**: Setelah konversi, test login dengan password baru
3. **Update User**: Beri tahu user tentang password baru mereka
4. **Security**: Ganti password default setelah login pertama kali

## Troubleshooting

Jika masih ada error:
1. Pastikan command berjalan dengan sukses
2. Clear cache: `php artisan cache:clear`
3. Restart server: `php artisan serve`
4. Cek log error di `storage/logs/laravel.log`
