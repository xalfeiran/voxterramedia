"""
enricher.py — visit a news site URL and extract structured metadata.

Extracted fields:
  name         — site / publication name
  description  — meta description or OG description
  language     — ISO 639-1 code (from <html lang> or heuristic)
  logo_url     — favicon or OG image
  rss_feeds    — list of RSS/Atom feed URLs found on the page
  type         — media type: newspaper | digital | tv | radio | magazine | national
  canonical    — canonical URL if different from input
  social       — dict of social-media profile URLs found
"""

import json
import logging
import re
import socket
import time
from typing import Dict, List, Optional, Set, Tuple
from urllib.parse import urljoin, urlparse

import requests
from bs4 import BeautifulSoup

import config

log = logging.getLogger(__name__)

# ── HTTP session ──────────────────────────────────────────────────────────────

_SESSION = requests.Session()
_SESSION.headers.update({"User-Agent": config.USER_AGENT})


def _get(url: str) -> Optional[requests.Response]:
    """Fetch a URL, returning None on any error."""
    try:
        resp = _SESSION.get(url, timeout=config.REQUEST_TIMEOUT, allow_redirects=True)
        resp.raise_for_status()
        return resp
    except Exception as exc:
        log.debug("Fetch failed for %s: %s", url, exc)
        return None


# ── Type detection ────────────────────────────────────────────────────────────

_TYPE_HINTS: List[Tuple[str, List[str]]] = [
    ("tv",        ["tv", "television", "canal", "channel", "noticias-tv"]),
    ("radio",     ["radio", "fm", "am", "ondas", "onda"]),
    ("magazine",  ["magazine", "revista", "review", "weekly", "monthly"]),
    ("newspaper", ["diario", "newspaper", "periodico", "periódico", "herald",
                   "tribune", "gazette", "times", "post", "journal", "press",
                   "chronicle", "daily", "news"]),
    ("national",  ["national", "nationwide", "federal", "government"]),
    ("digital",   []),   # catch-all
]


def _detect_type(url: str, title: str, description: str) -> str:
    text = " ".join([url, title, description]).lower()
    for media_type, keywords in _TYPE_HINTS:
        if any(kw in text for kw in keywords):
            return media_type
    return "digital"


# ── Language detection ────────────────────────────────────────────────────────

_LANG_RE = re.compile(r"^([a-z]{2})(?:[_-].*)?$", re.IGNORECASE)

_TLD_LANG: Dict[str, str] = {
    "mx": "es", "ar": "es", "es": "es", "cl": "es", "co": "es",
    "pe": "es", "ve": "es", "br": "pt", "pt": "pt",
    "fr": "fr", "be": "fr", "de": "de", "at": "de", "ch": "de",
    "it": "it", "nl": "nl", "pl": "pl", "ru": "ru", "jp": "ja",
    "cn": "zh", "tw": "zh", "kr": "ko", "sa": "ar", "eg": "ar",
    "ma": "ar", "tr": "tr", "in": "hi", "bd": "bn", "pk": "ur",
}


def _detect_language(soup: BeautifulSoup, url: str) -> str:
    # 1. <html lang="...">
    lang_attr = soup.find("html", attrs={"lang": True})
    if lang_attr:
        m = _LANG_RE.match(lang_attr["lang"])
        if m:
            return m.group(1).lower()

    # 2. og:locale
    og_locale = soup.find("meta", property="og:locale")
    if og_locale and og_locale.get("content"):
        m = _LANG_RE.match(og_locale["content"])
        if m:
            return m.group(1).lower()

    # 3. TLD heuristic
    tld = urlparse(url).netloc.rsplit(".", 1)[-1].lower()
    if tld in _TLD_LANG:
        return _TLD_LANG[tld]

    return "en"


# ── Logo / favicon ────────────────────────────────────────────────────────────

def _find_logo(soup: BeautifulSoup, base_url: str) -> str:
    # 1. OG image
    og = soup.find("meta", property="og:image")
    if og and og.get("content"):
        return urljoin(base_url, og["content"])

    # 2. Apple touch icon
    apple = soup.find("link", rel=lambda r: r and "apple-touch-icon" in r)
    if apple and apple.get("href"):
        return urljoin(base_url, apple["href"])

    # 3. Standard favicon
    icon = soup.find("link", rel=lambda r: r and "icon" in r)
    if icon and icon.get("href"):
        return urljoin(base_url, icon["href"])

    # 4. Default /favicon.ico
    parsed = urlparse(base_url)
    return f"{parsed.scheme}://{parsed.netloc}/favicon.ico"


# ── RSS feeds ─────────────────────────────────────────────────────────────────

def _find_rss_feeds(soup: BeautifulSoup, base_url: str) -> List[str]:
    feeds = []
    for link in soup.find_all("link", type=re.compile(r"(rss|atom)\+xml", re.I)):
        href = link.get("href", "")
        if href:
            feeds.append(urljoin(base_url, href))
    return feeds[:5]  # cap at 5


# ── Social links ──────────────────────────────────────────────────────────────

_SOCIAL_PATTERNS: Dict[str, str] = {
    "twitter":   r"https?://(www\.)?(twitter|x)\.com/[\w_]+",
    "facebook":  r"https?://(www\.)?facebook\.com/[\w.]+",
    "instagram": r"https?://(www\.)?instagram\.com/[\w.]+",
    "youtube":   r"https?://(www\.)?youtube\.com/(channel|user|@)[\w-]+",
    "linkedin":  r"https?://(www\.)?linkedin\.com/company/[\w-]+",
}


def _find_social(soup: BeautifulSoup) -> Dict[str, str]:
    social: Dict[str, str] = {}
    for a in soup.find_all("a", href=True):
        href = a["href"]
        for platform, pattern in _SOCIAL_PATTERNS.items():
            if platform not in social and re.match(pattern, href, re.I):
                social[platform] = href
    return social


# ── Location extraction ───────────────────────────────────────────────────────

def _extract_location_from_html(soup: BeautifulSoup) -> Dict[str, str]:
    """
    Try to extract city / region from the page's own markup.
    Returns a dict with any of: city, region, country_name.
    """
    result = {}  # type: Dict[str, str]

    # 1. JSON-LD (schema.org) — most reliable
    for script in soup.find_all("script", type="application/ld+json"):
        try:
            data = json.loads(script.string or "")
            # Handle both single object and @graph array
            items = data if isinstance(data, list) else [data]
            for item in items:
                addr = item.get("address") or item.get("location", {})
                if isinstance(addr, dict):
                    if addr.get("addressLocality"):
                        result["city"] = addr["addressLocality"].strip()
                    if addr.get("addressRegion"):
                        result["region"] = addr["addressRegion"].strip()
                    if addr.get("addressCountry"):
                        result["country_name"] = addr["addressCountry"].strip()
                if result.get("city"):
                    return result
        except Exception:
            pass

    # 2. <meta name="geo.placename"> / <meta name="geo.region">
    geo_place = soup.find("meta", attrs={"name": re.compile(r"geo\.placename", re.I)})
    if geo_place and geo_place.get("content"):
        result["city"] = geo_place["content"].strip()

    geo_region = soup.find("meta", attrs={"name": re.compile(r"geo\.region", re.I)})
    if geo_region and geo_region.get("content"):
        result["region"] = geo_region["content"].strip()

    if result.get("city"):
        return result

    # 3. OpenGraph locality tags (used by some CMSes)
    for prop in ("og:locality", "og:region"):
        tag = soup.find("meta", property=prop)
        if tag and tag.get("content"):
            key = "city" if prop == "og:locality" else "region"
            result[key] = tag["content"].strip()

    return result


# ── Nominatim geocoder (OpenStreetMap, free, no key) ─────────────────────────

_NOMINATIM_URL = "https://nominatim.openstreetmap.org/search"
_NOMINATIM_HEADERS = {
    "User-Agent": config.USER_AGENT,
    "Accept-Language": "en",
}


def _geocode_nominatim(city: str, country_name: str) -> Optional[Dict]:
    """
    Look up `city, country_name` on Nominatim.
    Returns {"city": str, "region": str, "latitude": float, "longitude": float}
    or None on failure.
    """
    query = "{}, {}".format(city, country_name)
    try:
        resp = _SESSION.get(
            _NOMINATIM_URL,
            params={"q": query, "format": "json", "limit": 1, "addressdetails": 1},
            headers=_NOMINATIM_HEADERS,
            timeout=10,
        )
        resp.raise_for_status()
        data = resp.json()
    except Exception as exc:
        log.debug("Nominatim error for %r: %s", query, exc)
        return None

    if not data:
        return None

    hit     = data[0]
    addr    = hit.get("address", {})
    lat     = float(hit.get("lat", 0))
    lon     = float(hit.get("lon", 0))

    # Nominatim returns the city under several keys depending on place type
    city_name = (
        addr.get("city")
        or addr.get("town")
        or addr.get("village")
        or addr.get("municipality")
        or city  # fall back to the query term
    )
    region_name = addr.get("state") or addr.get("county") or ""

    return {
        "city":      city_name,
        "region":    region_name,
        "latitude":  lat,
        "longitude": lon,
    }


# ── IP geolocation fallback ───────────────────────────────────────────────────

_IPAPI_URL = "http://ip-api.com/json/{}"


def _geolocate_by_ip(domain: str) -> Optional[Dict]:
    """
    Resolve domain → IP, then call ip-api.com (free, 45 req/min).
    Returns {"city": str, "region": str, "latitude": float, "longitude": float}
    or None on failure.
    """
    try:
        ip = socket.gethostbyname(domain)
    except Exception:
        return None

    # Skip private/loopback addresses
    if ip.startswith(("10.", "192.168.", "127.", "172.")):
        return None

    try:
        resp = _SESSION.get(
            _IPAPI_URL.format(ip),
            timeout=8,
            headers={"User-Agent": config.USER_AGENT},
        )
        resp.raise_for_status()
        data = resp.json()
    except Exception as exc:
        log.debug("ip-api error for %s: %s", domain, exc)
        return None

    if data.get("status") != "success":
        return None

    return {
        "city":      data.get("city", ""),
        "region":    data.get("regionName", ""),
        "latitude":  float(data.get("lat", 0)),
        "longitude": float(data.get("lon", 0)),
    }


def resolve_geo(
    soup: BeautifulSoup,
    url: str,
    country_name: str,
) -> Optional[Dict]:
    """
    Best-effort geolocation for a news site.

    Strategy:
      1. Extract city name from the page HTML (schema.org / meta tags)
      2. If found, geocode it with Nominatim (OSM)
      3. If not found, fall back to IP geolocation (ip-api.com)

    Returns {"city": str, "region": str, "latitude": float, "longitude": float}
    or None if nothing could be determined.
    """
    # Step 1 — HTML extraction
    location = _extract_location_from_html(soup)
    city_hint = location.get("city", "").strip()

    # Step 2 — Nominatim geocode
    if city_hint:
        geo = _geocode_nominatim(city_hint, country_name)
        if geo and (geo["latitude"] or geo["longitude"]):
            log.debug("Geo (Nominatim): %s → %.4f, %.4f", city_hint, geo["latitude"], geo["longitude"])
            return geo

    # Step 3 — IP fallback
    domain = urlparse(url).netloc.lstrip("www.")
    geo = _geolocate_by_ip(domain)
    if geo and (geo["latitude"] or geo["longitude"]):
        log.debug("Geo (IP): %s → %.4f, %.4f", domain, geo["latitude"], geo["longitude"])
        return geo

    return None


# ── Canonical URL ─────────────────────────────────────────────────────────────

def _canonical(soup: BeautifulSoup, fallback: str) -> str:
    tag = soup.find("link", rel="canonical")
    if tag and tag.get("href"):
        return tag["href"].rstrip("/")
    og_url = soup.find("meta", property="og:url")
    if og_url and og_url.get("content"):
        return og_url["content"].rstrip("/")
    return fallback


# ── Main enrichment function ──────────────────────────────────────────────────

def enrich(url: str, search_title: str = "", search_snippet: str = "",
           country_name: str = "") -> Optional[Dict]:
    """
    Fetch `url` and return an enriched metadata dict, or None if unreachable.

    The dict always contains at least:
        url, name, description, language, type, logo_url,
        rss_feeds, social, canonical,
        city, region, latitude, longitude   ← geolocation fields
    """
    time.sleep(config.REQUEST_DELAY)

    resp = _get(url)
    if resp is None:
        return None

    # Resolve any redirects
    final_url = resp.url.rstrip("/")

    # Only process HTML
    content_type = resp.headers.get("Content-Type", "")
    if "html" not in content_type:
        log.debug("Non-HTML content at %s (%s) — skipping", url, content_type)
        return None

    soup = BeautifulSoup(resp.text, "html.parser")

    # ── Name ──────────────────────────────────────────────────────────────────
    name = ""
    # og:site_name is the most reliable
    og_site = soup.find("meta", property="og:site_name")
    if og_site and og_site.get("content"):
        name = og_site["content"].strip()
    if not name:
        og_title = soup.find("meta", property="og:title")
        if og_title and og_title.get("content"):
            name = og_title["content"].strip()
    if not name and soup.title:
        name = soup.title.string.strip() if soup.title.string else ""
    if not name:
        name = search_title or urlparse(final_url).netloc

    # ── Description ───────────────────────────────────────────────────────────
    description = ""
    og_desc = soup.find("meta", property="og:description")
    if og_desc and og_desc.get("content"):
        description = og_desc["content"].strip()
    if not description:
        meta_desc = soup.find("meta", attrs={"name": "description"})
        if meta_desc and meta_desc.get("content"):
            description = meta_desc["content"].strip()
    if not description:
        description = search_snippet

    # Truncate to ~500 chars
    description = description[:500]

    language    = _detect_language(soup, final_url)
    logo_url    = _find_logo(soup, final_url)
    rss_feeds   = _find_rss_feeds(soup, final_url)
    social      = _find_social(soup)
    canonical   = _canonical(soup, final_url)
    media_type  = _detect_type(final_url, name, description)

    # ── Geolocation ───────────────────────────────────────────────────────────
    geo = resolve_geo(soup, final_url, country_name or "")
    city_name   = geo["city"]    if geo else ""
    region_name = geo["region"]  if geo else ""
    latitude    = geo["latitude"]  if geo else None
    longitude   = geo["longitude"] if geo else None

    return {
        "url":         final_url,
        "canonical":   canonical,
        "name":        name,
        "description": description,
        "language":    language,
        "type":        media_type,
        "logo_url":    logo_url,
        "rss_feeds":   rss_feeds,
        "social":      social,
        "city":        city_name,
        "region":      region_name,
        "latitude":    latitude,
        "longitude":   longitude,
    }
