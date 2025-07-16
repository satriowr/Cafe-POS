Aplikasi Pemesanan Menu

# Sistem Pemesanan Menu Digital Kafe Nala

Sistem ini dikembangkan untuk meningkatkan efisiensi layanan di Kafe Nala dengan memanfaatkan teknologi QR Code yang terhubung ke
sistem pemesanan digital. Tujuannya adalah untuk mengurangi antrian, mempercepat proses pemesanan, dan mempermudah manajemen
pesanan oleh admin dan dapur.

## Fitur

-   Scan QR Code untuk melihat daftar menu
-   Tambah menu ke keranjang
-   Lakukan pemesanan langsung dari ponsel
-   Update status pesanan secara real-time oleh dapur/admin
-   Laporan harian pesanan untuk admin/dapur
-   Cetak struk pemesanan

## Teknologi yang Digunakan

-   Laravel 11
-   Quick Response
-   MySQL
-   JavaScript

## Cara Instalasi

1. Clone repository ini:
    ```bash
    git clone https://github.com/username/nama-repo.git
    ```
2. Masuk ke folder proyek:
    ```bash
    cd nama-repo
    ```
3. Install dependency PHP:
    ```bash
    composer install
    ```
4. Copy file environment:
    ```bash
    cp .env.example .env
    ```
5. Konfigurasikan file `.env` untuk database dan setting lainnya.
6. Generate application key:
    ```bash
    php artisan key:generate
    ```
7. Jalankan migrasi database:
    ```bash
    php artisan migrate
    ```
8. Jalankan server lokal:
    ```bash
    php artisan serve
    ```

## Cara Menggunakan

-   Admin membuat QR Code berdasarkan nomor meja.
-   Pelanggan memindai QR Code yang mengarahkan ke halaman menu.
-   Pelanggan memilih makanan dan memasukkannya ke keranjang.
-   Pelanggan mengirim pesanan.
-   Dapur melihat daftar pesanan dan memperbarui status pesanan.
-   Admin mengkonfirmasi pembayaran
-   Admin dapat mencetak nota dan laporan harian pesanan.

## Kontributor

-   Levi Lulis Narulista

## Lisensi

Proyek ini bebas digunakan untuk tujuan pembelajaran.
