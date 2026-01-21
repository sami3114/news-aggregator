# News Aggregator API

A robust backend API for aggregating news articles from multiple sources, built with Laravel 12.x and Docker using Laravel Sail.

## Project Overview

News Aggregator is a RESTful API that fetches, stores, and serves news articles from multiple trusted sources. It provides a unified interface for accessing news content with powerful filtering, search capabilities, and personalized user feeds.

### Key Features

- **Multi-Source Aggregation** — Fetches articles from NewsAPI.org, The Guardian, and New York Times
- **Smart Filtering** — Filter articles by category, source, author, and date range
- **Full-Text Search** — Search across article titles, descriptions, and content
- **User Authentication** — Secure token-based authentication using Laravel Sanctum
- **Personalized Feeds** — Users can set preferences for sources, categories, and authors
- **Automated Updates** — Scheduled hourly fetching of latest articles
- **RESTful API** — Clean, consistent API endpoints with proper JSON responses
- **Docker Ready** — Fully containerized with Laravel Sail for easy development

---

## Tech Stack

| Technology | Version | Purpose |
|------------|---------|---------|
| Laravel | 12.x | PHP Framework |
| PHP | 8.2+ | Programming Language |
| Laravel Sail | Latest | Docker Development Environment |
| Docker | Latest | Containerization |
| MySQL | 8.0 | Primary Database |
| Redis | Alpine | Caching & Queue |
| Laravel Sanctum | Latest | API Authentication |

---

## Prerequisites

Before you begin, ensure you have the following installed on your system:

- **Docker Desktop** (Windows/Mac) or **Docker Engine** (Linux)
- **Docker Compose** (included with Docker Desktop)
- **Git** for cloning the repository

### Install Docker on Ubuntu

```bash
# Update package index
sudo apt update

# Install prerequisites
sudo apt install -y apt-transport-https ca-certificates curl software-properties-common

# Add Docker's official GPG key
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

# Add Docker repository
echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Install Docker
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

# Add your user to docker group (requires logout/login)
sudo usermod -aG docker $USER
```

---

## Installation & Setup

### 1. Clone the Repository

```bash
git clone https://github.com/sami3114/news-aggregator.git
cd news-aggregator
```

### 2. Environment Configuration

```bash
# Copy the example environment file
cp .env.example .env
```

### 3. Configure Environment Variables

Open `.env` and configure the following:

```env
# Application
APP_NAME="News Aggregator"
APP_URL=http://localhost
APP_PORT=8081

# Database (Sail defaults)
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=news_aggregator
DB_USERNAME=sail
DB_PASSWORD=password
FORWARD_DB_PORT=3307


# Redis (Sail defaults)
REDIS_HOST=redis
REDIS_PORT=6379

# News API Keys (Required)
NEWS_API_BASE_URL=https://newsapi.org/v2/
NEWS_API_KEY=your_newsapi_org_key

GUARDIAN_BASE_URL=https://content.guardianapis.com/
GUARDIAN_API_KEY=your_guardian_api_key

NYT_BASE_URL=https://api.nytimes.com/svc/
NYT_API_KEY=your_nyt_api_key
```

### 4. Install Dependencies

```bash
# Install PHP dependencies via Sail
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs
```

### 5. Start the Application

```bash
# Start all containers in detached mode
./vendor/bin/sail up -d

# Generate application key
./vendor/bin/sail artisan key:generate

# Run database migrations
./vendor/bin/sail artisan migrate

# Install Sanctum API (if not already installed)
./vendor/bin/sail artisan install:api
```

### 6. Fetch Initial News Articles

```bash
# Fetch from all configured sources
./vendor/bin/sail artisan news:fetch

# Or fetch from a specific source
./vendor/bin/sail artisan news:fetch --source=newsapi
./vendor/bin/sail artisan news:fetch --source=guardian
./vendor/bin/sail artisan news:fetch --source=nytimes
```

---

## Running the Project

### Start Containers

```bash
# Start in detached mode (background)
./vendor/bin/sail up -d

# Start with logs visible
./vendor/bin/sail up
```

### Stop Containers

```bash
# Stop all containers
./vendor/bin/sail down

# Stop and remove volumes (clears database)
./vendor/bin/sail down -v
```

### Run Artisan Commands

```bash
# General syntax
./vendor/bin/sail artisan <command>

# Examples
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan route:list
```

### Run Migrations & Seeders

```bash
# Run all migrations
./vendor/bin/sail artisan migrate

# Run migrations fresh (drop all tables and re-run)
./vendor/bin/sail artisan migrate:fresh

# Run seeders
./vendor/bin/sail artisan db:seed

# Migrate and seed together
./vendor/bin/sail artisan migrate:fresh --seed
```

---

## Common Sail Commands

| Command | Description |
|---------|-------------|
| `./vendor/bin/sail up -d` | Start containers in background |
| `./vendor/bin/sail down` | Stop all containers |
| `./vendor/bin/sail restart` | Restart all containers |
| `./vendor/bin/sail artisan` | Run Laravel Artisan commands |
| `./vendor/bin/sail composer` | Run Composer commands |
| `./vendor/bin/sail npm` | Run NPM commands |
| `./vendor/bin/sail mysql` | Access MySQL CLI |
| `./vendor/bin/sail redis` | Access Redis CLI |
| `./vendor/bin/sail shell` | SSH into the app container |
| `./vendor/bin/sail logs` | View container logs |
| `./vendor/bin/sail test` | Run PHPUnit tests |

### Create a Sail Alias (Recommended)

Add this to your `~/.bashrc` or `~/.zshrc`:

```bash
alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
```

Then reload your shell:

```bash
source ~/.bashrc
```

Now you can use `sail` instead of `./vendor/bin/sail`:

```bash
sail up -d
sail artisan migrate
sail down
```

---

## Environment Configuration

### Key `.env` Variables

```env
# ─────────────────────────────────────────────────────────
# APPLICATION
# ─────────────────────────────────────────────────────────
APP_NAME="News Aggregator"
APP_ENV=local                    # local, staging, production
APP_DEBUG=true                   # Set false in production
APP_URL=http://localhost

# ─────────────────────────────────────────────────────────
# DATABASE (Laravel Sail Defaults)
# ─────────────────────────────────────────────────────────
DB_CONNECTION=mysql
DB_HOST=mysql                    # Docker service name
DB_PORT=3306
DB_DATABASE=news_aggregator
DB_USERNAME=sail
DB_PASSWORD=password

# ─────────────────────────────────────────────────────────
# REDIS (Laravel Sail Defaults)
# ─────────────────────────────────────────────────────────
REDIS_HOST=redis                 # Docker service name
REDIS_PASSWORD=null
REDIS_PORT=6379

# ─────────────────────────────────────────────────────────
# DOCKER / SAIL CONFIGURATION
# ─────────────────────────────────────────────────────────
APP_PORT=80                      # Application port
FORWARD_DB_PORT=3306             # MySQL external port
FORWARD_REDIS_PORT=6379          # Redis external port

# ─────────────────────────────────────────────────────────
# NEWS API KEYS (Required)
# ─────────────────────────────────────────────────────────
NEWS_API_KEY=your_key_here       # https://newsapi.org/register
GUARDIAN_API_KEY=your_key_here   # https://open-platform.theguardian.com/access/
NYT_API_KEY=your_key_here        # https://developer.nytimes.com/accounts/create
```

### Docker Port Mapping

| Service | Internal Port | External Port | Variable |
|---------|---------------|---------------|----------|
| Application | 80 | 80 | `APP_PORT` |
| MySQL | 3306 | 3306 | `FORWARD_DB_PORT` |
| Redis | 6379 | 6379 | `FORWARD_REDIS_PORT` |

---

## Project Structure

```
news-aggregator/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── FetchNewsCommand.php    # Artisan command to fetch news
│   ├── Contracts/                       # Interfaces
│   │   ├── ArticleRepositoryInterface.php
│   │   └── NewsServiceInterface.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/                     # API Controllers
│   │   │       ├── ArticleController.php
│   │   │       ├── AuthController.php
│   │   │       └── UserPreferenceController.php
│   │   ├── Requests/                    # Form Request Validation
│   │   └── Resources/                   # API Response Transformers
│   ├── Models/                          # Eloquent Models
│   │   ├── Article.php
│   │   ├── User.php
│   │   └── UserPreference.php
│   ├── Repositories/                    # Data Access Layer
│   │   └── ArticleRepository.php
│   └── Services/                        # Business Logic
│       ├── NewsAggregatorService.php
│       └── NewsProviders/               # News Source Integrations
│           ├── BaseNewsService.php
│           ├── GuardianService.php
│           ├── NewsApiService.php
│           └── NewYorkTimesService.php
├── config/
│   └── services.php                     # News API configuration
├── database/
│   ├── factories/                       # Model Factories
│   └── migrations/                      # Database Migrations
├── routes/
│   ├── api.php                          # API Routes
│   └── console.php                      # Scheduled Tasks
├── tests/
│   └── Feature/                         # Feature Tests
├── .env.example                         # Environment Template
├── docker-compose.yml                   # Docker Configuration (Sail)
└── README.md                            # This File
```

---

## API Usage

### Base URL

```
http://127.0.0.1:8081/api
```

### Authentication Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/auth/register` | Register new user | No |
| POST | `/auth/login` | Login & get token | No |
| POST | `/auth/logout` | Logout user | Yes |
| GET | `/auth/user` | Get current user | Yes |

### Article Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/articles` | List all articles | No |
| GET | `/articles/search?q=` | Search articles | No |
| GET | `/articles/{id}` | Get single article | No |
| GET | `/categories` | List categories | No |
| GET | `/sources` | List sources | No |
| GET | `/authors` | List authors | No |

### User Preference Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/user/preferences` | Get user preferences | Yes |
| POST | `/user/preferences` | Update preferences | Yes |
| GET | `/user/feed` | Get personalized feed | Yes |

### Query Parameters for `/articles`

| Parameter | Type | Example | Description |
|-----------|------|---------|-------------|
| `keyword` | string | `technology` | Search in title/content |
| `category` | string | `business` | Filter by category |
| `source` | string | `guardian` | Filter by source |
| `author` | string | `John` | Filter by author |
| `from_date` | date | `2024-01-01` | Articles from date |
| `to_date` | date | `2024-12-31` | Articles until date |
| `per_page` | int | `20` | Items per page |

### Example Requests

```bash
# Get all articles
curl http://127.0.0.1:8081/api/articles

# Search articles
curl "http://127.0.0.1:8081/api/articles?keyword=technology&category=tech"

# Register user
curl -X POST http://127.0.0.1:8081/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"John","email":"john@example.com","password":"secret123","password_confirmation":"secret123"}'

# Login
curl -X POST http://127.0.0.1:8081/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"john@example.com","password":"secret123"}'

# Access protected route
curl http://127.0.0.1:8081/api/user/feed \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## Scheduled Tasks & Queue Workers

### Scheduled News Fetching

Articles are automatically fetched every hour. To run the scheduler:

```bash
# Run scheduler in foreground (for development)
./vendor/bin/sail artisan schedule:work

# View scheduled tasks
./vendor/bin/sail artisan schedule:list
```

### Queue Worker

For processing background jobs:

```bash
# Start queue worker
./vendor/bin/sail artisan queue:work

# Start with specific options
./vendor/bin/sail artisan queue:work --sleep=3 --tries=3
```

### Manual News Fetch

```bash
# Fetch from all sources
./vendor/bin/sail artisan news:fetch

# Fetch from specific source
./vendor/bin/sail artisan news:fetch --source=newsapi
./vendor/bin/sail artisan news:fetch --source=guardian
./vendor/bin/sail artisan news:fetch --source=nytimes
```
---

## Troubleshooting

### Port Conflicts

**Error:** `Bind for 0.0.0.0:80 failed: port is already allocated`

**Solution:** Change the port in `.env`:

```env
APP_PORT=8081
```

Or stop the conflicting service:

```bash
sudo lsof -i :80
sudo kill -9 <PID>
```

### Docker Permission Issues (Linux)

**Error:** `Got permission denied while trying to connect to the Docker daemon`

**Solution:**

```bash
# Add user to docker group
sudo usermod -aG docker $USER

# Apply changes (logout/login or run)
newgrp docker
```

### Sail Command Not Found

**Error:** `sail: command not found`

**Solution:**

```bash
# Use full path
./vendor/bin/sail up -d

# Or create alias in ~/.bashrc
alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
source ~/.bashrc
```

### Clearing All Caches

```bash
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan route:clear
./vendor/bin/sail artisan view:clear

# Or clear everything at once
./vendor/bin/sail artisan optimize:clear
```

### Database Connection Issues

**Error:** `SQLSTATE[HY000] [2002] Connection refused`

**Solution:**

```bash
# Ensure MySQL container is running
./vendor/bin/sail ps

# Wait for MySQL to be ready, then retry
./vendor/bin/sail down
./vendor/bin/sail up -d
sleep 10
./vendor/bin/sail artisan migrate
```

### Rebuild Containers

```bash
./vendor/bin/sail down
./vendor/bin/sail build --no-cache
./vendor/bin/sail up -d
```

---

## Best Practices

### Always Use Sail for Commands

```bash
# ✅ Correct
./vendor/bin/sail artisan migrate
./vendor/bin/sail composer require package-name

# ❌ Avoid (may cause version conflicts)
php artisan migrate
composer require package-name
```

### Environment Separation

- Use `.env` for local development
- Never commit `.env` to version control
- Use `.env.example` as a template
- Use different databases for testing

### Keep Dependencies Updated

```bash
./vendor/bin/sail composer update
```

### Regular Database Backups

```bash
# Export database
./vendor/bin/sail exec mysql mysqldump -u sail -ppassword news_aggregator > backup.sql

# Import database
./vendor/bin/sail exec -T mysql mysql -u sail -ppassword news_aggregator < backup.sql
```

---

## Getting API Keys

| Service | Registration URL |
|---------|------------------|
| NewsAPI.org | https://newsapi.org/register |
| The Guardian | https://open-platform.theguardian.com/access/ |
| New York Times | https://developer.nytimes.com/accounts/create |

---

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## Author

**Muhammad Sami Ullah**  
Email: muhammadsamiullah.it@outlook.com
