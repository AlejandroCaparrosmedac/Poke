# Quick Start Guide - Running the Pokémon Battle System

## Prerequisites

- PHP 8.2+
- Composer
- Node.js 16+
- npm or yarn
- SQLite (built-in to PHP)

## Step 1: Setup Laravel Backend

### 1.1 Navigate to project folder
```bash
cd proyecto_servidores
```

### 1.2 Install dependencies
```bash
composer install
npm install
```

### 1.3 Configure environment
```bash
# Copy example to .env
copy .env.example .env

# Generate app key
php artisan key:generate

# Create SQLite database
touch database/database.sqlite
```

### 1.4 Update .env with Showdown configuration
```env
SHOWDOWN_URL=http://localhost:9000
SHOWDOWN_TIMEOUT=30

# For broadcasting (optional, can use 'log' for development)
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=mt1
```

### 1.5 Run migrations
```bash
php artisan migrate
```

### 1.6 Build frontend assets
```bash
npm run build
```

## Step 2: Setup Pokémon Showdown Microservice

### 2.1 Navigate to Showdown folder
```bash
cd ../pokemon-showdown-master
```

### 2.2 Install dependencies (if not done)
```bash
npm install
```

### 2.3 Set port environment variable
```powershell
# PowerShell
$env:PORT=9000
```

or

```bash
# Bash/WSL
export PORT=9000
```

### 2.4 Start Showdown service
```bash
npm start
```

You should see:
```
Server is running on port 9000
```

## Step 3: Start Laravel Development Server

### 3.1 Open new terminal in project_servidores
```bash
cd proyecto_servidores
```

### 3.2 Start Laravel server
```bash
php artisan serve
```

You should see:
```
Laravel development server started: http://127.0.0.1:8000
```

## Step 4: Verify Everything is Running

### Check Laravel
```bash
curl http://localhost:8000/
```

### Check Showdown
```bash
curl http://localhost:9000/
```

### Check Database
```bash
php artisan tinker
>>> User::count()
=> 0
```

## Step 5: Test the API

### 5.1 Create a user account
```bash
curl -X POST http://localhost:8000/register \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "name=TestPlayer&email=test@example.com&password=password&password_confirmation=password"
```

### 5.2 Login
```bash
curl -X POST http://localhost:8000/login \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "email=test@example.com&password=password"
```

Get your auth token/session from response.

### 5.3 Create a team
```bash
curl -X POST http://localhost:8000/api/teams \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "name": "My First Team",
    "pokemon_data": [
      {
        "name": "Pikachu",
        "level": 50,
        "moves": ["Thunderbolt", "Quick Attack"]
      },
      {
        "name": "Charizard",
        "level": 50,
        "moves": ["Flamethrower", "Dragon Claw"]
      }
    ]
  }'
```

### 5.4 Create a PVP battle
```bash
curl -X POST http://localhost:8000/api/battles/pvp \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "team_id": 1,
    "format": "singles"
  }'
```

## Step 6: Development Workflow

### Running everything concurrently (optional)
```bash
# In proyecto_servidores folder
npm run dev
```

This runs:
- Laravel dev server
- Vite build server
- Queue listener
- Log viewer

### Watch for changes
```bash
npm run dev
```

### Run tests
```bash
php artisan test
```

### Check for errors
```bash
# View Laravel logs
tail -f storage/logs/laravel.log

# Or use pail for real-time logs
php artisan pail
```

## Troubleshooting

### Showdown won't start
```bash
# Check if port 9000 is in use
netstat -ano | findstr :9000

# Kill process using port
taskkill /PID <PID> /F

# Or use different port
$env:PORT=9001
npm start
```

### Laravel migration fails
```bash
# Reset database
php artisan migrate:fresh

# Or rollback and migrate again
php artisan migrate:rollback
php artisan migrate
```

### ShowdownClient connection error
```bash
# Verify Showdown is running
curl http://localhost:9000/

# Check Laravel logs
tail -f storage/logs/laravel.log

# Verify SHOWDOWN_URL in .env
cat .env | grep SHOWDOWN
```

### WebSocket events not working
```bash
# Check BROADCAST_CONNECTION in .env
# For development, use 'log':
BROADCAST_CONNECTION=log

# Check event logs
tail -f storage/logs/laravel.log | grep -i broadcast
```

### Database locked error
```bash
# Delete lock file and restart
rm storage/database.sqlite
touch storage/database.sqlite
php artisan migrate
```

## File Locations

- **Laravel Root**: `proyecto_servidores/`
- **Showdown Root**: `pokemon-showdown-master/`
- **Database**: `proyecto_servidores/database/database.sqlite`
- **Logs**: `proyecto_servidores/storage/logs/laravel.log`
- **Environment**: `proyecto_servidores/.env`
- **API Routes**: `proyecto_servidores/routes/web.php`
- **Models**: `proyecto_servidores/app/Models/`
- **Controllers**: `proyecto_servidores/app/Http/Controllers/`
- **Services**: `proyecto_servidores/app/Services/`

## Common Commands

```bash
# Database
php artisan migrate              # Run migrations
php artisan migrate:fresh        # Reset database
php artisan tinker              # Interactive shell

# Cache & Config
php artisan config:cache        # Cache config
php artisan cache:clear         # Clear cache
php artisan route:list          # List all routes

# Development
php artisan serve               # Start server
php artisan queue:listen        # Start queue worker
php artisan pail                # View logs in real-time

# Testing
php artisan test                # Run tests
php artisan test --filter=TestName  # Run specific test
```

## Next Steps

1. Read `POKEMON_BATTLE_BACKEND.md` for architecture details
2. Read `API_TESTING_GUIDE.md` for API examples
3. Create teams and test battles
4. Implement frontend using the API
5. Configure WebSocket broadcasting for real-time updates

## Support

Check these files for help:
- `IMPLEMENTATION_SUMMARY.md` - What was implemented
- `POKEMON_BATTLE_BACKEND.md` - How it works
- `API_TESTING_GUIDE.md` - How to use the API
- `storage/logs/laravel.log` - Error messages
- `README.md` - Original project info

## Status Indicators

✅ Everything working:
- Laravel server running on http://localhost:8000
- Showdown service running on http://localhost:9000
- Database migrations completed
- Teams can be created
- Battles can be initiated

⚠️ Check if having issues:
- Verify both servers are running
- Check environment variables
- Review error logs
- Ensure ports 8000 and 9000 are not in use
- Verify Showdown microservice is accessible

## Production Deployment

Before deploying to production:

```bash
# Set production environment
APP_ENV=production
APP_DEBUG=false

# Build frontend
npm run build

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Start queue worker (if using queues)
php artisan queue:work --daemon

# Use proper server (Nginx/Apache instead of artisan serve)
# Setup SSL/TLS
# Configure proper broadcasting (Pusher, etc.)
```

Happy battling! 🎮⚡
