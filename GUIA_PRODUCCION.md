# 🚀 GUÍA DE DESPLIEGUE A PRODUCCIÓN

## Checklist Preproducción

- [ ] Cambiar `APP_DEBUG=false` en `.env`
- [ ] Cambiar `APP_ENV=production` en `.env`
- [ ] Generar nueva `APP_KEY`
- [ ] Usar HTTPS en producción
- [ ] Configurar variable de entorno `DB_CONNECTION=sqlite`
- [ ] Cachear configuración
- [ ] Generar cache de rutas
- [ ] Generar cache de vistas
- [ ] Asegurar permisos de carpetas

---

## Pasos para Despliegue

### 1. Preparar Servidor
```bash
# En el servidor web (Apache/Nginx)
# Apuntar DocumentRoot a /proyecto_servidores/public

# Asegurar permisos
sudo chown -R www-data:www-data /ruta/proyecto_servidores
sudo chmod -R 755 /ruta/proyecto_servidores
sudo chmod -R 775 /ruta/proyecto_servidores/storage
sudo chmod -R 775 /ruta/proyecto_servidores/bootstrap/cache
```

### 2. Preparar Ambiente
```bash
# Clonar repositorio
git clone <tu-repo> proyecto_servidores
cd proyecto_servidores

# Copiar variables de entorno
cp .env.example .env

# Editar .env
nano .env
# Cambiar:
# APP_DEBUG=false
# APP_ENV=production
# APP_URL=https://tudominio.com
# DB_CONNECTION=sqlite
```

### 3. Instalar Dependencias
```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build  # Si tienes assets Vite
```

### 4. Configurar Base de Datos
```bash
# Generar nueva clave
php artisan key:generate

# Ejecutar migraciones
php artisan migrate --force

# Crear datos iniciales
php artisan db:seed
```

### 5. Optimizar Aplicación
```bash
# Cachear configuración
php artisan config:cache

# Cachear rutas
php artisan route:cache

# Cachear vistas (opcional pero recomendado)
php artisan view:cache

# Optimizar autoloader
composer dump-autoload --optimize
```

### 6. Configurar HTTPS
```apache
# Apache .htaccess (agregar en public/.htaccess)
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>
```

### 7. Configurar Web Server

#### Apache
```apache
<VirtualHost *:443>
    ServerName tudominio.com
    DocumentRoot /var/www/proyecto_servidores/public
    
    <Directory /var/www/proyecto_servidores>
        AllowOverride All
        Require all granted
    </Directory>
    
    SSLEngine on
    SSLCertificateFile /ruta/ssl.crt
    SSLCertificateKeyFile /ruta/ssl.key
</VirtualHost>
```

#### Nginx
```nginx
server {
    listen 443 ssl http2;
    server_name tudominio.com;
    
    root /var/www/proyecto_servidores/public;
    index index.php;
    
    ssl_certificate /ruta/ssl.crt;
    ssl_certificate_key /ruta/ssl.key;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 8. Configurar Caché
```bash
# En production, configurar caché en .env:
CACHE_DRIVER=redis  # O memcached, file, database
```

### 9. Configurar Sesiones
```bash
# En .env:
SESSION_DRIVER=cookie  # O database, redis
SESSION_DOMAIN=tudominio.com
SESSION_SECURE_COOKIES=true
```

### 10. Configurar Logs
```bash
# En .env:
LOG_CHANNEL=stack
LOG_LEVEL=warning
```

---

## Monitoreo en Producción

### Ver Logs
```bash
tail -f storage/logs/laravel.log

# Con filtros
tail -f storage/logs/laravel.log | grep ERROR
```

### Healthcheck
```bash
# Crear endpoint de health
php artisan tinker
> route('/health')
```

### Métricas
```php
// Agregar a una ruta de admin
Route::get('/admin/stats', function() {
    return [
        'users' => User::count(),
        'favorites' => Favorite::count(),
        'cache' => Cache::getStore(),
    ];
});
```

---

## Backup y Restore

### Backup de Base de Datos
```bash
# SQLite
cp database/database.sqlite database/database.sqlite.backup.$(date +%Y%m%d)

# Script automático
0 2 * * * cp /ruta/database.sqlite /backups/database.sqlite.$(date +\%Y\%m\%d)
```

### Restore
```bash
cp database/database.sqlite.backup database/database.sqlite
```

---

## Actualizaciones

### Actualizar Código
```bash
git pull origin main

composer install --no-dev --optimize-autoloader
npm install && npm run build

# Limpiar caches
php artisan cache:clear
php artisan view:clear

# Ejecutar migraciones si hay nuevas
php artisan migrate --force
```

### Sin Downtime
```bash
# 1. Poner en mantenimiento
php artisan down --render="errors::503"

# 2. Actualizar
git pull origin main
composer install --no-dev

# 3. Ejecutar migraciones
php artisan migrate --force

# 4. Cachear
php artisan config:cache
php artisan route:cache

# 5. Volver a online
php artisan up
```

---

## Seguridad en Producción

### Variables de Entorno
```bash
# .env debe contener:
APP_DEBUG=false
APP_ENV=production
APP_URL=https://tudominio.com
DB_CONNECTION=sqlite
SESSION_SECURE_COOKIES=true
HTTPS_ONLY=true
```

### Headers de Seguridad
```php
// En app/Http/Middleware/TrustProxies.php
protected $middlewareGroups = [
    'web' => [
        // ... otros middlewares ...
        \Illuminate\Http\Middleware\SetCacheHeaders::class,
    ],
];

// Agregar headers en nginx/apache
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "no-referrer-when-downgrade" always;
```

### Firewall / Rate Limiting
```php
// En routes/api.php o web.php
Route::middleware('throttle:60,1')->group(function () {
    // Rutas rate limitadas
});
```

---

## Performance en Producción

### Optimizar Caché
```php
// En config/cache.php
'default' => env('CACHE_DRIVER', 'redis'),

// Redis es ideal para producción
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Database Connection Pooling
```bash
# Instalar y usar ProxySQL o similar
# Conexiones persistentes a DB
```

### CDN para Assets
```blade
{{-- En vistas --}}
<img src="{{ env('CDN_URL') }}/images/pokemon/{{ $id }}.png" />
```

---

## Checklist Final

- [ ] .env configurado para producción
- [ ] APP_KEY generada
- [ ] Base de datos migrada
- [ ] Caché configurado
- [ ] Sesiones configuradas
- [ ] HTTPS activado
- [ ] Logs monitoreados
- [ ] Backups programados
- [ ] Headers de seguridad
- [ ] Rate limiting activo
- [ ] Errores no muestran detalles
- [ ] Emails configurados (si aplica)

---

## Comandos de Mantenimiento

```bash
# Limpiar todo
php artisan optimize:clear

# Cachear todo
php artisan optimize

# Ver estado de la aplicación
php artisan cache:forget cache_key

# Estadísticas
php artisan tinker
> User::count()
> Favorite::count()
> DB::table('cache')->count()
```

---

## Soporte y Troubleshooting

### Problema: "500 Error"
1. Verificar logs: `tail -f storage/logs/laravel.log`
2. Verificar permisos de carpetas
3. Verificar variables de entorno

### Problema: "No se puede escribir en storage"
```bash
sudo chmod -R 775 storage
sudo chown -R www-data:www-data storage
```

### Problema: "Database error"
1. Verificar que database.sqlite existe y tiene permisos
2. Ejecutar: `php artisan migrate --force`
3. Verificar DB_CONNECTION en .env

### Problema: "Caché corrupto"
```bash
php artisan cache:clear
php artisan cache:flush
php artisan config:cache
```

---

**¡Tu Pokédex está lista para producción! 🚀✨**
