# Task Scheduling & Notification Platform

A lightweight internal Task Scheduling Tool built with Laravel and Vue.js (Inertia.js) where managers can create, assign, and track tasks with user availability management.

## Features

### Backend (Laravel)
- **Task Management**: Full CRUD operations for tasks
- **User Availability**: Strict no-overlap rule enforcement
- **Search & Filter**: Search tasks by title/description, filter by status and assignee
- **Background Jobs**: Asynchronous user availability updates
- **RESTful API**: Clean API endpoints for all operations
- **Role-based Access**: Admin and User roles with different permissions
- **Real-time Notifications**: WebSocket notifications via Laravel Echo and Laravel Reverb
- **Event-driven Architecture**: Task assignment events with listeners

### Frontend (Vue.js + Inertia.js)
- **Modern UI**: Responsive design with Tailwind CSS and Shadcn Vue components
- **Task Dashboard**: List view with mobile-friendly filters
- **Real-time Validation**: User overlap detection with error messages
- **Task Management**: Modal-based create/edit forms with validation
- **Authentication**: Secure login system
- **Real-time Toasts**: WebSocket-powered notification toast system
- **SPA Navigation**: Seamless single-page application experience

## Tech Stack

- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Vue.js 3 with TypeScript
- **Framework**: Inertia.js (SPA)
- **Styling**: Tailwind CSS v4 with Shadcn Vue components
- **Database**: MySQL
- **WebSockets**: Laravel Reverb (with fallback to Pusher)
- **Queue System**: Laravel Queues for background jobs
- **Real-time**: Laravel Echo for WebSocket client

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

**Note**: Admin can create/edit/delete tasks and see all tasks. Regular users can only see their own assigned tasks.

## Database Schema

### Tables
- **users**: User accounts
- **task_statuses**: Task status types (To Do, In Progress, Completed, Cancelled)
- **tasks**: Task records with title, description, dates, assignee, and status
- **user_availabilities**: Availability records for date-range tracking

### Indexes
The database includes optimized indexes for:
- Task search (title, description)
- User-date range queries
- Status filtering

## Features Implementation

### User Availability Validation
- A user cannot be assigned overlapping tasks
- Validation checks for any date range overlap (including partial overlaps)
- Error messages display when assignment conflicts occur
- Validation happens on both create and update operations

### Background Jobs
- `UpdateUserAvailability` job runs asynchronously after task creation/update
- Generates availability records for each day in the task period
- Handles conflicts by checking existing availabilities

### Search & Filtering
- Search tasks by title or description (full-text search)
- Filter by task status
- Filter by assigned user
- Pagination support (20 tasks per page)

## API Endpoints

### Authentication
- `GET /login` - Show login form
- `POST /login` - Authenticate user
- `POST /logout` - Logout user

### Tasks
- `GET /dashboard` - List all tasks (with filters, role-based)
- `POST /tasks` - Create new task (admin only)
- `PUT /tasks/{task}` - Update task (admin only)
- `DELETE /tasks/{task}` - Delete task (admin only)

### User Availability
- `GET /availabilities` - Get user availability for date range

## Development

### Running Tests
```bash
php artisan test
```

### Building for Production
```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Queue Worker
To process background jobs:
```bash
php artisan queue:work
```

## Trade-offs & Assumptions

### Assumptions Made
1. **Single assignment**: Each task is assigned to exactly one user
2. **Daily availability**: Availability is tracked at the day level (not hour/minute level)
3. **Strict overlap rule**: Any overlap in date ranges is rejected (no partial conflicts allowed)
4. **Manager access**: All authenticated users can create and manage all tasks

### Trade-offs
1. **Database indexes**: Added for search performance but increase write overhead
2. **Synchronous validation**: Immediate overlap detection on create/update (alternative: async validation)
3. **Simple UI**: Focused on core functionality over advanced features like drag-and-drop Kanban
4. **Real-time notifications**: Implemented with Laravel Reverb (self-hosted WebSocket server)

### Role-based Access
- **Admin**: Can create, edit, delete tasks and see all tasks
- **User**: Can only view their own assigned tasks

### Real-time Notifications Setup

**Note**: The app works fine without real-time notifications. They are optional.

#### Laravel Reverb (Recommended - Self-hosted, Free)

Reverb is Laravel's official WebSocket server and requires no external services:

1. Reverb is already installed via composer
2. Add to your `.env` file:

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=local
REVERB_APP_KEY=local
REVERB_APP_SECRET=local
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
```

3. Start the Reverb server:
```bash
php artisan reverb:start
```

4. In another terminal, start the queue worker:
```bash
php artisan queue:work
```

**Important**: Make sure `REVERB_SCHEME=http` for local development (not https).

#### Alternative: Pusher (Hosted Service)

If you prefer a hosted WebSocket service instead of self-hosted Reverb:

1. Sign up for a free Pusher account at https://pusher.com (free tier includes 200k messages/day)
2. Create a new Pusher app at https://dashboard.pusher.com/get-started
3. Update your `.env`:

```env
BROADCAST_CONNECTION=pusher

PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

The app will automatically detect and use Pusher if configured.

**Without WebSockets**: Leave all broadcasting variables empty in `.env` and the app will work without real-time notifications.

### Future Enhancements
- Advanced filtering (date range, multiple assignees)
- Task comments and attachments
- Calendar view for tasks
- Email notifications alongside WebSocket notifications
- Multi-language support (i18n)
- Kanban board view
- Task priorities and labels
- Docker-based development environment optimization

## License

This project is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Contact

For questions or support, please open an issue in the repository.
