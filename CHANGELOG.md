# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Added
- Initial project setup with Laravel 12
- Complete database schema (33 tables)
- Authentication system with SMS OTP
- Multi-lottery support (Government, Yeekee, International)
- Real-time betting with WebSocket (Laravel Reverb)
- SMS Auto-Deposit system via smschecker integration
  - 15 Thai banks + PromptPay support
  - AES-256-GCM encrypted SMS processing
  - Unique decimal amount matching
  - Real-time deposit notifications
- Risk Management & Profit Control engine
  - AI auto-balance (win rate adjustment)
  - User risk profiling (Fish/Normal/Watch/Danger/Whale)
  - Real-time P&L dashboard
  - Number exposure monitoring
  - Anomaly detection system
- Gamification system
  - VIP levels (Bronze to Diamond)
  - Daily/weekly missions
  - Lucky spin with weighted rewards
  - Achievement badges
- AI Smart Number analysis
  - Hot/cold number detection
  - Frequency analysis
  - Smart pick algorithm
- Smart notification service (in-app, LINE, push, SMS)
- PWA support (manifest, service worker)
- Docker development environment
- CI/CD with GitHub Actions
- Auto-release with semantic versioning
- Code quality tools (Pint, PHPStan, Rector)
- Comprehensive test suite

### Infrastructure
- GitHub Actions CI pipeline (PHP 8.3/8.4, MySQL, Redis)
- Automated release workflow with changelog generation
- Docker Compose for local development
- Production Docker image (Alpine-based, multi-stage)
- Supervisor configuration for queue workers
- Nginx configuration with security headers
- PHPUnit + Pest test framework

---

## [0.1.0] - 2025-01-01

### Added
- Project scaffolding and documentation
- Initial architecture design

---

[Unreleased]: https://github.com/xjanova/lotto111/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/xjanova/lotto111/releases/tag/v0.1.0
