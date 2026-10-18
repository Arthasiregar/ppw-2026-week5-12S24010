<x-layout title="Tambah Peminjaman">
    <div class="container py-4">
        <div class="mb-4">
            <h1 class="h3 mb-1">Tambah Peminjaman</h1>
            <p class="text-muted mb-0">Catat peminjaman buku baru.</p>
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
            <div class="card-body">
                <form action="{{ route('peminjaman.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="buku_id" class="form-label">Buku</label>
                        <select name="buku_id" id="buku_id" class="form-select" required>
                            <option value="">-- Pilih buku --</option>
                            @foreach ($bukus as $buku)
                                <option value="{{ $buku->id }}"
                                    {{ old('buku_id') == $buku->id ? 'selected' : '' }}>
                                    {{ $buku->judul }} (stok: {{ $buku->stok }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="nim_peminjam" class="form-label">NIM Peminjam</label>
                        <input type="text" name="nim_peminjam" id="nim_peminjam"
                               class="form-control" value="{{ old('nim_peminjam') }}"
                               maxlength="15" required>
                    </div>

                    <div class="mb-3">
                        <label for="nama_peminjam" class="form-label">Nama Peminjam</label>
                        <input type="text" name="nama_peminjam" id="nama_peminjam"
                               class="form-control" value="{{ old('nama_peminjam') }}"
                               maxlength="150" required>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Pinjam Buku</button>
                        <a href="{{ route('peminjaman.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>