#!/usr/bin/env python3
"""
scout.py — VoxTerra AI Scout Bot
=================================

Picks a random country (or a specified one), searches the web for local /
regional news sites, enriches each discovered URL with metadata, and
upserts the results into the VoxTerra MySQL database.

Usage
-----
  # Random country, default settings
  python scout.py

  # Target a specific country by ISO code
  python scout.py --country MX

  # Limit results and add a delay between requests
  python scout.py --country BR --max 20 --delay 2

  # Dry run (search + enrich, no DB writes)
  python scout.py --dry-run

  # Run N jobs back-to-back (random country each time)
  python scout.py --jobs 5

  # Use only the first N query templates (faster)
  python scout.py --queries 3

Options
-------
  --country CODE    ISO 2-letter country code to target
  --max N           Max URLs to process per run (default: SCOUT_MAX_RESULTS env)
  --delay SECS      Seconds between site fetches (default: SCOUT_REQUEST_DELAY)
  --queries N       Number of search query templates to use (default: all)
  --jobs N          Number of scout jobs to run sequentially (default: 1)
  --dry-run         Search + enrich but skip DB writes
  --verbose         Debug-level logging
"""

import argparse
import logging
import sys
import time
from urllib.parse import urlparse

# Add scout/ dir to path so sibling modules are importable directly
import os
sys.path.insert(0, os.path.dirname(__file__))

import config
import db
import enricher
import searcher

# ── Logging setup ─────────────────────────────────────────────────────────────

def _setup_logging(verbose: bool) -> None:
    level = logging.DEBUG if verbose else logging.INFO
    fmt   = "%(asctime)s [%(levelname)s] %(name)s — %(message)s"
    handlers = [logging.StreamHandler(sys.stdout)]

    if config.LOG_FILE:
        handlers.append(logging.FileHandler(config.LOG_FILE, encoding="utf-8"))

    logging.basicConfig(level=level, format=fmt, handlers=handlers)


log = logging.getLogger("scout")

# ── URL filtering ─────────────────────────────────────────────────────────────

# Domains to skip (aggregators, social nets, large nationals, etc.)
_BLOCKLIST = {
    "google.com", "google.co", "bing.com", "yahoo.com", "youtube.com",
    "facebook.com", "twitter.com", "x.com", "instagram.com", "tiktok.com",
    "wikipedia.org", "wikimedia.org", "reddit.com", "linkedin.com",
    "bbc.com", "bbc.co.uk", "cnn.com", "reuters.com", "apnews.com",
    "bloomberg.com", "theguardian.com", "nytimes.com", "washingtonpost.com",
}


def _is_blocked(url: str) -> bool:
    netloc = urlparse(url).netloc.lower().lstrip("www.")
    return any(netloc == b or netloc.endswith("." + b) for b in _BLOCKLIST)


def _is_valid_url(url: str) -> bool:
    parsed = urlparse(url)
    return parsed.scheme in ("http", "https") and bool(parsed.netloc)


# ── Single scout job ──────────────────────────────────────────────────────────

def run_job(
    conn,
    country: dict,
    max_results: int,
    num_queries: int,
    delay: float,
    dry_run: bool,
) -> dict:
    """
    Execute one scout job for `country`.
    Returns a summary dict.
    """
    queries = searcher.build_queries(country["name"], country["code"])
    if num_queries:
        queries = queries[:num_queries]

    log.info(
        "🔍 Scout job — country: %s (%s) | queries: %d | max: %d | dry_run: %s",
        country["name"], country["code"], len(queries), max_results, dry_run,
    )

    # Log run start
    run_id = None
    if not dry_run:
        run_id = db.log_run_start(conn, country, queries[0])

    urls_found   = 0
    urls_saved   = 0
    urls_skipped = 0
    notes_parts  = []

    national_city_id: int | None = None

    for result in searcher.search_all_queries(
        queries, num_per_query=max_results, delay=delay
    ):
        if urls_found >= max_results:
            break

        url     = result["url"]
        title   = result.get("title", "")
        snippet = result.get("snippet", "")

        if not _is_valid_url(url) or _is_blocked(url):
            log.debug("Skipping blocked/invalid URL: %s", url)
            urls_skipped += 1
            continue

        urls_found += 1
        log.info("  [%d] %s", urls_found, url)

        # ── Enrich ────────────────────────────────────────────────────────────
        meta = enricher.enrich(url, search_title=title, search_snippet=snippet)
        if meta is None:
            log.warning("    ↳ could not enrich — skipping")
            urls_skipped += 1
            continue

        log.info(
            "    ↳ name=%r  lang=%s  type=%s  rss=%d",
            meta["name"], meta["language"], meta["type"], len(meta["rss_feeds"]),
        )

        if dry_run:
            urls_saved += 1
            continue

        # ── Resolve city_id (national stub) ───────────────────────────────────
        if national_city_id is None:
            national_city_id = db.get_or_create_national_city(conn, country)

        # ── Upsert ────────────────────────────────────────────────────────────
        try:
            outlet_id, created = db.upsert_media_outlet(
                conn,
                city_id=national_city_id,
                data={
                    "url":         meta["url"],
                    "name":        meta["name"],
                    "type":        meta["type"],
                    "language":    meta["language"],
                    "description": meta["description"],
                    "logo_url":    meta["logo_url"],
                },
            )
            action = "created" if created else "updated"
            log.info("    ↳ DB %s — outlet #%d", action, outlet_id)
            urls_saved += 1
        except Exception as exc:
            log.error("    ↳ DB error for %s: %s", url, exc)
            urls_skipped += 1

    # ── Finish log ────────────────────────────────────────────────────────────
    if not dry_run and run_id:
        db.log_run_finish(
            conn, run_id,
            urls_found=urls_found,
            urls_saved=urls_saved,
            urls_skipped=urls_skipped,
            notes="; ".join(notes_parts),
        )

    log.info(
        "✅ Job done — found: %d | saved: %d | skipped: %d",
        urls_found, urls_saved, urls_skipped,
    )
    return {"found": urls_found, "saved": urls_saved, "skipped": urls_skipped}


# ── CLI ───────────────────────────────────────────────────────────────────────

def parse_args() -> argparse.Namespace:
    p = argparse.ArgumentParser(
        description="VoxTerra AI Scout Bot — discovers local news sites worldwide.",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog=__doc__,
    )
    p.add_argument("--country",  metavar="CODE",  help="ISO 2-letter country code")
    p.add_argument("--max",      metavar="N",     type=int,
                   default=config.MAX_RESULTS_PER_RUN)
    p.add_argument("--delay",    metavar="SECS",  type=float,
                   default=config.REQUEST_DELAY)
    p.add_argument("--queries",  metavar="N",     type=int, default=0,
                   help="Limit to first N query templates (0 = all)")
    p.add_argument("--jobs",     metavar="N",     type=int, default=1,
                   help="Number of sequential jobs (each picks a random country)")
    p.add_argument("--dry-run",  action="store_true",
                   help="Search + enrich but skip DB writes")
    p.add_argument("--verbose",  action="store_true")
    return p.parse_args()


def main() -> None:
    args = parse_args()
    _setup_logging(args.verbose)

    log.info("=" * 60)
    log.info("VoxTerra Scout Bot starting")
    log.info("  Backend: %s", "SerpAPI" if config.USE_SERPAPI else "DuckDuckGo (free)")
    log.info("  DB:      %s@%s/%s", config.DB_USERNAME, config.DB_HOST, config.DB_DATABASE)
    log.info("  Dry run: %s", args.dry_run)
    log.info("=" * 60)

    conn = None
    if not args.dry_run:
        try:
            conn = db.get_connection()
            db.ensure_scout_runs_table(conn)
            log.info("DB connection OK")
        except Exception as exc:
            log.error("Cannot connect to DB: %s", exc)
            log.info("Falling back to dry-run mode (no DB writes).")
            args.dry_run = True

    totals = {"found": 0, "saved": 0, "skipped": 0}

    for job_n in range(1, args.jobs + 1):
        if args.jobs > 1:
            log.info("── Job %d / %d ─────────────────────────────", job_n, args.jobs)

        # Resolve country
        if args.country:
            if conn:
                country = db.get_country_by_code(conn, args.country)
            else:
                # Dry-run without DB: build a minimal dict
                country = {"id": 0, "code": args.country.upper(), "name": args.country}
            if not country:
                log.error("Country code '%s' not found in DB.", args.country)
                sys.exit(1)
        else:
            if conn:
                country = db.get_random_country(conn)
            else:
                log.error("No country specified and no DB connection for random pick.")
                sys.exit(1)

        if not country:
            log.error("No countries in DB. Seed the countries table first.")
            sys.exit(1)

        summary = run_job(
            conn=conn,
            country=country,
            max_results=args.max,
            num_queries=args.queries,
            delay=args.delay,
            dry_run=args.dry_run,
        )
        for k in totals:
            totals[k] += summary[k]

        if job_n < args.jobs:
            time.sleep(2)  # brief pause between jobs

    if args.jobs > 1:
        log.info("=" * 60)
        log.info(
            "All jobs done — total found: %d | saved: %d | skipped: %d",
            totals["found"], totals["saved"], totals["skipped"],
        )

    if conn:
        conn.close()


if __name__ == "__main__":
    main()
