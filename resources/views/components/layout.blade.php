<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ?? 'SIPUS-Del' }} — Institut Teknologi Del</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .navbar-del {
            background-color: #4C1D95;
        }

        .btn-del {
            background-color: #4C1D95;
            color: #ffffff;
        }

        .btn-del:hover {
            background-color: #3B0764;
            color: #ffffff;
        }

        .text-del {
            color: #4C1D95;
        }
    </style>
</head>

<body class="bg-light">

    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-dark navbar-del shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('buku.index') }}">
                SIPUS-Del Perpustakaan
            </a>

            <div class="navbar-nav flex-row gap-3 align-items-center">
                <a class="nav-link" href="{{ route('buku.index') }}">
                    Buku
                </a>

                <a class="nav-link" href="{{ route('kategori.index') }}">
                    Kategori
                </a>

                {{-- Informasi sesi dan tombol logout --}}
                @auth
                    <span class="navbar-text text-white">
                        {{ auth()->user()->name }}
                        | Login: {{ session('login_timestamp', '-') }}
                    </span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button type="submit" class="btn btn-light btn-sm">
                            Logout
                        </button>
                    </form>
                @else
                    <a class="nav-link" href="{{ route('login') }}">
                        Login
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Konten halaman --}}
    <div class="container pb-5">

        {{-- Pesan sukses --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close">
                </button>
            </div>
        @endif

        {{-- Pesan error --}}
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close">
                </button>
            </div>
        @endif

        {{ $slot }}

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
