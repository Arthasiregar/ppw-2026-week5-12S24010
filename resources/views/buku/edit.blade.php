<x-layout title="Edit Buku">
    <h3 class="text-del">Edit Buku</h3>
    <form action="{{ route('buku.update', $buku) }}" method="POST">
        @csrf
        @method('PUT')
        @include('buku._form')
        <button class="btn btn-del">Perbarui</button>
        <a href="{{ route('buku.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</x-layout>