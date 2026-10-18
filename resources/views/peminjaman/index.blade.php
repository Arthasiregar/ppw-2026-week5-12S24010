<x-layout title="Daftar Peminjaman">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Daftar Peminjaman</h1>
                <p class="text-muted mb-0">
                    Kelola data peminjaman buku perpustakaan.
                </p>
            </div>

            <a href="{{ route('peminjaman.create') }}"
               class="btn btn-primary">
                + Tambah Peminjaman
            </a>
        </div>


        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>No.</th>
                            <th>Kode Pinjam</th>
                            <th>Buku</th>
                            <th>NIM Peminjam</th>
                            <th>Nama Peminjam</th>
                            <th>Tanggal Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th>Tanggal Kembali</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($peminjamans as $peminjaman)
                            <tr>
                                <td>
                                    {{ $peminjamans->firstItem() + $loop->index }}
                                </td>
                                <td>
                                    {{ $peminjaman->kode_pinjam }}
                                </td>
                                <td>
                                    {{ $peminjaman->buku?->judul ?? 'Buku tidak ditemukan' }}
                                </td>
                                <td>
                                    {{ $peminjaman->nim_peminjam }}
                                </td>
                                <td>
                                    {{ $peminjaman->nama_peminjam }}
                                </td>
                                <td>
                                    {{ $peminjaman->tanggal_pinjam?->format('d-m-Y') }}
                                </td>
                                <td>
                                    {{ $peminjaman->tanggal_jatuh_tempo?->format('d-m-Y') }}
                                </td>
                                <td>
                                    {{ $peminjaman->tanggal_kembali?->format('d-m-Y') ?? '-' }}
                                </td>
                                <td>
                                    @if ($peminjaman->status === 'dipinjam')
                                        <span class="badge bg-warning text-dark">
                                            Dipinjam
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            Dikembalikan
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if ($peminjaman->status === 'dipinjam')
                                        <form action="{{ route('peminjaman.kembali', $peminjaman->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Kembalikan buku ini?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-success">
                                                Kembalikan
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    Belum ada data peminjaman.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $peminjamans->links() }}
        </div>
    </div>
</x-layout>