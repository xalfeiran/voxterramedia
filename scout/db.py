"""
db.py — MySQL helpers for the VoxTerra scout engine.

Responsibilities:
  - Open / close a connection to the voxterra DB
  - Return all countries (or a random one)
  - Find-or-create a region + city stub for a discovered site
  - Upsert a media_outlet record
  - Log each scout run to the scout_runs table (auto-created if missing)
"""

import re
import random
import logging
from datetime import datetime
from typing import Dict, List, Optional, Tuple

import pymysql
import pymysql.cursors

import airports
import config

log = logging.getLogger(__name__)


# ── Connection ────────────────────────────────────────────────────────────────

def get_connection() -> pymysql.connections.Connection:
    """Return a new pymysql connection to the voxterra DB."""
    return pymysql.connect(
        host=config.DB_HOST,
        port=config.DB_PORT,
        user=config.DB_USERNAME,
        password=config.DB_PASSWORD,
        database=config.DB_DATABASE,
        charset="utf8mb4",
        cursorclass=pymysql.cursors.DictCursor,
        autocommit=False,
    )


def ensure_scout_runs_table(conn: pymysql.connections.Connection) -> None:
    """Create scout_runs if it doesn't exist yet."""
    sql = """
    CREATE TABLE IF NOT EXISTS scout_runs (
        id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        country_id   BIGINT UNSIGNED NOT NULL,
        country_name VARCHAR(100)    NOT NULL,
        query        VARCHAR(255)    NOT NULL,
        urls_found   SMALLINT        NOT NULL DEFAULT 0,
        urls_saved   SMALLINT        NOT NULL DEFAULT 0,
        urls_skipped SMALLINT        NOT NULL DEFAULT 0,
        started_at   DATETIME        NOT NULL,
        finished_at  DATETIME        NULL,
        notes        TEXT            NULL,
        PRIMARY KEY (id),
        INDEX idx_country (country_id),
        INDEX idx_started (started_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    """
    with conn.cursor() as cur:
        cur.execute(sql)
    conn.commit()


# ── Countries ─────────────────────────────────────────────────────────────────

def get_all_countries(conn: pymysql.connections.Connection) -> List[Dict]:
    with conn.cursor() as cur:
        cur.execute("SELECT id, code, name, name_es FROM countries ORDER BY name")
        return cur.fetchall()


def get_random_country(conn: pymysql.connections.Connection) -> Optional[dict]:
    countries = get_all_countries(conn)
    if not countries:
        return None
    return random.choice(countries)


def get_country_by_code(
    conn: pymysql.connections.Connection, code: str
) -> Optional[dict]:
    with conn.cursor() as cur:
        cur.execute(
            "SELECT id, code, name, name_es FROM countries WHERE code = %s",
            (code.upper(),),
        )
        return cur.fetchone()


# ── Region / City stubs ───────────────────────────────────────────────────────

def _slugify(text: str) -> str:
    text = text.lower().strip()
    text = re.sub(r"[^\w\s-]", "", text)
    text = re.sub(r"[\s_-]+", "-", text)
    return text[:100]


def find_or_create_region(
    conn: pymysql.connections.Connection,
    country_id: int,
    region_name: str,
    region_code: str = "",
) -> int:
    """Return region.id, creating a stub record if needed."""
    slug = _slugify(region_name)
    with conn.cursor() as cur:
        cur.execute(
            "SELECT id FROM regions WHERE country_id = %s AND slug = %s",
            (country_id, slug),
        )
        row = cur.fetchone()
        if row:
            return row["id"]

        cur.execute(
            """
            INSERT INTO regions (country_id, code, name, name_es, slug, created_at, updated_at)
            VALUES (%s, %s, %s, %s, %s, NOW(), NOW())
            """,
            (country_id, region_code[:10] if region_code else None,
             region_name, region_name, slug),
        )
        conn.commit()
        return cur.lastrowid


def find_or_create_city(
    conn: pymysql.connections.Connection,
    region_id: int,
    city_name: str,
    lat: float = 0.0,
    lon: float = 0.0,
    country_code: str = "",
) -> int:
    """
    Return city.id, creating a stub record if needed.

    When a country_code is supplied, the city's IATA airport_code is resolved
    from the curated map and stored — both on insert and as a backfill for an
    existing row whose airport_code is still NULL.
    """
    slug = _slugify(city_name)
    code = airports.resolve(country_code, city_name) if country_code else None

    with conn.cursor() as cur:
        cur.execute(
            "SELECT id, airport_code FROM cities WHERE region_id = %s AND slug = %s",
            (region_id, slug),
        )
        row = cur.fetchone()
        if row:
            # Backfill the code if we now know it and the row is missing one.
            if code and not row.get("airport_code"):
                cur.execute(
                    "UPDATE cities SET airport_code = %s, updated_at = NOW() WHERE id = %s",
                    (code, row["id"]),
                )
                conn.commit()
            return row["id"]

        cur.execute(
            """
            INSERT INTO cities (region_id, name, slug, airport_code, latitude, longitude, created_at, updated_at)
            VALUES (%s, %s, %s, %s, %s, %s, NOW(), NOW())
            """,
            (region_id, city_name, slug, code, lat, lon),
        )
        conn.commit()
        return cur.lastrowid


def backfill_airport_codes(conn: pymysql.connections.Connection) -> Tuple[int, int]:
    """
    One-off: resolve and set airport_code for every city that lacks one.
    Returns (updated, scanned).
    """
    with conn.cursor() as cur:
        cur.execute(
            """
            SELECT c.id, c.name, co.code AS country_code
            FROM   cities    c
            JOIN   regions   r  ON r.id  = c.region_id
            JOIN   countries co ON co.id = r.country_id
            WHERE  c.airport_code IS NULL
            """
        )
        rows = cur.fetchall()

    updated = 0
    for row in rows:
        code = airports.resolve(row.get("country_code", ""), row.get("name", ""))
        if not code:
            continue
        try:
            with conn.cursor() as cur:
                cur.execute(
                    "UPDATE cities SET airport_code = %s, updated_at = NOW() WHERE id = %s",
                    (code, row["id"]),
                )
            conn.commit()
            updated += 1
        except Exception as exc:  # noqa: BLE001 - skip the row, keep going
            conn.rollback()
            log.warning("Could not set %s for city #%s (%s): %s",
                        code, row.get("id"), row.get("name"), exc)

    return updated, len(rows)


def get_or_create_national_city(
    conn: pymysql.connections.Connection, country: dict
) -> int:
    """
    Return a city_id for a "National / Online" placeholder city.
    Used when a discovered site covers the whole country rather than one city.
    """
    region_id = find_or_create_region(
        conn,
        country_id=country["id"],
        region_name="National",
        region_code="NAT",
    )
    return find_or_create_city(conn, region_id, "National / Online")


# ── Media outlet upsert ───────────────────────────────────────────────────────

def upsert_media_outlet(
    conn: pymysql.connections.Connection,
    city_id: int,
    data: dict,
) -> Tuple[int, bool]:
    """
    Insert or update a media_outlet row.

    `data` keys (all optional except url/name):
        url, name, slug, type, language, description,
        logo_url, latitude, longitude, rss_url, has_rss

    Returns (outlet_id, created: bool).
    """
    url: str   = data["url"]
    name: str  = data.get("name") or url
    slug: str  = data.get("slug") or _slugify(name)

    # Ensure slug is unique by appending a suffix if needed
    slug = _unique_slug(conn, slug, exclude_url=url)

    outlet_type = data.get("type", "digital")
    language    = (data.get("language") or "en")[:10]
    description = data.get("description") or None
    logo_url    = data.get("logo_url") or None
    lat         = data.get("latitude") or None
    lon         = data.get("longitude") or None
    rss_url     = data.get("rss_url") or None
    has_rss     = 1 if rss_url else 0

    with conn.cursor() as cur:
        cur.execute("SELECT id FROM media_outlets WHERE url = %s", (url,))
        existing = cur.fetchone()

        if existing:
            # Re-enrich path: update all enriched metadata including geo.
            # city_id / lat / lon: only overwrite when the enricher returned a
            # real value so we don't accidentally replace a known location with NULL.
            # rss_url: only overwrite when we have a new value.
            if rss_url:
                cur.execute(
                    """
                    UPDATE media_outlets
                    SET name        = %s,
                        type        = %s,
                        language    = %s,
                        description = COALESCE(%s, description),
                        logo_url    = COALESCE(%s, logo_url),
                        rss_url     = %s,
                        has_rss     = 1,
                        city_id     = COALESCE(%s, city_id),
                        latitude    = COALESCE(%s, latitude),
                        longitude   = COALESCE(%s, longitude),
                        updated_at  = NOW()
                    WHERE id = %s
                    """,
                    (name, outlet_type, language, description, logo_url,
                     rss_url, city_id or None, lat, lon, existing["id"]),
                )
            else:
                cur.execute(
                    """
                    UPDATE media_outlets
                    SET name        = %s,
                        type        = %s,
                        language    = %s,
                        description = COALESCE(%s, description),
                        logo_url    = COALESCE(%s, logo_url),
                        city_id     = COALESCE(%s, city_id),
                        latitude    = COALESCE(%s, latitude),
                        longitude   = COALESCE(%s, longitude),
                        updated_at  = NOW()
                    WHERE id = %s
                    """,
                    (name, outlet_type, language, description, logo_url,
                     city_id or None, lat, lon, existing["id"]),
                )
            conn.commit()
            return existing["id"], False

        cur.execute(
            """
            INSERT INTO media_outlets
                (city_id, name, slug, url, rss_url, has_rss, type, language, description,
                 logo_url, latitude, longitude, is_active, is_featured,
                 created_at, updated_at)
            VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,1,0,NOW(),NOW())
            """,
            (city_id, name, slug, url, rss_url, has_rss, outlet_type, language,
             description, logo_url, lat, lon),
        )
        conn.commit()
        return cur.lastrowid, True


def _unique_slug(conn, slug: str, exclude_url: str = "") -> str:
    """Append numeric suffix to slug until it doesn't conflict."""
    base = slug
    n = 1
    with conn.cursor() as cur:
        while True:
            cur.execute(
                "SELECT id FROM media_outlets WHERE slug=%s AND url!=%s",
                (slug, exclude_url),
            )
            if not cur.fetchone():
                return slug
            slug = f"{base}-{n}"
            n += 1


# ── RSS helpers ───────────────────────────────────────────────────────────────

def get_outlets_for_rss_check(
    conn: pymysql.connections.Connection,
    country_code: str = "",
    limit: int = 0,
) -> List[Dict]:
    """
    Return active media outlets to check for RSS feeds.
    Optionally scoped to a country code, and optionally capped to `limit` rows.
    """
    sql = """
        SELECT mo.id, mo.url, mo.name, mo.has_rss
        FROM media_outlets mo
        JOIN cities c ON c.id = mo.city_id
        JOIN regions r ON r.id = c.region_id
        JOIN countries ct ON ct.id = r.country_id
        WHERE mo.is_active = 1 AND mo.deleted_at IS NULL
    """
    params: list = []

    if country_code:
        sql += " AND ct.code = %s"
        params.append(country_code.upper())

    sql += " ORDER BY mo.id"

    if limit:
        sql += " LIMIT %s"
        params.append(limit)

    with conn.cursor() as cur:
        cur.execute(sql, params or None)
        return cur.fetchall()


def get_outlets_for_reenrich(
    conn: pymysql.connections.Connection,
    country_code: str = "",
    limit: int = 0,
) -> List[Dict]:
    """
    Return active outlets whose metadata should be refreshed by re-running the
    enricher.  Includes country_name so the enricher's geo logic can validate
    IP results against the expected country.

    Optionally scoped to a country code and/or capped to `limit` rows.
    """
    sql = """
        SELECT mo.id, mo.url, mo.name, mo.city_id,
               ct.name AS country_name, ct.code AS country_code
        FROM   media_outlets mo
        JOIN   cities    c  ON c.id  = mo.city_id
        JOIN   regions   r  ON r.id  = c.region_id
        JOIN   countries ct ON ct.id = r.country_id
        WHERE  mo.is_active = 1
          AND  mo.deleted_at IS NULL
    """
    params: list = []

    if country_code:
        sql += " AND ct.code = %s"
        params.append(country_code.upper())

    sql += " ORDER BY mo.id"

    if limit:
        sql += " LIMIT %s"
        params.append(limit)

    with conn.cursor() as cur:
        cur.execute(sql, params or None)
        return cur.fetchall()


def update_outlet_full(
    conn: pymysql.connections.Connection,
    outlet_id: int,
    city_id: int,
    data: dict,
) -> None:
    """
    Overwrite all enriched fields (including geo) for an existing outlet.
    Called by the re-enrich job.
    """
    rss_url = data.get("rss_url") or None
    has_rss = 1 if rss_url else 0

    with conn.cursor() as cur:
        cur.execute(
            """
            UPDATE media_outlets
            SET name        = %s,
                type        = %s,
                language    = %s,
                description = COALESCE(%s, description),
                logo_url    = COALESCE(%s, logo_url),
                rss_url     = COALESCE(%s, rss_url),
                has_rss     = GREATEST(has_rss, %s),
                city_id     = COALESCE(%s, city_id),
                latitude    = COALESCE(%s, latitude),
                longitude   = COALESCE(%s, longitude),
                updated_at  = NOW()
            WHERE id = %s
            """,
            (
                data.get("name") or data["url"],
                data.get("type", "digital"),
                (data.get("language") or "en")[:10],
                data.get("description") or None,
                data.get("logo_url") or None,
                rss_url,
                has_rss,
                city_id or None,
                data.get("latitude") or None,
                data.get("longitude") or None,
                outlet_id,
            ),
        )
    conn.commit()


def update_outlet_rss(
    conn: pymysql.connections.Connection,
    outlet_id: int,
    rss_url: Optional[str],
) -> None:
    """Set rss_url and has_rss for a single outlet."""
    has_rss = 1 if rss_url else 0
    with conn.cursor() as cur:
        cur.execute(
            "UPDATE media_outlets SET rss_url=%s, has_rss=%s, updated_at=NOW() WHERE id=%s",
            (rss_url or None, has_rss, outlet_id),
        )
    conn.commit()


# ── Scout run logging ─────────────────────────────────────────────────────────

def log_run_start(
    conn: pymysql.connections.Connection,
    country: dict,
    query: str,
) -> int:
    """Insert a new scout_runs row and return its id."""
    with conn.cursor() as cur:
        cur.execute(
            """
            INSERT INTO scout_runs (country_id, country_name, query, started_at)
            VALUES (%s, %s, %s, %s)
            """,
            (country["id"], country["name"], query, datetime.utcnow()),
        )
        conn.commit()
        return cur.lastrowid


def log_run_finish(
    conn: pymysql.connections.Connection,
    run_id: int,
    urls_found: int,
    urls_saved: int,
    urls_skipped: int,
    notes: str = "",
) -> None:
    with conn.cursor() as cur:
        cur.execute(
            """
            UPDATE scout_runs
            SET urls_found=%s, urls_saved=%s, urls_skipped=%s,
                finished_at=%s, notes=%s
            WHERE id=%s
            """,
            (urls_found, urls_saved, urls_skipped,
             datetime.utcnow(), notes or None, run_id),
        )
        conn.commit()
