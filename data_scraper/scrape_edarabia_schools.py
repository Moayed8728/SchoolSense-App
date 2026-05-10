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

DEFAULT_START_URL = "https://www.edarabia.com/schools/saudi-arabia/"
DEFAULT_RAW_OUTPUT = RAW_DIR / "edarabia_schools_raw.json"
DEFAULT_CLEAN_OUTPUT = CLEAN_DIR / "edarabia_schools_clean.csv"
DEFAULT_LOG_FILE = LOG_DIR / "edarabia_scraper.log"
DEFAULT_SKIPPED_OUTPUT = LOG_DIR / "edarabia_skipped_records.json"

CONNECT_TIMEOUT_SECONDS = 45
READ_TIMEOUT_SECONDS = 45
REQUEST_RETRY_ATTEMPTS = 4
POLITE_DELAY_MIN_SECONDS = 1.5
POLITE_DELAY_MAX_SECONDS = 4.0
RETRY_BACKOFF_BASE_SECONDS = 2.0
RETRY_BACKOFF_MAX_SECONDS = 45.0

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

REJECTED_URL_PARTS = (
    "/schools/",
    "/nurseries/",
    "/universities/",
    "/courses/",
    "/jobs/",
    "/events/",
    "/news/",
    "/edtalk/",
    "/guide/",
    "/category/",
    "/tag/",
    "/author/",
    "/login",
    "/register",
    "/privacy",
    "/terms",
    "/contacts",
    "/advertise",
    "/school-fees",
    "/school-holidays",
    "/public-holidays",
)

SCHOOL_WORDS = (
    "school",
    "schools",
    "college",
    "academy",
    "academies",
    "education",
    "prep",
    "international",
)

CURRICULUM_ALIASES = {
    "British": [r"\bbritish\b", r"\buk eyfs\b", r"\bgcse\b", r"\bigcse\b"],
    "American": [r"\bamerican\b"],
    "IB": [r"\bib\b", r"\binternational baccalaureate\b"],
    "CBSE": [r"\bcbse\b"],
    "French": [r"\bfrench\b"],
    "German": [r"\bgerman\b"],
    "Arabic": [r"\barabic\b"],
    "Islamic": [r"\bislamic\b"],
    "Montessori": [r"\bmontessori\b"],
    "SABIS": [r"\bsabis\b"],
    "UK EYFS": [r"\buk eyfs\b"],
}

LANGUAGES = [
    "English",
    "Arabic",
    "French",
    "German",
    "Spanish",
    "Urdu",
    "Hindi",
]

ACTIVITY_ALIASES = {
    "Sports": [r"\bsports?\b"],
    "Arts": [r"\barts?\b"],
    "Music": [r"\bmusic\b"],
    "Swimming": [r"\bswimming\b"],
    "Football": [r"\bfootball\b", r"\bsoccer\b"],
    "STEM Club": [r"\bstem\b"],
    "After School Activities": [r"\bafter-school\b", r"\bafter school\b", r"\bco-curricular\b"],
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


@dataclass
class Diagnostics:
    skipped_records: list[dict]
    failed_requests_count: int = 0
    duplicate_count: int = 0

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
        logging.warning("Skipped record reason=%s sourceUrl=%s", reason, record["sourceUrl"])

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
            "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language": "en-US,en;q=0.9",
            "Connection": "keep-alive",
        }
    )
    return session


def fetch(
    session: requests.Session,
    url: str,
    config: ScrapeConfig,
    diagnostics: Diagnostics,
) -> requests.Response | None:
    for attempt in range(1, REQUEST_RETRY_ATTEMPTS + 1):
        delay = random.uniform(config.delay, config.max_delay)
        logging.info("Request delay %.2fs before attempt %s/%s %s", delay, attempt, REQUEST_RETRY_ATTEMPTS, url)
        time.sleep(delay)

        started = time.perf_counter()
        try:
            response = session.get(url, timeout=(CONNECT_TIMEOUT_SECONDS, READ_TIMEOUT_SECONDS))
            duration = time.perf_counter() - started
            logging.info("Request completed in %.2fs status=%s url=%s", duration, response.status_code, url)

            if response.status_code < 400:
                return response

            if response.status_code not in {429, 500, 502, 503, 504}:
                diagnostics.failed_request()
                logging.error("Non-retryable response status=%s url=%s", response.status_code, url)
                return None

            logging.warning("Retryable response status=%s url=%s", response.status_code, url)
        except (requests.Timeout, requests.ConnectionError) as exc:
            duration = time.perf_counter() - started
            logging.warning("Request timeout/connection error after %.2fs url=%s error=%s", duration, url, exc)
        except requests.RequestException as exc:
            duration = time.perf_counter() - started
            diagnostics.failed_request()
            logging.warning("Request failed after %.2fs url=%s error=%s", duration, url, exc)
            return None

        if attempt < REQUEST_RETRY_ATTEMPTS:
            backoff = retry_backoff_seconds(attempt)
            logging.info("Retry backoff %.2fs before next attempt url=%s", backoff, url)
            time.sleep(backoff)

    diagnostics.failed_request()
    logging.error("Request failed after all retries url=%s", url)
    return None


def retry_backoff_seconds(attempt: int) -> float:
    exponential = RETRY_BACKOFF_BASE_SECONDS * (2 ** (attempt - 1))
    jitter = random.uniform(0.25, 1.75)
    return min(exponential + jitter, RETRY_BACKOFF_MAX_SECONDS)


def soup_from_response(response: requests.Response) -> BeautifulSoup:
    return BeautifulSoup(response.text, "lxml")


def strip_non_content_tags(soup: BeautifulSoup) -> BeautifulSoup:
    cleaned = BeautifulSoup(str(soup), "lxml")
    for element in cleaned.select("script, style, noscript, nav, header, footer, form"):
        element.decompose()
    return cleaned


def collect_listing_pages(config: ScrapeConfig, session: requests.Session, diagnostics: Diagnostics) -> list[str]:
    pages: list[str] = []
    seen = set()
    queue = [config.start_url]

    while queue and len(pages) < config.max_pages:
        url = queue.pop(0)
        if url in seen:
            continue
        seen.add(url)

        response = fetch(session, url, config, diagnostics)
        if not response:
            if url.rstrip("/") == config.start_url.rstrip("/"):
                logging.error("Start listing failed after all retries; stopping gracefully.")
                return []
            continue

        pages.append(url)
        soup = soup_from_response(response)
        for link in find_pagination_links(soup, url, config.start_url):
            if link not in seen and link not in queue:
                queue.append(link)

    logging.info("Collected %s listing page(s).", len(pages))
    return pages


def find_pagination_links(soup: BeautifulSoup, page_url: str, start_url: str) -> list[str]:
    links: list[str] = []
    for anchor in soup.find_all("a", href=True):
        text = clean_space(anchor.get_text(" ")).lower()
        href = normalize_url(anchor["href"], page_url)
        if not same_domain(start_url, href):
            continue
        if text in {"next", "last"} or re.fullmatch(r"\d+", text):
            if href not in links:
                links.append(href)
    return links


def collect_detail_urls(
    config: ScrapeConfig,
    session: requests.Session,
    listing_pages: Iterable[str],
    diagnostics: Diagnostics,
) -> list[str]:
    detail_urls: list[str] = []
    seen = set()

    for page_url in listing_pages:
        response = fetch(session, page_url, config, diagnostics)
        if not response:
            continue
        soup = soup_from_response(response)

        for url in extract_school_profile_urls(soup, page_url, config.start_url):
            if url in seen:
                continue
            seen.add(url)
            detail_urls.append(url)
            if config.limit and len(detail_urls) >= config.limit:
                logging.info("Detail URL limit reached: %s", config.limit)
                return detail_urls

    logging.info("Collected %s Edarabia school profile URL(s).", len(detail_urls))
    return detail_urls


def extract_school_profile_urls(soup: BeautifulSoup, page_url: str, start_url: str) -> list[str]:
    urls: list[str] = []

    for heading in soup.select("h2 a[href], h3 a[href], h4 a[href], h5 a[href]"):
        href = normalize_url(heading["href"], page_url)
        text = clean_space(heading.get_text(" "))
        if is_school_profile_url(href, text, start_url) and href not in urls:
            urls.append(href)

    return urls


def is_school_profile_url(url: str, link_text: str, start_url: str) -> bool:
    parsed = urlparse(url)
    path = parsed.path.lower()
    text = link_text.lower()

    if not same_domain(start_url, url):
        return False
    if parsed.scheme not in {"http", "https"}:
        return False
    if any(part in path for part in REJECTED_URL_PARTS):
        return False
    if path in {"", "/"}:
        return False
    if len([part for part in path.split("/") if part]) != 1:
        return False
    return any(word in text for word in SCHOOL_WORDS) or any(word in path for word in SCHOOL_WORDS)


def scrape_details(
    config: ScrapeConfig,
    session: requests.Session,
    detail_urls: Iterable[str],
    diagnostics: Diagnostics,
) -> list[dict]:
    records: list[dict] = []
    for detail_url in detail_urls:
        response = fetch(session, detail_url, config, diagnostics)
        if not response:
            diagnostics.skip({"sourceUrl": detail_url}, "failed_detail_request")
            continue
        record = extract_school_detail(response.text, detail_url)
        if record:
            records.append(record)
        else:
            diagnostics.skip({"sourceUrl": detail_url}, "malformed_record")
    return records


def extract_school_detail(html: str, detail_url: str) -> dict | None:
    soup = strip_non_content_tags(BeautifulSoup(html, "lxml"))
    page_text = clean_space(soup.get_text(" "))
    name = extract_name(soup)
    if not name:
        return None

    address = extract_after_label(page_text, "Address")
    city = extract_city(page_text, detail_url)
    website = extract_website(soup, detail_url)
    description = extract_description(soup, page_text, name)
    fees_text = extract_after_label(page_text, "Annual Fees") or extract_fee_snippet(page_text)
    curricula_text = extract_after_label(page_text, "Curriculum")

    return {
        "scrapeStrategy": "edarabia_static_html",
        "sourceUrl": detail_url,
        "name_raw": name,
        "country_raw": "SA",
        "city_raw": city,
        "address_raw": address,
        "website_raw": website,
        "description_raw": description,
        "fees_raw": fees_text,
        "curricula_raw": curricula_text,
        "activities_raw": extract_activity_text(page_text),
        "languages_raw": extract_language_text(page_text),
        "originalText": page_text,
        "scrapedAt": now_iso(),
    }


def extract_name(soup: BeautifulSoup) -> str:
    h1 = soup.find("h1")
    if h1:
        return clean_space(h1.get_text(" ")).removesuffix(" Verified Listing").strip()
    title = soup.find("meta", property="og:title") or soup.find("meta", attrs={"name": "title"})
    if title and title.get("content"):
        return clean_space(re.split(r"\s*[\(|-]\s*", title["content"])[0])
    return ""


def extract_after_label(page_text: str, label: str) -> str:
    labels = "Founded|Address|Leadership|Curriculum|Annual Fees|Gender|Grades or Year Groups|Postal Code|Tel|Timings"
    pattern = rf"\b{re.escape(label)}:\s*(.+?)(?=\s+(?:{labels}):\s*|$)"
    match = re.search(pattern, page_text, flags=re.I)
    value = clean_space(match.group(1)) if match else ""
    if label.lower() == "address":
        value = re.split(r"\s+\(\s*Map\s*\)|\s+×\s+", value, maxsplit=1, flags=re.I)[0]
    return clean_space(value)


def extract_fee_snippet(page_text: str) -> str:
    match = re.search(r"\bSAR\s+[\d,]+(?:\s*-\s*[\d,]+)?", page_text, flags=re.I)
    return clean_space(match.group(0)) if match else ""


def extract_city(page_text: str, detail_url: str) -> str:
    address = extract_after_label(page_text, "Address")
    for city in ("Riyadh", "Jeddah", "Dammam", "Dhahran", "Al Khobar", "Khobar", "Al Jubail", "Jubail", "Madina", "Medina"):
        if re.search(rf"\b{re.escape(city)}\b", address, re.I) or city.lower().replace(" ", "-") in detail_url.lower():
            return "Medina" if city == "Madina" else city
    return ""


def extract_website(soup: BeautifulSoup, detail_url: str) -> str:
    for anchor in soup.find_all("a", href=True):
        text = clean_space(anchor.get_text(" ")).lower()
        href = normalize_url(anchor["href"], detail_url)
        parsed = urlparse(href)
        if "visit website" not in text:
            continue
        if parsed.scheme not in {"http", "https"}:
            continue
        if same_domain(detail_url, href):
            continue
        return clean_tracking_params(href)
    return ""


def extract_description(soup: BeautifulSoup, page_text: str, school_name: str) -> str:
    overview = re.search(
        r"\bEmail Admissions\s+(.+?)\s+(?:Leadership|Curriculum|Annual Fees|Gender):",
        page_text,
        flags=re.I,
    )
    if overview:
        return clean_space(overview.group(1))

    paragraphs = []
    for paragraph in soup.find_all("p"):
        text = clean_space(paragraph.get_text(" "))
        if len(text) >= 80 and school_name.lower() in text.lower():
            paragraphs.append(text)
    if paragraphs:
        return paragraphs[0]

    meta = soup.find("meta", attrs={"name": "description"})
    if meta and meta.get("content"):
        return clean_space(meta["content"])
    return ""


def normalize_records(raw_records: Iterable[dict], diagnostics: Diagnostics) -> list[dict]:
    clean_records: list[dict] = []
    seen = set()

    for raw in raw_records:
        if not isinstance(raw, dict):
            diagnostics.skip(None, "malformed_record")
            continue

        try:
            name = clean_space(raw.get("name_raw"))
            source_url = clean_space(raw.get("sourceUrl"))
            website = clean_tracking_params(clean_space(raw.get("website_raw")))

            if not name:
                diagnostics.skip(raw, "missing_name")
                continue
            if source_url and urlparse(source_url).scheme not in {"http", "https"}:
                diagnostics.skip(raw, "invalid_url")
                continue

            key = (normalize_key(name), clean_space(raw.get("city_raw")).lower(), normalize_website_key(website))
            if key in seen:
                diagnostics.skip(raw, "duplicate_school")
                continue
            seen.add(key)

            fees_min, fees_max, currency, fee_period = normalize_fees(raw.get("fees_raw", ""))
            clean_records.append(
                {
                    "name": name,
                    "country": "SA",
                    "city": clean_space(raw.get("city_raw")),
                    "address": clean_space(raw.get("address_raw")),
                    "websiteUrl": website,
                    "sourceUrl": source_url,
                    "description": clean_space(raw.get("description_raw")),
                    "feesMin": fees_min,
                    "feesMax": fees_max,
                    "currency": currency,
                    "feePeriod": fee_period,
                    "curricula": "|".join(normalize_curricula(raw.get("curricula_raw", ""))),
                    "activities": "|".join(normalize_activities(raw.get("activities_raw", ""))),
                    "languages": "|".join(normalize_languages(raw.get("languages_raw", ""))),
                }
            )
        except Exception:
            logging.exception("Failed to normalize record: %s", raw.get("sourceUrl"))
            diagnostics.skip(raw, "failed_normalization")
    return clean_records


def normalize_fees(text: str) -> tuple[str, str, str, str]:
    text = clean_space(text)
    if not text:
        return "", "", "", ""
    numbers = [int(re.sub(r"[^\d]", "", match)) for match in re.findall(r"\d[\d,]*", text)]
    numbers = [number for number in numbers if number >= 100]
    if not numbers:
        return "", "", "", ""
    return str(min(numbers)), str(max(numbers)), "SAR", "yearly"


def normalize_curricula(text: str) -> list[str]:
    found = []
    for canonical, patterns in CURRICULUM_ALIASES.items():
        if any(re.search(pattern, text, re.I) for pattern in patterns):
            found.append(canonical)
    return unique_preserve_order(found)


def normalize_activities(text: str) -> list[str]:
    found = []
    for canonical, patterns in ACTIVITY_ALIASES.items():
        if any(re.search(pattern, text, re.I) for pattern in patterns):
            found.append(canonical)
    return unique_preserve_order(found)


def normalize_languages(text: str) -> list[str]:
    found = []
    for language in LANGUAGES:
        if re.search(rf"\b{re.escape(language)}\b", text, re.I):
            found.append(language)
    return unique_preserve_order(found)


def extract_activity_text(text: str) -> str:
    return "|".join(normalize_activities(text))


def extract_language_text(text: str) -> str:
    return "|".join(normalize_languages(text))


def save_raw(records: list[dict], output: Path, config: ScrapeConfig) -> None:
    output.parent.mkdir(parents=True, exist_ok=True)
    payload = {
        "source": config.start_url,
        "appendMode": True,
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


def load_existing_raw_records(input_path: Path) -> list[dict]:
    if not input_path.exists():
        return []

    try:
        payload = json.loads(input_path.read_text(encoding="utf-8"))
    except json.JSONDecodeError:
        logging.warning("Existing raw JSON is malformed and will not be merged: %s", input_path)
        return []

    records = payload.get("records", [])
    if not isinstance(records, list):
        logging.warning("Existing raw JSON records field is not a list and will not be merged: %s", input_path)
        return []

    return [record for record in records if isinstance(record, dict)]


def merge_raw_records(existing_records: list[dict], new_records: list[dict]) -> list[dict]:
    merged: dict[str, dict] = {}

    for record in [*existing_records, *new_records]:
        key = raw_record_key(record)
        if not key:
            continue
        merged[key] = record

    return list(merged.values())


def raw_record_key(record: dict) -> str:
    source_url = clean_space(record.get("sourceUrl"))
    if source_url:
        return f"source:{source_url.lower().rstrip('/')}"

    name = normalize_key(clean_space(record.get("name_raw")))
    city = clean_space(record.get("city_raw")).lower()
    website = normalize_website_key(clean_space(record.get("website_raw")))
    if name:
        return f"school:{name}:{city}:{website}"
    return ""


def save_skipped(diagnostics: Diagnostics, output: Path) -> None:
    output.parent.mkdir(parents=True, exist_ok=True)
    payload = {
        "generatedAt": now_iso(),
        "skippedCount": len(diagnostics.skipped_records),
        "failedRequestsCount": diagnostics.failed_requests_count,
        "duplicateCount": diagnostics.duplicate_count,
        "records": diagnostics.skipped_records,
    }
    output.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
    logging.info("Skipped records JSON saved to %s", output)


def run(config: ScrapeConfig) -> None:
    setup_logging(config.log_file)
    diagnostics = Diagnostics(skipped_records=[])
    session = make_session()

    logging.info("Starting Edarabia scraper.")
    logging.info("Start URL: %s", config.start_url)

    listing_pages = collect_listing_pages(config, session, diagnostics)
    detail_urls = collect_detail_urls(config, session, listing_pages, diagnostics) if listing_pages else []
    new_raw_records = scrape_details(config, session, detail_urls, diagnostics) if detail_urls else []

    if not new_raw_records:
        save_skipped(diagnostics, config.skipped_output)
        logging.error("Scrape returned 0 records; raw/clean outputs were not overwritten.")
        print_summary(0, 0, diagnostics, config, not_overwritten=True)
        return

    existing_raw_records = load_existing_raw_records(config.raw_output)
    raw_records = merge_raw_records(existing_raw_records, new_raw_records)
    logging.info(
        "Merged Edarabia raw records: existing=%s new=%s total=%s",
        len(existing_raw_records),
        len(new_raw_records),
        len(raw_records),
    )

    clean_records = normalize_records(raw_records, diagnostics)
    save_raw(raw_records, config.raw_output, config)
    save_clean(clean_records, config.clean_output)
    save_skipped(diagnostics, config.skipped_output)
    print_summary(len(raw_records), len(clean_records), diagnostics, config, not_overwritten=False)


def print_summary(
    raw_count: int,
    clean_count: int,
    diagnostics: Diagnostics,
    config: ScrapeConfig,
    not_overwritten: bool,
) -> None:
    print(
        "\nEdarabia scrape summary\n"
        f"- raw records: {raw_count}\n"
        f"- clean records: {clean_count}\n"
        f"- skipped records: {len(diagnostics.skipped_records)}\n"
        f"- failed requests: {diagnostics.failed_requests_count}\n"
        f"- raw output: {config.raw_output}\n"
        f"- clean output: {config.clean_output}\n"
        f"- skipped output: {config.skipped_output}\n"
        + ("- outputs: not overwritten because scrape returned 0 records\n" if not_overwritten else "")
    )


def clean_tracking_params(url: str) -> str:
    if not isinstance(url, str) or not url:
        return url
    parsed = urlparse(url)
    if parsed.scheme not in {"http", "https"} or not parsed.netloc:
        return url
    blocked = {"utm_campaign", "utm_medium", "utm_source", "utm_term", "utm_content"}
    query = [(key, value) for key, value in parse_qsl(parsed.query, keep_blank_values=True) if key.lower() not in blocked]
    return parsed._replace(query=urlencode(query, doseq=True)).geturl()


def normalize_url(url: str, base_url: str) -> str:
    absolute = urljoin(base_url, url)
    parsed = urlparse(absolute)
    return parsed._replace(fragment="").geturl()


def same_domain(url: str, other: str) -> bool:
    return urlparse(url).netloc.lower() == urlparse(other).netloc.lower()


def clean_space(value: str | None) -> str:
    if not value:
        return ""
    return re.sub(r"\s+", " ", str(value)).strip()


def normalize_key(value: str) -> str:
    return re.sub(r"[^a-z0-9]+", "", value.lower())


def normalize_website_key(value: str) -> str:
    if not value:
        return ""
    parsed = urlparse(value)
    return parsed.netloc.lower().removeprefix("www.") or normalize_key(value)


def unique_preserve_order(values: Iterable[str]) -> list[str]:
    seen = set()
    result = []
    for value in values:
        if value and value not in seen:
            seen.add(value)
            result.append(value)
    return result


def now_iso() -> str:
    return datetime.now(timezone.utc).isoformat()


def parse_args() -> ScrapeConfig:
    parser = argparse.ArgumentParser(description="Scrape public Saudi school listings from Edarabia.")
    parser.add_argument("--start-url", default=DEFAULT_START_URL)
    parser.add_argument("--limit", type=int, default=None)
    parser.add_argument("--max-pages", type=int, default=5)
    parser.add_argument("--delay", type=float, default=POLITE_DELAY_MIN_SECONDS)
    parser.add_argument("--max-delay", type=float, default=POLITE_DELAY_MAX_SECONDS)
    parser.add_argument("--raw-output", type=Path, default=DEFAULT_RAW_OUTPUT)
    parser.add_argument("--clean-output", type=Path, default=DEFAULT_CLEAN_OUTPUT)
    parser.add_argument("--log-file", type=Path, default=DEFAULT_LOG_FILE)
    parser.add_argument("--skipped-output", type=Path, default=DEFAULT_SKIPPED_OUTPUT)
    args = parser.parse_args()

    return ScrapeConfig(
        start_url=args.start_url,
        limit=args.limit,
        max_pages=args.max_pages,
        delay=args.delay,
        max_delay=max(args.max_delay, args.delay),
        raw_output=resolve_output_path(args.raw_output),
        clean_output=resolve_output_path(args.clean_output),
        log_file=resolve_output_path(args.log_file),
        skipped_output=resolve_output_path(args.skipped_output),
    )


def resolve_output_path(path: Path) -> Path:
    return path if path.is_absolute() else Path.cwd() / path


if __name__ == "__main__":
    run(parse_args())
