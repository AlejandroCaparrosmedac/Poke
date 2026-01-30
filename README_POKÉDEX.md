# 🎮 POKÉDEX - Guía Rápida de Inicio

## ⚡ Inicio Rápido (Windows)

```bash
# Opción 1: Ejecutar script automático
double-click start.bat

# Opción 2: Manual
cd c:\Users\AlumnoT\Desktop\proyecto_final_servidores\proyecto_servidores
php artisan serve
```

Luego abre: **http://127.0.0.1:8000**

---

## 👤 Credenciales de Prueba

```
Email:     test@example.com
Contraseña: password

O también:

Email:     demo@pokedex.com
Contraseña: demo1234
```

---

## 📋 Características Implementadas

✅ **Autenticación** - Login, registro y logout  
✅ **Pokédex** - Listado paginado de 1000+ Pokémon  
✅ **Detalles** - Información completa de cada Pokémon  
✅ **Favoritos** - Guardar tus Pokémon favoritos  
✅ **Caché** - Respuestas cacheadas por 24 horas  
✅ **Responsive** - Adaptable a todos los dispositivos  
✅ **Seguridad** - CSRF tokens, validaciones, hash de contraseñas  

---

## 🔧 Comandos Útiles

```bash
# Servir la aplicación
php artisan serve

# Ejecutar migraciones
php artisan migrate

# Crear datos de prueba
php artisan db:seed

# Limpiar caché
php artisan cache:clear

# Mostrar rutas
php artisan route:list

# Abrir consola interactiva
php artisan tinker
```

---

## 📁 Estructura Principal

```
projeto_servidores/
├── app/
│   ├── Http/Controllers/       # Controladores (Auth, Pokemon, Favorite)
│   ├── Models/                 # Modelos Eloquent (User, Favorite)
│   └── Services/               # Servicio PokemonService
├── database/
│   ├── migrations/             # Migraciones SQL
│   └── seeders/                # Datos de prueba
├── resources/
│   └── views/                  # Vistas Blade
└── routes/
    └── web.php                 # Definición de rutas
```

---

## 🌐 Rutas de la Aplicación

| Ruta | Descripción |
|------|-------------|
| `/` | Página de inicio |
| `/login` | Iniciar sesión |
| `/register` | Crear cuenta |
| `/pokemon` | Listado de Pokémon |
| `/pokemon/{id}` | Detalles de un Pokémon |
| `/favorites` | Mis favoritos |

---

## 🐛 Solucionar Problemas

### Error: "No existe la base de datos"
```bash
php artisan migrate --force
```

### Error: "APP_KEY not set"
```bash
php artisan key:generate
```

### Caché corrupto
```bash
php artisan cache:flush
php artisan config:cache
```

### Quiero resetear todo
```bash
php artisan migrate:refresh --seed
```

---

## 📚 Documentación Completa

Ver archivo **DOCUMENTACION.md** para detalles técnicos completos.

---

## 🎯 Próximos Pasos

1. Explore la aplicación registrándose
2. Agregue Pokémon a favoritos
3. Vea los detalles completos de cada Pokémon
4. Revise el código en `app/` para entender la arquitectura

---

## 💡 Tips

- La primera carga puede tomar más tiempo (se consulta PokeAPI)
- Las búsquedas posteriores son más rápidas (caché)
- Los favoritos se guardan en la base de datos local
- Puedes agregar/eliminar favoritos desde cualquier vista

---

**¡Que disfrutes capturando Pokémon! 🎮✨**
