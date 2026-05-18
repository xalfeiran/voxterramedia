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
from typing import Dict, Generator, List, Set

from urllib.parse import parse_qs, urlparse as _urlparse

import requests
from bs4 import BeautifulSoup

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
_CC_TLD: Dict[str, str] = {
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


def build_queries(country_name: str, country_code: str) -> List[str]:
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

def _search_serpapi(query: str, num: int = 10) -> List[Dict]:
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


# ── DuckDuckGo HTML scraper (no library, Python 3.6 safe) ────────────────────
#
# POSTs to https://html.duckduckgo.com/html/ — the same lite endpoint used
# by curl-based integrations. No API key, no third-party package needed.
# Only requires `requests` + `beautifulsoup4`, which are already in deps.

_DDG_URL = "https://html.duckduckgo.com/html/"
_DDG_HEADERS = {
    "User-Agent":      config.USER_AGENT,
    "Accept-Language": "en-US,en;q=0.9",
    "Accept":          "text/html,application/xhtml+xml",
    "Referer":         "https://duckduckgo.com/",
}


def _search_ddg(query: str, num: int = 10) -> List[Dict]:
    """Scrape DuckDuckGo HTML lite endpoint — zero external dependencies."""
    try:
        resp = requests.post(
            _DDG_URL,
            data={"q": query, "kl": "us-en"},
            headers=_DDG_HEADERS,
            timeout=15,
            allow_redirects=True,
        )
        resp.raise_for_status()
    except Exception as exc:
        log.warning("DDG request failed: %s", exc)
        return []

    soup = BeautifulSoup(resp.text, "html.parser")
    results = []

    for result in soup.select(".result"):
        # Title + URL
        a_tag = result.select_one(".result__a")
        if not a_tag:
            continue
        title = a_tag.get_text(strip=True)
        href  = a_tag.get("href", "")

        # DDG wraps the real URL in a redirect — unwrap it
        if "duckduckgo.com/l/" in href:
            qs  = parse_qs(_urlparse(href).query)
            url = qs.get("uddg", [href])[0]
        else:
            url = href

        # Snippet
        snippet_tag = result.select_one(".result__snippet")
        snippet = snippet_tag.get_text(strip=True) if snippet_tag else ""

        if url:
            results.append({"url": url, "title": title, "snippet": snippet})
        if len(results) >= num:
            break

    return results


# ── Public API ────────────────────────────────────────────────────────────────

def search(query: str, num: int = 10) -> List[Dict]:
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
    seen: Set[str] = set()
    unique = []
    for r in results:
        url = r.get("url", "").strip().rstrip("/")
        if url and url not in seen:
            seen.add(url)
            r["url"] = url
            unique.append(r)
    return unique


def search_all_queries(
    queries: List[str],
    num_per_query: int = 10,
    delay: float = 1.5,
) -> Generator[Dict, None, None]:
    """
    Iterate over multiple query strings, yielding unique result dicts.
    Applies a polite delay between requests.
    """
    seen_urls: Set[str] = set()
    for query in queries:
        for result in search(query, num=num_per_query):
            url = result["url"]
            if url not in seen_urls:
                seen_urls.add(url)
                yield result
        time.sleep(delay)
