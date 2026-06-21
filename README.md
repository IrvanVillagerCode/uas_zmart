# Z-MART Boutique & Daily Needs

Aplikasi E-Commerce (Boutique & Kebutuhan Harian) berbasis Laravel dengan integrasi Firebase Auth (sinkronisasi User ke database MySQL local). Proyek ini sudah dikonfigurasi dengan Docker Compose untuk memudahkan dijalankan di komputer atau laptop manapun tanpa kendala/error setup manual.

---

## Panduan Menjalankan Aplikasi Menggunakan Docker

Dengan setup Docker ini, database, web server (Nginx), dan PHP-FPM akan berjalan otomatis di dalam kontainer yang terisolasi. Database juga akan diinisialisasi dan di-seed dengan data produk awal secara otomatis.

### Prasyarat
Sebelum memulai, pastikan komputer Anda sudah terinstal:
1. **Docker Desktop** (untuk Windows/Mac) atau **Docker Engine & Docker Compose** (untuk Linux).
2. Pastikan Docker Daemon dalam posisi aktif (Running).

---

### Langkah-Langkah Menjalankan Aplikasi

1. **Clone Repository**
   Buka terminal/CMD Anda lalu clone repository ini:
   ```bash
   git clone https://github.com/IrvanVillagerCode/uas_zmart.git
   cd uas_zmart
   ```

2. **Jalankan Docker Compose**
   Gunakan perintah berikut untuk membangun (*build*) image dan menjalankan kontainer di latar belakang (*detached mode*):
   ```bash
   docker compose up --build -d
   ```
   *Catatan: Proses pertama kali mungkin memakan waktu beberapa menit untuk mengunduh base image PHP, MySQL, Nginx, serta mengompilasi extension.*

3. **Mekanisme Otomatis saat Startup**
   Setelah kontainer aktif, script entrypoint (`docker/entrypoint.sh`) akan melakukan hal berikut secara otomatis:
   * Menyalin `.env.example` menjadi `.env` jika file `.env` belum ada di root.
   * Membuat `APP_KEY` aplikasi Laravel baru secara otomatis.
   * Menunggu database MySQL siap menerima koneksi.
   * Memeriksa database. Jika kosong atau tidak memiliki struktur lengkap, script akan menghapus tabel sisa dan mengimpor struktur tabel bersih dari `zmart.sql`.
   * Menjalankan *Seeder* (`php artisan db:seed --force`) untuk memuat katalog produk terbaru (Fashion & Kebutuhan Harian) serta user default (Admin & Customer).

4. **Akses Aplikasi**
   Setelah seluruh proses selesai (status kontainer menunjukkan *running*):
   * **Web Server (Nginx)** berjalan di alamat: **`http://localhost:8080`**
   * **Database MySQL** dapat diakses dari host menggunakan port **`33066`** (username: `root`, password: *(kosong)*, database: `zmart`).

5. **Akun Pengujian Default (Sudah Ter-seed)**
   * **Admin Dashboard**:
     * Username: `admin`
     * Email: `admin@zmart.id`
     * Password: `admin123`
   * **Customer Dashboard**:
     * Username: `user1`
     * Email: `user1@zmart.id`
     * Password: `user123`

---

### Perintah Docker Bermanfaat Lainnya

* **Melihat Log Jalannya Kontainer (Real-time)**
  Jika Anda ingin melihat proses migrasi, seeding, atau logs PHP-FPM:
  ```bash
  docker logs -f zmart-app
  ```

* **Menghentikan Aplikasi**
  Untuk mematikan seluruh kontainer tanpa menghapus data database yang sudah tersimpan:
  ```bash
  docker compose down
  ```

* **Mematikan dan Menghapus Volume Database (Reset Ulang Database)**
  Jika Anda ingin mereset total database dan memulainya dari database kosong/impor baru:
  ```bash
  docker compose down -v
  ```
