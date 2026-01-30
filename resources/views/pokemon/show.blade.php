<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Detalles de Pokémon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/darkmode.css') }}" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .detail-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            padding: 3rem;
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
        .pokemon-image-detail {
            height: 300px;
            object-fit: contain;
            margin-bottom: 2rem;
        }
        .stats-bar {
            background-color: #e9ecef;
            border-radius: 5px;
            overflow: hidden;
        }
        .stats-bar .progress-bar {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        }
        .type-badge {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 20px;
            font-weight: bold;
            margin-right: 5px;
            margin-bottom: 5px;
        }
        .ability-badge {
            background-color: #e9ecef;
            padding: 8px 12px;
            border-radius: 5px;
            margin-right: 5px;
            margin-bottom: 5px;
            display: inline-block;
        }
        .ability-hidden {
            background-color: #fff3cd;
            border-left: 3px solid #ffc107;
        }
        .btn-back {
            margin-bottom: 1rem;
        }
        h2 {
            color: #667eea;
            font-weight: bold;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        h2:first-child {
            margin-top: 0;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
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
                        <a class="nav-link" href="{{ route('favorites.index') }}">
                            ❤️ Favoritos
                        </a>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="nav-link btn btn-link" style="border: none; cursor: pointer;">
                                Cerrar Sesión
                            </button>
                        </form>
                    </li>
                    <li class="nav-item">
                        <button id="theme-toggle" class="theme-toggle-btn" onclick="window.darkModeManager.toggle()" title="Cambiar tema">
                            🌙
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (isset($pokemon['error']))
            <div class="alert alert-danger mt-3">
                {{ $pokemon['error'] }}
            </div>
            <a href="{{ route('pokemon.index') }}" class="btn btn-primary mt-3">Volver a Pokédex</a>
        @else
            <div class="detail-container">
                <a href="{{ route('pokemon.index') }}" class="btn btn-secondary btn-back">← Volver</a>

                <div class="row">
                    <div class="col-md-4 text-center">
                        <img src="{{ $pokemon['image'] }}" class="pokemon-image-detail" alt="{{ $pokemon['name'] }}">
                        <h1 class="pokemon-name text-capitalize">{{ $pokemon['name'] }}</h1>
                        <p class="text-muted">#{{ $pokemon['id'] }}</p>

                        @if ($isFavorite)
                            <form method="POST" action="{{ route('favorites.destroy-by-pokemon', ['pokemonId' => $pokemon['id']]) }}" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">
                                    ❤️ Eliminar de Favoritos
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('favorites.store') }}" style="display: inline;">
                                @csrf
                                <input type="hidden" name="pokemon_id" value="{{ $pokemon['id'] }}">
                                <input type="hidden" name="pokemon_name" value="{{ $pokemon['name'] }}">
                                <input type="hidden" name="pokemon_image" value="{{ $pokemon['image'] }}">
                                <button type="submit" class="btn btn-outline-danger">
                                    🤍 Agregar a Favoritos
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="col-md-8">
                        <!-- Tipos -->
                        <h2>Tipos</h2>
                        <div class="mb-3">
                            @foreach ($pokemon['types'] as $type)
                                <span class="type-badge" style="background-color: #667eea; color: white;">
                                    {{ ucfirst($type) }}
                                </span>
                            @endforeach
                        </div>

                        <!-- Dimensiones -->
                        <h2>Dimensiones</h2>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Altura:</strong> {{ $pokemon['height'] }} m</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Peso:</strong> {{ $pokemon['weight'] }} kg</p>
                            </div>
                        </div>

                        <!-- Habilidades -->
                        <h2>Habilidades</h2>
                        <div class="mb-4">
                            @foreach ($pokemon['abilities'] as $ability)
                                <span class="ability-badge @if($ability['is_hidden']) ability-hidden @endif">
                                    {{ ucfirst(str_replace('-', ' ', $ability['name'])) }}
                                    @if($ability['is_hidden'])
                                        <span class="badge bg-warning">Oculta</span>
                                    @endif
                                </span>
                            @endforeach
                        </div>

                        <!-- Estadísticas -->
                        <h2>Estadísticas Base</h2>
                        <div class="row">
                            @foreach ($pokemon['stats'] as $stat)
                                <div class="col-md-6 mb-3">
                                    <p class="mb-1"><strong>{{ ucfirst($stat['name']) }}:</strong> {{ $stat['base_stat'] }}</p>
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar" role="progressbar"
                                            style="width: {{ ($stat['base_stat'] / 255) * 100 }}%;">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
    <script src="{{ asset('js/darkmode.js') }}"></script>
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
