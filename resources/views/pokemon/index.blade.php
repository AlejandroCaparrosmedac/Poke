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
            border: 2px solid #ddd;
            font-size: 1.2rem;
            cursor: pointer;
            z-index: 10;
            transition: all 0.3s ease;
        }
        .favorite-btn:hover {
            border-color: #ff6b6b;
            background-color: #fff5f5;
        }
        .favorite-btn.favorited {
            color: #ff6b6b;
            background-color: #ffe0e0;
            border-color: #ff6b6b;
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
        .filter-section {
            background: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        .filter-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }
        .search-input {
            border-radius: 6px;
            border: 2px solid #e0e0e0;
            padding: 12px 16px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        .search-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .filter-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-select {
            border-radius: 6px;
            border: 2px solid #e0e0e0;
            padding: 10px 12px;
            font-size: 0.95rem;
            transition: border-color 0.3s;
        }
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn-search {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 6px;
            padding: 12px 30px;
            font-weight: 600;
            color: white;
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }
        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            color: white;
        }
        .btn-reset {
            background-color: #e0e0e0;
            color: #333;
            border: none;
            border-radius: 6px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.2s;
            cursor: pointer;
        }
        .btn-reset:hover {
            background-color: #d0d0d0;
        }
        .filter-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .active-filters {
            margin-top: 15px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .filter-badge {
            background-color: #667eea;
            color: white;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">🎮 POKÉDEX</a>
            <a href="https://prod.liveshare.vsengsaas.visualstudio.com/join?DDB6277C987946E84655ECAC0779A2F0D60A">juan gilipollas</a>
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
        <!-- Sección de Búsqueda y Filtros -->
        <div class="filter-section">
            <h3 class="filter-title">🔍 Buscar y Filtrar</h3>

            <form method="GET" action="{{ route('pokemon.index') }}" id="filterForm">
                <!-- Búsqueda por nombre o ID -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <input type="text"
                            class="form-control search-input"
                            name="search"
                            placeholder="Buscar por nombre o ID (ej: pikachu, 25)"
                            value="{{ $activeSearch ?? '' }}"
                            id="searchInput">
                        <small class="text-muted d-block mt-2">Escribe el nombre o número del Pokémon</small>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="filter-group">
                    <!-- Filtro por Tipo -->
                    <div>
                        <label for="typeSelect" class="form-label fw-bold mb-2">Tipo de Pokémon</label>
                        <select class="form-select" name="type" id="typeSelect" onchange="updateQueryParam('type', this.value)">
                            <option value="">Todos los tipos</option>
                            @foreach($types ?? [] as $t)
                                <option value="{{ $t['name'] }}" @if(($activeType ?? '') === $t['name']) selected @endif>
                                    {{ ucfirst($t['name']) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro por Generación -->
                    <div>
                        <label for="generationSelect" class="form-label fw-bold mb-2">Generación</label>
                        <select class="form-select" name="generation" id="generationSelect" onchange="updateQueryParam('generation', this.value)">
                            <option value="">Todas las generaciones</option>
                            @foreach($generations ?? [] as $gen)
                                <option value="{{ $gen['id'] }}" @if(($activeGeneration ?? '') == $gen['id']) selected @endif>
                                    {{ ucfirst(str_replace('-', ' ', $gen['name'])) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="filter-buttons">
                    <button type="submit" class="btn btn-search">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    <a href="{{ route('pokemon.index') }}" class="btn btn-reset">
                        ✕ Limpiar
                    </a>
                </div>

                <!-- Mostrar filtros activos -->
                @if($activeSearch || $activeType || $activeGeneration)
                    <div class="active-filters">
                        <strong class="d-block w-100 mb-2">Filtros activos:</strong>
                        @if($activeSearch)
                            <span class="filter-badge">
                                🔍 Búsqueda: {{ $activeSearch }}
                            </span>
                        @endif
                        @if($activeType)
                            <span class="filter-badge">
                                🏷️ Tipo: {{ ucfirst($activeType) }}
                            </span>
                        @endif
                        @if($activeGeneration)
                            <span class="filter-badge">
                                🎮 Gen: {{ $activeGeneration }}
                            </span>
                        @endif
                    </div>
                @endif
            </form>
        </div>

        <!-- Información general -->
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
                            <a class="page-link" href="{{ route('pokemon.index', array_merge(['page' => $current_page - 1], request()->only(['search', 'type', 'generation']))) }}">Anterior</a>
                        </li>
                    @endif

                    @php
                        $pages_to_show = 5;
                        $start_page = max(1, $current_page - floor($pages_to_show / 2));
                        $end_page = min($last_page, $start_page + $pages_to_show - 1);
                        $start_page = max(1, $end_page - $pages_to_show + 1);
                    @endphp

                    @for ($i = $start_page; $i <= $end_page; $i++)
                        <li class="page-item @if($i == $current_page) active @endif">
                            <a class="page-link" href="{{ route('pokemon.index', array_merge(['page' => $i], request()->only(['search', 'type', 'generation']))) }}">{{ $i }}</a>
                        </li>
                    @endfor

                    @if ($current_page < $last_page)
                        <li class="page-item">
                            <a class="page-link" href="{{ route('pokemon.index', array_merge(['page' => $current_page + 1], request()->only(['search', 'type', 'generation']))) }}">Siguiente</a>
                        </li>
                    @endif
                </ul>
            </nav>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para actualizar parámetros dinámicamente
        function updateQueryParam(paramName, paramValue) {
            const form = document.getElementById('filterForm');
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = paramName;
            input.value = paramValue;

            // Eliminar input anterior si existe
            const existingInput = form.querySelector(`input[name="${paramName}"]`);
            if (existingInput) {
                existingInput.value = paramValue;
            } else {
                form.appendChild(input);
            }

            form.submit();
        }

        // Favoritos
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
