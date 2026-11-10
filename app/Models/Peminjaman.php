<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjamans';

    protected $fillable = [
        'buku_id',
        'nim_peminjam',
        'nama_peminjam',
        'tanggal_pinjam',
        'durasi_hari',
        'tanggal_jatuh_tempo',
        'tanggal_kembali',
        'status',
    ];

    public function buku(): BelongsTo
    {
        return $this->belongsTo(Buku::class);
    }
}