<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Mis Favoritos - Pokédex</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/darkmode.css') }}" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .pokemon-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
            height: 100%;
        }
        .pokemon-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        .pokemon-image {
            height: 200px;
            object-fit: contain;
            padding: 20px;
            background-color: #f0f0f0;
        }
        .pokemon-name {
            font-weight: bold;
            text-transform: capitalize;
            font-size: 1.1rem;
        }
        .alert {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 1050;
            max-width: 400px;
        }
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }
        .empty-state i {
            font-size: 4rem;
            color: #ccc;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">🎮 POKÉDEX</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('pokemon.index') }}">Pokémon</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('favorites.index') }}">
                            ❤️ Favoritos
                        </a>
                    </li>
                    <li class="nav-item">
                        <button id="theme-toggle" class="theme-toggle-btn" onclick="window.darkModeManager.toggle()" title="Cambiar tema">
                            🌙
                        </button>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="nav-link btn btn-link" style="border: none; cursor: pointer;">
                                Cerrar Sesión
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Alertas -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Contenido principal -->
    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="mb-0">❤️ Mis Pokémon Favoritos</h1>
                <p class="text-muted">Total: {{ $favorites->total() }} favoritos</p>
            </div>
        </div>

        @if ($favorites->count() > 0)
            <!-- Grid de Favoritos -->
            <div class="row g-4">
                @foreach ($favorites as $favorite)
                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="card pokemon-card">
                            <img src="{{ $favorite->pokemon_image }}" class="pokemon-image" alt="{{ $favorite->pokemon_name }}">
                            <div class="card-body">
                                <h5 class="card-title pokemon-name">{{ $favorite->pokemon_name }}</h5>
                                <p class="card-text text-muted">#{{ $favorite->pokemon_id }}</p>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('pokemon.show', ['id' => $favorite->pokemon_id]) }}" class="btn btn-sm btn-primary flex-grow-1">
                                        Ver Detalles
                                    </a>
                                    <form method="POST" action="{{ route('favorites.destroy', ['favorite' => $favorite->id]) }}" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar de favoritos">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Paginación -->
            @if ($favorites->lastPage() > 1)
                <nav class="mt-5" aria-label="Paginación">
                    <ul class="pagination justify-content-center">
                        {{ $favorites->links('pagination::bootstrap-5') }}
                    </ul>
                </nav>
            @endif
        @else
            <!-- Estado vacío -->
            <div class="empty-state">
                <div style="font-size: 4rem; margin-bottom: 1rem;">😢</div>
                <h3>No tienes Pokémon favoritos aún</h3>
                <p class="text-muted">Comienza a agregar tus Pokémon favoritos desde el listado principal</p>
                <a href="{{ route('pokemon.index') }}" class="btn btn-primary mt-3">
                    Ir a la Pokédex
                </a>
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/darkmode.js') }}"></script>
</body>
</html>
