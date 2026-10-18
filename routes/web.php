
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PeminjamanController;

/*
|--------------------------------------------------------------------------
| Rute Autentikasi
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'showLogin'])
    ->middleware('guest')
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.process');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


/*
|--------------------------------------------------------------------------
| Rute yang Memerlukan Login
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Buku: tambah, simpan, edit, update, hapus
    Route::resource('buku', BukuController::class)
        ->except(['index', 'show']);

    // Kategori: tambah, simpan, edit, update, hapus
    Route::resource('kategori', KategoriController::class)
        ->except(['index', 'show']);

    // Peminjaman: daftar, formulir, dan simpan
    Route::get('/peminjaman', [PeminjamanController::class, 'index'])
        ->name('peminjaman.index');

    Route::get('/peminjaman/create', [PeminjamanController::class, 'create'])
        ->name('peminjaman.create');

    Route::post('/peminjaman', [PeminjamanController::class, 'store'])
        ->name('peminjaman.store');

    // Pengembalian buku
    Route::patch('/peminjaman/{id}/kembali', [PeminjamanController::class, 'kembali'])
        ->name('peminjaman.kembali');
});


/*
|--------------------------------------------------------------------------
| Rute Publik - Hanya Membaca Data
|--------------------------------------------------------------------------
*/

// Katalog buku
Route::resource('buku', BukuController::class)
    ->only(['index', 'show']);

// Katalog kategori
Route::resource('kategori', KategoriController::class)
    ->only(['index', 'show']);
