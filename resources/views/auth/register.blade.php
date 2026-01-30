<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Registrarse - Pokédex</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/darkmode.css') }}" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        body.dark-mode {
            background: linear-gradient(135deg, #2d2d5f 0%, #3d2d4a 100%);
        }
        .register-card {
            width: 100%;
            max-width: 400px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
        }
        .register-card .card-header {
            background-color: #667eea;
            color: white;
            border-radius: 10px 10px 0 0;
            text-align: center;
            padding: 1.5rem;
        }
        body.dark-mode .register-card .card-header {
            background-color: #2d2d5f;
        }
        .register-card h3 {
            margin: 0;
            font-weight: bold;
        }
        .btn-register {
            background-color: #667eea;
            border: none;
            padding: 10px;
            font-weight: bold;
        }
        .btn-register:hover {
            background-color: #764ba2;
        }
        .login-link {
            text-align: center;
            margin-top: 1rem;
        }
        .login-link a {
            color: #667eea;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        .theme-toggle-register {
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
        .theme-toggle-register:hover {
            transform: scale(1.1);
        }
        body.dark-mode .theme-toggle-register {
            background: #2d2d2d;
            color: white;
        }
    </style>
</head>
<body>
    <button id="theme-toggle" class="theme-toggle-register" onclick="window.darkModeManager.toggle()" title="Cambiar tema">
        🌙
    </button>

    <div class="register-card card">
        <div class="card-header">
            <h3>🎮 POKÉDEX</h3>
            <p class="mb-0">Crea tu cuenta</p>
        </div>
        <div class="card-body p-4">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Nombre</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                        id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                        id="email" name="email" value="{{ old('email') }}" required>
                    @error('email')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                        id="password" name="password" required>
                    @error('password')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                    <input type="password" class="form-control" id="password_confirmation"
                        name="password_confirmation" required>
                </div>

                <button type="submit" class="btn btn-register btn-primary w-100 text-white">Registrarse</button>
            </form>

            <div class="login-link">
                ¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/darkmode.js') }}"></script>
</body>
</html>
