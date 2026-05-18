# VoxTerra.media — Worldwide News Media Catalog

An interactive catalog of **news media outlets** from around the world.  
Built with **Laravel 11** (JSON API) + **React 18 + TypeScript** (SPA), with a Leaflet interactive map.

---

## Tech Stack

| Layer | Technologies |
|---|---|
| Backend API | PHP 8.3, Laravel 11, Laravel Sanctum, MySQL 8 |
| Frontend | React 18, TypeScript, Vite 5, Tailwind CSS 3 |
| Map | Leaflet 1.9 + react-leaflet |
| State | TanStack Query v5, Zustand |
| Forms | React Hook Form + Zod |
| Infrastructure | Docker Compose (PHP-FPM, Nginx, MySQL, Node) |

---

## Project Structure

```
voxterra-media/
├── backend/                   # Laravel 11 JSON API
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/V1/
│   │   │   ├── Requests/
│   │   │   └── Resources/
│   │   └── Models/            # Country, Region, City, MediaOutlet, User
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/           # 66 outlets across 3 countries
│   └── routes/api.php
├── frontend/                  # React 18 SPA
│   └── src/
│       ├── api/               # axios client + TanStack Query hooks
│       ├── components/map/    # Leaflet map
│       ├── pages/public/      # Landing, Explore, Country, Outlet
│       ├── stores/            # Zustand (filters, auth)
│       ├── types/             # TypeScript interfaces
│       └── lib/               # utilities, constants
├── docker/
│   └── nginx.conf
├── docker-compose.yml
└── README.md
```

---

## Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) 4+
- Node 20+ (for local frontend dev without Docker)
- Composer 2+ (for local backend dev without Docker)

---

## Quick Start — Docker

```bash
# 1. Clone & enter the repo
git clone <your-repo> voxterra-media && cd voxterra-media

# 2. Copy env files
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env

# 3. Start all services
docker compose up -d

# 4. Run migrations & seed (wait ~10s for MySQL to be ready)
docker compose exec php php artisan migrate --seed

# 5. Open in browser
#    API:      http://localhost:8000/api/v1/stats
#    Frontend: http://localhost:5173
```

---

## Local Development (without Docker)

### Backend

```bash
cd backend
composer install
cp .env.example .env

# Edit .env: set DB_HOST=127.0.0.1, DB_PORT=3306, your credentials
php artisan key:generate
php artisan migrate --seed
php artisan serve              # → http://localhost:8000
```

### Frontend

```bash
cd frontend
npm install
cp .env.example .env           # VITE_API_BASE_URL=http://localhost:8000/api/v1
npm run dev                    # → http://localhost:5173
```

---

## API Reference

All endpoints are under `/api/v1/`.

### Public (no auth, 60 req/min)

| Method | Endpoint | Description |
|---|---|---|
| GET | `/countries` | List all 3 countries with outlet counts |
| GET | `/countries/{code}` | Country detail with regions (CA / US / MX) |
| GET | `/media-outlets` | Paginated, filterable list |
| GET | `/media-outlets/map` | Lightweight map markers payload |
| GET | `/media-outlets/{slug}` | Single outlet detail |
| GET | `/stats` | Aggregate counts by country, type, language |

**Query params for `/media-outlets`:** `country`, `region`, `city`, `type`, `language`, `search`, `featured`, `bbox`, `per_page`

### Auth (10 req/min)

| Method | Endpoint | Description |
|---|---|---|
| POST | `/auth/login` | Returns Sanctum bearer token |
| POST | `/auth/logout` | Revokes current token |
| GET | `/auth/me` | Returns authenticated user |

### Admin (bearer token required)

| Method | Endpoint | Description |
|---|---|---|
| POST | `/admin/media-outlets` | Create outlet |
| PUT | `/admin/media-outlets/{id}` | Update outlet |
| DELETE | `/admin/media-outlets/{id}` | Soft-delete outlet |
| POST | `/admin/media-outlets/{id}/toggle-featured` | Toggle featured flag |

---

## Data Model

```
Country (CA / US / MX)
  └── Region (state / province)
        └── City
              └── MediaOutlet
```

**MediaOutlet types:** `national` · `newspaper` · `digital` · `tv` · `radio` · `magazine`  
**Languages:** `en` · `es` · `fr`

---

## Seed Data

The seeder loads all **66 outlets** from the reference catalog:

| Country | Count | Notable outlets |
|---|---|---|
| 🇨🇦 Canada | 14 | Globe and Mail, CBC, Le Devoir, Toronto Star |
| 🇺🇸 USA | 28 | NYT, WaPo, WSJ, LA Times, Chicago Tribune |
| 🇲🇽 México | 24 | Reforma, El Universal, La Jornada, Animal Político |

---

## Frontend Routes

| Path | Page |
|---|---|
| `/` | Landing page with hero, stats, featured outlets |
| `/explore` | Full-screen interactive Leaflet map |
| `/countries/:code` | Country page with outlet grid |
| `/outlets/:slug` | Individual outlet detail |

---

## Deployment Notes

- Set `APP_ENV=production` and `APP_DEBUG=false` in backend `.env`
- Run `npm run build` in `/frontend` and serve `dist/` via Nginx or a CDN
- Set `SANCTUM_STATEFUL_DOMAINS` and `CORS_ALLOWED_ORIGINS` to your production domain
- Use `php artisan optimize` for production caching
- Add a cron for `php artisan schedule:run` if you add scheduled tasks later
