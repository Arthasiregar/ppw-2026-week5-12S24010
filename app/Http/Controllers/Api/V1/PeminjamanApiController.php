<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PeminjamanResource;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamanApiController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/peminjamans (Bearer Token)
     * Filter ?status=dipinjam|dikembalikan dan ?nim=...
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'nullable|in:dipinjam,dikembalikan',
            'nim' => 'nullable|string|max:15',
        ]);

        $perPage = min(max((int) $request->query('per_page', 5), 1), 50);

        $paginator = Peminjaman::query()
            ->with('buku')
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('nim'), fn ($q, $nim) => $q->where('nim_peminjam', strtoupper(trim($nim))))
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return $this->successResponse(
            data: PeminjamanResource::collection($paginator->items()),
            message: 'Daftar transaksi peminjaman berhasil diambil.',
            meta: [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total_items' => $paginator->total(),
                'total_pages' => $paginator->lastPage(),
                'links' => [
                    'first' => $paginator->url(1),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                    'last' => $paginator->url($paginator->lastPage()),
                ],
            ]
        );
    }

    /**
     * GET /api/v1/peminjamans/{id} (Bearer Token)
     * Dipakai oleh link "self" pada HATEOAS.
     */
    public function show(string $id): JsonResponse
    {
        $peminjaman = Peminjaman::with('buku')->find($id);

        if (!$peminjaman) {
            return $this->notFound($id);
        }

        return $this->successResponse(
            data: new PeminjamanResource($peminjaman),
            message: 'Detail peminjaman berhasil ditemukan.'
        );
    }

    /**
     * POST /api/v1/peminjamans (Bearer Token)
     * Transaksi ACID: kunci baris buku, cek konflik dan stok, kurangi stok, simpan.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'buku_id' => 'required|integer|exists:bukus,id',
            'nim_peminjam' => 'required|string|max:15',
            'nama_peminjam' => 'required|string|max:150',
            'durasi_hari' => 'nullable|integer|min:1|max:30',
        ]);

        $nim = strtoupper(trim($validated['nim_peminjam']));
        $durasi = $validated['durasi_hari'] ?? 7;

        return DB::transaction(function () use ($validated, $nim, $durasi) {
            // 1. Kunci baris buku (SELECT ... FOR UPDATE) agar tidak terjadi race condition stok
            $buku = Buku::where('id', $validated['buku_id'])->lockForUpdate()->first();

            if (!$buku) {
                return $this->notFound((string) $validated['buku_id']);
            }

            // 2. Mahasiswa masih meminjam buku yang sama dan belum dikembalikan -> 409
            $sudahPinjam = Peminjaman::where('buku_id', $buku->id)
                ->where('nim_peminjam', $nim)
                ->where('status', 'dipinjam')
                ->exists();

            if ($sudahPinjam) {
                return $this->problemResponse(
                    title: 'Conflict - Active Loan Exists',
                    detail: "NIM '{$nim}' masih meminjam buku '{$buku->judul}' dan belum mengembalikannya.",
                    status: 409,
                    errorSlug: 'active-loan-exists'
                );
            }

            // 3. Stok habis -> 422
            if ($buku->stok < 1) {
                return $this->problemResponse(
                    title: 'Unprocessable Content - Out of Stock',
                    detail: "Stok buku sedang habis. Buku '{$buku->judul}' tidak dapat dipinjam saat ini.",
                    status: 422,
                    errorSlug: 'out-of-stock'
                );
            }

            // 4. Kurangi stok dan catat peminjaman secara atomik
            $buku->decrement('stok');

            $peminjaman = Peminjaman::create([
                'buku_id' => $buku->id,
                'nim_peminjam' => $nim,
                'nama_peminjam' => $validated['nama_peminjam'],
                'tanggal_pinjam' => now()->toDateString(),
                'durasi_hari' => $durasi,
                'tanggal_jatuh_tempo' => now()->addDays($durasi)->toDateString(),
                'status' => 'dipinjam',
            ]);

            $peminjaman->load('buku');

            return $this->createdResponse(
                data: new PeminjamanResource($peminjaman),
                locationUrl: url("/api/v1/peminjamans/{$peminjaman->id}"),
                message: "Peminjaman buku '{$buku->judul}' berhasil dicatat. Sisa stok: {$buku->stok}."
            );
        });
    }

    /**
     * PATCH /api/v1/peminjamans/{id}/kembalikan (Bearer Token)
     * Transaksi ACID: kunci peminjaman dan buku, ubah status, tambah stok (+1).
     */
    public function kembalikan(string $id): JsonResponse
    {
        return DB::transaction(function () use ($id) {
            $peminjaman = Peminjaman::where('id', $id)->lockForUpdate()->first();

            if (!$peminjaman) {
                return $this->notFound($id);
            }

            // Sudah dikembalikan sebelumnya -> 409
            if ($peminjaman->status === 'dikembalikan') {
                return $this->problemResponse(
                    title: 'Conflict - Already Returned',
                    detail: 'Buku pada transaksi ini sudah dikembalikan sebelumnya.',
                    status: 409,
                    errorSlug: 'already-returned'
                );
            }

            $buku = Buku::where('id', $peminjaman->buku_id)->lockForUpdate()->first();

            $peminjaman->update([
                'status' => 'dikembalikan',
                'tanggal_kembali' => now()->toDateString(),
            ]);

            if ($buku) {
                $buku->increment('stok');
            }

            $peminjaman->load('buku');

            return $this->successResponse(
                data: new PeminjamanResource($peminjaman),
                message: 'Buku berhasil dikembalikan dan stok telah diperbarui.'
            );
        });
    }

    private function notFound(string $id): JsonResponse
    {
        return $this->problemResponse(
            title: 'Resource Not Found',
            detail: "Data dengan ID '{$id}' tidak ditemukan di basis data.",
            status: 404,
            errorSlug: 'resource-not-found'
        );
    }
}