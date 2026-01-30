@echo off
REM Script para iniciar la Pokédex en Windows
REM ==================================================

echo.
echo ==================================================
echo    POKÉDEX - Inicialización del Proyecto
echo ==================================================
echo.

REM Verificar si estamos en el directorio correcto
if not exist "artisan" (
    echo ERROR: No estás en el directorio raíz del proyecto
    echo Asegúrate de estar en: c:\Users\AlumnoT\Desktop\proyecto_final_servidores\proyecto_servidores
    pause
    exit /b 1
)

REM Instalar dependencias si no existen
if not exist "vendor" (
    echo.
    echo [1] Instalando dependencias PHP...
    call composer install
    if %ERRORLEVEL% neq 0 (
        echo ERROR: Fallo la instalación de Composer
        pause
        exit /b 1
    )
)

REM Generar clave de aplicación si no existe
for /f "tokens=*" %%i in ('findstr /c:"APP_KEY=" .env') do set KEY_EXISTS=true

if not defined KEY_EXISTS (
    echo.
    echo [2] Generando clave de aplicación...
    call php artisan key:generate
    if %ERRORLEVEL% neq 0 (
        echo ERROR: Fallo generar la clave
        pause
        exit /b 1
    )
)

REM Ejecutar migraciones
echo.
echo [3] Ejecutando migraciones...
call php artisan migrate --force
if %ERRORLEVEL% neq 0 (
    echo ERROR: Fallo en las migraciones
    pause
    exit /b 1
)

REM Limpiar caché
echo.
echo [4] Limpiando caché...
call php artisan cache:clear
call php artisan config:cache

REM Iniciar servidor
echo.
echo ==================================================
echo    Servidor iniciado correctamente
echo ==================================================
echo.
echo URL: http://127.0.0.1:8000
echo.
echo Credenciales de prueba:
echo   Email: test@example.com
echo   Contraseña: password
echo.
echo Presiona Ctrl+C para detener el servidor
echo ==================================================
echo.

php artisan serve
