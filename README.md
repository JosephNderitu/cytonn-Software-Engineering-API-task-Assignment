# 🗂️ Task Manager API
### Cytonn Software Engineering Internship — Take-Home Assignment 2026

A fully containerised **Task Management REST API** built with **Laravel 11**, **PostgreSQL 16**, **Nginx**, and **Docker** — complete with a dark-mode dashboard UI, database viewer, daily reports, CI/CD pipeline, and live deployment on Render.

---

## 🌐 Live Demo

| Service | URL |
|---------|-----|
| **Dashboard UI** | https://task-manager-api-2n3d.onrender.com |
| **API Base URL** | https://task-manager-api-2n3d.onrender.com/api |
| **Health Check** | https://task-manager-api-2n3d.onrender.com/up |

> The app is hosted on Render's free tier — it may take **30–60 seconds** to wake up on the first request if it has been idle.

---

## 📌 About the Project

This project implements a **Task Management REST API** that allows users to create, list, update, and delete tasks — with strict business rules enforced at the application layer. On top of the API, I built a **full dark-mode single-page dashboard** so evaluators can interact with the system visually without needing Postman.

The entire stack runs inside **Docker containers** locally and deploys automatically to **Render** via a **GitHub Actions CI/CD pipeline** on every push to `main`.

---

## 🛠️ Tech Stack

| Layer | Technology | Why I chose it |
|-------|-----------|----------------|
| Framework | Laravel 11 (PHP 8.3) | Clean MVC, Eloquent ORM, built-in validation |
| Database | PostgreSQL 16 | Robust, production-grade, free on Render |
| Web Server | Nginx (Alpine) | Lightweight, fast reverse proxy |
| Process Manager | Supervisor | Manages Nginx + PHP-FPM in one container |
| Containerisation | Docker + Docker Compose | Consistent dev/prod environment |
| CI/CD | GitHub Actions | Auto test + deploy on every push to main |
| Deployment | Render | Free tier, native Docker support |
| DB Admin (local) | pgAdmin 4 | Visual PostgreSQL browser |
| Frontend | Vanilla JS + HTML/CSS | No framework needed — clean and fast |
| Charts | Chart.js | Daily report visualisations |

---

## 📁 Project Structure

```
task-manager-api/
│
├── 📁 docker/
│   ├── nginx/
│   │   ├── default.conf          # Local Nginx virtual host config
│   │   └── prod.conf             # Production Nginx config (single container)
│   ├── php/
│   │   ├── Dockerfile            # Local PHP 8.3-FPM image
│   │   ├── Dockerfile.prod       # Production image (PHP + Nginx + Supervisor)
│   │   ├── local.ini             # PHP config overrides for local dev
│   │   └── start.sh              # Production startup script
│   ├── pgadmin/
│   │   └── servers.json          # pgAdmin auto-connect config
│   └── supervisor/
│       └── supervisord.conf      # Supervisor process config
│
├── 📁 src/                       # Laravel 11 application
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── TaskController.php        # All 5 task endpoints
│   │   │   │   └── DatabaseController.php    # DB viewer API
│   │   │   └── Requests/
│   │   │       ├── CreateTaskRequest.php     # Validation rules
│   │   │       └── UpdateTaskStatusRequest.php
│   │   └── Models/
│   │       └── Task.php                      # Eloquent model + helpers
│   ├── database/
│   │   ├── migrations/
│   │   │   └── xxxx_create_tasks_table.php   # Tasks table schema
│   │   └── seeders/
│   │       ├── DatabaseSeeder.php
│   │       └── TaskSeeder.php                # 8 demo tasks
│   ├── resources/views/
│   │   └── dashboard.blade.php              # Full SPA dashboard
│   ├── routes/
│   │   ├── api.php               # All API routes
│   │   └── web.php               # Dashboard route
│   └── bootstrap/
│       └── app.php               # Laravel bootstrap with API routing
│
├── 📁 .github/
│   └── workflows/
│       └── deploy.yml            # CI/CD pipeline (GitHub Actions)
│
├── 📁 postman/
│   └── Task_Manager_API.postman_collection.json
│
├── docker-compose.yml            # Local dev orchestration
├── render.yaml                   # Render deployment blueprint
└── README.md
```

---

## 🗄️ Database Schema

**Engine:** PostgreSQL 16  
**Table:** `tasks`

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint | Primary key, auto-increment |
| `title` | varchar(255) | Task title |
| `due_date` | date | Deadline (today or future only) |
| `priority` | enum | `low`, `medium`, `high` |
| `status` | enum | `pending`, `in_progress`, `done` |
| `created_at` | timestamp | Auto-managed by Laravel |
| `updated_at` | timestamp | Auto-managed by Laravel |

**Unique constraint:** `(title, due_date)` — no duplicate task titles on the same day.

**Indexes:** `status`, `priority`, `due_date` — for fast filtering and sorting.

---

## 🔌 API Endpoints

All endpoints are prefixed with `/api` and return JSON.

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

**Business rules enforced:**
- `title` + `due_date` combination must be unique
- `due_date` must be today or a future date
- `priority` must be `low`, `medium`, or `high`
- `status` always starts as `pending` — cannot be set manually

**Success response — 201:**
```json
{
  "success": true,
  "message": "Task created successfully.",
  "data": { "id": 1, "title": "Fix production bug", ... }
}
```

---

### 2. List Tasks
```http
GET /api/tasks
GET /api/tasks?status=pending
GET /api/tasks?status=in_progress
GET /api/tasks?status=done
```

Results are sorted by **priority (high → low)**, then **due_date ascending**.  
Returns a meaningful message if no tasks exist.

---

### 3. Update Task Status
```http
PATCH /api/tasks/{id}/status
Content-Type: application/json

{ "status": "in_progress" }
```

**Status flow is strictly linear:**
```
pending → in_progress → done
```
You cannot skip a step or go backwards. Any attempt returns `422` with a clear message.

---

### 4. Delete Task
```http
DELETE /api/tasks/{id}
```

Only tasks with status `done` can be deleted.  
Attempting to delete a `pending` or `in_progress` task returns **403 Forbidden**.

---

### 5. Daily Report *(Bonus)*
```http
GET /api/tasks/report?date=2026-04-01
```

**Response:**
```json
{
  "success": true,
  "date": "2026-04-01",
  "summary": {
    "high":   { "pending": 2, "in_progress": 1, "done": 0 },
    "medium": { "pending": 1, "in_progress": 0, "done": 3 },
    "low":    { "pending": 0, "in_progress": 0, "done": 1 }
  }
}
```

---

### DB Viewer Endpoints *(Powers the dashboard)*
```http
GET /api/db/tables              # List all tables
GET /api/db/tables/{tableName}  # Get columns + rows for a table
```

---

## 💻 Dashboard UI

The app ships with a full **dark-mode single-page dashboard** accessible at the root URL. Built with Vanilla JS — no framework required.

**Features:**
- 📊 Live stats cards (total, pending, in progress, done)
- ✅ Create tasks with inline validation errors
- ➡️ Advance task status with a single click
- 🗑️ Delete completed tasks
- 🔍 Search and filter by status
- 📈 Daily report with Chart.js bar chart
- 🗄️ Live database viewer — browse all PostgreSQL tables

---

## 🚀 Running Locally

### Prerequisites
- Docker Desktop (running)
- Git

### Steps

**1. Clone the repository**
```bash
git clone https://github.com/JosephNderitu/cytonn-Software-Engineering-API-task-Assignment.git
cd cytonn-Software-Engineering-API-task-Assignment
```

**2. Start Docker containers**
```bash
docker compose up -d --build
```
> First build takes 3–5 minutes — it compiles PHP extensions from source.

**3. Install dependencies & set up Laravel**
```bash
docker compose exec app composer install
docker compose exec -u root app chmod 666 /var/www/.env
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
```

**4. You're live!**

| URL | Description |
|-----|-------------|
| http://localhost:8080 | Dashboard UI |
| http://localhost:8080/api/tasks | API |
| http://localhost:5050 | pgAdmin (DB browser) |

**pgAdmin credentials:**
- Email: `admin@taskapi.com`
- Password: `admin123`
- The database auto-connects — no extra setup needed.

---

## 🧪 Testing with Postman

1. Open Postman
2. Click **Import** → select `postman/Task_Manager_API.postman_collection.json`
3. The collection has **12 pre-built requests** covering:

| # | Test | Expected |
|---|------|----------|
| 1 | Create task (valid) | 201 ✅ |
| 2 | Create task (duplicate title+date) | 422 ✅ |
| 3 | Create task (past due_date) | 422 ✅ |
| 4 | List all tasks | 200 ✅ |
| 5 | Filter by status=pending | 200 ✅ |
| 6 | Filter by status=in_progress | 200 ✅ |
| 7 | Update status pending→in_progress | 200 ✅ |
| 8 | Update status in_progress→done | 200 ✅ |
| 9 | Skip status (pending→done) | 422 ✅ |
| 10 | Delete non-done task | 403 ✅ |
| 11 | Delete done task | 200 ✅ |
| 12 | Daily report | 200 ✅ |

> For the live deployment, change the base URL in Postman from `http://localhost:8080` to `https://task-manager-api-2n3d.onrender.com`

---

## ⚙️ CI/CD Pipeline

Every push to `main` triggers a **3-stage GitHub Actions pipeline:**

```
Push to main
     │
     ▼
┌─────────────┐     ┌─────────────────┐     ┌──────────────────┐
│  Job 1      │────▶│  Job 2          │────▶│  Job 3           │
│  Test & Lint│     │  Build Docker   │     │  Deploy to Render│
│             │     │  Image          │     │                  │
│ • PHPUnit   │     │ • Verify build  │     │ • Trigger Render │
│ • Migrations│     │   compiles      │     │   deploy via API │
│ • Route list│     │ • Cache layers  │     │ • Auto live in   │
└─────────────┘     └─────────────────┘     │   ~3 minutes     │
                                            └──────────────────┘
```

**Pipeline file:** `.github/workflows/deploy.yml`

Jobs 2 and 3 only run on pushes to `main` — pull requests only run tests.

---

## 🐳 Docker Architecture

### Local development (4 containers)
```
docker-compose.yml
├── app        PHP 8.3-FPM (Alpine) — Laravel application
├── nginx      Nginx 1.25 (Alpine) — reverse proxy on port 8080
├── postgres   PostgreSQL 16 (Alpine) — database on port 5432
└── pgadmin    pgAdmin 4 — database browser on port 5050
```

### Production (1 container on Render)
```
Dockerfile.prod
└── Single container
    ├── PHP 8.3-FPM — handles PHP processing
    ├── Nginx — serves on port 8080
    └── Supervisor — keeps both processes running
```

In production, Nginx and PHP-FPM run inside the **same container** managed by Supervisor. This is the standard approach for single-container Docker deployments on platforms like Render.

---

## 🌍 Deployment on Render

The app deploys automatically via `render.yaml` blueprint.

**Services provisioned:**
- **Web Service** — Docker container (Free tier)
- **PostgreSQL** — Managed database (Free tier, 1GB)

**On every deploy, the startup script automatically:**
1. Waits for the database to be ready
2. Runs pending migrations
3. Seeds demo data if the tasks table is empty
4. Caches config, routes, and views for performance
5. Starts Nginx + PHP-FPM via Supervisor

### Deploying manually
```bash
git add .
git commit -m "your message"
git push origin main
# GitHub Actions picks it up automatically
```

---

## 🔧 Useful Commands

```bash
# Start all local services
docker compose up -d

# Stop all services
docker compose down

# View live app logs
docker compose logs -f app

# Run any artisan command
docker compose exec app php artisan <command>

# Fresh database (wipe + re-migrate + re-seed)
docker compose exec app php artisan migrate:fresh --seed

# Access PostgreSQL directly
docker compose exec postgres psql -U task_user -d task_manager

# Rebuild containers after Dockerfile changes
docker compose up -d --build
```

---

## 📋 Business Rules Summary

| Rule | Endpoint | Behaviour |
|------|----------|-----------|
| Unique `title` per `due_date` | `POST /api/tasks` | 422 if duplicate |
| `due_date` must be today or future | `POST /api/tasks` | 422 if past |
| Status must progress linearly | `PATCH /api/tasks/{id}/status` | 422 if skip or revert |
| Only `done` tasks can be deleted | `DELETE /api/tasks/{id}` | 403 if not done |

---

## 🗝️ Environment Variables

| Variable | Description |
|----------|-------------|
| `APP_KEY` | Laravel encryption key |
| `APP_ENV` | `local` or `production` |
| `APP_DEBUG` | `true` locally, `false` in production |
| `DB_HOST` | PostgreSQL hostname |
| `DB_DATABASE` | Database name |
| `DB_USERNAME` | Database user |
| `DB_PASSWORD` | Database password |

---

*Built with ❤️ by Joseph Gikuru — Cytonn Software Engineering Internship 2026*