# Task Scheduling & Notification Platform

A lightweight internal Task Scheduling Tool built with Laravel and Vue.js (Inertia.js) where managers can create, assign, and track tasks with user availability management.

## Requirements

**For Docker setup:**
- Docker Desktop for Windows/Mac or Docker Engine for Linux
- Docker Compose plugin (comes with Docker Desktop)
- **Note**: Docker setup is optional. You can run the app locally without Docker.

**For local setup:**
- PHP >= 8.2
- Composer
- Node.js >= 18
- npm or yarn
- MySQL

## Installation

### Option 1: Docker (Recommended)

1. Clone the repository:
```bash
git clone https://github.com/razinabrahamyan/deluxe.git
cd deluxe
```

2. Copy environment file:
```bash
cp .docker.env.example .env
```

3. Build and start containers:
```bash
docker compose up -d --build
```

4. Install dependencies and setup:
```bash
docker compose exec php-cli composer install
docker compose exec php-cli php artisan key:generate
docker compose exec php-cli php artisan migrate:fresh --seed
```

5. Build frontend assets:
```bash
docker compose exec php-cli npm ci
docker compose exec php-cli npm run build
```

Visit `http://localhost:8000` in your browser.

**Docker commands:**
```bash
# Stop containers
docker compose down

# View logs
docker compose logs -f

# Restart services
docker compose restart

# Run artisan commands
docker compose exec php-cli php artisan [command]

# Access MySQL
docker compose exec mysql mysql -u taskuser -ppassword task_manager
```

For more Docker details, see [DOCKER.md](DOCKER.md).

### Option 2: Local Development

1. Clone the repository:
```bash
git clone <repository-url>
cd deluxe-task
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install JavaScript dependencies:
```bash
npm install
```

4. Configure environment:
```bash
cp .env.example .env
php artisan key:generate
```

5. Configure database in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_manager
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

6. Run migrations and seeders:
```bash
php artisan migrate:fresh --seed
```

7. Build assets:
```bash
npm run build
```

8. Start the development server:
```bash
# Terminal 1: Laravel server
php artisan serve

# Terminal 2: Vite dev server
npm run dev

# Terminal 3: Reverb WebSocket server (for real-time notifications)
php artisan reverb:start

# Terminal 4: Queue worker (for background jobs)
php artisan queue:work
```

Visit `http://localhost:8000` in your browser.

## Admin Credentials

**Email**: admin@example.com  
**Password**: password

### Additional Test Users

All users have the password: `password`

- user1@example.com (user1)
- user2@example.com (user2)
- user3@example.com (user3)
- user4@example.com (user4)
- user5@example.com (user5)

