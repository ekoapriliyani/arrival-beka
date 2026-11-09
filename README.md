# Arrival Beka — QR Tag Scanner Wire Rod

Aplikasi web Laravel untuk mencatat **arrival wire rod** berbasis **Purchase Order (PO)**. Data coil dibaca dari **QR code/tag** (format barcode mengikuti masing-masing supplier) atau diinput manual jika QR tidak terbaca. Hasil scan dapat diekspor ke **Excel (.xlsx)**.

## Fitur utama

- **Kelola Supplier**: atur nama/kode supplier, **delimiter**, dan **urutan/format barcode** per supplier.
- **Header Arrival (PO)**: buat PO dengan nomor PO, tanggal, item, deskripsi (opsional), dan supplier.
- **Scan QR**: scan tag coil menggunakan kamera browser (ZXing). Payload QR diparsing sesuai delimiter & format barcode supplier aktif.
- **Input Manual**: cadangan input data coil dari label fisik saat QR tidak terbaca (manufacturer mengikuti supplier PO; tanggal QR terisi otomatis hari ini).
- **Riwayat PO**: buka kembali PO tersimpan, lihat jumlah scan, hapus PO beserta detailnya.
- **Detail Scan**: tabel data coil (Heat No, Coil No, Weight MT, Grade, Size, sumber Scan/Manual).
- **Offline-friendly (antrean lokal)**: jika simpan detail ke server gagal, data scan disimpan sementara di `localStorage` dan dikirim ulang otomatis saat koneksi/server tersedia.
- **Export Excel**: unduh data arrival (header + detail) sebagai file `.xlsx`.

## Teknologi

- **Backend**: PHP **^8.3**, Laravel **^13**, SQLite (default).
- **Frontend**: Blade + JavaScript, ikon SVG inline (Heroicons) via komponen `<x-icon>`, library **ZXing** (scan) dan **SheetJS/xlsx** (export) dari CDN.
- **Tooling**: Vite, Tailwind CSS v4 (terpasang), PHPUnit.

## Prasyarat

- PHP **8.3+** (proyek ini berjalan baik di PHP 8.4).
- [Composer](https://getcomposer.org/)
- [Node.js & npm](https://nodejs.org/) (untuk instalasi dependensi frontend/build; halaman utama memakai CSS inline + CDN).
- Ekstensi PHP umum Laravel (mis. `mbstring`, `openssl`, `pdo_sqlite`/driver DB yang dipakai, `fileinfo`, dll.).
- Opsional (Windows): [Laravel Herd](https://herd.laravel.com/) untuk serving otomatis domain `.test`.

## Instalasi

### Opsi cepat (disarankan)

Dari root proyek:

```bash
composer setup
```

Script `composer setup` akan: install Composer dependencies, menyalin `.env.example` → `.env` (jika belum ada), generate app key, menjalankan migrasi, `npm install --ignore-scripts`, dan `npm run build`.

### Instalasi manual

```bash
composer install
cp .env.example .env        # Windows PowerShell: Copy-Item .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build               # opsional; diperlukan jika memakai aset Vite
```

> Database default menggunakan **SQLite**. Pastikan konfigurasi `DB_CONNECTION`/file database di `.env` sesuai (Laravel dapat memakai SQLite sesuai konfigurasi proyek).

## Menjalankan aplikasi

### Laravel Herd (Windows/macOS)

Jika proyek berada di direktori Herd (mis. `C:\Herd\arrival-beka`), Herd biasanya melayani aplikasi di:

- `http://arrival-beka.test`

### PHP built-in server

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

Buka: `http://127.0.0.1:8000`

### Mode development (jika diperlukan)

```bash
composer run dev
```

> **Catatan kamera/scan QR**: akses kamera browser umumnya memerlukan konteks aman (**`https://`** atau **`localhost`**). Jika kamera gagal, izinkan akses kamera di browser.

## Cara penggunaan

### 1) Tambah & atur Supplier

1. Buka menu **Kelola Supplier** (`/suppliers`, tautan dari halaman scanner).
2. Isi **Nama Supplier** (wajib), **Kode** (opsional), dan **Delimiter** (pemisah antar bagian di barcode, mis. `-`, `|`, `;`, `#`, `~`, `,`).
3. Tentukan **Format Barcode**: urutan field yang dipisahkan koma, **sesuai urutan data di QR code** supplier.
4. Simpan. Pratinjau “contoh barcode” membantu memverifikasi urutan.

**Field barcode yang didukung:**
- Tersedia: `manufacturer`, `grade`, `heat_no`, `coil_no`, `weight`, `size`, `qr_date`
- **Wajib ada** dalam format: `manufacturer`, `grade`, `heat_no`, `coil_no`, `weight`, `size`
- Opsional: `qr_date` (format tanggal `YYYY-MM-DD` pada QR; jika tidak valid, sistem mengosongkannya saat parsing scan)

**Contoh format:**
```text
manufacturer,grade,heat_no,coil_no,weight,size,qr_date
```
Dengan delimiter `-`, contoh payload: `Nippon Steel-SWRH82B-12345678-87654321-2.369-5.5-2026-11-01`

> Supplier yang sudah dipakai oleh data arrival **tidak dapat dihapus**.

### 2) Buat Header Arrival (PO)

1. Buka **Scanner** (`/`).
2. Isi **Purchase Order**, **Tanggal**, **Item**, **Description** (opsional), dan pilih **Supplier**.
3. Klik **Simpan Header**. Tombol **Mulai Scan** dan **Input Manual** aktif setelah header tersimpan.

### 3) Scan QR

1. Pastikan **PO Aktif** sudah dipilih/tersimpan.
2. Klik **Mulai Scan** dan izinkan akses kamera.
3. Arahkan kamera ke QR tag. Sistem mem-parsing data berdasarkan **delimiter & format barcode supplier** pada PO aktif, lalu menyimpan detail ke server.
4. Jika format/jumlah bagian QR tidak sesuai pengaturan supplier, aplikasi menampilkan peringatan — periksa konfigurasi supplier atau QR.

### 4) Input Manual (fallback)

1. Pada PO aktif, klik **Input Manual**.
2. Isi data dari label fisik: Manufacturer, Grade, Heat No, Coil No, Weight (MT), Size (mm), Tanggal QR (opsional).
3. Simpan. Data tercatat dengan sumber **Manual** (scan QR tercatat sebagai **Scan**).

### 5) Riwayat PO, Clear, dan Hapus

- **Riwayat PO**: klik **Buka** untuk memuat PO beserta detailnya; klik **Hapus** untuk menghapus PO dan seluruh scan-nya (konfirmasi).
- **Clear**: menghapus **semua detail scan** pada PO aktif, **header PO tetap**.
- **PO Baru**: mulai header baru (hentikan scan terlebih dahulu jika sedang aktif).

### 6) Export Excel

1. Pastikan ada **PO aktif** dan data scan.
2. Klik **Export Excel**.
3. File `.xlsx` berisi info header (PO, tanggal, item, supplier, deskripsi) dan tabel detail (termasuk waktu scan & sumber).

## Struktur data (ringkas)

- **Supplier**: `name`, `code`, `delimiter`, `barcode_format`.
- **Arrival Header (PO)**: `po_number`, `tanggal`, `item`, `description`, `supplier_id`.
- **Arrival Detail (coil/tag)**: `manufacturer`, `grade`, `heat_no`, `coil_no`, `weight`, `size`, `qr_date`, `source` (`scan`/`manual`), `scanned_at`.

## Rute utama

| Method | URI | Keterangan |
|---|---|---|
| GET | `/` | Halaman scanner (PO aktif, scan, riwayat) |
| GET/POST/PUT/DELETE | `/suppliers` (+ `/{supplier}`) | Kelola supplier (JSON untuk aksi form) |
| GET/POST | `/arrivals` | Daftar/buat header arrival |
| GET/DELETE | `/arrivals/{arrival}` | Detail/hapus PO |
| POST/DELETE | `/arrivals/{arrival}/details` | Tambah detail scan/manual / clear detail |

## Pengujian

```bash
php artisan test --compact
# atau
composer test
```

## Lisensi

Aplikasi ini dibangun di atas Laravel (MIT). Gunakan sesuai kebutuhan proyek Anda.
