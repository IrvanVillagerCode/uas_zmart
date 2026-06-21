# Z-MART Boutique & Daily Needs - Setup & Troubleshooting Guide

Aplikasi E-Commerce (Pakaian & Kebutuhan Harian) berbasis **Laravel 12** terintegrasi dengan **Firebase Auth** (sinkronisasi user ke database lokal) dan **Midtrans Payment Gateway** (pembayaran Sandbox).

Panduan ini memuat langkah instalasi dari awal (*git clone*), penjelasan proses *login/register*, penanganan error umum, serta konfigurasi Docker agar kompatibel dengan berbagai perangkat (termasuk Mac Apple Silicon).

---

## 📋 Daftar Ini
1. [Metode A: Setup Menggunakan Docker (Direkomendasikan)](#-metode-a-setup-menggunakan-docker-direkomendasikan)
2. [Metode B: Setup Natively Menggunakan Laragon / XAMPP](#-metode-b-setup-natively-menggunakan-laragon--xampp)
3. [Alur Login, Register & Sinkronisasi User](#-alur-login-register--sinkronisasi-user)
4. [Alur Uji Coba Pembayaran Midtrans](#-alur-uji-coba-pembayaran-midtrans)
5. [Pecahkan Masalah & Solusi Error Umum](#-pecahkan-masalah--solusi-error-umum)
6. [Kompatibilitas Docker di Berbagai Perangkat](#-kompatibilitas-docker-di-berbagai-perangkat)

---

## 🐳 Metode A: Setup Menggunakan Docker (Direkomendasikan)

Dengan Docker Compose, seluruh lingkungan server web (Nginx), PHP-FPM, dan database (MySQL) berjalan secara otomatis di dalam kontainer yang terisolasi.

### Langkah-Langkah:
1. **Clone Repository**
   Buka terminal/CMD/PowerShell Anda, lalu clone repository ini:
   ```bash
   git clone https://github.com/IrvanVillagerCode/uas_zmart.git
   cd uas_zmart
   ```
2. **Pastikan Docker Desktop Aktif**
   Buka aplikasi Docker Desktop di komputer Anda dan pastikan statusnya *Engine Running*.
3. **Jalankan Docker Compose**
   Bangun image (*build*) dan jalankan seluruh container di background:
   ```bash
   docker compose up --build -d
   ```
4. **Mekanisme Startup Otomatis**
   Setelah kontainer menyala, script entrypoint (`docker/entrypoint.sh`) otomatis bekerja:
   * Menyalin file `.env.example` menjadi `.env`.
   * Membuat kunci enkripsi `APP_KEY` Laravel.
   * Menunggu database MySQL siap.
   * Mengimpor struktur tabel dari `zmart.sql` jika database kosong.
   * Menjalankan seeder produk dan akun demo default.
5. **Akses Aplikasi**
   * Buka browser dan ketik alamat: **[http://localhost:8080](http://localhost:8080)**
   * Koneksi database dari host berada di port **`33066`** (User: `root`, Password: *(kosong)*, DB: `zmart`).

---

## 💻 Metode B: Setup Natively Menggunakan Laragon / XAMPP

Jika Anda tidak menggunakan Docker dan ingin menjalankannya secara lokal menggunakan Laragon atau XAMPP.

### Langkah-Langkah:
1. **Clone Repository**
   ```bash
   git clone https://github.com/IrvanVillagerCode/uas_zmart.git
   cd uas_zmart
   ```
2. **Salin File Konfigurasi Lingkungan**
   Duplikat `.env.example` menjadi `.env`:
   * **Windows (CMD/PowerShell)**: `copy .env.example .env`
   * **Linux/macOS**: `cp .env.example .env`
3. **Konfigurasi Database lokal di `.env`**
   Buka file `.env` Anda dan sesuaikan konfigurasi port MySQL default lokal (Laragon/XAMPP port default: `3306`):
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=zmart
   DB_USERNAME=root
   DB_PASSWORD=
   
   # Gunakan driver file untuk kecepatan & kemudahan setup lokal
   SESSION_DRIVER=file
   CACHE_STORE=file
   ```
4. **Install Dependensi Proyek**
   Jalankan instalasi package PHP dan Node.js:
   ```bash
   composer install
   npm install
   ```
5. **Buat Kunci Aplikasi & Jalankan Migrasi**
   ```bash
   php artisan key:generate
   ```
6. **Import File SQL Database**
   * Buat database kosong bernama `zmart` di HeidiSQL, phpMyAdmin, atau MySQL client Anda.
   * Import file database **[zmart.sql](file:///c:/laragon/www/uas_zmart/zmart.sql)** ke dalam database `zmart` yang baru dibuat.
   * Jalankan migrasi tambahan untuk memastikan tabel sistem lengkap:
     ```bash
     php artisan migrate
     ```
7. **Jalankan Aplikasi**
   Kompilasi asset frontend menggunakan Vite dan nyalakan server lokal:
   * Jalankan dev server Vite: `npm run dev` (di terminal ke-1)
   * Jalankan server Laravel: `php artisan serve` (di terminal ke-2)
   * Akses aplikasi di browser melalui: **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

---

## 🔐 Alur Login, Register & Sinkronisasi User

Aplikasi ini menggunakan **Firebase Auth** di sisi frontend untuk mengelola pendaftaran dan login yang aman (termasuk tombol Google Sign-In), kemudian menyinkronkan data tersebut ke database lokal.

### Akun Pengujian Default:
| Peran (Role) | Email | Password | Username |
|---|---|---|---|
| **Admin** | `admin@zmart.id` | `admin123` | `admin` |
| **Customer** | `user1@zmart.id` | `user123` | `user1` |

### Cara Kerja Registrasi User Baru:
1. Pengguna masuk ke halaman **Register** dan mengisi data diri atau klik **Google Sign-In**.
2. Firebase SDK mendaftarkan user ke server Firebase. Setelah berhasil, Firebase akan menghasilkan token ID user (`uid`).
3. Frontend secara background mengirim data user beserta `uid` ke route API sinkronisasi lokal (`/register-sync` or `/google-sync`).
4. Laravel menerima request tersebut, memverifikasi data, lalu membuat record user baru di database lokal `users` agar terintegrasi dengan tabel `cart` dan `orders` lokal.

---

## 💳 Alur Uji Coba Pembayaran Midtrans

Proyek ini telah dikonfigurasi dengan API Key Sandbox Midtrans.

1. Masuk (*login*) ke situs sebagai customer.
2. Tambahkan beberapa pakaian atau sembako ke keranjang belanja, lalu klik **Checkout**.
3. Pilih metode pembayaran **Midtrans (Virtual Account, QRIS, E-Wallet)**.
4. Klik **Konfirmasi & Buat Pesanan**. Anda akan otomatis dialihkan ke halaman **Midtrans Snap hosted page**.
5. Anda dapat membatalkan atau menyelesaikan pembayaran menggunakan simulator pembayaran Midtrans Sandbox.
6. Jika pembayaran tertunda (*pending*), Anda dapat kembali ke **Dashboard** -> **Pesanan Saya** dan mengklik tombol **💳 Bayar Sekarang** untuk melanjutkan pembayaran.
7. Di lingkungan produksi/terowongan online (ngrok), Midtrans akan mengirim webhook notification ke `/midtrans/notification` untuk mengubah status pesanan di database lokal menjadi `success` atau `failed` secara real-time.

---

## 🛠️ Pecahkan Masalah & Solusi Error Umum

### 1. Error: `SQLSTATE[HY000] [2002] Connection refused`
* **Gejala**: Halaman memuat error koneksi database tidak dapat dilakukan.
* **Penyebab**: Laravel mencoba terhubung ke database tetapi server MySQL tidak aktif, atau port yang ditarget salah.
* **Solusi**:
  * **Jika menggunakan Docker**: Pastikan kontainer `zmart-db` berjalan (`docker ps`). Jika Docker Desktop tidak aktif, Anda tidak dapat menggunakan port `33066`.
  * **Jika menggunakan Laragon/XAMPP**: Pastikan service MySQL di Laragon/XAMPP sudah diklik **Start** dan berjalan di port `3306`. Pastikan `.env` Anda menggunakan `DB_PORT=3306`.

### 2. Error: `Table 'zmart.sessions' doesn't exist`
* **Gejala**: Aplikasi Laravel crash langsung saat dibuka dengan pesan tabel `sessions` tidak ditemukan.
* **Penyebab**: Konfigurasi `SESSION_DRIVER` di set ke `database`, tetapi tabel `sessions` belum terbuat di DB lokal.
* **Solusi**:
  1. Ubah konfigurasi driver session di file **[.env](file:///c:/laragon/www/uas_zmart/.env)** menjadi `file`:
     ```env
     SESSION_DRIVER=file
     ```
  2. Hapus tabel `migrations` yang rusak di database Anda jika ada, lalu jalankan migrasi Laravel untuk membangun struktur tabel sistem bawaan:
     ```bash
     php artisan migrate
     ```
  3. Bersihkan cache Laravel agar konfigurasi baru aktif:
     ```bash
     php artisan optimize:clear
     ```

### 3. Bentrokan Port (Port Conflict) `8080` atau `33066` di Docker
* **Penyebab**: Komputer Anda sudah menggunakan port `8080` (misal oleh Apache/Laragon lain) atau port `33066`.
* **Solusi**:
  Buka file **[docker-compose.yml](file:///c:/laragon/www/uas_zmart/docker-compose.yml)**, lalu ubah pemetaan port host di sisi kiri sebelum tanda titik dua (`:`):
  ```yaml
  # Contoh mengubah port web server dari 8080 ke 8085
  web:
    ports:
      - "8085:80"   # Format -> "PORT_HOST:PORT_CONTAINER"
      
  # Contoh mengubah port database host dari 33066 ke 3307
  db:
    ports:
      - "3307:3306"
  ```
  Jalankan kembali docker-compose setelah diubah: `docker compose up -d`.

---

## 🍏 Kompatibilitas Docker di Berbagai Perangkat

Agar kontainer Docker dapat berjalan lancar tanpa hambatan arsitektur di sistem operasi dan hardware yang berbeda:

### 1. Kompatibilitas Apple Silicon (Mac M1/M2/M3)
Image database `mysql:8.0` terkadang gagal memuat atau berjalan lambat di prosesor ARM Mac (M1/M2/M3) karena masalah emulasi x86.

**Solusi**: Tambahkan opsi `platform: linux/amd64` atau gunakan image MariaDB yang mendukung multi-arsitektur di dalam file **[docker-compose.yml](file:///c:/laragon/www/uas_zmart/docker-compose.yml)**:
```yaml
  db:
    image: mysql:8.0
    platform: linux/amd64   # Tambahkan baris ini untuk kompatibilitas Apple Silicon / ARM
    container_name: zmart-db
    ...
```

### 2. Kompatibilitas Windows WSL2 (Docker Desktop)
Pastikan Anda menggunakan backend **WSL2** di Docker Desktop daripada Hyper-V demi performa volume mounting folder yang jauh lebih cepat.
* Buka **Docker Desktop Settings** -> **General** -> Centang **Use the WSL 2 based engine**.
* Pastikan file line endings proyek Anda menggunakan format **LF** (bukan CRLF Windows) pada file script shell seperti `docker/entrypoint.sh` agar tidak terjadi error `\\r: command not found` saat entrypoint dieksekusi di dalam container Linux.
