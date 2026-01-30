<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pokédex - Inicio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/darkmode.css') }}" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        body.dark-mode {
            background: linear-gradient(135deg, #2d2d5f 0%, #3d2d4a 100%);
        }
        .welcome-container {
            text-align: center;
            color: white;
        }
        .welcome-container h1 {
            font-size: 4rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            margin-bottom: 1rem;
        }
        .welcome-container p {
            font-size: 1.5rem;
            margin-bottom: 2rem;
        }
        .btn-custom {
            padding: 12px 30px;
            font-size: 1.1rem;
            margin: 0 10px;
        }
        .theme-toggle-welcome {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s;
        }
        .theme-toggle-welcome:hover {
            transform: scale(1.1);
        }
        body.dark-mode .theme-toggle-welcome {
            background: #2d2d2d;
            color: white;
        }
    </style>
</head>
<body>
    <button id="theme-toggle" class="theme-toggle-welcome" onclick="window.darkModeManager.toggle()" title="Cambiar tema">
        🌙
    </button>

    <div class="welcome-container">
        <h1>🎮 POKEDEX</h1>
        <p>Descubre y colecciona todos los Pokémon</p>
        <div>
            <a href="{{ route('login') }}" class="btn btn-light btn-custom">Iniciar Sesión</a>
            <a href="{{ route('register') }}" class="btn btn-warning btn-custom">Registrarse</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/darkmode.js') }}"></script>
</body>
</html>
