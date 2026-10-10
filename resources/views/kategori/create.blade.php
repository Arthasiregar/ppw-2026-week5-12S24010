<x-layout title="Tambah Kategori">
    <h3 class="text-del">Tambah Kategori</h3>
    <form action="{{ route('kategori.store') }}" method="POST">
        @csrf
        @include('kategori._form')
        <button class="btn btn-del">Simpan</button>
        <a href="{{ route('kategori.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</x-layout>