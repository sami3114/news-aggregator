# News Aggregator API

A RESTful API for aggregating news articles from multiple sources, built with Laravel 12.x and Laravel Sail (Docker).

## Features

- Multi-source aggregation (NewsAPI, The Guardian, New York Times)
- Filter by category, source, author, date range
- Full-text search
- Token-based authentication (Laravel Sanctum)
- Personalized user feeds
- Automated hourly article fetching

## Tech Stack

Laravel 12.x • PHP 8.2+ • MySQL 8.0 • Redis • Docker (Sail) • Sanctum

## Prerequisites

- Docker & Docker Compose
- Git

## Installation

```bash
# Clone repository
git clone https://github.com/sami3114/news-aggregator.git
cd news-aggregator

# Copy environment file
cp .env.example .env

# Install dependencies
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html \
    laravelsail/php82-composer:latest composer install --ignore-platform-reqs

# Start containers
./vendor/bin/sail up -d

# Install Sanctum API (run once, interactive)
./vendor/bin/sail artisan install:api

# Run setup command (migrate + seed + fetch news)
./vendor/bin/sail artisan app:setup --seed --fetch

# Run test
./vendor/bin/sail artisan test
```

## Setup Command

```bash
# Basic setup (key generate, migrate, optimize)
./vendor/bin/sail artisan app:setup

# Setup with database seeding (10 users, 15 articles)
./vendor/bin/sail artisan app:setup --seed

# Setup with fresh migration (drops all tables)
./vendor/bin/sail artisan app:setup --fresh --seed

# Full setup (seed + fetch live news)
./vendor/bin/sail artisan app:setup --seed --fetch
```

## Environment Variables

Add your API keys to `.env`:

```env
APP_PORT=8081
FORWARD_DB_PORT=3307

NEWS_API_BASE_URL=https://newsapi.org/v2/
NEWS_API_KEY=cd59f487ec4644c487e2f1efbb461c18

GUARDIAN_BASE_URL=https://content.guardianapis.com/
GUARDIAN_API_KEY=f915cced-03cb-410d-a28a-32a01a30445e

NYT_BASE_URL=https://api.nytimes.com/svc/
NYT_API_KEY=02kTSs7zjt3EE9DzkfL2W2R4QOjkDo59J2QpcXcoCTcLW0yh
```

Get API keys from:
- [NewsAPI.org](https://newsapi.org/register)
- [The Guardian](https://open-platform.theguardian.com/access/)
- [New York Times](https://developer.nytimes.com/accounts/create)

## API Endpoints

**Base URL:** `http://127.0.0.1:8081/api`

### Authentication
| Method | Endpoint         | Description              |
|--------|------------------|--------------------------|
| POST | `/auth/register` | Register user            |
| POST | `/auth/login`    | Login                    |
| POST | `/auth/logout`   | Logout (Auth)            |
| POST | `/user`          | Current User with Preference |

### Articles
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/articles` | List articles |
| GET | `/articles/{article}` | Single article |
| GET | `/categories` | List categories |
| GET | `/sources` | List sources |
| GET | `/authors` | List authors |

### User Preferences (Auth Required)
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/user/preferences` | Update preferences |
| GET | `/user/feed` | Personalized feed |

### Query Parameters

```
/articles?q=technology&category=business&source=guardian&from_date=2024-01-01&per_page=20
```

## Project Structure

```
app/
├── Console/Commands/          # Artisan commands (SetupCommand, FetchNewsCommand)
├── Contracts/                 # Interfaces
├── Http/Controllers/Api/      # API controllers
├── Models/                    # Eloquent models
├── Repositories/              # Data access layer
└── Services/
    └── NewsProviders/         # News source integrations

database/
└── seeders/                   # UserSeeder, ArticleSeeder, etc.
```

## Troubleshooting

**Port conflict:** Change `APP_PORT` in `.env`

**Clear cache:**
```bash
./vendor/bin/sail artisan optimize:clear
```

**Rebuild containers:**
```bash
./vendor/bin/sail down && ./vendor/bin/sail build --no-cache && ./vendor/bin/sail up -d
```

## License

MIT License

## Author

**Muhammad Sami Ullah** — muhammadsamiullah.it@outlook.com
