<x-layout title="Daftar Buku">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0 text-del">Daftar Buku</h3>
        <a href="{{ route('buku.create') }}" class="btn btn-del">+ Tambah Buku</a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover bg-white shadow-sm align-middle">
            <thead>
                <tr>
                    <th>ISBN</th>
                    <th>Judul</th>
                    <th>Penulis</th>
                    <th>Kategori</th>
                    <th>Tahun</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bukus as $buku)
                    <tr>
                        <td>{{ $buku->isbn }}</td>
                        <td>{{ $buku->judul }}</td>
                        <td>{{ $buku->penulis }}</td>
                        <td>{{ $buku->kategori->nama_kategori }}</td>
                        <td>{{ $buku->tahun_terbit }}</td>
                        <td>{{ $buku->stok }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('buku.show', $buku) }}" class="btn btn-sm btn-info">Detail</a>
                                <a href="{{ route('buku.edit', $buku) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('buku.destroy', $buku) }}" method="POST"
                                      onsubmit="return confirm('Yakin hapus buku ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">Belum ada data buku.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $bukus->links() }}
</x-layout>