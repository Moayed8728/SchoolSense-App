from __future__ import annotations

import argparse
import json
import logging
import random
import re
import time
from dataclasses import dataclass
from datetime import datetime, timezone
from pathlib import Path
from typing import Iterable
from urllib.parse import parse_qsl, urlencode, urljoin, urlparse

import pandas as pd
import requests
from bs4 import BeautifulSoup
from requests.adapters import HTTPAdapter


BASE_DIR = Path(__file__).resolve().parent
RAW_DIR = BASE_DIR / "data" / "raw"
CLEAN_DIR = BASE_DIR / "data" / "clean"
LOG_DIR = BASE_DIR / "data" / "logs"

DEFAULT_START_URL = "https://www.international-schools-database.com/"
DEFAULT_RAW_OUTPUT = RAW_DIR / "international_schools_raw.json"
DEFAULT_CLEAN_OUTPUT = CLEAN_DIR / "international_schools_clean.csv"
DEFAULT_LOG_FILE = LOG_DIR / "international_schools_scraper.log"
DEFAULT_SKIPPED_OUTPUT = LOG_DIR / "skipped_records.json"
CONNECT_TIMEOUT_SECONDS = 45
READ_TIMEOUT_SECONDS = 45
REQUEST_RETRY_ATTEMPTS = 4
RETRY_BACKOFF_BASE_SECONDS = 2.0
RETRY_BACKOFF_MAX_SECONDS = 45.0
POLITE_DELAY_MIN_SECONDS = 1.5
POLITE_DELAY_MAX_SECONDS = 4.0

REJECTED_URL_PARTS = (
    "/articles/",
    "/news/",
    "/account/",
    "/about-us",
    "/country/",
    "/in#",
    "top-schools",
    "/terms",
    "/privacy",
    "/contact",
)

CSV_COLUMNS = [
    "name",
    "country",
    "city",
    "address",
    "websiteUrl",
    "sourceUrl",
    "description",
    "feesMin",
    "feesMax",
    "currency",
    "feePeriod",
    "curricula",
    "activities",
    "languages",
]

COUNTRY_ALIASES = {
    "sa": "SA",
    "ksa": "SA",
    "saudi": "SA",
    "saudi arabia": "SA",
    "ae": "AE",
    "uae": "AE",
    "u.a.e": "AE",
    "united arab emirates": "AE",
    "my": "MY",
    "malaysia": "MY",
    "us": "US",
    "usa": "US",
    "u.s.a": "US",
    "united states": "US",
    "united states of america": "US",
    "gb": "GB",
    "uk": "GB",
    "u.k": "GB",
    "britain": "GB",
    "great britain": "GB",
    "united kingdom": "GB",
}

CITY_COUNTRY_ALIASES = {
    "jeddah": "SA",
    "riyadh": "SA",
    "dammam-metropolitan-area": "SA",
    "medina": "SA",
    "thuwal": "SA",
    "yanbu": "SA",
    "dubai-sharjah-ajman": "AE",
    "abu-dhabi": "AE",
    "kuala-lumpur": "MY",
}

CURRICULUM_PATTERNS = {
    "British": [
        r"\bbritish curriculum\b",
        r"\buk curriculum\b",
        r"\bnational curriculum for england\b",
        r"\bbritish\b",
    ],
    "American": [
        r"\bamerican curriculum\b",
        r"\bus curriculum\b",
        r"\bu\.s\. curriculum\b",
        r"\bamerican\b",
    ],
    "IB": [
        r"\binternational baccalaureate\b",
        r"\bib diploma\b",
        r"\bib curriculum\b",
        r"\bib\b",
    ],
    "Cambridge": [
        r"\bcambridge\b",
        r"\bigcse\b",
        r"\ba levels?\b",
    ],
    "Indian CBSE": [
        r"\bcbse\b",
        r"\bindian curriculum\b",
    ],
    "SABIS": [
        r"\bsabis\b",
    ],
}

LANGUAGE_PATTERNS = [
    "English",
    "Arabic",
    "French",
    "Spanish",
    "German",
    "Mandarin",
    "Chinese",
    "Italian",
    "Hindi",
    "Urdu",
    "Malay",
]

ACTIVITY_PATTERNS = {
    "Football": [r"\bfootball\b", r"\bsoccer\b"],
    "Basketball": [r"\bbasketball\b"],
    "Swimming": [r"\bswimming\b"],
    "Robotics": [r"\brobotics\b"],
    "STEM Club": [r"\bstem\b"],
    "Arts": [r"\barts?\b", r"\bvisual arts?\b"],
    "Music": [r"\bmusic\b"],
    "Debate": [r"\bdebate\b"],
    "Science Club": [r"\bscience club\b"],
    "Coding Club": [r"\bcoding\b", r"\bprogramming\b"],
    "Drama": [r"\bdrama\b", r"\btheatre\b", r"\btheater\b"],
}

CURRENCY_PATTERNS = {
    "SAR": [r"\bSAR\b", r"\bSR\b", r"\bSaudi Riyal", r"﷼"],
    "AED": [r"\bAED\b", r"\bDirham"],
    "USD": [r"\bUSD\b", r"\$"],
    "GBP": [r"\bGBP\b", r"£"],
    "MYR": [r"\bMYR\b", r"\bRM\b"],
}


@dataclass
class ScrapeConfig:
    start_url: str
    limit: int | None
    max_pages: int
    delay: float
    max_delay: float
    raw_output: Path
    clean_output: Path
    log_file: Path
    skipped_output: Path
    normalize_only: bool


@dataclass
class ScrapeDiagnostics:
    skipped_records: list[dict]
    duplicate_count: int = 0
    failed_requests_count: int = 0

    def skip(self, raw: dict | None, reason: str) -> None:
        raw = raw or {}
        record = {
            "timestamp": now_iso(),
            "reason": reason,
            "sourceUrl": clean_space(raw.get("sourceUrl")),
            "schoolName": clean_space(raw.get("name_raw")),
        }
        self.skipped_records.append(record)
        if reason == "duplicate_school":
            self.duplicate_count += 1
        logging.warning(
            "Skipped record: reason=%s sourceUrl=%s schoolName=%s",
            reason,
            record["sourceUrl"],
            record["schoolName"],
        )

    def failed_request(self) -> None:
        self.failed_requests_count += 1


def setup_logging(log_file: Path) -> None:
    log_file.parent.mkdir(parents=True, exist_ok=True)
    logging.basicConfig(
        level=logging.INFO,
        format="%(asctime)s %(levelname)s %(message)s",
        handlers=[
            logging.FileHandler(log_file, encoding="utf-8"),
            logging.StreamHandler(),
        ],
    )


def make_session() -> requests.Session:
    session = requests.Session()
    adapter = HTTPAdapter(pool_connections=8, pool_maxsize=8)
    session.mount("http://", adapter)
    session.mount("https://", adapter)
    session.headers.update(
        {
            "User-Agent": (
                "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
                "AppleWebKit/537.36 (KHTML, like Gecko) "
                "Chrome/124.0 Safari/537.36 SchoolSenseResearchBot/1.0"
            ),
            "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,application/json;q=0.8,*/*;q=0.7",
            "Accept-Language": "en-US,en;q=0.9",
            "Connection": "keep-alive",
        }
    )
    return session


def fetch(
    session: requests.Session,
    url: str,
    delay: float,
    max_delay: float | None = None,
    diagnostics: ScrapeDiagnostics | None = None,
) -> requests.Response | None:
    max_delay = max_delay if max_delay is not None else POLITE_DELAY_MAX_SECONDS

    for attempt in range(1, REQUEST_RETRY_ATTEMPTS + 1):
        sleep_for = polite_delay_seconds(delay, max_delay)
        logging.info("Request delay %.2fs before attempt %s/%s %s", sleep_for, attempt, REQUEST_RETRY_ATTEMPTS, url)
        time.sleep(sleep_for)

        started_at = time.perf_counter()
        try:
            response = session.get(
                url,
                timeout=(CONNECT_TIMEOUT_SECONDS, READ_TIMEOUT_SECONDS),
            )
            duration = time.perf_counter() - started_at
            logging.info(
                "Request completed in %.2fs status=%s attempt=%s/%s url=%s",
                duration,
                response.status_code,
                attempt,
                REQUEST_RETRY_ATTEMPTS,
                url,
            )

            if response.status_code < 400:
                return response

            if response.status_code not in {429, 500, 502, 503, 504}:
                logging.warning("Fetch failed without retryable status %s %s", response.status_code, url)
                if diagnostics:
                    diagnostics.failed_request()
                return None

            logging.warning("Fetch retryable status=%s attempt=%s/%s %s", response.status_code, attempt, REQUEST_RETRY_ATTEMPTS, url)
        except (requests.Timeout, requests.ConnectionError) as exc:
            duration = time.perf_counter() - started_at
            logging.warning(
                "Fetch timeout/connection error after %.2fs attempt=%s/%s url=%s error=%s",
                duration,
                attempt,
                REQUEST_RETRY_ATTEMPTS,
                url,
                exc,
            )
        except requests.RequestException as exc:
            duration = time.perf_counter() - started_at
            logging.warning(
                "Fetch request error after %.2fs attempt=%s/%s url=%s error=%s",
                duration,
                attempt,
                REQUEST_RETRY_ATTEMPTS,
                url,
                exc,
            )
            if diagnostics:
                diagnostics.failed_request()
            return None

        if attempt < REQUEST_RETRY_ATTEMPTS:
            backoff = retry_backoff_seconds(attempt)
            logging.info("Retry backoff %.2fs before next attempt url=%s", backoff, url)
            time.sleep(backoff)

    logging.error("Fetch failed after all retries url=%s", url)
    if diagnostics:
        diagnostics.failed_request()
    return None


def polite_delay_seconds(min_delay: float, max_delay: float) -> float:
    lower = max(min_delay, POLITE_DELAY_MIN_SECONDS)
    upper = max(max_delay, lower, POLITE_DELAY_MAX_SECONDS)
    return random.uniform(lower, upper)


def retry_backoff_seconds(attempt: int) -> float:
    exponential = RETRY_BACKOFF_BASE_SECONDS * (2 ** (attempt - 1))
    jitter = random.uniform(0.25, 1.75)
    return min(exponential + jitter, RETRY_BACKOFF_MAX_SECONDS)


def soup_from_response(response: requests.Response) -> BeautifulSoup:
    return BeautifulSoup(response.text, "lxml")


def clean_space(value: str | None) -> str:
    if not value:
        return ""
    return re.sub(r"\s+", " ", value).strip()


def same_domain(url: str, other: str) -> bool:
    return urlparse(url).netloc == urlparse(other).netloc


def normalize_url(url: str, base_url: str) -> str:
    absolute = urljoin(base_url, url)
    parsed = urlparse(absolute)
    return parsed._replace(fragment="").geturl()


def inspect_api_candidates(
    session: requests.Session,
    start_url: str,
    delay: float,
    max_delay: float,
    diagnostics: ScrapeDiagnostics,
) -> list[str]:
    """Look for structured-data/API hints before falling back to HTML parsing."""
    response = fetch(session, start_url, delay, max_delay, diagnostics)
    if not response:
        return []

    soup = soup_from_response(response)
    candidates: set[str] = set()

    for script in soup.find_all("script", src=True):
        src = normalize_url(script["src"], start_url)
        if any(marker in src.lower() for marker in ("api", "graphql", "json", "_next")):
            candidates.add(src)

    inline_text = "\n".join(script.get_text(" ", strip=True) for script in soup.find_all("script"))
    for match in re.findall(r"""["']([^"']*(?:api|graphql|json)[^"']*)["']""", inline_text, flags=re.I):
        if match.startswith(("http", "/")):
            candidates.add(normalize_url(match, start_url))

    if soup.find("script", id="__NEXT_DATA__"):
        candidates.add("__NEXT_DATA__")

    if soup.find("script", type="application/ld+json"):
        candidates.add("application/ld+json")

    if candidates:
        logging.info("Structured/API candidates discovered: %s", ", ".join(sorted(candidates)))
    else:
        logging.info("No obvious structured API candidate found; using static HTML parsing.")

    return sorted(candidates)


def collect_from_json_ld(
    session: requests.Session,
    start_url: str,
    delay: float,
    max_delay: float,
    diagnostics: ScrapeDiagnostics,
) -> list[dict]:
    """Collect schema.org style school records if the page exposes them."""
    response = fetch(session, start_url, delay, max_delay, diagnostics)
    if not response:
        return []

    soup = soup_from_response(response)
    raw_records: list[dict] = []

    for script in soup.find_all("script", type="application/ld+json"):
        text = script.string or script.get_text(strip=True)
        if not text:
            continue
        try:
            payload = json.loads(text)
        except json.JSONDecodeError:
            continue

        nodes = payload if isinstance(payload, list) else [payload]
        for node in nodes:
            raw_records.extend(extract_school_nodes_from_json(node, start_url))

    if raw_records:
        logging.info("Collected %s raw records from JSON-LD.", len(raw_records))

    return raw_records


def extract_school_nodes_from_json(node: object, start_url: str) -> list[dict]:
    records: list[dict] = []
    if isinstance(node, list):
        for item in node:
            records.extend(extract_school_nodes_from_json(item, start_url))
        return records

    if not isinstance(node, dict):
        return records

    node_type = node.get("@type")
    types = node_type if isinstance(node_type, list) else [node_type]
    if any(str(item).lower() in {"school", "educationalorganization"} for item in types):
        address = node.get("address") if isinstance(node.get("address"), dict) else {}
        records.append(
            {
                "scrapeStrategy": "api_json_ld",
                "sourceUrl": node.get("url") or start_url,
                "listingPageUrl": start_url,
                "name_raw": clean_space(node.get("name")),
                "country_raw": clean_space(address.get("addressCountry")),
                "city_raw": clean_space(address.get("addressLocality")),
                "address_raw": clean_space(address.get("streetAddress")),
                "website_raw": clean_space(node.get("url")),
                "description_raw": clean_space(node.get("description")),
                "fees_raw": "",
                "curricula_raw": "",
                "activities_raw": "",
                "languages_raw": "",
                "originalText": json.dumps(node, ensure_ascii=False),
                "scrapedAt": now_iso(),
            }
        )

    for value in node.values():
        if isinstance(value, (dict, list)):
            records.extend(extract_school_nodes_from_json(value, start_url))

    return records


def collect_listing_pages(
    session: requests.Session,
    start_url: str,
    max_pages: int,
    delay: float,
    max_delay: float,
    diagnostics: ScrapeDiagnostics,
) -> list[str]:
    visited: set[str] = set()
    queue: list[str] = [start_url]
    listing_pages: list[str] = []

    while queue and len(listing_pages) < max_pages:
        current = queue.pop(0)
        if current in visited:
            continue
        visited.add(current)

        response = fetch(session, current, delay, max_delay, diagnostics)
        if not response:
            if current.rstrip("/") == start_url.rstrip("/"):
                logging.error(
                    "Listing page failed after all retries; stopping scraper gracefully: %s",
                    current,
                )
                break
            continue

        listing_pages.append(current)
        soup = soup_from_response(response)

        for link in find_pagination_links(soup, current):
            if link not in visited and link not in queue and same_domain(start_url, link):
                queue.append(link)

    logging.info("Collected %s listing page(s).", len(listing_pages))
    return listing_pages


def find_pagination_links(soup: BeautifulSoup, page_url: str) -> list[str]:
    links: set[str] = set()
    for anchor in soup.find_all("a", href=True):
        text = clean_space(anchor.get_text(" ")).lower()
        rel = " ".join(anchor.get("rel", [])).lower()
        href = normalize_url(anchor["href"], page_url)
        path_query = f"{urlparse(href).path}?{urlparse(href).query}".lower()

        if (
            "next" in text
            or "next" in rel
            or re.search(r"([?&]page=\d+|/page/\d+)", path_query)
        ):
            links.add(href)

    return sorted(links)


def collect_detail_urls(
    session: requests.Session,
    listing_pages: Iterable[str],
    start_url: str,
    limit: int | None,
    delay: float,
    max_delay: float,
    diagnostics: ScrapeDiagnostics,
) -> list[str]:
    detail_urls: list[str] = []
    seen: set[str] = set()

    for page_url in listing_pages:
        response = fetch(session, page_url, delay, max_delay, diagnostics)
        if not response:
            continue
        soup = soup_from_response(response)

        for url in extract_detail_links(soup, page_url, start_url):
            if url in seen:
                continue
            seen.add(url)
            detail_urls.append(url)
            if limit and len(detail_urls) >= limit:
                logging.info("Detail URL limit reached: %s", limit)
                return detail_urls

        if not detail_urls and probably_requires_javascript(soup):
            logging.warning(
                "The listing page looks JavaScript-rendered. Browser automation may be needed: %s",
                page_url,
            )

    logging.info("Collected %s school detail URL(s).", len(detail_urls))
    return detail_urls


def extract_detail_links(soup: BeautifulSoup, page_url: str, start_url: str) -> list[str]:
    links: list[str] = []
    seen: set[str] = set()

    for card in soup.select("div.school-row"):
        raw_hrefs: list[str] = []
        if card.get("href"):
            raw_hrefs.append(card["href"])
        raw_hrefs.extend(anchor["href"] for anchor in card.select("h2.school-name a[href]"))

        for raw_href in raw_hrefs:
            href = normalize_url(raw_href, page_url)
            if href not in seen and is_school_profile_url(href, page_url, start_url):
                links.append(href)
                seen.add(href)

    if links:
        return links

    # Last-resort static fallback for the same listing body shape. This stays
    # strict: no header/menu/footer/article links are accepted.
    for anchor in soup.select("h2.school-name a[href]"):
        href = normalize_url(anchor["href"], page_url)
        if href not in seen and is_school_profile_url(href, page_url, start_url):
            links.append(href)
            seen.add(href)

    return links


def is_school_profile_url(url: str, listing_url: str, start_url: str) -> bool:
    parsed = urlparse(url)
    path = parsed.path.lower()
    city_slug = city_slug_from_listing_url(listing_url)

    if not same_domain(start_url, url):
        return False
    if any(rejected in url.lower() for rejected in REJECTED_URL_PARTS):
        return False
    if not city_slug:
        return False

    return bool(re.fullmatch(rf"/in/{re.escape(city_slug)}/[a-z0-9][a-z0-9-]*/?", path))


def city_slug_from_listing_url(url: str) -> str:
    parts = [part for part in urlparse(url).path.lower().split("/") if part]
    if len(parts) >= 2 and parts[0] == "in":
        return parts[1]
    return ""


def extract_school_detail(
    session: requests.Session,
    detail_url: str,
    listing_url: str,
    delay: float,
    max_delay: float,
    diagnostics: ScrapeDiagnostics,
) -> dict | None:
    response = fetch(session, detail_url, delay, max_delay, diagnostics)
    if not response:
        return None

    soup = soup_from_response(response)
    content_soup = strip_non_content_tags(soup)
    page_text = clean_space(content_soup.get_text(" "))
    school_text = school_specific_text(page_text)
    facts_text = profile_facts_text(page_text)
    title = extract_title(content_soup)
    name = extract_name(content_soup, title)
    description = extract_description(content_soup)
    website = extract_official_website(content_soup, detail_url)
    address = extract_address_text(school_text)
    fees = extract_fees_text(school_text)
    country, city = infer_country_city(content_soup, detail_url)

    return {
        "scrapeStrategy": "static_html",
        "sourceUrl": detail_url,
        "listingPageUrl": listing_url,
        "name_raw": name,
        "country_raw": country,
        "city_raw": city,
        "address_raw": address,
        "website_raw": website,
        "description_raw": description,
        "fees_raw": fees,
        "curricula_raw": extract_curriculum_text(facts_text),
        "activities_raw": extract_activity_text(school_text),
        "languages_raw": extract_language_text(facts_text),
        "originalText": school_text,
        "scrapedAt": now_iso(),
    }


def strip_non_content_tags(soup: BeautifulSoup) -> BeautifulSoup:
    cleaned = BeautifulSoup(str(soup), "lxml")
    for element in cleaned.select("script, style, noscript, nav, header, footer"):
        element.decompose()
    return cleaned


def extract_title(soup: BeautifulSoup) -> str:
    og_title = soup.find("meta", property="og:title")
    if og_title and og_title.get("content"):
        return clean_space(og_title["content"])
    if soup.title:
        return clean_space(soup.title.get_text(" "))
    return ""


def extract_name(soup: BeautifulSoup, fallback_title: str) -> str:
    h1 = soup.find("h1")
    if h1:
        return clean_school_name(h1.get_text(" "))
    return clean_school_name(re.split(r"\s+[-|]\s+", fallback_title)[0])


def clean_school_name(value: str) -> str:
    value = clean_space(value)
    return re.sub(
        r":\s*Details(?:,\s*Fees and Reviews| and (?:Fees|Information)|,\s*Reviews)?\s*$",
        "",
        value,
        flags=re.I,
    )


def extract_description(soup: BeautifulSoup) -> str:
    meta = soup.find("meta", attrs={"name": "description"})
    if meta and meta.get("content"):
        return clean_space(meta["content"])

    og = soup.find("meta", property="og:description")
    if og and og.get("content"):
        return clean_space(og["content"])

    paragraphs = [
        clean_space(p.get_text(" "))
        for p in soup.find_all("p")
        if len(clean_space(p.get_text(" "))) >= 80
    ]
    return paragraphs[0] if paragraphs else ""


def extract_official_website(soup: BeautifulSoup, source_url: str) -> str:
    source_host = urlparse(source_url).netloc.lower()
    blocked_hosts = ("facebook.", "instagram.", "twitter.", "x.com", "linkedin.", "youtube.", "google.")

    preferred: list[str] = []
    external: list[str] = []

    for anchor in soup.find_all("a", href=True):
        href = normalize_url(anchor["href"], source_url)
        parsed = urlparse(href)
        text = clean_space(anchor.get_text(" ")).lower()

        if parsed.scheme not in {"http", "https"}:
            continue
        if parsed.netloc.lower() == source_host:
            continue
        if any(host in parsed.netloc.lower() for host in blocked_hosts):
            continue

        if any(word in text for word in ("website", "official", "visit")):
            preferred.append(href)
        else:
            external.append(href)

    return (preferred or external or [""])[0]


def extract_labeled_text(soup: BeautifulSoup, labels: tuple[str, ...], max_length: int = 600) -> str:
    label_re = re.compile("|".join(re.escape(label) for label in labels), re.I)

    for element in soup.find_all(string=label_re):
        parent = element.parent
        if not parent or parent.name in {"script", "style", "noscript"}:
            continue
        nearby = clean_space(parent.get_text(" "))
        if 15 < len(nearby) <= max_length:
            return nearby
        sibling = parent.find_next_sibling()
        if sibling:
            value = clean_space(sibling.get_text(" "))
            if value and len(value) <= max_length:
                return value

    for element in soup.find_all(True):
        class_text = " ".join(element.get("class", []))
        if label_re.search(class_text):
            value = clean_space(element.get_text(" "))
            if value and len(value) <= max_length:
                return value

    return ""


def school_specific_text(page_text: str) -> str:
    text = re.split(r"\bGet in contact with\b", page_text, maxsplit=1, flags=re.I)[0]
    return clean_space(text)


def profile_facts_text(page_text: str) -> str:
    match = re.search(
        r"\bCity\s+.+?(?=\s+Other Schools in\b|\s+Do you know\b|\s+Quick summary\b)",
        page_text,
        flags=re.I,
    )
    return clean_space(match.group(0)) if match else school_specific_text(page_text)


def extract_address_text(page_text: str) -> str:
    match = re.search(
        r"\bAddress:\s*(.+?)(?=\s+(?:Is this school|School details|Do you know|Get in contact|Other Schools in)\b|$)",
        page_text,
        flags=re.I,
    )
    return clean_space(match.group(1)) if match else ""


def extract_fees_text(page_text: str) -> str:
    no_public_fees = re.compile(r"does not make (?:their )?fees public|fees are not public", re.I)
    if no_public_fees.search(page_text):
        return ""

    match = re.search(
        r"\bYearly fees:?\s*(from:?\s*\S+\s*[\d,]+(?:\s*to:?\s*\S+\s*[\d,]+)?)",
        page_text,
        flags=re.I,
    )
    if match:
        return clean_space(f"Yearly fees {match.group(1)}")

    return ""


def extract_curriculum_text(page_text: str) -> str:
    snippets = []
    for canonical, patterns in CURRICULUM_PATTERNS.items():
        if any(re.search(pattern, page_text, re.I) for pattern in patterns):
            snippets.append(canonical)
    return "|".join(snippets)


def extract_activity_text(page_text: str) -> str:
    snippets = []
    for canonical, patterns in ACTIVITY_PATTERNS.items():
        if any(re.search(pattern, page_text, re.I) for pattern in patterns):
            snippets.append(canonical)
    return "|".join(snippets)


def extract_language_text(page_text: str) -> str:
    found = []
    for language in LANGUAGE_PATTERNS:
        if re.search(rf"\b{re.escape(language)}\b", page_text, re.I):
            found.append(language)
    return "|".join(found)


def infer_country_city(soup: BeautifulSoup, detail_url: str) -> tuple[str, str]:
    breadcrumb_text = " ".join(
        clean_space(item.get_text(" "))
        for item in soup.select('[class*="breadcrumb"], nav[aria-label*="breadcrumb" i]')
    )
    path_parts = [part for part in urlparse(detail_url).path.split("/") if part]
    combined = clean_space(f"{breadcrumb_text} {' '.join(path_parts)}")

    country = ""
    for alias, code in COUNTRY_ALIASES.items():
        if re.search(rf"\b{re.escape(alias)}\b", combined, re.I):
            country = code
            break
    if not country and len(path_parts) >= 2:
        country = CITY_COUNTRY_ALIASES.get(path_parts[1].lower(), "")

    city = ""
    if breadcrumb_text:
        pieces = [clean_space(piece) for piece in re.split(r"[>/|]", breadcrumb_text) if clean_space(piece)]
        for piece in reversed(pieces):
            if "school" not in piece.lower() and piece.lower() not in COUNTRY_ALIASES:
                city = piece
                break

    if not city and len(path_parts) >= 2:
        city = path_parts[-2].replace("-", " ").title()

    return country, city


def probably_requires_javascript(soup: BeautifulSoup) -> bool:
    text = clean_space(soup.get_text(" "))
    if len(text) < 300 and soup.find("script"):
        return True
    return bool(re.search(r"enable javascript|requires javascript", text, re.I))


def normalize_records(raw_records: Iterable[dict], diagnostics: ScrapeDiagnostics) -> list[dict]:
    clean_records: list[dict] = []
    seen: set[tuple[str, str, str]] = set()

    for raw in raw_records:
        if not isinstance(raw, dict):
            diagnostics.skip(None, "malformed_record")
            continue

        try:
            source_url = clean_space(raw.get("sourceUrl"))
            if source_url and urlparse(source_url).scheme not in {"http", "https"}:
                diagnostics.skip(raw, "invalid_url")
                continue

            name = clean_space(raw.get("name_raw"))
            city = clean_space(raw.get("city_raw"))
            website = clean_website_url(raw.get("website_raw"))
            country = normalize_country(raw.get("country_raw", ""))

            if not name:
                diagnostics.skip(raw, "missing_name")
                continue

            try:
                fees_min, fees_max, currency, fee_period = normalize_fees(raw.get("fees_raw", ""))
            except (TypeError, ValueError, re.error):
                diagnostics.skip(raw, "failed_fee_parsing")
                continue

            curricula = normalize_curricula(raw.get("curricula_raw", "") or raw.get("originalText", ""))
            activities = normalize_activities(raw.get("activities_raw", "") or raw.get("originalText", ""))
            languages = normalize_languages(raw.get("languages_raw", "") or raw.get("originalText", ""))

            dedupe_key = (
                normalize_key(name),
                city.lower(),
                normalize_website_key(website),
            )
            if dedupe_key in seen:
                diagnostics.skip(raw, "duplicate_school")
                continue
            seen.add(dedupe_key)

            clean_records.append(
                {
                    "name": name,
                    "country": country,
                    "city": city,
                    "address": clean_space(raw.get("address_raw")),
                    "websiteUrl": website,
                    "sourceUrl": source_url,
                    "description": clean_space(raw.get("description_raw")),
                    "feesMin": fees_min,
                    "feesMax": fees_max,
                    "currency": currency,
                    "feePeriod": fee_period,
                    "curricula": "|".join(curricula),
                    "activities": "|".join(activities),
                    "languages": "|".join(languages),
                }
            )
        except Exception:
            logging.exception("Failed normalization for %s", raw.get("sourceUrl"))
            diagnostics.skip(raw, "failed_normalization")
            continue

    return clean_records


def clean_website_url(value: str | None) -> str:
    value = clean_space(value)
    if not value:
        return ""

    return clean_tracking_params(value)


def clean_tracking_params(url: str) -> str:
    if not isinstance(url, str) or not url:
        return url

    parsed = urlparse(url)
    if parsed.scheme not in {"http", "https"}:
        return url

    if not parsed.netloc:
        return url

    tracking_params = {"utm_campaign", "utm_medium", "utm_source", "utm_term", "utm_content"}
    query = [
        (key, val)
        for key, val in parse_qsl(parsed.query, keep_blank_values=True)
        if key.lower() not in tracking_params
    ]

    return parsed._replace(query=urlencode(query, doseq=True)).geturl()


def normalize_country(value: str) -> str:
    value = clean_space(value)
    if not value:
        return ""
    lower = value.lower().replace(".", "")
    if lower in COUNTRY_ALIASES:
        return COUNTRY_ALIASES[lower]
    if len(value) == 2:
        return value.upper()
    return ""


def normalize_fees(text: str) -> tuple[str, str, str, str]:
    text = clean_space(text)
    if not text:
        return "", "", "", ""

    currency = ""
    for code, patterns in CURRENCY_PATTERNS.items():
        if any(re.search(pattern, text, re.I) for pattern in patterns):
            currency = code
            break

    numbers = [
        int(re.sub(r"[^\d]", "", value))
        for value in re.findall(r"\d[\d,\. ]{2,}\d", text)
        if re.sub(r"[^\d]", "", value)
    ]
    numbers = [number for number in numbers if number >= 100]

    fees_min = str(min(numbers)) if numbers else ""
    fees_max = str(max(numbers)) if numbers else ""

    fee_period = ""
    if re.search(r"\b(year|annual|annually|yearly)\b", text, re.I):
        fee_period = "yearly"
    elif re.search(r"\b(term|semester)\b", text, re.I):
        fee_period = "semester"
    elif numbers:
        fee_period = "yearly"

    return fees_min, fees_max, currency, fee_period


def normalize_curricula(text: str) -> list[str]:
    found = []
    for canonical, patterns in CURRICULUM_PATTERNS.items():
        if any(re.search(pattern, text, re.I) for pattern in patterns):
            found.append(canonical)
    return unique_preserve_order(found)


def normalize_activities(text: str) -> list[str]:
    found = []
    for canonical, patterns in ACTIVITY_PATTERNS.items():
        if any(re.search(pattern, text, re.I) for pattern in patterns):
            found.append(canonical)
    return unique_preserve_order(found)


def normalize_languages(text: str) -> list[str]:
    found = []
    for language in LANGUAGE_PATTERNS:
        if re.search(rf"\b{re.escape(language)}\b", text, re.I):
            found.append(language)
    return unique_preserve_order(found)


def unique_preserve_order(values: Iterable[str]) -> list[str]:
    seen = set()
    result = []
    for value in values:
        if value and value not in seen:
            seen.add(value)
            result.append(value)
    return result


def normalize_key(value: str) -> str:
    return re.sub(r"[^a-z0-9]+", "", value.lower())


def normalize_website_key(value: str) -> str:
    if not value:
        return ""
    parsed = urlparse(value)
    host = parsed.netloc.lower().removeprefix("www.")
    return host or normalize_key(value)


def save_raw(records: list[dict], output: Path, config: ScrapeConfig) -> None:
    output.parent.mkdir(parents=True, exist_ok=True)
    payload = {
        "source": config.start_url,
        "scrapedAt": now_iso(),
        "recordCount": len(records),
        "records": records,
    }
    output.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
    logging.info("Raw JSON saved to %s", output)


def save_clean(records: list[dict], output: Path) -> None:
    output.parent.mkdir(parents=True, exist_ok=True)
    frame = pd.DataFrame(records, columns=CSV_COLUMNS)
    frame.to_csv(output, index=False, encoding="utf-8")
    logging.info("Clean CSV saved to %s", output)


def load_raw_records(input_path: Path) -> list[dict]:
    if not input_path.exists():
        logging.error("Raw JSON file does not exist: %s", input_path)
        return []

    try:
        payload = json.loads(input_path.read_text(encoding="utf-8"))
    except json.JSONDecodeError:
        logging.exception("Raw JSON file is malformed: %s", input_path)
        return []

    records = payload.get("records", [])
    if not isinstance(records, list):
        logging.error("Raw JSON records field is not a list: %s", input_path)
        return []

    return records


def save_skipped_records(diagnostics: ScrapeDiagnostics, output: Path) -> None:
    output.parent.mkdir(parents=True, exist_ok=True)
    payload = {
        "generatedAt": now_iso(),
        "skippedCount": len(diagnostics.skipped_records),
        "duplicateCount": diagnostics.duplicate_count,
        "records": diagnostics.skipped_records,
    }
    output.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
    logging.info("Skipped records JSON saved to %s", output)


def collect_static_html(
    config: ScrapeConfig,
    session: requests.Session,
    diagnostics: ScrapeDiagnostics,
) -> list[dict]:
    listing_pages = collect_listing_pages(
        session=session,
        start_url=config.start_url,
        max_pages=config.max_pages,
        delay=config.delay,
        max_delay=config.max_delay,
        diagnostics=diagnostics,
    )
    detail_urls = collect_detail_urls(
        session=session,
        listing_pages=listing_pages,
        start_url=config.start_url,
        limit=config.limit,
        delay=config.delay,
        max_delay=config.max_delay,
        diagnostics=diagnostics,
    )

    records: list[dict] = []
    detail_to_listing = {url: config.start_url for url in detail_urls}
    for detail_url in detail_urls:
        record = extract_school_detail(
            session=session,
            detail_url=detail_url,
            listing_url=detail_to_listing.get(detail_url, config.start_url),
            delay=config.delay,
            max_delay=config.max_delay,
            diagnostics=diagnostics,
        )
        if record:
            records.append(record)
        if config.limit and len(records) >= config.limit:
            break

    return records


def collect_with_browser_automation() -> list[dict]:
    raise RuntimeError(
        "Browser automation is intentionally not enabled by default. "
        "Install Playwright and add a site-specific collector only if API/HTML parsing cannot access the data."
    )


def run(config: ScrapeConfig) -> None:
    setup_logging(config.log_file)
    logging.info("Starting SchoolSense public directory scraper.")
    logging.info("Start URL: %s", config.start_url)

    diagnostics = ScrapeDiagnostics(skipped_records=[])

    if config.normalize_only:
        raw_records = load_raw_records(config.raw_output)
        if not raw_records:
            save_skipped_records(diagnostics, config.skipped_output)
            logging.error(
                "Normalize-only aborted: raw collection count is 0; clean CSV was not overwritten."
            )
            print(
                "\nNormalize-only summary\n"
                "- raw records: 0\n"
                "- clean records: 0\n"
                "- skipped records: 0\n"
                "- output: not overwritten because raw JSON has no records\n"
            )
            return

        clean_records = normalize_records(raw_records, diagnostics)
        save_clean(clean_records, config.clean_output)
        save_skipped_records(diagnostics, config.skipped_output)
        print(
            "\nNormalize-only summary\n"
            f"- raw records: {len(raw_records)}\n"
            f"- clean records: {len(clean_records)}\n"
            f"- skipped records: {len(diagnostics.skipped_records)}\n"
            f"- duplicate count: {diagnostics.duplicate_count}\n"
        )
        return

    session = make_session()
    api_candidates = inspect_api_candidates(session, config.start_url, config.delay, config.max_delay, diagnostics)

    raw_records: list[dict] = []
    if "application/ld+json" in api_candidates or "__NEXT_DATA__" in api_candidates:
        raw_records = collect_from_json_ld(session, config.start_url, config.delay, config.max_delay, diagnostics)

    if not raw_records:
        raw_records = collect_static_html(config, session, diagnostics)

    if not raw_records:
        save_skipped_records(diagnostics, config.skipped_output)
        if diagnostics.failed_requests_count > 0:
            logging.error(
                "No records were collected after %s failed request(s); existing raw/clean outputs were not overwritten.",
                diagnostics.failed_requests_count,
            )
        else:
            logging.error("Raw collection count is 0; existing raw/clean outputs were not overwritten.")
        print(
            "\nScrape summary\n"
            "- raw records: 0\n"
            "- clean records: 0\n"
            "- skipped records: 0\n"
            "- duplicate count: 0\n"
            f"- failed requests count: {diagnostics.failed_requests_count}\n"
            f"- skipped records file: {config.skipped_output}\n"
            "- outputs: not overwritten because collection failed\n"
        )
        return

    save_raw(raw_records, config.raw_output, config)
    clean_records = normalize_records(raw_records, diagnostics)
    save_clean(clean_records, config.clean_output)
    save_skipped_records(diagnostics, config.skipped_output)

    summary = (
        "Done. Raw records: %s. Clean records: %s. Skipped records: %s. "
        "Duplicates: %s. Failed requests: %s."
    )
    logging.info(
        summary,
        len(raw_records),
        len(clean_records),
        len(diagnostics.skipped_records),
        diagnostics.duplicate_count,
        diagnostics.failed_requests_count,
    )
    print(
        "\nScrape summary\n"
        f"- raw records: {len(raw_records)}\n"
        f"- clean records: {len(clean_records)}\n"
        f"- skipped records: {len(diagnostics.skipped_records)}\n"
        f"- duplicate count: {diagnostics.duplicate_count}\n"
        f"- failed requests count: {diagnostics.failed_requests_count}\n"
        f"- skipped records file: {config.skipped_output}\n"
    )


def now_iso() -> str:
    return datetime.now(timezone.utc).isoformat()


def parse_args() -> ScrapeConfig:
    parser = argparse.ArgumentParser(
        description="Collect public school-directory data and export SchoolSense-ready CSV."
    )
    parser.add_argument("--start-url", default=DEFAULT_START_URL, help="Listing/city URL to begin from.")
    parser.add_argument("--limit", type=int, default=None, help="Maximum school detail pages to collect.")
    parser.add_argument("--max-pages", type=int, default=20, help="Maximum listing/pagination pages to scan.")
    parser.add_argument("--delay", type=float, default=POLITE_DELAY_MIN_SECONDS, help="Minimum delay between requests in seconds.")
    parser.add_argument("--max-delay", type=float, default=POLITE_DELAY_MAX_SECONDS, help="Maximum randomized delay between requests in seconds.")
    parser.add_argument("--raw-output", type=Path, default=DEFAULT_RAW_OUTPUT)
    parser.add_argument("--clean-output", type=Path, default=DEFAULT_CLEAN_OUTPUT)
    parser.add_argument("--log-file", type=Path, default=DEFAULT_LOG_FILE)
    parser.add_argument("--skipped-output", type=Path, default=DEFAULT_SKIPPED_OUTPUT)
    parser.add_argument("--normalize-only", action="store_true", help="Do not scrape; rebuild clean CSV from existing raw JSON.")
    args = parser.parse_args()

    return ScrapeConfig(
        start_url=args.start_url,
        limit=args.limit,
        max_pages=args.max_pages,
        delay=args.delay,
        max_delay=args.max_delay,
        raw_output=resolve_output_path(args.raw_output),
        clean_output=resolve_output_path(args.clean_output),
        log_file=resolve_output_path(args.log_file),
        skipped_output=resolve_output_path(args.skipped_output),
        normalize_only=args.normalize_only,
    )


def resolve_output_path(path: Path) -> Path:
    return path if path.is_absolute() else Path.cwd() / path


if __name__ == "__main__":
    run(parse_args())
