# KostKu

KostKu adalah dashboard administrasi pengelolaan rumah kost berbasis Laravel. Aplikasi ini membantu pemilik properti mengelola kost, kamar, penyewa, booking, pembayaran, dan laporan dalam satu antarmuka responsif.

## Fitur

- Autentikasi dan otorisasi admin
- Dashboard statistik dan grafik dinamis
- CRUD properti, kamar, dan penyewa
- Approval/reject booking dengan pembaruan status kamar otomatis
- Verifikasi pembayaran
- Laporan pendapatan dan okupansi dengan filter tanggal
- Export CSV/Excel dan halaman cetak PDF
- Layout responsif untuk mobile, tablet, dan desktop

## Teknologi

- Laravel 12
- PHP 8.2+
- MySQL 8
- Blade, Tailwind CSS 4, dan JavaScript
- Vite

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Buat database MySQL bernama `kostku`, sesuaikan konfigurasi database pada `.env`, kemudian jalankan:

```bash
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
php artisan serve
```

## Akun Demo

```text
Email: admin@kostku.com
Password: password
```

Ganti kredensial demo sebelum menggunakan aplikasi pada lingkungan produksi.

## Pengujian

```bash
php artisan test
vendor/bin/pint --test
npm run build
```
