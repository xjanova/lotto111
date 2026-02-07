<p align="center">
  <img src="https://raw.githubusercontent.com/xjanova/lotto111/main/public/images/logo.svg" alt="Lotto Platform" width="120">
</p>

<h1 align="center">Lotto Platform</h1>

<p align="center">
  <strong>Modern Online Lottery System built with Laravel 12</strong>
</p>

<p align="center">
  <a href="#features">Features</a> &bull;
  <a href="#tech-stack">Tech Stack</a> &bull;
  <a href="#installation">Installation</a> &bull;
  <a href="#architecture">Architecture</a> &bull;
  <a href="#testing">Testing</a> &bull;
  <a href="#deployment">Deployment</a> &bull;
  <a href="#documentation">Documentation</a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP 8.3+">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/Livewire-3.x-FB70A9?style=flat-square&logo=livewire&logoColor=white" alt="Livewire 3">
  <img src="https://img.shields.io/badge/Tailwind-4.x-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white" alt="Tailwind 4">
  <img src="https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=flat-square&logo=mysql&logoColor=white" alt="MySQL 8.0+">
  <img src="https://img.shields.io/badge/Redis-7.x-DC382D?style=flat-square&logo=redis&logoColor=white" alt="Redis">
  <img src="https://img.shields.io/github/actions/workflow/status/xjanova/lotto111/ci.yml?style=flat-square&label=CI" alt="CI Status">
  <img src="https://img.shields.io/github/v/release/xjanova/lotto111?style=flat-square" alt="Release">
  <img src="https://img.shields.io/badge/license-proprietary-blue?style=flat-square" alt="License">
</p>

---

## Overview

**Lotto Platform** is a comprehensive, production-ready online lottery system designed for the Thai market. Built from the ground up with **Laravel 12**, it provides a full-featured lottery betting experience with real-time capabilities, AI-powered risk management, and automatic SMS-based deposit processing.

The system handles the complete lifecycle: user registration with SMS OTP, lottery browsing and betting, real-time result announcements, financial transactions, affiliate management, and a powerful admin back-office with profit control.

---

## Features

### Core Platform
- **Multi-Lottery Support** - Government lottery, Yeekee (144 rounds/day), international lotteries (40+ types)
- **Real-time Betting** - Live countdown, instant bet placement, dynamic rate calculation
- **Complete Financial System** - Deposits, withdrawals, transaction history, financial reports
- **Affiliate/Referral** - Multi-level referral system with commission tracking
- **SMS OTP Authentication** - Secure phone-based registration and verification
- **PWA Ready** - Installable as mobile app, offline support, push notifications

### Advanced Features
- **AI Smart Number Analysis** - Hot/cold numbers, frequency distribution, smart picks
- **Gamification System** - VIP levels (Bronze-Diamond), daily missions, lucky spin, badges
- **Social & Group Play** - Syndicate betting, group chat, automatic prize splitting
- **Live Draw Experience** - Animated reveals, live reactions, win celebrations
- **Smart Notifications** - AI-timed alerts via in-app, LINE, push, SMS
- **Personal Analytics** - Spending graphs, win rates, heat maps, monthly reports
- **Dark/Light Theme** - System preference detection, custom theme colors
- **Multi-Language** - Thai, English, Lao, Myanmar, Cambodian

### SMS Auto-Deposit (Bankless)
- **15 Thai Banks + PromptPay** supported
- Automatic SMS interception via [smschecker](https://github.com/xjanova/smschecker) Android app
- **Unique decimal amount matching** (e.g., 500.37 THB)
- **AES-256-GCM** encrypted communication
- **3-5 second** deposit confirmation
- Real-time WebSocket notifications
- Admin manual matching for edge cases

### Risk Management & Profit Control
- **Real-time P&L Dashboard** - Live margin, exposure, user activity
- **AI Auto-Balance Engine** - Automatic win rate adjustment every 5 minutes
- **User Risk Profiling** - Fish / Normal / Watch / Danger / Whale classification
- **Per-User Control** - Win rate override, payout rate adjustment, number blocking
- **Number Exposure Monitoring** - Auto-block when exposure exceeds thresholds
- **Anomaly Detection** - Consecutive wins, bot behavior, collusion patterns
- **Comprehensive Audit Trail** - Every rate adjustment logged with context

---

## Tech Stack

| Layer | Technology | Purpose |
|-------|-----------|---------|
| **Language** | PHP 8.3+ | Backend runtime with enums, fibers, readonly properties |
| **Framework** | Laravel 12 | Full-stack web framework |
| **Frontend** | Blade + Livewire 3 + Alpine.js | Reactive server-rendered UI |
| **Styling** | Tailwind CSS 4 | Utility-first CSS framework |
| **Database** | MySQL 8.0+ | Primary data store |
| **Cache/Queue** | Redis 7.x | Caching, sessions, queue broker |
| **WebSocket** | Laravel Reverb | Real-time bidirectional communication |
| **Auth** | Laravel Sanctum | SPA & API token authentication |
| **SMS** | ThaiSMS / ThaiBulkSMS | OTP verification |
| **SMS Deposit** | smschecker | Automatic bank SMS processing |
| **Testing** | Pest + PHPUnit | Unit, feature, and integration tests |
| **Code Quality** | Pint + PHPStan + Rector | Linting, static analysis, refactoring |
| **CI/CD** | GitHub Actions | Automated testing and deployment |
| **Containers** | Docker + Docker Compose | Development and production environments |

---

## Architecture

```
                        ┌─────────────┐
                        │   Browser   │
                        │  (PWA/SPA)  │
                        └──────┬──────┘
                               │ HTTPS
                        ┌──────▼──────┐
                        │   Nginx     │
                        │  (Reverse   │
                        │   Proxy)    │
                        └──────┬──────┘
                               │
              ┌────────────────┼────────────────┐
              │                │                │
       ┌──────▼──────┐ ┌──────▼──────┐ ┌───────▼──────┐
       │   Laravel    │ │   Reverb    │ │   Queue      │
       │   App        │ │ (WebSocket) │ │   Workers    │
       │  + Livewire  │ │             │ │              │
       └──────┬──────┘ └──────┬──────┘ └───────┬──────┘
              │                │                │
              └────────────────┼────────────────┘
                               │
              ┌────────────────┼────────────────┐
              │                │                │
       ┌──────▼──────┐ ┌──────▼──────┐ ┌───────▼──────┐
       │   MySQL      │ │   Redis     │ │  Storage     │
       │   Database   │ │   Cache     │ │  (S3/Local)  │
       └─────────────┘ └─────────────┘ └──────────────┘

       ┌─────────────┐
       │  Android App │ ──── SMS ────▶ Laravel API
       │ (smschecker) │    (encrypted)   (auto-deposit)
       └─────────────┘
```

### Design Patterns
- **Action Pattern** - Single-purpose classes for complex operations
- **Service Pattern** - Reusable business logic (Balance, Risk, Gamification)
- **Event-Driven** - Side effects via Events + Listeners
- **Repository Pattern** - Optional database abstraction for testing

### Key Services

| Service | Responsibility |
|---------|---------------|
| `RiskEngineService` | AI-powered P&L control, user profiling, auto-balance |
| `SmsDepositService` | Bridge between deposits and smschecker |
| `SmsPaymentProcessorService` | SMS decryption, validation, matching |
| `GamificationService` | VIP levels, XP, missions, spin, badges |
| `NumberAnalysisService` | Hot/cold analysis, AI smart picks |
| `NotificationService` | Multi-channel smart notifications |
| `BalanceService` | Wallet credit/debit operations |

---

## Installation

### Prerequisites
- PHP 8.3+
- Composer 2.x
- Node.js 20+ & npm
- MySQL 8.0+
- Redis 7.x
- Git

### Quick Start

```bash
# Clone the repository
git clone https://github.com/xjanova/lotto111.git
cd lotto111

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate --seed

# Build assets
npm run build

# Start development server
php artisan serve
```

### Docker Setup

```bash
# Start all services
docker compose up -d

# Run migrations
docker compose exec app php artisan migrate --seed

# Access at http://localhost:8080
```

### Post-Installation

```bash
# Create SMS Checker device
php artisan smschecker:create-device "Device 1"

# Start queue worker
php artisan queue:work redis --queue=high,default,low

# Start WebSocket server
php artisan reverb:start

# Start scheduler
php artisan schedule:work
```

---

## Testing

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage --min=80

# Run specific test suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Run Pest tests
./vendor/bin/pest

# Run with parallel execution
php artisan test --parallel
```

### Test Structure
```
tests/
├── Unit/
│   ├── Services/
│   │   ├── RiskEngineServiceTest.php
│   │   ├── SmsDepositServiceTest.php
│   │   ├── GamificationServiceTest.php
│   │   └── BalanceServiceTest.php
│   ├── Enums/
│   └── Models/
├── Feature/
│   ├── Auth/
│   ├── Lottery/
│   ├── Finance/
│   ├── Deposit/
│   ├── Risk/
│   └── Admin/
└── Integration/
    ├── SmsCheckerIntegrationTest.php
    └── RiskEngineIntegrationTest.php
```

---

## Code Quality

```bash
# Format code (Laravel Pint)
./vendor/bin/pint

# Static analysis (PHPStan level 6)
./vendor/bin/phpstan analyse

# Automated refactoring (Rector)
./vendor/bin/rector process --dry-run

# All quality checks at once
composer quality
```

---

## Deployment

### Production Checklist

```bash
# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan icons:cache

# Build production assets
npm run build

# Run database migrations
php artisan migrate --force
```

### Recommended Server Setup
- **Web Server**: Nginx with PHP-FPM (PHP 8.3)
- **Process Manager**: Supervisor (queue workers, Reverb)
- **SSL**: Let's Encrypt / Cloudflare
- **CDN**: Cloudflare for static assets
- **Monitoring**: Laravel Telescope (dev), Sentry (production)

---

## Documentation

| Document | Description |
|----------|-------------|
| [PRD.md](docs/PRD.md) | Product Requirements Document |
| [ARCHITECTURE.md](docs/ARCHITECTURE.md) | System Architecture & Design Patterns |
| [DATABASE.md](docs/DATABASE.md) | Core Database Schema (21 tables) |
| [DATABASE_RISK_CONTROL.md](docs/DATABASE_RISK_CONTROL.md) | Risk Management Schema (8 tables) |
| [DATABASE_SMS_DEPOSIT.md](docs/DATABASE_SMS_DEPOSIT.md) | SMS Deposit Schema (4 tables) |
| [ADVANCED_FEATURES.md](docs/ADVANCED_FEATURES.md) | 12 Advanced Features |
| [ADMIN_RISK_CONTROL.md](docs/ADMIN_RISK_CONTROL.md) | Risk Control Admin Guide |
| [SMS_AUTO_DEPOSIT.md](docs/SMS_AUTO_DEPOSIT.md) | SMS Auto-Deposit Integration |
| [SETUP.md](docs/SETUP.md) | Installation & Server Setup |
| [CONTRIBUTING.md](CONTRIBUTING.md) | Contributing Guidelines |
| [CHANGELOG.md](CHANGELOG.md) | Version History |

---

## Project Structure

```
lotto/
├── app/
│   ├── Actions/           # Single-purpose action classes
│   ├── Console/Commands/  # Artisan commands
│   ├── Enums/             # PHP 8.1+ Enums (14 enums)
│   ├── Events/            # Broadcast events
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/       # REST API controllers
│   │   │   ├── Admin/     # Admin panel controllers
│   │   │   └── App/       # Member web controllers
│   │   ├── Middleware/     # Custom middleware
│   │   └── Requests/      # Form request validation
│   ├── Jobs/              # Queue jobs
│   ├── Listeners/         # Event listeners
│   ├── Livewire/          # Livewire components
│   ├── Models/            # Eloquent models
│   └── Services/          # Business logic
│       ├── AI/            # AI number analysis
│       ├── Deposit/       # SMS auto-deposit
│       └── Risk/          # Risk management engine
├── config/                # Configuration files
├── database/              # Migrations, seeders, factories
├── docs/                  # Project documentation
├── public/                # Public assets
├── resources/             # Views, CSS, JS
├── routes/                # Route definitions
├── tests/                 # Test suites
├── docker/                # Docker configuration
└── .github/workflows/     # CI/CD pipelines
```

---

## Database Schema

The system uses **33 database tables** organized into functional groups:

| Group | Tables | Description |
|-------|--------|-------------|
| **Core** | 21 | Users, lottery, betting, finance, affiliate |
| **Risk Management** | 8 | Risk profiles, exposure, alerts, snapshots |
| **SMS Deposit** | 4 | Devices, notifications, amounts, nonces |

---

## API Overview

### Public Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/register` | User registration |
| POST | `/api/auth/login` | Authentication |
| GET | `/api/results` | Lottery results |

### Authenticated Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/lottery/bet` | Place a bet |
| POST | `/api/deposit/sms` | Create SMS deposit |
| GET | `/api/deposit/{id}/status` | Check deposit status |
| GET | `/api/tickets` | View betting history |

### SMS Checker Device API
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/sms-payment/notify` | Receive encrypted SMS |
| GET | `/api/v1/sms-payment/status` | Device heartbeat |

### Admin Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin/risk/dashboard` | Risk management dashboard |
| PUT | `/admin/risk/users/{id}/win-rate` | Set user win rate |
| POST | `/admin/sms-deposit/manual-match` | Manual SMS matching |

---

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct, development workflow, and the process for submitting pull requests.

## Security

If you discover a security vulnerability, please send an email to security@example.com. All security vulnerabilities will be promptly addressed.

## License

This project is proprietary software. Unauthorized copying, distribution, or modification is strictly prohibited.

---

<p align="center">
  Built with &#10084; using <a href="https://laravel.com">Laravel</a>
</p>
