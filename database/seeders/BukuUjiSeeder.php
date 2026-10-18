<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Database\Seeder;

class BukuUjiSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = Kategori::first();

        if (!$kategori) {
            throw new \RuntimeException(
                'Kategori belum tersedia. Periksa proses seeding kategori.'
            );
        }

        Buku::updateOrCreate(
            ['isbn' => '978-000-0000-00-0'],
            [
                'judul' => 'Buku Uji Stok Kosong',
                'penulis' => 'Tim Uji',
                'penerbit' => 'Penerbit Uji',
                'tahun_terbit' => 2026,
                'kategori_id' => $kategori->id,
                'stok' => 0,
                'sinopsis' => 'Buku untuk pengujian stok kosong.',
            ]
        );

        Buku::updateOrCreate(
            ['isbn' => '978-000-0000-01-0'],
            [
                'judul' => 'Buku Uji Stok Satu',
                'penulis' => 'Tim Uji',
                'penerbit' => 'Penerbit Uji',
                'tahun_terbit' => 2026,
                'kategori_id' => $kategori->id,
                'stok' => 1,
                'sinopsis' => 'Buku untuk pengujian stok satu.',
            ]
        );
    }
}
