<x-layout title="Detail Kategori">
    <h3 class="text-del">{{ $kategori->nama_kategori }} ({{ $kategori->kode_kategori }})</h3>

    <h5 class="mt-4">Buku dalam kategori ini</h5>
    <ul class="list-group mb-3">
        @forelse($kategori->bukus as $buku)
            <li class="list-group-item">
                <a href="{{ route('buku.show', $buku) }}">{{ $buku->judul }}</a>
                <span class="text-muted">— {{ $buku->penulis }}</span>
            </li>
        @empty
            <li class="list-group-item text-muted">Belum ada buku di kategori ini.</li>
        @endforelse
    </ul>
    <a href="{{ route('kategori.index') }}" class="btn btn-secondary">Kembali</a>
</x-layout>