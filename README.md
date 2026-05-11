# SchoolSense

![Laravel](https://img.shields.io/badge/Laravel-12-red?style=for-the-badge&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-pgvector-4169E1?style=for-the-badge&logo=postgresql&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-7.x-646CFF?style=for-the-badge&logo=vite&logoColor=white)
![Tests](https://img.shields.io/badge/Tests-Pest%20%2F%20PHPUnit-6E9F18?style=for-the-badge)

## 🚀 Overview

**SchoolSense** is a full-stack school discovery and management platform built to help parents search, compare, and evaluate schools using structured data and AI-assisted insights.

The application combines a searchable school directory, semantic search, school comparison summaries, parent favorites, role-based dashboards, school manager workflows, admin review tools, and data enrichment pipelines. It is designed as a practical software engineering project that demonstrates backend architecture, relational data modeling, AI integration, data ingestion, authorization, testing, and maintainable Laravel application design.

SchoolSense includes a RAG-style retrieval pipeline where structured school profiles are converted into searchable documents, embedded with Gemini, stored in PostgreSQL using pgvector, and retrieved through semantic similarity plus metadata filters.

## 🎯 What Problem It Solves

Finding the right school can involve comparing incomplete, scattered, and difficult-to-filter information across many sources. SchoolSense centralizes school data and makes it easier to:

- Discover schools using natural language and structured filters
- Compare schools using relevant parent-facing criteria
- Save favorite schools for later review
- Keep school profile data accurate through managed update workflows
- Enrich school records with contact data from official websites
- Support admin review before public data changes are applied

## ✨ Key Features

| Area | Functionality |
| --- | --- |
| School directory | Browse school profiles with fees, curricula, activities, languages, contact details, and descriptions |
| Semantic search | Search schools using Gemini embeddings and PostgreSQL pgvector similarity queries |
| Structured filtering | Filter by city, fee range, curriculum, activities, and languages |
| AI comparison | Generate concise school comparison summaries using Gemini reasoning |
| Favorites | Authenticated parents can save and manage favorite schools |
| School manager flow | School representatives can apply to manage school profiles |
| Admin review | Admins can approve or reject manager applications and school update requests |
| Data quality | Contact enrichment workflow extracts possible emails, phone numbers, and contact pages |
| Data import | CSV importer loads real school datasets into normalized database tables |
| Scraping tools | Python scripts collect external raw school data for later cleaning and import |
| Access control | Role-based permissions for parents, school managers, and admins |

## 🏗️ Architecture

SchoolSense follows a layered Laravel architecture with clear separation between HTTP controllers, domain models, service classes, console commands, database migrations, and Blade views.

```text
User Request
    |
    v
Routes -> Controllers -> Services -> Models / Database
                          |
                          +-> Gemini API
                          +-> pgvector Search
                          +-> Contact Scraper
                          +-> Import / Embedding Commands
```

### High-Level Workflow

1. **School data is collected** through seeders, CSV imports, and optional Python scraper outputs.
2. **School records are normalized** into schools, curricula, activities, languages, fee bands, documents, and embeddings.
3. **School documents are generated** from profile data and converted into vector embeddings.
4. **Search combines AI and metadata** by ranking semantic similarity while applying structured filters.
5. **Parents browse, compare, and favorite** schools through public and authenticated pages.
6. **School managers submit updates** through a controlled workflow.
7. **Admins review changes** before updates become part of the public directory.

## 🧰 Technology Stack

| Layer | Tools |
| --- | --- |
| Backend | Laravel 12, PHP 8.2+, Eloquent ORM, Artisan commands |
| Frontend | Blade, Tailwind CSS, Alpine.js, Vite |
| Authentication | Laravel Breeze |
| Database | PostgreSQL 16, pgvector |
| AI / APIs | Google Gemini embeddings and text generation |
| Testing | Pest, PHPUnit, Mockery |
| Data tooling | Python scraper scripts, CSV import pipeline |
| Infrastructure | Docker Compose for local PostgreSQL with pgvector |

## 🧠 Engineering Concepts Demonstrated

- **MVC architecture** using Laravel controllers, models, views, routes, middleware, and requests
- **Service-oriented design** for search, embeddings, scraping, comparison summaries, and document generation
- **Role-based access control** for parent, school manager, and admin workflows
- **Semantic search** using vector embeddings, similarity scoring, relevance thresholds, and metadata filtering
- **Relational data modeling** with many-to-many taxonomy relationships for curricula, activities, and languages
- **Soft deletes** for safer school record lifecycle management
- **Data normalization** through reusable taxonomy normalization and import logic
- **Approval workflows** for manager applications and proposed school profile updates
- **Progressive data enrichment** through website scraping and admin-reviewed contact updates
- **Caching** for generated AI comparison summaries
- **Input validation** through Laravel form request classes
- **Rate limiting** on expensive public flows such as search, comparison, and applications
- **Automated testing** for authentication, profiles, and semantic search query behavior

## ⚙️ Important Engineering Decisions

### Hybrid Search Strategy

SchoolSense does not rely only on vector search. The search service combines semantic similarity with direct school-name matching so exact or near-exact school searches remain reliable while still supporting natural language discovery.

### Admin-Reviewed Data Changes

School profile updates are not applied directly by school managers. They move through an admin review process, which protects public data quality and creates a safer workflow for user-generated changes.

### AI With Fallback Behavior

AI-generated comparison summaries are treated as an enhancement, not a dependency. If the AI service is unavailable, the application still displays the underlying school data.

### Normalized Taxonomies

Curricula, activities, and languages are stored in separate taxonomy tables with pivot relationships. This keeps filtering flexible and avoids duplicated free-text values across school records.

### Separate Import and Embedding Pipelines

CSV import and embedding generation are independent Artisan commands. This makes the data pipeline easier to operate, retry, and scale.

## ✅ Requirements

- PHP 8.2 or newer
- Composer
- Node.js and npm
- Docker and Docker Compose, or a local PostgreSQL installation with pgvector enabled
- Google Gemini API access for AI-powered search and comparison features
- Python 3.12+ for optional scraper scripts

## 📦 Installation

Clone the repository:

```bash
git clone <repository-url>
cd SchoolSense
```

Install PHP and JavaScript dependencies:

```bash
composer install
npm install
```

Create the environment file:

```bash
cp .env.example .env
php artisan key:generate
```

Start the database service:

```bash
docker compose up -d
```

Run migrations and seed development data:

```bash
php artisan migrate --seed
```

Build frontend assets:

```bash
npm run build
```

Start the local development environment:

```bash
composer run dev
```

## 🔐 Environment Configuration

Use placeholders in your local `.env` file. Do not commit real credentials, API keys, tokens, or secrets.

```env
APP_NAME=SchoolSense
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=<application-url>

DB_CONNECTION=pgsql
DB_HOST=<database-host>
DB_PORT=<database-port>
DB_DATABASE=<database-name>
DB_USERNAME=<database-user>
DB_PASSWORD=<database-password>

QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_STORE=database

GEMINI_API_KEY=<your-gemini-api-key>
GEMINI_EMBEDDING_MODEL=gemini-embedding-001
GEMINI_EMBEDDING_DIMENSIONS=768
GEMINI_REASONING_MODEL=gemini-2.5-flash
```

## 🕹️ Usage

### Run the Application

```bash
composer run dev
```

This starts the Laravel development server, Vite, and the queue listener.

### Import School Data

```bash
php artisan schools:import-real-dataset --file=data_scraper/data/clean/international_schools_clean.csv
```

Use `--replace` only when the CSV should become the source of truth:

```bash
php artisan schools:import-real-dataset --file=path/to/schools.csv --replace
```

### Generate School Embeddings

```bash
php artisan schools:embed --limit=50
```

Regenerate embeddings even if the content hash has not changed:

```bash
php artisan schools:embed --force --limit=50
```

### Enrich School Contact Data

```bash
php artisan schools:fetch-contacts --limit=10
```

For a single school:

```bash
php artisan schools:fetch-contacts <school-id>
```

### Run Tests

```bash
php artisan test
```

### Build Assets

```bash
npm run build
```

## 🧹 Data Scraper

The `data_scraper` directory contains Python scripts for collecting raw school data from external directory sources. Scraped data can be cleaned and imported into the Laravel database through the CSV import command.

Set up the scraper environment:

```bash
python -m venv .venv
.venv\Scripts\activate
pip install -r data_scraper/requirements.txt
```

Run a scraper with a public start URL:

```bash
python data_scraper/scrape_international_schools.py --start-url <public-directory-url>
```

## 📁 Project Structure

```text
SchoolSense/
├── app/
│   ├── Console/Commands/        # Import, embedding, diagnostics, and enrichment commands
│   ├── Http/Controllers/        # Public, admin, search, comparison, and manager controllers
│   ├── Http/Middleware/         # Role authorization middleware
│   ├── Http/Requests/           # Form validation request classes
│   ├── Models/                  # Eloquent models and relationships
│   ├── Services/                # Search, AI, scraping, documents, and comparison services
│   └── Support/                 # Shared helper logic
├── database/
│   ├── migrations/              # Database schema and relationship tables
│   └── seeders/                 # Development seed data
├── data_scraper/                # Python scraper scripts and generated datasets
├── docker/postgres/init/        # PostgreSQL / pgvector initialization
├── resources/
│   ├── css/                     # Tailwind entrypoint
│   ├── js/                      # JavaScript entrypoint
│   └── views/                   # Blade UI templates
├── routes/                      # Web, auth, and console routes
├── tests/                       # Feature and unit tests
├── composer.json
├── package.json
└── docker-compose.yml
```

## 🧪 Testing Strategy

The test suite focuses on both application behavior and critical implementation details:

- Authentication and profile flows
- Semantic search query construction
- Metadata-filtered pgvector retrieval
- Relevance threshold behavior
- Empty-query handling
- Alignment between supported search filters and the search page UI

## 🗺️ Future Improvements

- Add queued background jobs for large embedding batches
- Add a richer school recommendation engine based on parent preferences
- Add version history for school profile updates
- Add advanced admin analytics for data quality and search behavior
- Add API endpoints for mobile or external integrations
- Add full-text search fallback for environments without pgvector
- Add CI/CD workflow for tests, linting, and deployment checks
- Add more granular permissions for multi-user school management teams
- Add monitoring for AI API failures, latency, and quota usage


## 📄 License

This project is currently provided as a custom educational and portfolio project. Add the intended open-source or private license before production distribution.
