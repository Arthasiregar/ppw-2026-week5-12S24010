<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Buku;
use App\Traits\ApiResponse;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class BukuReferensiApiController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/bukus/{id}/referensi-eksternal
     * SIPUS-Del bertindak sebagai SERVICE CONSUMER terhadap OpenLibrary REST API.
     */
    public function show(string $id): JsonResponse
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Data dengan ID '{$id}' tidak ditemukan di basis data.",
                status: 404,
                errorSlug: 'resource-not-found'
            );
        }

        // Normalisasi ISBN: buang tanda hubung/spasi
        $isbn = strtoupper(preg_replace('/[^0-9Xx]/', '', (string) $buku->isbn));
        $bibkey = "ISBN:{$isbn}";

        try {
            $response = Http::acceptJson()
                ->withHeaders(['User-Agent' => 'SIPUS-Del/1.0 (Praktikum PPW IT Del)'])
                ->when(app()->environment('local'), fn ($http) => $http->withoutVerifying())
                ->timeout(8)
                ->get('https://openlibrary.org/api/books', [
                    'bibkeys' => $bibkey,
                    'format' => 'json',
                    'jscmd' => 'data',
                ]);
        } catch (ConnectionException $e) {
            // Timeout, DNS gagal, atau tidak ada koneksi internet
            return $this->problemResponse(
                title: 'Gateway Timeout - External Service Unreachable',
                detail: 'Layanan OpenLibrary tidak dapat dihubungi atau melewati batas waktu 8 detik.',
                status: 504,
                errorSlug: 'external-service-timeout'
            );
        }

        if ($response->failed()) {
            return $this->problemResponse(
                title: 'Bad Gateway - External Service Error',
                detail: "Layanan OpenLibrary merespons dengan status {$response->status()}.",
                status: 502,
                errorSlug: 'external-service-error'
            );
        }

        $payload = $response->json();
        $item = is_array($payload) ? ($payload[$bibkey] ?? null) : null;

        // OpenLibrary mengembalikan {} jika ISBN tidak dikenal
        if (empty($item)) {
            return $this->problemResponse(
                title: 'External Reference Not Found',
                detail: "ISBN '{$buku->isbn}' tidak ditemukan pada katalog OpenLibrary.",
                status: 404,
                errorSlug: 'external-reference-not-found'
            );
        }

        return $this->successResponse(
            data: [
                'buku_lokal' => [
                    'id' => $buku->id,
                    'judul' => $buku->judul,
                    'isbn' => $buku->isbn,
                ],
                'referensi_eksternal' => [
                    'sumber' => 'OpenLibrary',
                    'judul' => $item['title'] ?? null,
                    'penulis' => collect($item['authors'] ?? [])->pluck('name')->values()->all(),
                    'penerbit' => collect($item['publishers'] ?? [])->pluck('name')->values()->all(),
                    'jumlah_halaman' => $item['number_of_pages'] ?? null,
                    'tanggal_terbit' => $item['publish_date'] ?? null,
                    'tautan_pratinjau' => $item['url'] ?? null,
                    'sampul' => $item['cover']['medium'] ?? null,
                ],
            ],
            message: 'Metadata tambahan buku berhasil diambil dari OpenLibrary.'
        );
    }
}