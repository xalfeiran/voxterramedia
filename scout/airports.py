"""
airports.py — resolve a city name to an IATA airport / metro code.

Uses a bundled, curated city -> IATA map (data/airport_codes.json), keyed by
ISO-2 country code. Matching is accent-insensitive and slug-based so that
"São Paulo", "sao-paulo" and "SAO PAULO" all resolve identically.

A single code may map to several cities (DFW = Dallas + Fort Worth); that is
intentional — the API news endpoint aggregates across all cities sharing a code.
"""

import json
import logging
import os
import re
import unicodedata
from typing import Dict, Optional

log = logging.getLogger("scout.airports")

_DATA_PATH = os.path.join(os.path.dirname(__file__), "data", "airport_codes.json")
_MAP: Optional[Dict[str, Dict[str, str]]] = None


def _ascii(text: str) -> str:
    """Strip accents/diacritics: 'München' -> 'Munchen'."""
    return (
        unicodedata.normalize("NFKD", text)
        .encode("ascii", "ignore")
        .decode("ascii")
    )


def _key(text: str) -> str:
    """Normalize a city name to a comparison key (ascii, lowercase, hyphenated)."""
    text = _ascii(text or "").lower().strip()
    text = re.sub(r"[^\w\s-]", "", text)
    text = re.sub(r"[\s_-]+", "-", text)
    return text.strip("-")


def _load() -> Dict[str, Dict[str, str]]:
    global _MAP
    if _MAP is None:
        try:
            with open(_DATA_PATH, encoding="utf-8") as fh:
                raw = json.load(fh)
            _MAP = {}
            for country, cities in raw.items():
                sub = {}
                for city, code in cities.items():
                    if code:
                        sub[_key(city)] = str(code).strip().upper()
                _MAP[country.strip().upper()] = sub
            log.debug("Loaded airport map: %d countries", len(_MAP))
        except Exception as exc:  # noqa: BLE001 - never let this break a scout run
            log.warning("Could not load airport map (%s); codes disabled.", exc)
            _MAP = {}
    return _MAP


def resolve(country_code: str, city_name: str) -> Optional[str]:
    """
    Return the IATA code for a city within a country, or None if unknown.

    >>> resolve("US", "Fort Worth")
    'DFW'
    """
    if not country_code or not city_name:
        return None
    sub = _load().get(country_code.strip().upper())
    if not sub:
        return None
    return sub.get(_key(city_name))
