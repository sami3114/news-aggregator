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

# Setup application
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan install:api

# Fetch initial articles
./vendor/bin/sail artisan news:fetch
```

## Environment Variables

Add your API keys to `.env`:

```env
APP_PORT=8081
FORWARD_DB_PORT=3307

NEWS_API_KEY=your_newsapi_key
GUARDIAN_API_KEY=your_guardian_key
NYT_API_KEY=your_nyt_key
```

Get API keys from:
- [NewsAPI.org](https://newsapi.org/register)
- [The Guardian](https://open-platform.theguardian.com/access/)
- [New York Times](https://developer.nytimes.com/accounts/create)

## Common Commands

```bash
./vendor/bin/sail up -d              # Start containers
./vendor/bin/sail down               # Stop containers
./vendor/bin/sail artisan news:fetch # Fetch articles
./vendor/bin/sail artisan migrate    # Run migrations
./vendor/bin/sail shell              # Enter container
```

## API Endpoints

**Base URL:** `http://127.0.0.1:8081/api`

### Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/auth/register` | Register user |
| POST | `/auth/login` | Login |
| POST | `/auth/logout` | Logout (Auth) |

### Articles
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/articles` | List articles |
| GET | `/articles/search?q=` | Search |
| GET | `/articles/{article}` | Single article |
| GET | `/categories` | List categories |
| GET | `/sources` | List sources |
| GET | `/authors` | List authors |

### User Preferences (Auth Required)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/user/preferences` | Get preferences |
| POST | `/user/preferences` | Update preferences |
| GET | `/user/feed` | Personalized feed |

### Query Parameters

```
/articles?keyword=tech&category=business&source=guardian&from_date=2024-01-01&per_page=20
```

## Project Structure

```
app/
├── Console/Commands/          # Artisan commands
├── Contracts/                 # Interfaces
├── Http/Controllers/Api/      # API controllers
├── Models/                    # Eloquent models
├── Repositories/              # Data access layer
└── Services/
    ├── NewsAggregatorService.php
    └── NewsProviders/         # News source integrations
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
