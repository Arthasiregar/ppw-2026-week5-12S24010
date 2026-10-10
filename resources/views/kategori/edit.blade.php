<x-layout title="Edit Kategori">
    <h3 class="text-del">Edit Kategori</h3>
    <form action="{{ route('kategori.update', $kategori) }}" method="POST">
        @csrf
        @method('PUT')
        @include('kategori._form')
        <button class="btn btn-del">Perbarui</button>
        <a href="{{ route('kategori.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</x-layout>