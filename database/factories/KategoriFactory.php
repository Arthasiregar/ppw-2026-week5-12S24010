<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class KategoriFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kode_kategori' => strtoupper(fake()->unique()->lexify('???')),
            'nama_kategori' => ucwords(fake()->words(2, true)),
        ];
    }
}