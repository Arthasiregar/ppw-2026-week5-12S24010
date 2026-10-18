<x-layout title="Katalog Buku">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Katalog Buku</h1>
                <p class="text-muted mb-0">Koleksi buku perpustakaan kampus Del.</p>
            </div>

            @auth
                <a href="{{ route('buku.create') }}" class="btn btn-primary">+ Tambah Buku</a>
            @endauth
        </div>

        {{-- Kartu statistik (DB::table) --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Total Judul Buku</div>
                        <div class="fs-3 fw-bold">{{ number_format($stats['total_judul']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Total Stok Tersedia</div>
                        <div class="fs-3 fw-bold">{{ number_format($stats['total_stok']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Total Sedang Dipinjam</div>
                        <div class="fs-3 fw-bold">{{ number_format($stats['total_dipinjam']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Total Dikembalikan</div>
                        <div class="fs-3 fw-bold">{{ number_format($stats['total_dikembalikan']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter pencarian dinamis --}}
        <form method="GET" action="{{ route('buku.index') }}" class="row g-2 mb-3">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control"
                       placeholder="Cari judul, penulis, atau ISBN..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <select name="kategori_id" class="form-select">
                    <option value="">Semua kategori</option>
                    @foreach ($kategoris as $kategori)
                        <option value="{{ $kategori->id }}"
                            {{ request('kategori_id') == $kategori->id ? 'selected' : '' }}>
                            {{ $kategori->nama_kategori }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Cari</button>
                <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>

        @if (session('last_search'))
            <p class="text-muted small">Pencarian terakhir: <strong>{{ session('last_search') }}</strong></p>
        @endif

        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>No.</th>
                            <th>ISBN</th>
                            <th>Judul</th>
                            <th>Penulis</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Jumlah Dipinjam</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bukus as $buku)
                            <tr>
                                <td>{{ $bukus->firstItem() + $loop->index }}</td>
                                <td>{{ $buku->isbn }}</td>
                                <td>{{ $buku->judul }}</td>
                                <td>{{ $buku->penulis }}</td>
                                <td>{{ $buku->kategori?->nama_kategori ?? '-' }}</td>
                                <td>
                                    @if ($buku->stok < 1)
                                        <span class="badge bg-danger">0</span>
                                    @else
                                        {{ $buku->stok }}
                                    @endif
                                </td>
                                <td>{{ $buku->peminjamans_count }}</td>
                                <td class="text-nowrap">
                                    <a href="{{ route('buku.show', $buku) }}"
                                       class="btn btn-sm btn-outline-primary">Detail</a>
                                    @auth
                                        <a href="{{ route('buku.edit', $buku) }}"
                                           class="btn btn-sm btn-outline-warning">Edit</a>
                                        <form action="{{ route('buku.destroy', $buku) }}" method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Hapus buku ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                        </form>
                                    @endauth
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">Buku tidak ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $bukus->links() }}
        </div>
    </div>
</x-layout>