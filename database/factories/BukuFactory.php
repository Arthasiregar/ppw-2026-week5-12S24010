<?php

namespace Database\Factories;

use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

class BukuFactory extends Factory
{
    public function definition(): array
    {
        $awalan = [
            'Pengantar', 'Dasar-Dasar', 'Panduan Praktis', 'Konsep Modern',
            'Belajar', 'Analisis dan Perancangan', 'Implementasi', 'Strategi',
        ];

        $topik = [
            'Pemrograman Web', 'Basis Data', 'Jaringan Komputer', 'Sistem Informasi',
            'Kecerdasan Buatan', 'Struktur Data', 'Rekayasa Perangkat Lunak',
            'Keamanan Informasi', 'Manajemen Proyek TI', 'Pemrograman Python',
        ];

        $penerbit = [
            'Informatika Bandung', 'Andi Offset', 'Erlangga',
            'Gramedia Pustaka Utama', 'Elex Media Komputindo', 'Deepublish',
        ];

        return [
            'isbn' => fake()->unique()->isbn13(),
            'judul' => fake()->randomElement($awalan) . ' ' . fake()->randomElement($topik),
            'penulis' => fake()->name(),
            'penerbit' => fake()->randomElement($penerbit),
            'tahun_terbit' => fake()->numberBetween(2005, 2025),
            'kategori_id' => Kategori::inRandomOrder()->value('id'),
            'stok' => fake()->numberBetween(0, 25),
            'sinopsis' => fake()->paragraph(3),
        ];
    }
}