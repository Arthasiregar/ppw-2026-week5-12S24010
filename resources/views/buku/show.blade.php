<x-layout title="Detail Buku">
    <h3 class="text-del">{{ $buku->judul }}</h3>
    <ul class="list-group mb-3">
        <li class="list-group-item">ISBN: {{ $buku->isbn }}</li>
        <li class="list-group-item">Penulis: {{ $buku->penulis }}</li>
        <li class="list-group-item">Penerbit: {{ $buku->penerbit }}</li>
        <li class="list-group-item">Tahun Terbit: {{ $buku->tahun_terbit }}</li>
        <li class="list-group-item">Kategori: {{ $buku->kategori->nama_kategori }}</li>
        <li class="list-group-item">Stok: {{ $buku->stok }}</li>
        <li class="list-group-item">Sinopsis: {{ $buku->sinopsis ?: '-' }}</li>
    </ul>
    <a href="{{ route('buku.index') }}" class="btn btn-secondary">Kembali</a>
</x-layout>