# Task Manager API

A RESTful Task Management API built with **Laravel 11**, **PostgreSQL**, and **Nginx** — fully containerised with **Docker Compose**.

---

## Tech Stack

| Layer      | Technology              |
|------------|-------------------------|
| Framework  | Laravel 11 (PHP 8.3)    |
| Database   | PostgreSQL 16           |
| Web Server | Nginx 1.25 (Alpine)     |
| Runtime    | PHP-FPM 8.3 (Alpine)    |
| Container  | Docker + Docker Compose |

---

## Project Structure

```
task-manager-api/
├── docker/
│   ├── nginx/default.conf          # Nginx virtual host
│   └── php/
│       ├── Dockerfile              # PHP 8.3-fpm image
│       └── local.ini               # PHP config overrides
├── src/                            # Laravel application
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/TaskController.php
│   │   │   └── Requests/
│   │   │       ├── CreateTaskRequest.php
│   │   │       └── UpdateTaskStatusRequest.php
│   │   └── Models/Task.php
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   └── routes/api.php
├── postman/                        # Postman collection
├── docker-compose.yml
└── README.md
```

---

## Local Setup (Docker)

### Prerequisites
- Docker Desktop installed and running
- Git

### Steps

**1. Clone / enter the project**
```bash
cd task-manager-api
```

**2. Install Laravel inside the container**
```bash
# Start containers first
docker compose up -d --build

# Install Composer dependencies
docker compose exec app composer install

# Generate app key
docker compose exec app php artisan key:generate

# Run migrations
docker compose exec app php artisan migrate

# (Optional) Seed demo data
docker compose exec app php artisan db:seed
```

**3. The API is live at:**
```
http://localhost:8080/api/tasks
```

---

## API Endpoints

### Base URL
```
http://localhost:8080
```

### 1. Create Task
```http
POST /api/tasks
Content-Type: application/json

{
  "title": "Fix production bug",
  "due_date": "2026-04-10",
  "priority": "high"
}
```
**Rules:**
- `title` + `due_date` combination must be unique
- `due_date` must be today or future
- `priority` must be `low`, `medium`, or `high`
- Status defaults to `pending`

---

### 2. List Tasks
```http
GET /api/tasks
GET /api/tasks?status=pending
GET /api/tasks?status=in_progress
GET /api/tasks?status=done
```
Sorted by priority (high → low), then due_date ascending.

---

### 3. Update Task Status
```http
PATCH /api/tasks/{id}/status
Content-Type: application/json

{
  "status": "in_progress"
}
```
**Status flow:** `pending` → `in_progress` → `done`  
Cannot skip or revert.

---

### 4. Delete Task
```http
DELETE /api/tasks/{id}
```
Only tasks with status `done` can be deleted. Returns `403` otherwise.

---

### 5. Daily Report (Bonus)
```http
GET /api/tasks/report?date=2026-04-10
```
**Response:**
```json
{
  "success": true,
  "date": "2026-04-10",
  "summary": {
    "high":   { "pending": 2, "in_progress": 1, "done": 0 },
    "medium": { "pending": 1, "in_progress": 0, "done": 3 },
    "low":    { "pending": 0, "in_progress": 0, "done": 1 }
  }
}
```

---

## Postman Collection

Import `postman/Task_Manager_API.postman_collection.json` into Postman.  
The collection includes all 12 test cases covering happy paths and all business rule violations.

---

## Deployment (Railway)

1. Push your code to GitHub
2. Create a new project on [Railway](https://railway.app)
3. Add a **PostgreSQL** plugin
4. Set the following environment variables from Railway's PostgreSQL plugin:
   ```
   DB_CONNECTION=pgsql
   DB_HOST=<railway-postgres-host>
   DB_PORT=5432
   DB_DATABASE=<db-name>
   DB_USERNAME=<username>
   DB_PASSWORD=<password>
   APP_KEY=<generate with: php artisan key:generate --show>
   ```
5. Set the start command:
   ```bash
   php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT
   ```

---

## Useful Docker Commands

```bash
# Start all services
docker compose up -d

# Stop all services
docker compose down

# View logs
docker compose logs -f app

# Run artisan commands
docker compose exec app php artisan <command>

# Access PostgreSQL shell
docker compose exec postgres psql -U task_user -d task_manager

# Fresh migration + seed
docker compose exec app php artisan migrate:fresh --seed
```

---

## Database

**Engine:** PostgreSQL 16  
**Table:** `tasks`

| Column      | Type         | Notes                          |
|-------------|--------------|--------------------------------|
| id          | bigint       | Primary key, auto-increment    |
| title       | varchar(255) | Task title                     |
| due_date    | date         | Deadline (today or future)     |
| priority    | enum         | low, medium, high              |
| status      | enum         | pending, in_progress, done     |
| created_at  | timestamp    | Auto-managed by Laravel        |
| updated_at  | timestamp    | Auto-managed by Laravel        |

**Unique constraint:** `(title, due_date)` — prevents duplicate task titles on the same day.

---

## Business Rules Summary

| Rule | Endpoint | Behaviour |
|------|----------|-----------|
| Unique title per due_date | POST /tasks | 422 if duplicate |
| due_date ≥ today | POST /tasks | 422 if past date |
| Status: linear only | PATCH /tasks/{id}/status | 422 if skip/revert |
| Delete only done tasks | DELETE /tasks/{id} | 403 if not done |