"""
config.py — loads settings from the backend .env and environment variables.
"""

import os
from pathlib import Path
from dotenv import dotenv_values

# Resolve the backend .env relative to this file's location
_SCOUT_DIR = Path(__file__).parent
_BACKEND_ENV = _SCOUT_DIR.parent / "backend" / ".env"

# Merge: backend .env first, then real env vars override
_env = {**dotenv_values(_BACKEND_ENV), **os.environ}

# ── Database ──────────────────────────────────────────────────────────────────
DB_HOST     = _env.get("DB_HOST", "127.0.0.1")
DB_PORT     = int(_env.get("DB_PORT", 3306))
DB_DATABASE = _env.get("DB_DATABASE", "voxterra")
DB_USERNAME = _env.get("DB_USER") or _env.get("DB_USERNAME", "voxterra")
DB_PASSWORD = _env.get("DB_PASSWORD", "secret")

# ── Search backends ───────────────────────────────────────────────────────────
# SerpAPI key  →  https://serpapi.com  (free tier: 100 searches/month)
SERPAPI_KEY = _env.get("SERPAPI_KEY", "")

# Fallback: use duckduckgo-search (no key needed) when SERPAPI_KEY is empty
USE_SERPAPI = bool(SERPAPI_KEY)

# ── Scout behaviour ───────────────────────────────────────────────────────────
# Max search results to process per scout run
MAX_RESULTS_PER_RUN = int(_env.get("SCOUT_MAX_RESULTS", 10))

# Seconds to wait between HTTP requests (be polite)
REQUEST_DELAY = float(_env.get("SCOUT_REQUEST_DELAY", 1.5))

# HTTP timeout for site enrichment requests
REQUEST_TIMEOUT = int(_env.get("SCOUT_REQUEST_TIMEOUT", 15))

# User-agent string for enrichment requests
USER_AGENT = (
    "Mozilla/5.0 (compatible; VoxTerraScout/1.0; +https://voxterra.media)"
)

# ── Logging ───────────────────────────────────────────────────────────────────
LOG_FILE = _env.get("SCOUT_LOG_FILE", str(_SCOUT_DIR / "scout.log"))
