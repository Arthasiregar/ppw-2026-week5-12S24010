<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun uji pustakawan (wajib ada sesuai modul)
        User::updateOrCreate(
            ['email' => 'pustakawan@del.ac.id'],
            [
                'name' => 'Pustakawan Del',
                'password' => Hash::make('password123'),
            ]
        );

        // Kategori contoh
        $informatika = Kategori::updateOrCreate(
            ['kode_kategori' => 'INF'],
            ['nama_kategori' => 'Informatika']
        );
        $sistemInformasi = Kategori::updateOrCreate(
            ['kode_kategori' => 'SI'],
            ['nama_kategori' => 'Sistem Informasi']
        );
        $umum = Kategori::updateOrCreate(
            ['kode_kategori' => 'UMM'],
            ['nama_kategori' => 'Umum']
        );

        // Buku contoh
        $bukus = [
            [
                'isbn' => '978-602-03-0001-1',
                'judul' => 'Pemrograman Web dengan Laravel',
                'penulis' => 'Andi Pratama',
                'penerbit' => 'Informatika Press',
                'tahun_terbit' => 2023,
                'kategori_id' => $informatika->id,
                'stok' => 5,
                'sinopsis' => 'Panduan membangun aplikasi web dengan Laravel.',
            ],
            [
                'isbn' => '978-602-03-0002-8',
                'judul' => 'Basis Data dan Transaksi ACID',
                'penulis' => 'Siti Rahayu',
                'penerbit' => 'Del Publishing',
                'tahun_terbit' => 2022,
                'kategori_id' => $informatika->id,
                'stok' => 3,
                'sinopsis' => 'Konsep basis data relasional dan transaksi.',
            ],
            [
                'isbn' => '978-602-03-0003-5',
                'judul' => 'Analisis dan Perancangan Sistem Informasi',
                'penulis' => 'Budi Santoso',
                'penerbit' => 'Del Publishing',
                'tahun_terbit' => 2021,
                'kategori_id' => $sistemInformasi->id,
                'stok' => 2,
                'sinopsis' => 'Metode analisis kebutuhan dan perancangan sistem.',
            ],
            [
                'isbn' => '978-602-03-0004-2',
                'judul' => 'Arsitektur Web Services dan REST API',
                'penulis' => 'Chandra Nainggolan',
                'penerbit' => 'Informatika Press',
                'tahun_terbit' => 2024,
                'kategori_id' => $informatika->id,
                'stok' => 1,
                'sinopsis' => 'Pengantar SOAP, REST, dan OpenAPI.',
            ],
            [
                'isbn' => '978-602-03-0005-9',
                'judul' => 'Pengantar Kewirausahaan',
                'penulis' => 'Dewi Lestari',
                'penerbit' => 'Nusantara Books',
                'tahun_terbit' => 2020,
                'kategori_id' => $umum->id,
                'stok' => 0,
                'sinopsis' => null,
            ],
        ];

        foreach ($bukus as $data) {
            Buku::updateOrCreate(['isbn' => $data['isbn']], $data);
        }
    }
}