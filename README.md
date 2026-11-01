# Sistem Informasi Sertifikat Sukarobot Academy

Aplikasi web untuk menerbitkan, menyimpan, mencari, dan memverifikasi sertifikat secara terpusat di Sukarobot Academy. Menggantikan pengelolaan sertifikat manual yang selama ini sulit dicari, datanya tidak sinkron, dan keasliannya sering dipertanyakan.

> Status: **tahap persiapan implementasi**. Struktur database, model, dan seeder sudah tersedia. Fitur aplikasi dikerjakan bertahap (lihat [Status pengembangan](#status-pengembangan)).

## Latar belakang dan tujuan

Sukarobot Academy menerbitkan lima jenis sertifikat: **magang, Praktik Kerja Lapangan (PKL), pelatihan LPK, uji kompetensi (ujikom) DUDI, dan kursus**. Pengelolaan manual menimbulkan masalah berikut:

- Sertifikat mudah hilang dan sulit dicari kembali.
- Data peserta dan data pada sertifikat tidak sinkron.
- Belum ada cara langsung untuk memeriksa keaslian sertifikat.

Tujuan sistem:

1. Admin dapat menerbitkan, mengelola, dan mengarsipkan sertifikat di satu tempat.
2. Peserta dapat mencari dan mengunduh sertifikat miliknya (PDF) **tanpa membuat akun**.
3. Pihak luar dapat memverifikasi keaslian sertifikat lewat **QR code atau kode verifikasi**.
4. Data peserta dan sertifikat konsisten lewat database terpusat.

## Pengguna

| Pengguna       | Akun        | Yang dapat dilakukan                                                                                                     |
| -------------- | ----------- | ------------------------------------------------------------------------------------------------------------------------ |
| **Admin**      | Ada (login) | Kelola peserta, jenis, dan template; terbitkan sertifikat; cari dan unduh semua sertifikat; batalkan dan terbitkan ulang |
| **Peserta**    | Tidak ada   | Cari sertifikat sendiri dengan nomor sertifikat + email terdaftar, lalu unduh PDF                                        |
| **Pihak luar** | Tidak ada   | Verifikasi keaslian sertifikat lewat QR atau kode verifikasi                                                             |

Hanya admin yang login. Halaman peserta dan verifikasi bersifat publik.

## Fitur (kebutuhan fungsional)

| Kode | Fitur                                                                                             | Pengguna            |
| ---- | ------------------------------------------------------------------------------------------------- | ------------------- |
| F-01 | Login dan logout admin                                                                            | Admin               |
| F-02 | Kelola data peserta (tambah, ubah, hapus, lihat), email wajib; impor dari Excel atau input manual | Admin               |
| F-03 | Kelola jenis sertifikat                                                                           | Admin               |
| F-04 | Kelola template sertifikat per jenis                                                              | Admin               |
| F-05 | Terbitkan sertifikat (pilih peserta, jenis, periode; isi nilai, predikat, durasi)                 | Admin               |
| F-06 | Nomor sertifikat otomatis dan unik (urutan per jenis dan per tahun)                               | Sistem              |
| F-07 | Pembuatan PDF otomatis dengan QR code verifikasi                                                  | Sistem              |
| F-08 | Cari, lihat, dan unduh seluruh sertifikat                                                         | Admin               |
| F-09 | Cari sertifikat dengan nomor sertifikat dan email terdaftar                                       | Peserta             |
| F-10 | Unduh sertifikat sendiri (PDF) setelah data valid                                                 | Peserta             |
| F-11 | Verifikasi keaslian: status valid, dibatalkan, atau tidak ditemukan                               | Peserta, pihak luar |
| F-12 | Batalkan sertifikat yang salah dan terbitkan ulang dengan nomor baru                              | Admin               |

## Alur singkat

**Admin:** login → pastikan jenis dan template ada → tambah peserta (manual atau impor Excel) → pilih peserta, jenis, periode, isi data pendukung → **Terbitkan** → sistem membuat nomor unik, token publik, QR, dan PDF → admin menyampaikan nomor sertifikat ke peserta secara manual. Jika ada kesalahan data: batalkan, lalu terbitkan ulang dengan nomor baru.

**Peserta:** buka halaman pencarian → masukkan nomor sertifikat dan email → jika cocok dan sertifikat aktif, lihat ringkasan → unduh PDF. Jika tidak cocok, tampil pesan seragam "Data tidak cocok", dan percobaan dibatasi.

**Verifikasi:** pindai QR atau buka link/kode verifikasi → sistem memeriksa token dan status → tampil valid (dengan data singkat), dibatalkan, atau tidak ditemukan.

## Teknologi

- **Backend:** Laravel (PHP 8.2+; Laravel terbaru memerlukan PHP 8.3+)
- **Database:** MySQL / MariaDB
- **Frontend:** Blade + Tailwind CSS (Vite)
- **PDF:** barryvdh/laravel-dompdf
- **QR code:** endroid/qr-code
- **Impor Excel:** maatwebsite/excel

Semua berbasis gratis atau open source.

## Struktur database

Nama tabel dan kolom mengikuti ERD (bukan konvensi plural Laravel).

| Tabel                 | Fungsi                                                                                                        |
| --------------------- | ------------------------------------------------------------------------------------------------------------- |
| `admin`               | Akun admin (username, `password_hash`)                                                                        |
| `peserta`             | Nama, email, asal instansi                                                                                    |
| `jenis_sertifikat`    | Magang, PKL, LPK, Ujikom DUDI, Kursus                                                                         |
| `template_sertifikat` | Template tiap jenis (berkas desain, konfigurasi posisi)                                                       |
| `sertifikat`          | Nomor, `token_publik`, `kode_verifikasi`, periode, nilai, predikat, status (`aktif`/`dibatalkan`), lokasi PDF |
| `nomor_urut`          | Pencatat urutan nomor per jenis per tahun (unik `id_jenis` + `tahun`)                                         |

Relasi utama: jenis → template, jenis → sertifikat, template → sertifikat, peserta → sertifikat, admin → sertifikat.

## Keamanan dan privasi

- Kata sandi admin disimpan ter-hash; hanya admin yang dapat mengubah data.
- Peserta hanya bisa mengakses sertifikat miliknya (nomor sertifikat + email), dengan **pembatasan percobaan (rate limiting)** dan pesan gagal yang seragam.
- **Email peserta** hanya tampil di halaman admin. Tidak dicetak di sertifikat dan tidak muncul di halaman publik, nama berkas, metadata PDF, atau URL.
- Link verifikasi dan QR memakai **token acak**, bukan nomor urut.
- PDF disimpan di penyimpanan privat (`storage/app/private`) dan hanya diberikan lewat sistem.
- Nomor sertifikat tidak dapat diubah setelah terbit; kesalahan ditangani dengan pembatalan dan penerbitan ulang.
- Situs diakses lewat HTTPS saat produksi.

## Cakupan

**Termasuk:** lima jenis sertifikat, tiga jenis pengguna di atas, web responsif (komputer dan ponsel), keluaran berupa berkas PDF.

**Di luar cakupan:** pendaftaran peserta, pembayaran, absensi, LMS, aplikasi mobile khusus, integrasi sistem lain, pengiriman otomatis lewat email/WhatsApp (termasuk OTP), dan pencetakan fisik. Nomor sertifikat disampaikan admin ke peserta secara manual.

## Menunggu konfirmasi perusahaan

- Data pendamping pencarian peserta (email atau tanggal lahir). Saat ini dipakai **email**.
- Format penomoran sertifikat.
- Bentuk tanda tangan (gambar atau tanda tangan elektronik tersertifikasi).
- Cara dan pihak yang menyampaikan nomor ke peserta.
- Data yang tampil di halaman verifikasi (nama lengkap atau disamarkan).
- Migrasi data sertifikat lama.
- Ketersediaan hosting dan domain.

## Instalasi (pengembangan lokal)

Prasyarat: PHP dengan ekstensi `pdo_mysql`, `gd`, `mbstring`, `zip`, `intl`, `fileinfo`; Composer; Node.js (LTS); MySQL/MariaDB.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Buat database `sertifikat_sukarobot` (utf8mb4), lalu atur `.env`:

```
DB_CONNECTION=mysql
DB_DATABASE=sertifikat_sukarobot
DB_USERNAME=root
DB_PASSWORD=
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
APP_TIMEZONE=Asia/Jakarta
ADMIN_USERNAME=admin
ADMIN_PASSWORD=          # wajib diisi
```

Jalankan migrasi dan seeder, lalu aplikasi:

```bash
php artisan migrate --seed
php artisan serve        # terminal 1
npm run dev              # terminal 2
```

Buka `http://127.0.0.1:8000`.

## Struktur folder penting

```
app/Models/              Admin, Peserta, JenisSertifikat, TemplateSertifikat, NomorUrut, Sertifikat
database/migrations/     6 migrasi sesuai ERD
database/seeders/        Jenis sertifikat dan akun admin awal
storage/app/private/     PDF sertifikat dan berkas template (privat)
docs/                    Dokumen requirement, use case, flowchart, ERD
```

## Status pengembangan

- [x] Perencanaan, analisis kebutuhan, dan perancangan (use case, flowchart, ERD)
- [x] Struktur database, model, dan seeder
- [x] F-01 Login dan logout admin
- [ ] F-03, F-04 Jenis dan template sertifikat
- [ ] F-02 Data peserta (CRUD, impor Excel)
- [ ] F-06, F-05, F-07 Penomoran, penerbitan, PDF + QR
- [ ] F-08, F-12 Daftar sertifikat admin, pembatalan dan terbit ulang
- [ ] F-11 Verifikasi publik
- [ ] F-09, F-10 Pencarian dan unduh peserta (dengan rate limiting)
- [ ] Backup database berkala dan pengujian

### Login admin

Halaman login tersedia di `/login`. Akun admin awal dibuat melalui seeder dengan nilai `ADMIN_USERNAME` dan `ADMIN_PASSWORD` dari `.env`. Setelah berhasil masuk, admin diarahkan ke dashboard sementara; fitur pemulihan password belum tersedia dan admin perlu menghubungi administrator sistem.

## Dokumentasi perancangan

Dokumen requirement, use case, flowchart (admin, peserta, verifikasi), dan ERD disimpan di folder `docs/`.
