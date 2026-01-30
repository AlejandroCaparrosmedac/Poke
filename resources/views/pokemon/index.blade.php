<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pokédex - Listado</title>
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
        .favorite-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: white;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            z-index: 10;
        }
        .favorite-btn.favorited {
            color: #ff6b6b;
        }
        .pokemon-card {
            position: relative;
        }
        .alert {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 1050;
            max-width: 400px;
        }
        .badge {
            margin-right: 5px;
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
                        <a class="nav-link active" href="{{ route('pokemon.index') }}">Pokémon</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('favorites.index') }}">
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

    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('warning') }}
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
                <h1 class="mb-0">📚 Pokédex</h1>
                <p class="text-muted">Total: {{ $total }} Pokémon</p>
            </div>
            <div class="col-md-4 text-end">
                <span class="badge bg-info">Página {{ $current_page }} de {{ $last_page }}</span>
            </div>
        </div>

        @if (isset($error))
            <div class="alert alert-danger">
                {{ $error }}
            </div>
        @else
            <!-- Grid de Pokémon -->
            <div class="row g-4">
                @foreach ($pokemon as $p)
                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="card pokemon-card">
                            <button class="favorite-btn @if(in_array($p['id'], $favoriteIds)) favorited @endif"
                                data-pokemon-id="{{ $p['id'] }}"
                                data-pokemon-name="{{ $p['name'] }}"
                                data-pokemon-image="{{ $p['image'] }}"
                                title="Agregar a favoritos">
                                ❤️
                            </button>
                            <img src="{{ $p['image'] }}" class="pokemon-image" alt="{{ $p['name'] }}">
                            <div class="card-body">
                                <h5 class="card-title pokemon-name">{{ $p['name'] }}</h5>
                                <p class="card-text text-muted">#{{ $p['id'] }}</p>
                                <a href="{{ route('pokemon.show', ['id' => $p['id']]) }}" class="btn btn-sm btn-primary">
                                    Ver Detalles
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Paginación -->
            <nav class="mt-5" aria-label="Paginación">
                <ul class="pagination justify-content-center">
                    @if ($current_page > 1)
                        <li class="page-item">
                            <a class="page-link" href="{{ route('pokemon.index', ['page' => $current_page - 1]) }}">Anterior</a>
                        </li>
                    @endif

                    @for ($i = 1; $i <= $last_page && $i <= 5; $i++)
                        <li class="page-item @if($i == $current_page) active @endif">
                            <a class="page-link" href="{{ route('pokemon.index', ['page' => $i]) }}">{{ $i }}</a>
                        </li>
                    @endfor

                    @if ($current_page < $last_page)
                        <li class="page-item">
                            <a class="page-link" href="{{ route('pokemon.index', ['page' => $current_page + 1]) }}">Siguiente</a>
                        </li>
                    @endif
                </ul>
            </nav>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.favorite-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const pokemonId = this.dataset.pokemonId;
                const pokemonName = this.dataset.pokemonName;
                const pokemonImage = this.dataset.pokemonImage;
                const isFavorited = this.classList.contains('favorited');

                if (isFavorited) {
                    // Eliminar de favoritos
                    fetch(`/favorites/pokemon/${pokemonId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => {
                        if (response.ok) {
                            this.classList.remove('favorited');
                            location.reload();
                        }
                    });
                } else {
                    // Agregar a favoritos
                    fetch('/favorites', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            pokemon_id: pokemonId,
                            pokemon_name: pokemonName,
                            pokemon_image: pokemonImage
                        })
                    })
                    .then(response => {
                        if (response.ok) {
                            this.classList.add('favorited');
                            location.reload();
                        }
                    });
                }
            });
        });
    </script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('js/darkmode.js') }}"></script>
</body>
</html>
