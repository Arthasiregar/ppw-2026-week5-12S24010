# SIPUS-Del Tahap 3: Web Services (SOAP Legacy & RESTful API v1)

Tugas Mandiri Minggu 9, **12S3101 Pemrograman & Pengujian Aplikasi Web**, Institut Teknologi Del (Ganjil 2026/2027).

## 1. Identitas Mahasiswa

| | |
|---|---|
| **Nama Lengkap** | Artha Liebe Siregar |
| **NIM** | 12S24010 |
| **Kelas** | 13SI |
| **Program Studi** | S1-Sistem Informasi |
| **GitHub** | [@Arthasiregar](https://github.com/Arthasiregar) |
| **Repositori** | `ppw-2026-week5-12S24010` (branch pengerjaan: `week9-rest-api`) |
| **Framework** | Laravel 11.x (PHP 8.4, SQLite), Laravel Sanctum |

SIPUS-Del adalah sistem informasi perpustakaan kampus yang pada Minggu 5 dan 6 dibangun sebagai aplikasi web monolitik (Blade, MVC, session). Pada Minggu 9, sistem diperluas menjadi **penyedia layanan (Service Provider)** melalui endpoint SOAP dan RESTful API v1, sekaligus menjadi **pengguna layanan (Service Consumer)** dengan memanggil OpenLibrary REST API. Antarmuka web Blade dari minggu sebelumnya tetap dipertahankan dan tidak diubah.

## Daftar Isi

1. [Identitas Mahasiswa](#1-identitas-mahasiswa)
2. [Analisis Kebutuhan dan Kelayakan Web Services](#2-analisis-kebutuhan-dan-kelayakan-web-services)
3. [Katalog Endpoint Web Services](#3-katalog-endpoint-web-services)
4. [Panduan Instalasi dan Pengujian Cepat](#4-panduan-instalasi-dan-pengujian-cepat)
5. [Galeri Bukti Pengujian](#5-galeri-bukti-pengujian)
6. [Arsitektur dan Keputusan Desain](#6-arsitektur-dan-keputusan-desain)
7. [Riwayat Git](#7-riwayat-git)
8. [Kesesuaian dengan Spesifikasi Tugas](#8-kesesuaian-dengan-spesifikasi-tugas)

---

## 2. Analisis Kebutuhan dan Kelayakan Web Services

### 2.1 Pemetaan Peran Provider dan Consumer

UPT Perpustakaan IT Del memiliki tiga kebutuhan integrasi lintas sistem. Setiap kebutuhan dipetakan ke satu peran arsitektur:

| No | Kebutuhan Integrasi | Peran SIPUS-Del | Teknologi | Alasan |
|---|---|---|---|---|
| 1 | Sistem Administrasi Yudisium (legacy) perlu mengecek tanggungan buku mahasiswa | **Service Provider** | SOAP (XML, WSDL) | Sistem yudisium lama hanya memahami SOAP. Data peminjaman adalah domain sah SIPUS-Del, sehingga diekspos secara terkontrol. |
| 2 | Aplikasi Mobile dan Kiosk Mandiri perlu katalog serta transaksi peminjaman | **Service Provider** | REST API v1 (JSON, Bearer Token) | Klien modern membutuhkan layanan ringan, stateless, dan mudah diskalakan. |
| 3 | Detail buku diperkaya metadata eksternal (jumlah halaman, penerbit, tautan pratinjau, sampul) | **Service Consumer** | Laravel `Http` facade ke OpenLibrary REST API | Katalog bibliografi dunia berada di luar domain inti perpustakaan kampus. Membangunnya sendiri tidak efisien. |

### 2.2 Perbandingan SOAP dan REST untuk Studi Kasus Perpustakaan

| Aspek | SOAP (Bebas Pustaka Yudisium) | REST (Mobile dan Kiosk) |
|---|---|---|
| Format pesan | Wajib XML (`<soap:Envelope>`) | JSON |
| Kontrak layanan | File WSDL (`/soap/sipus.wsdl`) | OpenAPI 3.0 (`docs/openapi-sipus-v1.yaml`) |
| Ukuran payload | Besar (banyak tag pembungkus) | Ringkas |
| Penanganan galat | `<soap:Fault>` | Kode status HTTP + RFC 7807 (`application/problem+json`) |
| Pemanfaatan HTTP | Hanya `POST` sebagai pipa | Verb dan status code semantik (GET, POST, PUT, PATCH, DELETE) |
| Manajemen state | Tidak bergantung session | Stateless penuh (Bearer Token Sanctum) |
| Cocok untuk | Integrasi sistem legacy atau enterprise | Aplikasi mobile, kiosk, SPA, mitra baru |

### 2.3 Alasan Teknis Memilih REST untuk Mobile dan Kiosk

1. **Efisiensi bandwidth.** JSON jauh lebih ringkas daripada amplop XML SOAP. Hal ini penting bagi perangkat mobile dengan koneksi terbatas.
2. **Stateless.** Setiap request membawa identitas sendiri lewat `Authorization: Bearer <token>`. Server tidak menyimpan session, sehingga layanan mudah ditempatkan di belakang beberapa server (load balancer).
3. **Semantik HTTP.** Status `201 Created` (dengan header `Location`), `204 No Content`, `404`, `409 Conflict`, dan `422` membuat klien memahami hasil operasi tanpa membongkar isi respons.
4. **Hypermedia (HATEOAS).** Tautan `_links` bersifat dinamis: `pinjam_buku` hanya muncul bila stok lebih dari 0, dan `kembalikan_buku` hanya muncul bila status peminjaman `dipinjam`. Klien tidak perlu menduplikasi aturan bisnis.
5. **Ekosistem.** Seluruh bahasa klien (Kotlin, Dart, JavaScript) mendukung JSON secara native.

SOAP tetap dipertahankan hanya untuk satu kebutuhan, yaitu integrasi dengan sistem yudisium lama yang tidak dapat diubah.

---

## 3. Katalog Endpoint Web Services

Seluruh endpoint REST berada di bawah prefiks `/api/v1`. Postman memakai variabel `{{base_url}}` bernilai `http://localhost:8000/api/v1`.

### 3.1 SOAP (Sistem Legacy Yudisium)

| Method | URI | Auth | Deskripsi | Status Respons |
|---|---|---|---|---|
| GET | `/soap/sipus.wsdl` | Publik | Kontrak WSDL operasi `CheckClearanceStatus` | 200 |
| POST | `/soap/sipus` | Publik | Menerima `<soap:Envelope>` berisi `<nim_peminjam>`, mengembalikan `<active_loans>` dan `<is_cleared>` | 200 (sukses), 500 (`<soap:Fault>`: tag kosong atau XML cacat) |

### 3.2 REST: Autentikasi

| Method | URI | Auth | Deskripsi | Status Respons |
|---|---|---|---|---|
| POST | `/api/v1/auth/login` | Publik | Verifikasi kredensial dan penerbitan Bearer Token | 200, 401, 422 |
| GET | `/api/v1/auth/me` | Bearer | Profil pemilik token aktif | 200, 401 |
| POST | `/api/v1/auth/logout` | Bearer | Mencabut (revoke) token yang sedang dipakai | 200, 401 |

### 3.3 REST: Katalog Buku

| Method | URI | Auth | Deskripsi | Status Respons |
|---|---|---|---|---|
| GET | `/api/v1/bukus` | Publik | Koleksi terpaginasi. Parameter: `search`, `kategori_id`, `tersedia=1`, `sort`, `per_page` | 200 |
| GET | `/api/v1/bukus/{id}` | Publik | Detail buku, objek kategori, dan riwayat peminjaman | 200, 404 |
| POST | `/api/v1/bukus` | Bearer | Membuat buku baru (+ header `Location`) | 201, 401, 422 |
| PUT | `/api/v1/bukus/{id}` | Bearer | Penggantian penuh (seluruh atribut wajib) | 200, 401, 404, 422 |
| PATCH | `/api/v1/bukus/{id}` | Bearer | Pembaruan parsial (`sometimes\|required`) | 200, 400, 401, 404, 422 |
| DELETE | `/api/v1/bukus/{id}` | Bearer | Menghapus buku. Ditolak bila ada peminjaman aktif | 204, 401, 404, 409 |
| GET | `/api/v1/bukus/{id}/referensi-eksternal` | Publik | **Consumer**: metadata tambahan dari OpenLibrary | 200, 404, 502, 504 |

### 3.4 REST: Sirkulasi Peminjaman (ACID)

| Method | URI | Auth | Deskripsi | Status Respons |
|---|---|---|---|---|
| GET | `/api/v1/peminjamans` | Bearer | Daftar transaksi terpaginasi. Filter: `status=dipinjam\|dikembalikan`, `nim` | 200, 401 |
| POST | `/api/v1/peminjamans` | Bearer | Mencatat peminjaman (`DB::transaction()` + `lockForUpdate()`) | 201, 401, 409, 422 |
| PATCH | `/api/v1/peminjamans/{id}/kembalikan` | Bearer | Mengembalikan buku (stok +1) secara atomik | 200, 401, 404, 409 |

### 3.5 Format Galat (RFC 7807)

Seluruh galat 401, 404, dan 422 pada `/api/*` dikembalikan dengan `Content-Type: application/problem+json` dan atribut `type`, `title`, `status`, `detail`, `instance` (ditambah objek `errors` pada 422).

Pembedaan dua galat 404 pada endpoint referensi eksternal:

| Kasus | `title` | Server menghubungi OpenLibrary? |
|---|---|---|
| Buku lokal ada, tetapi ISBN tidak ada di OpenLibrary | `External Reference Not Found` | Ya |
| Buku lokal tidak ada | `Resource Not Found` | Tidak |

---

## 4. Panduan Instalasi dan Pengujian Cepat

### 4.1 Instalasi

```bash
# 1. Clone repositori dan pindah ke branch Minggu 9
git clone https://github.com/Arthasiregar/ppw-2026-week5-12S24010.git
cd ppw-2026-week5-12S24010
git checkout week9-rest-api

# 2. Pasang dependensi
composer install

# 3. Siapkan environment
cp .env.example .env          # Windows PowerShell: copy .env.example .env
php artisan key:generate

# 4. Siapkan database SQLite dan isi data contoh
touch database/database.sqlite          # Windows PowerShell: New-Item database\database.sqlite
php artisan migrate:fresh --seed

# 5. Jalankan server
php artisan serve
```

Jika `composer install` menolak paket karena *security advisory* Laravel 11, tambahkan flag `--no-security-blocking`.

### 4.2 Akun Uji (Seeder Lokal)

| Email | Password |
|---|---|
| `pustakawan@del.ac.id` | `password123` |

Akun ini hanya untuk pengujian lokal oleh dosen dan asisten.

### 4.3 Impor Koleksi Postman

1. Buka Postman, klik **Import**, lalu pilih `docs/SIPUS-Del-APIv1.postman_collection.json`.
2. Pada tab **Variables** milik collection, pastikan:
   - `base_url` = `http://localhost:8000/api/v1`
   - `token` = kosong (isi dengan `data.access_token` hasil login)
   - `buku_id` = kosong (terisi otomatis oleh script post-response pada request pembuatan buku)
3. Jalankan request **Login**, salin `access_token` ke variabel `token`.
4. Tambahkan header `Accept: application/json` pada setiap request agar galat validasi berupa JSON, bukan redirect HTML.

### 4.4 Kontrak OpenAPI

Berkas kontrak: [`docs/openapi-sipus-v1.yaml`](docs/openapi-sipus-v1.yaml). Isinya dapat ditempel ke <https://editor.swagger.io> untuk melihat dokumentasi interaktif.

### 4.5 Keamanan Token

- Berkas `.env` dan `vendor/` tidak di-commit (terdaftar di `.gitignore`). Gunakan `.env.example` sebagai acuan.
- Tidak ada Personal Access Token GitHub (`ghp_...`) yang ditulis di kode maupun README. Token pada screenshot sudah disensor.
- Pada kode konsumsi OpenLibrary, verifikasi SSL hanya dimatikan pada `app()->environment('local')` (lihat bagian 6.4).

---

## 5. Galeri Bukti Pengujian

Tujuh skenario wajib (poin 5.3.5 modul) ditampilkan berurutan, diikuti bukti tambahan.

### (a) Uji SOAP XML dan `<soap:Fault>`

| Request SOAP sukses (`<active_loans>`, `<is_cleared>`) | Respons `<soap:Fault>` |
|---|---|
| ![Request SOAP sukses](docs/ss/req_soap_sukses.png) | ![SOAP Fault](docs/ss/soap_fault.png) |

### (b) Login Bearer Token

| `POST /auth/login` (token diterbitkan) | Login via request alternatif | `GET /auth/me` dengan token |
|---|---|---|
| ![POST login](docs/ss/post_login.png) | ![Login request 1](docs/ss/req1_login.png) | ![GET login](docs/ss/get_login.png) |

Akses tanpa token ditolak dengan `401 Unauthorized` (RFC 7807):

![GET tanpa token](docs/ss/get_tanpatoken.png)

### (c) GET Katalog Terpaginasi dan HATEOAS

Katalog dengan buku bersetok 0 membuktikan tautan `pinjam_buku` **hilang** secara dinamis ketika stok habis:

![Katalog dengan stok 0](docs/ss/katalog_stok0.png)

<!-- Tambahkan screenshot GET /bukus yang menampilkan blok "meta" paginasi, lalu hilangkan komentar pada baris berikut:
![Katalog terpaginasi](docs/screenshots/NAMA_FILE_KATALOG.png)
-->

### (d) POST Buku: 201 Created dan Header `Location`

![POST buku baru](docs/ss/post_bukubaru.png)

### (e) Perbedaan PUT dan PATCH

| `PUT` (penggantian penuh, atribut yang tidak dikirim direset) | `PATCH` (hanya atribut yang dikirim berubah) |
|---|---|
| ![PUT](docs/ss/put.png) | ![PATCH](docs/ss/patch.png) |

### (f) Peminjaman Sukses dan Penolakan (422 / 409)

| Peminjaman sukses (`201 Created`) | Penolakan 422 (validasi atau stok habis) | Penolakan 409 (konflik) |
|---|---|---|
| ![Peminjaman sukses](docs/ss/pinjambuku_sukses.png) | ![422](docs/ss/422.png) | ![409](docs/ss/409.png) |

Pengembalian buku dan penolakan penghapusan buku yang sedang dipinjam:

| Pengembalian buku (`200 OK`, stok +1) | Hapus buku yang sedang dipinjam (`409 Conflict`) |
|---|---|
| ![Kembalikan buku](docs/ss/kembalikan_buku.png) | ![Hapus buku dipinjam](docs/ss/hapusbukuyangdipinjam.png) |

### (g) Latihan GitHub REST API (token disensor)

<!-- Tambahkan screenshot latihan GitHub REST API (GET /users/{username} atau PATCH /user, token disensor), lalu hilangkan komentar pada baris berikut:
![GitHub REST API](docs/screenshots/NAMA_FILE_GITHUB.png)
-->

### Bukti Tambahan: REST Service Consumer (OpenLibrary)

Fitur `GET /api/v1/bukus/{id}/referensi-eksternal` diuji dalam empat skenario:

| Skenario | Hasil yang Diharapkan | Bukti |
|---|---|---|
| **R1**: buku lokal ada, ISBN tidak ada di OpenLibrary | `404`, `External Reference Not Found` | ![R1](docs/ss/R1.png) |
| **R2**: pembuatan buku uji dengan ISBN nyata | `201 Created` + `Location` | ![R2](docs/ss/R2.png) |
| **R2b**: referensi eksternal sukses | `200 OK`, blok `referensi_eksternal` | ![R2b](docs/ss/R2%20b.png) |
| **R2c**: hapus buku uji | `204 No Content`, body kosong | ![R2c](docs/ss/R2%20c.png) |
| **R3**: buku lokal tidak ada | `404`, `Resource Not Found` (OpenLibrary tidak dihubungi) | ![R3](docs/ss/R3.png) |

### Bukti Tambahan: Galat Global RFC 7807

| `401` tanpa token | `404` resource tidak ada | `422` validasi gagal |
|---|---|---|
| ![401](docs/ss/req2_401.png) | ![404](docs/ss/req2_404.png) | ![422](docs/ss/req2_422.png) |

---

## 6. Arsitektur dan Keputusan Desain

### 6.1 Struktur Berkas Utama

| Lapisan | Lokasi | Tanggung jawab |
|---|---|---|
| Rute REST | `routes/api.php` | Prefiks `/api/v1`, pemisahan rute publik dan `auth:sanctum` |
| Rute SOAP | `routes/web.php` | `/soap/sipus.wsdl` dan `/soap/sipus` (dikecualikan dari CSRF) |
| Controller SOAP | `app/Http/Controllers/Soap/LibrarySoapController.php` | WSDL, pemrosesan envelope, `<soap:Fault>` |
| Controller REST | `app/Http/Controllers/Api/V1/` | `BukuApiController`, `PeminjamanApiController`, `BukuReferensiApiController`, `AuthTokenController` |
| Resource | `app/Http/Resources/Api/V1/` | `BukuResource`, `PeminjamanResource` (pemisah skema database dari JSON publik, HATEOAS) |
| Konfigurasi galat | `bootstrap/app.php` | Penyeragaman galat 401, 404, 422 ke RFC 7807 |

### 6.2 Transaksi Sirkulasi ACID

Peminjaman dan pengembalian dibungkus `DB::transaction()` dengan `lockForUpdate()` pada baris buku. Pengunci eksklusif mencegah *race condition*: dua permintaan serentak untuk buku bersetok 1 tidak dapat sama-sama lolos.

- Stok kurang dari 1: dibatalkan dengan `422 Unprocessable Content`.
- Mahasiswa masih meminjam buku yang sama dan belum mengembalikan: `409 Conflict`.
- Pengembalian pada transaksi berstatus `dikembalikan`: `409 Conflict`.

### 6.3 Mitigasi N+1 Query

Daftar buku memakai `with('kategori')->withCount('peminjamans')` sehingga jumlah kueri tetap konstan berapa pun jumlah barisnya.

### 6.4 Konsumsi OpenLibrary (REST Service Consumer)

- Pemanggilan memakai `Http::acceptJson()->timeout(8)` ke `https://openlibrary.org/api/books` dengan parameter `bibkeys=ISBN:{isbn}`, `format=json`, `jscmd=data`.
- Header `User-Agent` kustom disertakan sesuai etika penggunaan API publik.
- Pengecekan SSL (`withoutVerifying()`) hanya aktif pada environment `local` untuk mengatasi `cURL error 60` di Windows. Pada produksi, verifikasi tetap aktif.
- Penanganan galat: ISBN tidak ditemukan menghasilkan `404 External Reference Not Found`; layanan eksternal bermasalah menghasilkan `502`; koneksi gagal atau timeout menghasilkan `504 Gateway Timeout - External Service Unreachable`.

---

## 7. Riwayat Git

Repositori memakai **Conventional Commits** pada branch `week9-rest-api`:

| Commit | Isi |
|---|---|
| `feat(soap): add concise LibrarySoapController and WSDL contract for clearance check` | Endpoint SOAP dan WSDL |
| `feat(api-core): install laravel api routing, ApiResponse trait, and RFC 7807 exception handler` | Infrastruktur API dan galat global |
| `feat(api-auth): implement stateless Sanctum bearer token login and revocation` | Login, me, logout |
| `feat(api-books): build BukuApiController and BukuResource with dynamic HATEOAS links` | CRUD buku dan HATEOAS |
| `feat(api-loans): implement ACID book borrowing and return REST endpoints with lockForUpdate` | Sirkulasi peminjaman |
| `feat(api-consumer): integrate external book metadata lookup via Laravel Http client` | Konsumsi OpenLibrary |
| `docs(contract): add OpenAPI 3.0 specification, Postman collection, and test report in README` | Kontrak, koleksi Postman, README |

Lihat riwayat lengkap dengan `git log --oneline`. Berkas `.env` dan token rahasia tidak pernah di-commit.

---

## 8. Kesesuaian dengan Spesifikasi Tugas

| No | Persyaratan | Bobot | Pemenuhan | Bagian |
|---|---|---|---|---|
| 1 | Analisis kelayakan dan endpoint SOAP bebas pustaka (WSDL, envelope, Fault) | 15% | README bagian 2, `LibrarySoapController`, bukti (a) | [2](#2-analisis-kebutuhan-dan-kelayakan-web-services), [5a](#a-uji-soap-xml-dan-soapfault) |
| 2 | REST API Provider katalog buku (GET, POST, PUT vs PATCH, DELETE, N+1 bebas) | 25% | `BukuApiController`, `BukuResource`, bukti (c), (d), (e) | [3.3](#33-rest-katalog-buku), [5](#5-galeri-bukti-pengujian) |
| 3 | Sirkulasi peminjaman ACID dan Bearer Token Sanctum | 30% | `PeminjamanApiController`, `AuthTokenController`, bukti (b), (f) | [3.2](#32-rest-autentikasi), [3.4](#34-rest-sirkulasi-peminjaman-acid), [6.2](#62-transaksi-sirkulasi-acid) |
| 4 | HATEOAS dinamis, galat global RFC 7807, REST Consumer (`Http`) | 15% | `_links` kontekstual, `bootstrap/app.php`, `BukuReferensiApiController` | [3.5](#35-format-galat-rfc-7807), [6.4](#64-konsumsi-openlibrary-rest-service-consumer) |
| 5 | Kontrak OpenAPI, koleksi Postman, bukti uji, Git | 15% | `docs/openapi-sipus-v1.yaml`, `docs/SIPUS-Del-APIv1.postman_collection.json`, galeri bagian 5 | [4](#4-panduan-instalasi-dan-pengujian-cepat), [5](#5-galeri-bukti-pengujian), [7](#7-riwayat-git) |

---

Institut Teknologi Del, Fakultas Informatika dan Teknik Elektro. Dosen pengampu: Chandro Pardede, S.Kom., M.Sc.