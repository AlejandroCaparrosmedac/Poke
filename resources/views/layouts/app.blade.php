<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Pokédex')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Dark Mode CSS -->
    <link href="{{ asset('css/darkmode.css') }}" rel="stylesheet">

    <style>
        :root {
            --primary-color: #007bff;
            --dark-bg: #1a1a1a;
            --dark-text: #e0e0e0;
        }

        body.dark-mode {
            background-color: var(--dark-bg);
            color: var(--dark-text);
        }

        body.dark-mode .card {
            background-color: #2d2d2d;
            border-color: #444;
        }

        body.dark-mode .btn-primary {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        body.dark-mode .form-control,
        body.dark-mode .form-select {
            background-color: #2d2d2d;
            color: var(--dark-text);
            border-color: #444;
        }

        body.dark-mode .text-muted {
            color: #999 !important;
        }

        body.dark-mode .list-group-item {
            background-color: #2d2d2d;
            border-color: #444;
            color: var(--dark-text);
        }

        body.dark-mode .list-group-item.active {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        nav.navbar {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        body.dark-mode nav.navbar {
            background-color: #2d2d2d !important;
            border-bottom: 1px solid #444;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('pokemon.index') }}">
                <i class="fas fa-chess-pawn"></i> Pokédex
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('pokemon.index') }}">
                                <i class="fas fa-book"></i> Pokédex
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('favorites.index') }}">
                                <i class="fas fa-heart"></i> Favoritos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('battles.index') }}">
                                <i class="fas fa-fire"></i> Batallas
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i> {{ auth()->user()->name }}
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="userDropdown">
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt"></i> Salir
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link btn btn-link" onclick="window.darkModeManager.toggle()" title="Cambiar tema">
                                <i class="fas fa-moon"></i>
                            </button>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">
                                <i class="fas fa-user-plus"></i> Registrarse
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="py-4">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-light border-top mt-5 py-3">
        <div class="container text-center small text-muted">
            <p>&copy; {{ date('Y') }} Pokédex con Pokémon Showdown. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <!-- Dark Mode JS -->
    <script src="{{ asset('js/darkmode.js') }}"></script>
    @stack('scripts')
</body>
</html>
