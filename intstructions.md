# Claude Code Prompt: News Media Catalog Platform
 
Build a full-stack web application called **"NewsCatalog"** — an interactive directory of news media outlets across North America (Canada, USA, Mexico), organized by country, region/state, and city, with an interactive map visualization and an admin dashboard for content management.
 
## Tech Stack
 
**Backend:**
- PHP 8.3+ with Laravel 11
- MySQL 8 (or PostgreSQL 16)
- Laravel Sanctum for API authentication
- Laravel Scout (optional, for search) with Meilisearch or database driver
**Frontend:**
- React 18 with TypeScript
- Vite as the build tool
- React Router v6 for routing
- TanStack Query (React Query) for server state
- Tailwind CSS for styling
- Leaflet + react-leaflet for the interactive map
- Zustand for client state (filters, UI state)
- React Hook Form + Zod for form validation
- shadcn/ui components for the admin dashboard
- Lucide React for icons
**Architecture:**
- Decoupled: Laravel as a pure JSON API (`/api/v1/*`), React as an SPA consuming it
- Both apps in a monorepo: `/backend` (Laravel) and `/frontend` (React)
- Docker Compose for local development (PHP-FPM, Nginx, MySQL, Node)
## Domain Model
 
Create the following Eloquent models with migrations, factories, and seeders:
 
**`Country`** — id, code (ISO-2: CA/US/MX), name, name_es, slug, flag_emoji, latitude, longitude, default_zoom, timestamps
 
**`Region`** — id, country_id (FK), code, name, name_es, slug, latitude, longitude, timestamps (states/provinces)
 
**`City`** — id, region_id (FK), name, slug, latitude, longitude, population (nullable), timestamps
 
**`MediaOutlet`** — id, city_id (FK), name, slug, url, type (enum: national, newspaper, digital, tv, radio, magazine), language (enum: en, es, fr), description (text, nullable), logo_url (nullable), founded_year (nullable), is_active (boolean), is_featured (boolean), latitude (override, nullable), longitude (override, nullable), timestamps, soft deletes
 
**`User`** — standard Laravel user with a `role` field (enum: admin, editor, viewer)
 
**Relationships:**
- Country `hasMany` Regions; Region `belongsTo` Country
- Region `hasMany` Cities; City `belongsTo` Region
- City `hasMany` MediaOutlets; MediaOutlet `belongsTo` City
- MediaOutlet has accessor `effective_latitude` / `effective_longitude` (fallback to city's coords)
## Seed Data
 
Seed the database with the 66 media outlets from the catalog provided (14 Canada, 28 USA, 24 Mexico). Create a structured seeder that builds the geographic hierarchy first (countries → regions → cities), then attaches media outlets. Include realistic data for: Reforma, El Universal, NYT, WaPo, Globe and Mail, CBC, Toronto Star, Diario de Yucatán, La Jornada Maya, etc. Use proper lat/lon coordinates.
 
## REST API (Laravel)
 
Build a versioned API under `/api/v1/`:
 
**Public endpoints (no auth):**
- `GET /api/v1/countries` — list all countries with counts of regions/outlets
- `GET /api/v1/countries/{code}` — country detail with regions
- `GET /api/v1/regions?country=US` — regions filtered by country
- `GET /api/v1/cities?region={id}` — cities by region
- `GET /api/v1/media-outlets` — paginated, filterable list. Query params: `country`, `region`, `city`, `type`, `language`, `search`, `featured`, `bbox` (geographic bounding box: `minLon,minLat,maxLon,maxLat`), `per_page`
- `GET /api/v1/media-outlets/{slug}` — single outlet detail
- `GET /api/v1/media-outlets/map` — lightweight endpoint returning only the fields needed for map markers (id, name, lat, lon, country, type, url) — optimized for fast initial load
- `GET /api/v1/stats` — aggregate counts (total outlets, per country, per type)
**Admin endpoints (auth required, role: admin/editor):**
- `POST /api/v1/auth/login` — Sanctum token-based login
- `POST /api/v1/auth/logout`
- `GET /api/v1/auth/me`
- Full CRUD: `apiResource` routes for `countries`, `regions`, `cities`, `media-outlets`
- `POST /api/v1/media-outlets/{id}/toggle-featured`
- `POST /api/v1/media-outlets/bulk-import` — accepts CSV/JSON
**API standards:**
- Use Laravel API Resources for response shaping
- Standard envelope: `{ data: ..., meta: { pagination, filters_applied } }`
- Use Form Requests for validation
- Throttle public endpoints (60/min) and auth endpoints (10/min)
- CORS configured for the React app's origin
## Frontend — Public Site (React)
 
**Routes:**
- `/` — landing page with hero, featured outlets, "Explore the map" CTA, stats counters (animated)
- `/explore` — the main interactive map experience (the artifact built in the previous turn, but production-grade)
- `/countries/:code` — country page with embedded smaller map and list grouped by region
- `/outlets/:slug` — outlet detail page with metadata, embedded mini-map, "visit site" CTA
- `/about` — short page about the project
**The `/explore` page must include:**
- Full-screen Leaflet map with dark CartoDB tiles
- Markers color-coded by country (CA: red, US: blue, MX: green)
- Marker clustering at low zoom levels (use `react-leaflet-cluster`)
- Right-side panel (collapsible on mobile) with:
  - Search input (debounced, 300ms)
  - Country filter pills with counts
  - Type filter (multi-select)
  - Language filter
  - Scrollable list of outlets matching current filters
- URL state synchronization (filters reflected in query params; shareable links)
- Click on list item → map flies to location and opens popup
- Click on marker → popup with name, location, type badge, "Visit site" link
- Loading skeletons during data fetch
- Empty state when no results match
**Design system:**
- Dark theme by default with light theme toggle (persisted in localStorage)
- Inter or similar geometric sans-serif for UI
- Generous whitespace, subtle borders, hover transitions
- Mobile-responsive (map fullscreen, panel becomes bottom sheet)
- Accessible: keyboard navigation, ARIA labels, focus rings, sufficient contrast
## Frontend — Admin Dashboard (React)
 
**Routes (under `/admin`, protected):**
- `/admin/login`
- `/admin` — dashboard overview: stats cards, recent additions, chart of outlets by country/type (use Recharts)
- `/admin/outlets` — data table with sorting, filtering, pagination, bulk actions
- `/admin/outlets/new` and `/admin/outlets/:id/edit` — form with all fields, including a Leaflet map for picking lat/lon
- `/admin/countries`, `/admin/regions`, `/admin/cities` — CRUD tables
- `/admin/users` — user management (admins only)
- `/admin/import` — bulk CSV import with preview and validation feedback
**Admin requirements:**
- Use shadcn/ui components: Table, Dialog, Form, Select, Sheet, Toast
- Protected routes via `<RequireAuth>` wrapper checking Sanctum token
- Optimistic updates with TanStack Query mutations
- Toast notifications for success/error states
- Confirmation dialogs for destructive actions
- Breadcrumbs and clear navigation sidebar
## Deliverables / Project Structure
 
```
newscatalog/
├── backend/                    # Laravel
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/V1/
│   │   │   ├── Requests/
│   │   │   ├── Resources/
│   │   │   └── Middleware/
│   │   ├── Models/
│   │   └── Services/
│   ├── database/
│   │   ├── migrations/
│   │   ├── factories/
│   │   └── seeders/
│   ├── routes/api.php
│   └── tests/Feature/Api/
├── frontend/                   # React + Vite
│   ├── src/
│   │   ├── api/                # axios client, query hooks
│   │   ├── components/
│   │   │   ├── ui/             # shadcn primitives
│   │   │   ├── map/
│   │   │   └── admin/
│   │   ├── pages/
│   │   │   ├── public/
│   │   │   └── admin/
│   │   ├── hooks/
│   │   ├── stores/             # zustand
│   │   ├── lib/
│   │   └── types/              # TypeScript types matching API
│   └── vite.config.ts
├── docker-compose.yml
├── .env.example
└── README.md
```
 
## Implementation Order
 
1. Scaffold Laravel project, configure DB, create migrations and models with relationships
2. Build seeders with the 66 media outlets across countries/regions/cities
3. Build the public API endpoints with resources, request validation, and feature tests
4. Add Sanctum auth and admin endpoints with policy-based authorization
5. Scaffold the React app with Vite, TypeScript, Tailwind, routing, and TanStack Query
6. Build the landing page and the `/explore` map experience
7. Build the admin dashboard with shadcn/ui
8. Add Docker Compose, write a clear README with setup instructions, and ensure `npm run build` + `php artisan serve` works end-to-end
## Quality Requirements
 
- Write feature tests for every API endpoint (PHPUnit/Pest)
- TypeScript strict mode on the frontend; no `any` types
- Use Eloquent eager loading to avoid N+1 queries (add `Model::preventLazyLoading()` in non-production)
- Index foreign keys, slug columns, and the lat/lon columns
- Validate all inputs server-side regardless of client validation
- Use database transactions for multi-step writes
- Implement proper error boundaries in React
- Include a `.env.example` for both backend and frontend
- Write a README covering: prerequisites, install, seed, run, test, deploy notes
Start by scaffolding both projects, then build incrementally. Show me the structure first before generating large amounts of code, and check in after each major milestone (models/migrations → seeders → API → public frontend → admin).

