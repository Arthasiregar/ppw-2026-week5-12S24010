<x-layout title="Daftar Kategori">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0 text-del">Daftar Kategori</h3>
        <a href="{{ route('kategori.create') }}" class="btn btn-del">+ Tambah Kategori</a>
    </div>

    <table class="table table-striped table-hover bg-white shadow-sm align-middle">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama Kategori</th>
                <th>Jumlah Buku</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kategoris as $kategori)
                <tr>
                    <td>{{ $kategori->kode_kategori }}</td>
                    <td>{{ $kategori->nama_kategori }}</td>
                    <td>{{ $kategori->bukus_count }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('kategori.show', $kategori) }}" class="btn btn-sm btn-info">Detail</a>
                            <a href="{{ route('kategori.edit', $kategori) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('kategori.destroy', $kategori) }}" method="POST"
                                  onsubmit="return confirm('Yakin hapus kategori ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">Belum ada data kategori.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $kategoris->links() }}
</x-layout>