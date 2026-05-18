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

import logging
import re
import time
from urllib.parse import urljoin, urlparse

import requests
from bs4 import BeautifulSoup

import config

log = logging.getLogger(__name__)

# ── HTTP session ──────────────────────────────────────────────────────────────

_SESSION = requests.Session()
_SESSION.headers.update({"User-Agent": config.USER_AGENT})


def _get(url: str) -> requests.Response | None:
    """Fetch a URL, returning None on any error."""
    try:
        resp = _SESSION.get(url, timeout=config.REQUEST_TIMEOUT, allow_redirects=True)
        resp.raise_for_status()
        return resp
    except Exception as exc:
        log.debug("Fetch failed for %s: %s", url, exc)
        return None


# ── Type detection ────────────────────────────────────────────────────────────

_TYPE_HINTS: list[tuple[str, list[str]]] = [
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

_TLD_LANG: dict[str, str] = {
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

def _find_rss_feeds(soup: BeautifulSoup, base_url: str) -> list[str]:
    feeds = []
    for link in soup.find_all("link", type=re.compile(r"(rss|atom)\+xml", re.I)):
        href = link.get("href", "")
        if href:
            feeds.append(urljoin(base_url, href))
    return feeds[:5]  # cap at 5


# ── Social links ──────────────────────────────────────────────────────────────

_SOCIAL_PATTERNS: dict[str, str] = {
    "twitter":   r"https?://(www\.)?(twitter|x)\.com/[\w_]+",
    "facebook":  r"https?://(www\.)?facebook\.com/[\w.]+",
    "instagram": r"https?://(www\.)?instagram\.com/[\w.]+",
    "youtube":   r"https?://(www\.)?youtube\.com/(channel|user|@)[\w-]+",
    "linkedin":  r"https?://(www\.)?linkedin\.com/company/[\w-]+",
}


def _find_social(soup: BeautifulSoup) -> dict[str, str]:
    social: dict[str, str] = {}
    for a in soup.find_all("a", href=True):
        href = a["href"]
        for platform, pattern in _SOCIAL_PATTERNS.items():
            if platform not in social and re.match(pattern, href, re.I):
                social[platform] = href
    return social


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

def enrich(url: str, search_title: str = "", search_snippet: str = "") -> dict | None:
    """
    Fetch `url` and return an enriched metadata dict, or None if unreachable.

    The dict always contains at least:
        url, name, description, language, type, logo_url,
        rss_feeds, social, canonical
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
    }
