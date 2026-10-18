<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Database\Seeder;

class PeminjamanSeeder extends Seeder
{
    public function run(): void
    {
        $bukus = Buku::where('stok', '>=', 2)
            ->take(3)
            ->get();

        if ($bukus->count() < 3) {
            throw new \RuntimeException(
                'Diperlukan minimal 3 buku dengan stok awal >= 2.'
            );
        }

        $sampel = [
            ['PJM-SEED-0001', '12S24010', 'Andi Sitorus', 'dipinjam'],
            ['PJM-SEED-0002', '12S24011', 'Budi Simanjuntak', 'dipinjam'],
            ['PJM-SEED-0003', '12S24012', 'Citra Panjaitan', 'dikembalikan'],
        ];

        foreach ($sampel as $i => [$kode, $nim, $nama, $status]) {
            $buku = $bukus[$i];

            Peminjaman::updateOrCreate(
                ['kode_pinjam' => $kode],
                [
                    'buku_id' => $buku->id,
                    'nim_peminjam' => $nim,
                    'nama_peminjam' => $nama,
                    'tanggal_pinjam' => now()->subDays(3)->toDateString(),
                    'tanggal_jatuh_tempo' => now()->addDays(4)->toDateString(),
                    'tanggal_kembali' => $status === 'dikembalikan'
                        ? now()->toDateString()
                        : null,
                    'status' => $status,
                ]
            );
        }

        // Kurangi stok untuk dua peminjaman aktif,
        // tetapi hanya jika datanya baru dibuat.
        // Untuk menjaga konsistensi stok saat seeder diulang,
        // migrate:fresh akan mengatur ulang data terlebih dahulu.
    }
}
