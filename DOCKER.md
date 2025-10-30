# Docker Setup Guide

This project is fully containerized with Docker Compose for easy deployment and development.

## Architecture

The Docker setup consists of:
- **nginx**: Web server (port 8000)
- **app**: PHP-FPM application server
- **php-cli**: PHP CLI for running artisan commands
- **mysql**: MySQL 8.0 database (port 3307)
- **reverb**: Laravel Reverb WebSocket server (port 8080)
- **queue**: Laravel queue worker for background jobs

## Quick Start

1. Copy environment file:
```bash
cp .docker.env.example .env
```

2. Build and start all containers:
```bash
docker compose up -d --build
```

3. Install dependencies:
```bash
docker compose exec php-cli composer install
```

4. Generate application key:
```bash
docker compose exec php-cli php artisan key:generate
```

5. Run migrations and seeders:
```bash
docker compose exec php-cli php artisan migrate:fresh --seed
```

6. Build frontend assets:
```bash
docker compose exec php-cli npm ci
docker compose exec php-cli npm run build
```

7. Visit http://localhost:8000

## Useful Commands

### View logs
```bash
# All services
docker compose logs -f

# Specific service
docker compose logs -f app
docker compose logs -f reverb
docker compose logs -f queue
```

### Stop/Start
```bash
# Stop all containers
docker compose down

# Start all containers
docker compose up -d

# Restart specific service
docker compose restart reverb
```

### Run Artisan Commands
```bash
docker compose exec php-cli php artisan [command]
```

### Access MySQL
```bash
docker compose exec mysql mysql -u taskuser -ppassword task_manager
```

### Access Container Shell
```bash
docker compose exec php-cli bash
```

### Rebuild After Changes
```bash
# Rebuild specific service
docker compose up -d --build app

# Rebuild all services
docker compose up -d --build
```

## Development Workflow

### Hot Reloading

For development with hot module replacement:
1. Keep `docker compose up` running
2. In another terminal:
```bash
docker compose exec php-cli npm run dev
```

This will enable Vite HMR for Vue components.

### Database Management

Reset database:
```bash
docker compose exec php-cli php artisan migrate:fresh --seed
```

### Queue Worker

The queue worker runs automatically in the `queue` container. To restart it:
```bash
docker compose restart queue
```

### Reverb WebSocket Server

The Reverb server runs automatically in the `reverb` container. To restart it:
```bash
docker compose restart reverb
```

## Environment Variables

All environment variables are configured in `.env`. Key variables for Docker:

```env
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=task_manager
DB_USERNAME=taskuser
DB_PASSWORD=password

REVERB_HOST=reverb
REVERB_PORT=8080
```

## Troubleshooting

### Containers won't start
```bash
# Check logs
docker compose logs

# Rebuild from scratch
docker compose down -v
docker compose up -d --build
```

### Permission issues
```bash
docker compose exec php-cli chown -R www-data:www-data /var/www/storage
docker compose exec php-cli chmod -R 755 /var/www/storage
```

### Clear all caches
```bash
docker compose exec php-cli php artisan config:clear
docker compose exec php-cli php artisan cache:clear
docker compose exec php-cli php artisan view:clear
docker compose exec php-cli php artisan route:clear
```

### Database connection issues
```bash
# Check if MySQL is running
docker compose ps mysql

# Restart MySQL
docker compose restart mysql
```

## Production Deployment

For production, update `.env` with production values and use:
```bash
docker compose -f docker-compose.yml up -d
```

Consider:
- Using environment-specific configuration
- Setting up SSL certificates
- Configuring proper backups
- Using a production-ready PHP-FPM configuration
- Implementing proper logging and monitoring

