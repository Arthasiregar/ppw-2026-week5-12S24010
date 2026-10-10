@php $b = $buku ?? null; @endphp

<div class="mb-3">
    <label class="form-label">ISBN</label>
    <input type="text" name="isbn" value="{{ old('isbn', $b?->isbn) }}"
           class="form-control @error('isbn') is-invalid @enderror">
    @error('isbn') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label class="form-label">Judul (minimal 5 karakter)</label>
    <input type="text" name="judul" value="{{ old('judul', $b?->judul) }}"
           class="form-control @error('judul') is-invalid @enderror">
    @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Penulis</label>
        <input type="text" name="penulis" value="{{ old('penulis', $b?->penulis) }}"
               class="form-control @error('penulis') is-invalid @enderror">
        @error('penulis') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Penerbit</label>
        <input type="text" name="penerbit" value="{{ old('penerbit', $b?->penerbit) }}"
               class="form-control @error('penerbit') is-invalid @enderror">
        @error('penerbit') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Tahun Terbit</label>
        <input type="number" name="tahun_terbit" value="{{ old('tahun_terbit', $b?->tahun_terbit) }}"
               class="form-control @error('tahun_terbit') is-invalid @enderror">
        @error('tahun_terbit') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Kategori</label>
        <select name="kategori_id" class="form-select @error('kategori_id') is-invalid @enderror">
            <option value="">-- Pilih --</option>
            @foreach($kategoris as $kat)
                <option value="{{ $kat->id }}" @selected(old('kategori_id', $b?->kategori_id) == $kat->id)>
                    {{ $kat->nama_kategori }}
                </option>
            @endforeach
        </select>
        @error('kategori_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Stok (minimal 0)</label>
        <input type="number" name="stok" value="{{ old('stok', $b?->stok) }}"
               class="form-control @error('stok') is-invalid @enderror">
        @error('stok') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Sinopsis</label>
    <textarea name="sinopsis" rows="4"
              class="form-control @error('sinopsis') is-invalid @enderror">{{ old('sinopsis', $b?->sinopsis) }}</textarea>
    @error('sinopsis') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>