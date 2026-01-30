# 📚 EJEMPLOS DE CÓDIGO - POKÉDEX LARAVEL

## Índice
1. [Autenticación](#autenticación)
2. [Servicio PokeAPI](#servicio-pokeapi)
3. [Controladores](#controladores)
4. [Modelos](#modelos)
5. [Rutas](#rutas)
6. [Vistas](#vistas)
7. [Validaciones](#validaciones)
8. [Caché](#caché)

---

## AUTENTICACIÓN

### Formulario de Login
```blade
<form method="POST" action="{{ route('login') }}">
    @csrf
    <input type="email" name="email" value="{{ old('email') }}" required>
    <input type="password" name="password" required>
    <label>
        <input type="checkbox" name="remember"> Recuérdame
    </label>
    <button type="submit">Iniciar Sesión</button>
</form>
```

### Controlador de Login
```php
public function login(Request $request): RedirectResponse
{
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();
        return redirect()->intended('pokemon');
    }

    return back()->withInput($request->only('email'));
}
```

---

## SERVICIO POKEAPI

### Obtener Lista Paginada
```php
$pokemonService = new PokemonService();
$result = $pokemonService->getPokemonList($page = 1, $limit = 20);

// Devuelve:
// [
//     'pokemon' => [...],
//     'total' => 1025,
//     'current_page' => 1,
//     'per_page' => 20,
//     'last_page' => 52
// ]
```

### Obtener Detalles
```php
$pokemon = $pokemonService->getPokemonDetail(1); // 'bulbasaur' o 1

// Devuelve:
// [
//     'id' => 1,
//     'name' => 'bulbasaur',
//     'image' => 'url...',
//     'types' => ['grass', 'poison'],
//     'abilities' => [...],
//     'stats' => [...],
//     'height' => 0.7,
//     'weight' => 6.9
// ]
```

### Con Caché
```php
// Automáticamente cacheado por 24 horas
$pokemon = $pokemonService->getPokemonDetail('charizard');

// Cache key: pokemon_detail_charizard
// El siguiente request usa el caché, no la API
```

---

## CONTROLADORES

### PokemonController - Listado
```php
public function index(): View
{
    $page = request('page', 1);
    $result = $this->pokemonService->getPokemonList($page, 20);

    $favoriteIds = auth()->user()->favorites()->pluck('pokemon_id')->toArray();

    return view('pokemon.index', [
        'pokemon' => $result['pokemon'],
        'favoriteIds' => $favoriteIds,
        // ... pagination data
    ]);
}
```

### PokemonController - Detalles
```php
public function show($id): View
{
    $pokemon = $this->pokemonService->getPokemonDetail($id);
    $isFavorite = auth()->user()->isFavorite($id);

    return view('pokemon.show', [
        'pokemon' => $pokemon,
        'isFavorite' => $isFavorite,
    ]);
}
```

### FavoriteController - Agregar
```php
public function store(): RedirectResponse
{
    $pokemonId = request('pokemon_id');
    
    $exists = auth()->user()->favorites()
        ->where('pokemon_id', $pokemonId)
        ->exists();

    if ($exists) {
        return back()->with('warning', 'Ya está en favoritos');
    }

    Favorite::create([
        'user_id' => auth()->id(),
        'pokemon_id' => $pokemonId,
        'pokemon_name' => request('pokemon_name'),
        'pokemon_image' => request('pokemon_image'),
    ]);

    return back()->with('success', 'Agregado a favoritos');
}
```

### FavoriteController - Eliminar
```php
public function destroy(Favorite $favorite): RedirectResponse
{
    if ($favorite->user_id !== auth()->id()) {
        return back()->with('error', 'No autorizado');
    }

    $favorite->delete();
    return back()->with('success', 'Eliminado de favoritos');
}
```

---

## MODELOS

### Modelo User con Relación
```php
class User extends Authenticatable
{
    // ... propiedades ...

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function isFavorite($pokemonId): bool
    {
        return $this->favorites()->where('pokemon_id', $pokemonId)->exists();
    }
}

// Uso:
$user = Auth::user();
$favorites = $user->favorites()->get();
$isFavorite = $user->isFavorite(25); // Pikachu
```

### Modelo Favorite
```php
class Favorite extends Model
{
    protected $fillable = [
        'user_id',
        'pokemon_id',
        'pokemon_name',
        'pokemon_image',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

// Uso:
$favorite = Favorite::find(1);
$user = $favorite->user;
```

---

## RUTAS

### Rutas Públicas (Guest)
```php
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});
```

### Rutas Protegidas (Auth)
```php
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    Route::get('/pokemon', [PokemonController::class, 'index'])->name('pokemon.index');
    Route::get('/pokemon/{id}', [PokemonController::class, 'show'])->name('pokemon.show');
    
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites', [FavoriteController::class, 'store'])->name('favorites.store');
    Route::delete('/favorites/{favorite}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');
});
```

### Usando en Vistas
```blade
<!-- Link a ruta -->
<a href="{{ route('pokemon.index') }}">Pokédex</a>

<!-- Enviar a ruta con método POST -->
<form action="{{ route('favorites.store') }}" method="POST">
    @csrf
    <input type="hidden" name="pokemon_id" value="{{ $pokemon['id'] }}">
    <button type="submit">Agregar a Favoritos</button>
</form>

<!-- Enviar DELETE -->
<form action="{{ route('favorites.destroy', $favorite->id) }}" method="POST">
    @csrf
    @method('DELETE')
    <button type="submit">Eliminar</button>
</form>
```

---

## VISTAS

### Navbar
```blade
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('pokemon.index') }}">🎮 POKÉDEX</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="{{ route('pokemon.index') }}">Pokémon</a>
            <a class="nav-link" href="{{ route('favorites.index') }}">❤️ Favoritos</a>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="nav-link btn btn-link">Cerrar Sesión</button>
            </form>
        </div>
    </div>
</nav>
```

### Card de Pokémon
```blade
<div class="card pokemon-card">
    <button class="favorite-btn @if(in_array($p['id'], $favoriteIds)) favorited @endif" 
        data-pokemon-id="{{ $p['id'] }}"
        data-pokemon-name="{{ $p['name'] }}"
        data-pokemon-image="{{ $p['image'] }}">
        ❤️
    </button>
    <img src="{{ $p['image'] }}" class="pokemon-image" alt="{{ $p['name'] }}">
    <div class="card-body">
        <h5 class="card-title">{{ $p['name'] }}</h5>
        <p class="card-text text-muted">#{{ $p['id'] }}</p>
        <a href="{{ route('pokemon.show', ['id' => $p['id']]) }}" class="btn btn-sm btn-primary">
            Ver Detalles
        </a>
    </div>
</div>
```

### Paginación
```blade
<nav aria-label="Paginación">
    <ul class="pagination">
        @if ($current_page > 1)
            <li class="page-item">
                <a class="page-link" href="{{ route('pokemon.index', ['page' => $current_page - 1]) }}">
                    Anterior
                </a>
            </li>
        @endif

        @for ($i = 1; $i <= $last_page && $i <= 5; $i++)
            <li class="page-item @if($i == $current_page) active @endif">
                <a class="page-link" href="{{ route('pokemon.index', ['page' => $i]) }}">
                    {{ $i }}
                </a>
            </li>
        @endfor

        @if ($current_page < $last_page)
            <li class="page-item">
                <a class="page-link" href="{{ route('pokemon.index', ['page' => $current_page + 1]) }}">
                    Siguiente
                </a>
            </li>
        @endif
    </ul>
</nav>
```

### Estadísticas con Barras
```blade
@foreach ($pokemon['stats'] as $stat)
    <div class="mb-3">
        <p><strong>{{ ucfirst($stat['name']) }}:</strong> {{ $stat['base_stat'] }}</p>
        <div class="progress" style="height: 5px;">
            <div class="progress-bar" style="width: {{ ($stat['base_stat'] / 255) * 100 }}%;">
            </div>
        </div>
    </div>
@endforeach
```

---

## VALIDACIONES

### En Controlador
```php
// Login
$validated = $request->validate([
    'email' => ['required', 'email'],
    'password' => ['required'],
]);

// Registro
$validated = $request->validate([
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
    'password' => ['required', 'string', 'min:8', 'confirmed'],
]);
```

### En Formulario (con Errores)
```blade
<div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email" class="form-control @error('email') is-invalid @enderror" 
        id="email" name="email" value="{{ old('email') }}" required>
    @error('email')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>
```

---

## CACHÉ

### En Servicio (Automático)
```php
public function getPokemonList($page = 1, $limit = 20)
{
    $cacheKey = "pokemon_list_{$offset}_{$limit}";

    return Cache::remember($cacheKey, self::CACHE_MINUTES, function () use ($offset, $limit) {
        // Primera vez: consulta a API
        // Siguientes veces: devuelve el caché
        return $this->fetchFromAPI();
    });
}
```

### Limpiar Caché Manual
```php
// En controlador o ruta
php artisan cache:clear
php artisan cache:flush
```

### Verificar en Tinker
```bash
php artisan tinker
> Cache::has('pokemon_list_0_20')
> Cache::get('pokemon_list_0_20')
> Cache::forget('pokemon_list_0_20')
```

---

## CONSULTAS A BASE DE DATOS

### Obtener Favoritos del Usuario
```php
$favorites = auth()->user()->favorites()->paginate(20);
// SELECT * FROM favorites WHERE user_id = ? LIMIT 20 OFFSET 0

$favoriteIds = auth()->user()->favorites()->pluck('pokemon_id')->toArray();
// [1, 4, 7, 25, ...] - Solo IDs para búsqueda rápida

$isFavorite = auth()->user()->isFavorite(25);
// SELECT COUNT(*) FROM favorites WHERE user_id = ? AND pokemon_id = 25
```

### Verificar Duplicados
```php
$exists = auth()->user()->favorites()
    ->where('pokemon_id', $pokemonId)
    ->exists();
```

---

## DEBUGGING

### Con Tinker
```bash
php artisan tinker

> User::first()
> auth()->user()->favorites
> Cache::get('pokemon_list_0_20')
> Favorite::all()
> Auth::loginUsingId(1)
```

### Ver Logs
```bash
tail -f storage/logs/laravel.log
```

### Queries SQL
```php
// En config/database.php
'log_queries' => true

// O dinámicamente:
DB::enableQueryLog();
// ... hacer consultas ...
dd(DB::getQueryLog());
```

---

## EJEMPLO COMPLETO: AGREGAR A FAVORITOS

### 1. Vista (Formulario)
```blade
<form method="POST" action="{{ route('favorites.store') }}">
    @csrf
    <input type="hidden" name="pokemon_id" value="{{ $pokemon['id'] }}">
    <input type="hidden" name="pokemon_name" value="{{ $pokemon['name'] }}">
    <input type="hidden" name="pokemon_image" value="{{ $pokemon['image'] }}">
    <button type="submit">Agregar a Favoritos</button>
</form>
```

### 2. Ruta
```php
Route::post('/favorites', [FavoriteController::class, 'store'])->name('favorites.store');
```

### 3. Controlador
```php
public function store(): RedirectResponse
{
    $exists = auth()->user()->favorites()
        ->where('pokemon_id', request('pokemon_id'))
        ->exists();

    if ($exists) {
        return back()->with('warning', 'Ya está en favoritos');
    }

    Favorite::create([
        'user_id' => auth()->id(),
        'pokemon_id' => request('pokemon_id'),
        'pokemon_name' => request('pokemon_name'),
        'pokemon_image' => request('pokemon_image'),
    ]);

    return back()->with('success', 'Agregado a favoritos');
}
```

### 4. Resultado en BD
```sql
INSERT INTO favorites (user_id, pokemon_id, pokemon_name, pokemon_image, created_at, updated_at)
VALUES (1, 25, 'pikachu', 'url...', NOW(), NOW());
```

---

## EJEMPLO COMPLETO: MOSTRAR DETALLES

### 1. Ruta
```php
Route::get('/pokemon/{id}', [PokemonController::class, 'show'])->name('pokemon.show');
```

### 2. Controlador
```php
public function show($id): View
{
    $pokemon = $this->pokemonService->getPokemonDetail($id);
    
    if (isset($pokemon['error'])) {
        return back()->with('error', $pokemon['error']);
    }

    $isFavorite = auth()->user()->isFavorite($id);

    return view('pokemon.show', [
        'pokemon' => $pokemon,
        'isFavorite' => $isFavorite,
    ]);
}
```

### 3. Vista
```blade
<div class="detail-container">
    <h1>{{ $pokemon['name'] }}</h1>
    
    <img src="{{ $pokemon['image'] }}" class="pokemon-image">
    
    <div class="tipos">
        @foreach ($pokemon['types'] as $type)
            <span class="badge">{{ $type }}</span>
        @endforeach
    </div>
    
    <div class="estadisticas">
        @foreach ($pokemon['stats'] as $stat)
            <div>{{ $stat['name'] }}: {{ $stat['base_stat'] }}</div>
        @endforeach
    </div>
</div>
```

---

**Fin de ejemplos de código**
