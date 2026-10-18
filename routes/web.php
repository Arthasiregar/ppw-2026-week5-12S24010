<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\KategoriController;

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
| Rute Buku dan Kategori - Memerlukan Login untuk Mutasi
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Buku: tambah, simpan, edit, update, hapus
    Route::resource('buku', BukuController::class)
        ->except(['index', 'show']);

    // Kategori: tambah, simpan, edit, update, hapus
    Route::resource('kategori', KategoriController::class)
        ->except(['index', 'show']);

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
