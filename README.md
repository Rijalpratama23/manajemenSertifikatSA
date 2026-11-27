# Sistem Informasi Sertifikat Sukarobot Academy

Modul manajemen sertifikat untuk Sukarobot Academy. Modul ini menjadi bagian dari **sistem perusahaan yang sudah berjalan**: admin membukanya dari menu navigasi sistem utama, lalu langsung masuk ke dashboard manajemen sertifikat. Modul menerbitkan, menyimpan, mencari, dan memverifikasi sertifikat secara terpusat, dengan data peserta yang diambil dari sistem utama lewat API.

> Status: **tahap persiapan implementasi, dengan revisi dari atasan**. Struktur database, model, seeder, dan sebagian besar fitur admin sudah tersedia. Revisi terbaru (integrasi dengan sistem utama dan penghapusan pengguna peserta) belum diterapkan di kode dan dicatat sebagai rencana. Lihat [Perubahan terbaru](#perubahan-terbaru) dan [Status pengembangan](#status-pengembangan).

## Perubahan terbaru

Hasil diskusi dengan atasan:

1. **Terintegrasi dengan sistem perusahaan.** Modul ini menjadi halaman atau tautan di navigasi sistem utama. Satu klik membawa admin ke dashboard manajemen sertifikat.
2. **Jenis sertifikat lebih dari satu dan dapat bertambah.** Jenis awal: kursus, magang, PKL, ujikom DUDI, dan jenis lain sesuai kebutuhan perusahaan.
3. **Data peserta (siswa) berasal dari sistem utama lewat API.** Sistem utama dapat mengirim data siswa ke modul ini, dan sebaliknya modul ini dapat mengirim data ke sistem utama.
4. **Pengguna hanya admin.** Tidak ada pengguna peserta, sehingga halaman cari dan unduh sertifikat untuk peserta (F-09 dan F-10) dihapus dari cakupan.

Dampaknya pada versi sebelumnya:

| Bagian       | Sebelumnya                            | Sekarang                                                                                    |
| ------------ | ------------------------------------- | ------------------------------------------------------------------------------------------- |
| Pengguna     | Admin, peserta, pihak luar            | Admin saja. Halaman verifikasi publik tetap sebagai tujuan QR code (lihat catatan di bawah) |
| Data peserta | Input manual atau impor Excel         | Peserta kursus dari API sistem utama. Jenis lain (magang, PKL, ujikom) menunggu konfirmasi  |
| Akses        | Aplikasi mandiri dengan login sendiri | Dibuka dari menu sistem utama. Mekanisme login menunggu konfirmasi                          |
| F-09, F-10   | Cari dan unduh oleh peserta           | Dihapus                                                                                     |
| Fitur baru   | -                                     | F-13, F-14, F-15 (integrasi)                                                                |

## Latar belakang dan tujuan

Sukarobot Academy menerbitkan beberapa jenis sertifikat, yaitu **kursus, magang, Praktik Kerja Lapangan (PKL), uji kompetensi (ujikom) DUDI, dan jenis lainnya**. Pengelolaan manual menimbulkan masalah berikut:

- Sertifikat mudah hilang dan sulit dicari kembali.
- Data peserta dan data pada sertifikat tidak sinkron.
- Belum ada cara langsung untuk memeriksa keaslian sertifikat.

Tujuan sistem:

1. Admin dapat menerbitkan, mengelola, dan mengarsipkan sertifikat di satu tempat.
2. Data peserta konsisten dengan sistem utama karena diambil lewat API, bukan diketik ulang.
3. Pihak luar dapat memeriksa keaslian sertifikat lewat **QR code atau kode verifikasi**.
4. Data peserta dan sertifikat konsisten lewat database terpusat.

## Pengguna

| Pengguna  | Akun                          | Yang dapat dilakukan                                                                                                                                 |
| --------- | ----------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Admin** | Ada (akses dari sistem utama) | Kelola peserta, jenis, dan template; terbitkan sertifikat; cari dan unduh semua sertifikat; batalkan dan terbitkan ulang; memantau sinkronisasi data |

Admin adalah satu-satunya pengguna sistem. Halaman verifikasi keaslian bersifat publik dan baca-saja karena menjadi tujuan QR code pada sertifikat. Halaman itu bukan pengguna dengan akun, dan tidak ada fitur untuk peserta.

## Jenis sertifikat

Jenis sertifikat dikelola admin lewat F-03, jadi daftarnya dapat bertambah tanpa mengubah kode. Jenis awal:

- Kursus
- Magang
- PKL
- Ujikom DUDI
- Jenis lain sesuai kebutuhan perusahaan (misalnya pelatihan)

Setiap jenis memiliki kode singkat (dipakai pada nomor sertifikat) dan template sendiri.

## Fitur (kebutuhan fungsional)

| Kode | Fitur                                                                                                                                                                   | Pengguna            | Status                                  |
| ---- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------- | --------------------------------------- |
| F-01 | Login dan logout admin                                                                                                                                                  | Admin               | Tersedia, perlu disesuaikan dengan F-15 |
| F-02 | Kelola data peserta (lihat, tambah, ubah, hapus). Data peserta kursus diterima dari sistem utama; jenis lain dengan input manual atau impor Excel (menunggu konfirmasi) | Admin               | Tersedia, perlu disesuaikan dengan F-13 |
| F-03 | Kelola jenis sertifikat                                                                                                                                                 | Admin               | Tersedia                                |
| F-04 | Kelola template sertifikat per jenis                                                                                                                                    | Admin               | Tersedia                                |
| F-05 | Terbitkan sertifikat (pilih peserta, jenis, periode; isi nilai, predikat, durasi)                                                                                       | Admin               | Tersedia                                |
| F-06 | Nomor sertifikat otomatis dan unik (urutan per jenis dan per tahun)                                                                                                     | Sistem              | Tersedia                                |
| F-07 | Pembuatan PDF otomatis dengan QR code verifikasi                                                                                                                        | Sistem              | Tersedia                                |
| F-08 | Cari, lihat, dan unduh seluruh sertifikat                                                                                                                               | Admin               | Tersedia                                |
| F-09 | ~~Cari sertifikat oleh peserta~~                                                                                                                                        | -                   | Dihapus                                 |
| F-10 | ~~Unduh sertifikat oleh peserta~~                                                                                                                                       | -                   | Dihapus                                 |
| F-11 | Verifikasi keaslian: status valid, dibatalkan, atau tidak ditemukan                                                                                                     | Publik (tanpa akun) | Tersedia                                |
| F-12 | Batalkan sertifikat yang salah dan terbitkan ulang dengan nomor baru                                                                                                    | Admin               | Tersedia                                |
| F-13 | Menerima data peserta dari sistem utama lewat API                                                                                                                       | Sistem              | Rencana                                 |
| F-14 | Mengirim data sertifikat ke sistem utama lewat API                                                                                                                      | Sistem              | Rencana                                 |
| F-15 | Akses dari menu navigasi sistem utama langsung ke dashboard sertifikat                                                                                                  | Admin               | Rencana                                 |

## Integrasi dengan sistem utama (rencana)

Bagian ini masih rancangan awal. Spesifikasi API sistem utama (alamat, format data, autentikasi) belum diterima, jadi rincian di bawah harus disesuaikan.

```
  Sistem utama Sukarobot                    Modul sertifikat
 +-----------------------+   data siswa   +------------------------+
 | Data siswa/peserta    | -------------> | Tabel peserta          |
 | Menu navigasi         |                | Penerbitan & arsip     |
 |  -> "Sertifikat"      | <------------- | Data sertifikat        |
 +-----------------------+ data sertifikat+------------------------+
            |                                        ^
            +----- klik menu: masuk dashboard -------+
```

**Data masuk (F-13).** Sistem utama mengirim atau menyediakan data siswa kursus: nama, email, asal instansi, dan ID siswa di sistem utama. Modul menyimpannya di tabel `peserta` dengan penanda `id_eksternal`. Penyimpanan bersifat _upsert_ (data yang sama tidak diduplikasi) dan aman diulang.

**Data keluar (F-14).** Saat sertifikat diterbitkan atau dibatalkan, modul mengirim ke sistem utama: ID siswa, nomor sertifikat, jenis, status, tanggal terbit, dan link verifikasi. Dengan begitu sistem utama dapat menampilkan sertifikat milik siswa tanpa perlu modul ini menyediakan halaman untuk peserta.

**Akses (F-15).** Menu di sistem utama membuka modul ini. Bentuk autentikasinya, apakah satu login bersama (SSO atau token bertanda tangan dari sistem utama) atau login terpisah seperti sekarang, menunggu konfirmasi.

Prinsip rancangan yang diusulkan:

- Sistem utama menjadi sumber data peserta kursus. Kolom yang berasal dari API tidak diubah manual di modul ini, supaya tidak bertabrakan dengan data sumber.
- Pengiriman keluar berjalan lewat antrean dengan percobaan ulang bila gagal, dan setiap pengiriman tercatat di log sinkronisasi.
- Kegagalan sinkronisasi tidak menghalangi penerbitan sertifikat. Statusnya ditandai dan dapat dikirim ulang.
- Komunikasi antar sistem memakai HTTPS dan token khusus (bukan kata sandi admin).

## Alur singkat

**Admin:** buka menu Sertifikat dari sistem utama → dashboard → pastikan jenis dan template tersedia → pastikan data peserta sudah masuk dari sistem utama (atau tambah manual untuk jenis tertentu) → pilih peserta, jenis, periode, isi data pendukung → **Terbitkan** → sistem membuat nomor unik, token publik, QR, dan PDF → data sertifikat dikirim ke sistem utama. Jika ada kesalahan data: batalkan, lalu terbitkan ulang dengan nomor baru.

**Verifikasi:** pindai QR atau buka link atau kode verifikasi → sistem memeriksa token dan status → tampil valid (dengan data singkat), dibatalkan, atau tidak ditemukan.

## Teknologi

- **Backend:** Laravel (PHP 8.2+; Laravel terbaru memerlukan PHP 8.3+)
- **Database:** MySQL / MariaDB
- **Frontend:** Blade + Tailwind CSS (Vite)
- **PDF:** barryvdh/laravel-dompdf
- **QR code:** endroid/qr-code
- **Impor Excel:** maatwebsite/excel
- **Integrasi API (rencana):** HTTP Client bawaan Laravel, antrean (queue) untuk pengiriman keluar, dan autentikasi token untuk API masuk (misalnya Laravel Sanctum)

Semua berbasis gratis atau open source.

## Struktur database

Nama tabel dan kolom mengikuti ERD (bukan konvensi plural Laravel).

| Tabel                 | Fungsi                                                                                                        |
| --------------------- | ------------------------------------------------------------------------------------------------------------- |
| `admin`               | Akun admin (username, `password_hash`)                                                                        |
| `peserta`             | Nama, email, asal instansi                                                                                    |
| `jenis_sertifikat`    | Kursus, magang, PKL, ujikom DUDI, dan jenis lain                                                              |
| `peserta_jenis_sertifikat` | Hubungan peserta dengan jenis sertifikat sebelum sertifikat diterbitkan                                       |
| `template_sertifikat` | Template tiap jenis (berkas desain, konfigurasi posisi)                                                       |
| `sertifikat`          | Nomor, `token_publik`, `kode_verifikasi`, periode, nilai, predikat, status (`aktif`/`dibatalkan`), lokasi PDF |
| `nomor_urut`          | Pencatat urutan nomor per jenis per tahun (unik `id_jenis` + `tahun`)                                         |

Relasi utama: peserta ↔ jenis sertifikat melalui `peserta_jenis_sertifikat`, peserta → sertifikat, jenis → template, jenis → sertifikat, template → sertifikat, admin → sertifikat.

**Perubahan database yang direncanakan** untuk integrasi (belum ada di migrasi):

| Tabel                     | Perubahan                                                                                                                                            |
| ------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------- |
| `peserta`                 | Tambah `id_eksternal` (ID siswa di sistem utama, unik, boleh kosong untuk data manual), `sumber` (`api`, `manual`, `impor`), dan `disinkronkan_pada` |
| `sertifikat`              | Tambah `status_sinkron` (`belum`, `terkirim`, `gagal`) dan `disinkronkan_pada`                                                                       |
| `log_sinkronisasi` (baru) | Arah (masuk atau keluar), data yang diproses, status, pesan galat, dan waktu                                                                         |
| `admin`                   | Disesuaikan dengan keputusan autentikasi (F-15), misalnya `password_hash` boleh kosong bila memakai login bersama                                    |

## Keamanan dan privasi

- Kata sandi admin disimpan ter-hash; hanya admin yang dapat mengubah data.
- **Email peserta** hanya tampil di halaman admin. Tidak dicetak di sertifikat dan tidak muncul di halaman publik, nama berkas, metadata PDF, atau URL.
- Link verifikasi dan QR memakai **token acak**, bukan nomor urut. Halaman verifikasi dibatasi jumlah percobaannya (rate limiting) dan memakai pesan gagal yang seragam.
- PDF disimpan di penyimpanan privat (`storage/app/private`) dan hanya diberikan lewat sistem kepada admin.
- Nomor sertifikat tidak dapat diubah setelah terbit; kesalahan ditangani dengan pembatalan dan penerbitan ulang.
- **API (rencana):** setiap permintaan antar sistem diautentikasi dengan token, dibatasi jumlahnya, divalidasi isinya, dan dicatat. Token disimpan di `.env`, bukan di kode.
- Situs diakses lewat HTTPS saat produksi.

## Cakupan

**Termasuk:** jenis sertifikat yang dapat dikelola admin, satu jenis pengguna (admin), integrasi data peserta dan sertifikat dengan sistem utama lewat API, halaman verifikasi publik, web responsif (komputer dan ponsel), keluaran berupa berkas PDF.

**Di luar cakupan:** halaman atau akun untuk peserta, pendaftaran peserta, pembayaran, absensi, LMS, aplikasi mobile khusus, pengiriman otomatis lewat email atau WhatsApp, dan pencetakan fisik. Integrasi hanya dengan sistem utama Sukarobot, bukan sistem lain.

## Menunggu konfirmasi perusahaan

**Integrasi**

- Spesifikasi API sistem utama: alamat, format data, autentikasi, dan kolom yang tersedia.
- Arah dan pemicu sinkronisasi: sistem utama mengirim (_push_) atau modul mengambil (_pull_), serta seberapa sering.
- Cara masuk admin: satu login bersama (SSO atau token) atau login terpisah.
- Bentuk akses: satu domain dengan jalur tersendiri, atau subdomain.
- Data apa yang dikirim balik ke sistem utama, dan bagaimana sistem utama menampilkannya kepada siswa.
- Aturan bila data di dua sistem berbeda (sistem mana yang dianggap benar).

**Data dan fitur**

- Asal data peserta untuk magang, PKL, dan ujikom: dari API juga, atau input manual dan impor Excel.
- Apakah email peserta masih wajib, karena tidak lagi dipakai sebagai kunci pencarian.
- Apakah halaman verifikasi publik tetap dipertahankan sebagai tujuan QR code.
- Data yang tampil di halaman verifikasi (nama lengkap atau disamarkan).
- Bentuk tanda tangan (gambar atau tanda tangan elektronik tersertifikasi).
- Format penomoran dan daftar kode jenis yang pasti.
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

Secara default, PDF mengikuti `QUEUE_CONNECTION`. Pada konfigurasi lokal `sync`, PDF dibuat langsung saat diterbitkan sehingga tidak memerlukan worker. Untuk memproses PDF di latar belakang, atur `QUEUE_CONNECTION=database`, jalankan migrasi antrean, lalu jalankan worker:

```bash
php artisan queue:work certificate_pdfs --queue=certificate-pdfs --tries=3 --timeout=240
```

Halaman hasil penerbitan akan diperbarui otomatis selama PDF sedang diproses. Jika job gagal setelah tiga percobaan, periksa dengan `php artisan queue:failed`, lalu ulangi job tertentu dengan `php artisan queue:retry <uuid>`.

### Konfigurasi integrasi (rencana)

Variabel berikut belum dipakai oleh kode dan nama finalnya mengikuti spesifikasi API sistem utama:

```
SISTEM_UTAMA_BASE_URL=       # alamat API sistem utama
SISTEM_UTAMA_API_TOKEN=      # token untuk memanggil sistem utama (keluar)
INTEGRASI_API_TOKEN=         # token yang dipakai sistem utama memanggil modul ini (masuk)
```

## Struktur folder penting

```
app/Models/              Admin, Peserta, JenisSertifikat, TemplateSertifikat, NomorUrut, Sertifikat
database/migrations/     6 migrasi sesuai ERD (migrasi integrasi menyusul)
database/seeders/        Jenis sertifikat dan akun admin awal
storage/app/private/     PDF sertifikat dan berkas template (privat)
docs/                    Dokumen requirement, use case, flowchart, ERD
```

## Status pengembangan

- [x] Perencanaan, analisis kebutuhan, dan perancangan (use case, flowchart, ERD)
- [x] Struktur database, model, dan seeder
- [x] F-01 Login dan logout admin
- [x] F-03, F-04 Jenis dan template sertifikat
- [x] F-02 Data peserta (CRUD, impor Excel)
- [x] F-06, F-05, F-07 Penomoran, penerbitan, PDF + QR
- [x] F-08, F-12 Arsip sertifikat admin, pencarian/filter, pembatalan dan alur terbit ulang
- [x] F-11 Verifikasi publik
- [ ] ~~F-09, F-10 Pencarian dan unduh peserta~~ (dihapus dari cakupan: tidak ada pengguna peserta)
- [ ] Menerima spesifikasi API dari sistem utama
- [ ] F-13 Menerima data peserta dari sistem utama
- [ ] F-14 Mengirim data sertifikat ke sistem utama
- [ ] F-15 Akses dari menu sistem utama dan penyesuaian login
- [ ] Penyesuaian seeder jenis sertifikat dan data peserta (kolom `id_eksternal`, `sumber`)
- [ ] Penyesuaian dokumen perancangan (use case, flowchart, ERD)
- [ ] Backup database berkala dan pengujian

### Login admin

Halaman login tersedia di `/login`. Akun admin awal dibuat melalui seeder dengan nilai `ADMIN_USERNAME` dan `ADMIN_PASSWORD` dari `.env`. Setelah berhasil masuk, admin diarahkan ke dashboard sementara; fitur pemulihan password belum tersedia dan admin perlu menghubungi administrator sistem. Mekanisme ini bersifat sementara sampai F-15 diputuskan.

### Penerbitan sertifikat

Nomor sertifikat dibuat dalam format `KODE/JJJJ/NNNN` (contoh: `MAGANG/2026/0001`), dengan urutan terpisah per jenis dan tahun terbit. Berkas PDF dan latar template disimpan di penyimpanan privat. QR code mengarah ke halaman verifikasi publik yang hanya menampilkan status dan informasi sertifikat non-pribadi.

## Dokumentasi perancangan

Dokumen requirement, use case, flowchart, dan ERD disimpan di folder `docs/`. Setelah revisi ini, bagian berikut perlu diperbarui:

- **Requirement dan use case:** aktor peserta dan F-09, F-10 dihapus, lalu ditambahkan F-13 sampai F-15 dan sistem utama sebagai aktor eksternal.
- **Flowchart:** alur peserta dihapus. Alur admin ditambah langkah data peserta dari API dan pengiriman data ke sistem utama.
- **ERD:** tambahan kolom pada `peserta` dan `sertifikat`, serta tabel `log_sinkronisasi`.
