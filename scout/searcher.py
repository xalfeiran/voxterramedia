"""
searcher.py — search backend adapter for the VoxTerra scout engine.

Priority:
  1. SerpAPI  (when SERPAPI_KEY is set)  — reliable, structured JSON
  2. DuckDuckGo (duckduckgo-search lib)  — free fallback, no key needed

Each backend returns a list of dicts:
    { "url": str, "title": str, "snippet": str }
"""

import logging
import time
from typing import Generator

import config

log = logging.getLogger(__name__)

# ── Query templates ───────────────────────────────────────────────────────────

# Each template has a {country} placeholder.
# We mix English + local-language patterns to broaden coverage.
_QUERY_TEMPLATES = [
    "local news site {country}",
    "regional newspaper {country}",
    "online news portal {country}",
    "noticias locales {country}",           # Spanish
    "journal local {country}",              # French
    "lokale nachrichten {country}",         # German
    "notícias regionais {country}",         # Portuguese
    "{country} local news website",
    "{country} regional media outlet",
    "{country} independent news",
    "site:*.{tld} noticias OR news",        # TLD-scoped search (filled below)
]

# Country-code → ccTLD map (top 80 countries by coverage)
_CC_TLD: dict[str, str] = {
    "MX": "com.mx", "AR": "com.ar", "BR": "com.br", "CL": "cl",
    "CO": "com.co", "PE": "com.pe", "VE": "com.ve", "EC": "com.ec",
    "BO": "com.bo", "PY": "com.py", "UY": "com.uy", "CR": "co.cr",
    "GT": "com.gt", "HN": "hn",     "SV": "com.sv", "NI": "com.ni",
    "PA": "com.pa", "DO": "com.do", "CU": "co.cu",  "PR": "com.pr",
    "ES": "es",     "FR": "fr",     "DE": "de",     "IT": "it",
    "PT": "pt",     "GB": "co.uk",  "NL": "nl",     "BE": "be",
    "CH": "ch",     "AT": "at",     "PL": "pl",     "SE": "se",
    "NO": "no",     "DK": "dk",     "FI": "fi",     "IE": "ie",
    "GR": "gr",     "RO": "ro",     "CZ": "cz",     "HU": "hu",
    "SK": "sk",     "HR": "hr",     "RS": "rs",     "BG": "bg",
    "UA": "ua",     "RU": "ru",     "TR": "com.tr", "IL": "co.il",
    "SA": "com.sa", "AE": "ae",     "EG": "com.eg", "MA": "co.ma",
    "NG": "com.ng", "ZA": "co.za",  "KE": "co.ke",  "GH": "com.gh",
    "ET": "com.et", "TZ": "co.tz",  "UG": "co.ug",  "CM": "cm",
    "IN": "co.in",  "PK": "com.pk", "BD": "com.bd", "LK": "lk",
    "NP": "com.np", "MM": "com.mm", "TH": "co.th",  "VN": "vn",
    "ID": "co.id",  "MY": "com.my", "PH": "com.ph", "SG": "com.sg",
    "CN": "cn",     "JP": "co.jp",  "KR": "co.kr",  "TW": "com.tw",
    "HK": "hk",     "AU": "com.au", "NZ": "co.nz",  "CA": "ca",
    "US": "com",    "MX": "com.mx",
}


def build_queries(country_name: str, country_code: str) -> list[str]:
    """Return a list of search query strings for a given country."""
    tld = _CC_TLD.get(country_code.upper(), "")
    queries = []
    for tmpl in _QUERY_TEMPLATES:
        if "{tld}" in tmpl:
            if tld:
                queries.append(tmpl.format(country=country_name, tld=tld))
        else:
            queries.append(tmpl.format(country=country_name))
    return queries


# ── SerpAPI backend ───────────────────────────────────────────────────────────

def _search_serpapi(query: str, num: int = 10) -> list[dict]:
    try:
        from serpapi import GoogleSearch  # type: ignore
    except ImportError:
        log.error("serpapi package not installed. Run: pip install google-search-results")
        return []

    params = {
        "q":       query,
        "num":     num,
        "hl":      "en",
        "gl":      "us",
        "api_key": config.SERPAPI_KEY,
    }
    try:
        results = GoogleSearch(params).get_dict()
        organic = results.get("organic_results", [])
        return [
            {
                "url":     r.get("link", ""),
                "title":   r.get("title", ""),
                "snippet": r.get("snippet", ""),
            }
            for r in organic
            if r.get("link")
        ]
    except Exception as exc:
        log.warning("SerpAPI error: %s", exc)
        return []


# ── DuckDuckGo fallback ───────────────────────────────────────────────────────

def _search_ddg(query: str, num: int = 10) -> list[dict]:
    try:
        from ddgs import DDGS  # type: ignore  (pip install ddgs)
    except ImportError:
        try:
            from duckduckgo_search import DDGS  # type: ignore  (legacy name)
        except ImportError:
            log.error(
                "ddgs package not installed. Run: pip install ddgs"
            )
            return []

    results = []
    try:
        with DDGS() as ddgs:
            for r in ddgs.text(query, max_results=num):
                results.append({
                    "url":     r.get("href", ""),
                    "title":   r.get("title", ""),
                    "snippet": r.get("body", ""),
                })
    except Exception as exc:
        log.warning("DDG error: %s", exc)
    return results


# ── Public API ────────────────────────────────────────────────────────────────

def search(query: str, num: int = 10) -> list[dict]:
    """
    Run a search and return a deduplicated list of result dicts.
    Uses SerpAPI if configured, otherwise DuckDuckGo.
    """
    if config.USE_SERPAPI:
        log.debug("SerpAPI search: %s", query)
        results = _search_serpapi(query, num)
    else:
        log.debug("DDG search: %s", query)
        results = _search_ddg(query, num)

    # Deduplicate by URL
    seen: set[str] = set()
    unique = []
    for r in results:
        url = r.get("url", "").strip().rstrip("/")
        if url and url not in seen:
            seen.add(url)
            r["url"] = url
            unique.append(r)
    return unique


def search_all_queries(
    queries: list[str],
    num_per_query: int = 10,
    delay: float = 1.5,
) -> Generator[dict, None, None]:
    """
    Iterate over multiple query strings, yielding unique result dicts.
    Applies a polite delay between requests.
    """
    seen_urls: set[str] = set()
    for query in queries:
        for result in search(query, num=num_per_query):
            url = result["url"]
            if url not in seen_urls:
                seen_urls.add(url)
                yield result
        time.sleep(delay)
