<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $kategoris = [
            ['kode_kategori' => 'PRG', 'nama_kategori' => 'Pemrograman'],
            ['kode_kategori' => 'DBS', 'nama_kategori' => 'Basis Data'],
            ['kode_kategori' => 'JRK', 'nama_kategori' => 'Jaringan Komputer'],
            ['kode_kategori' => 'SIF', 'nama_kategori' => 'Sistem Informasi'],
            ['kode_kategori' => 'AIX', 'nama_kategori' => 'Kecerdasan Buatan'],
        ];

        foreach ($kategoris as $kategori) {
            Kategori::factory()->create($kategori);
        }

        Buku::factory(20)->create();
    }
}