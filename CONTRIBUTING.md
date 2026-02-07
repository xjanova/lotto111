# Contributing to Lotto Platform

Thank you for considering contributing to Lotto Platform! This document provides guidelines and instructions for contributing.

---

## Development Setup

### Prerequisites
- PHP 8.3+
- Composer 2.x
- Node.js 20+ & npm
- MySQL 8.0+
- Redis 7.x
- Docker (optional)

### Getting Started

```bash
# Clone the repository
git clone https://github.com/xjanova/lotto111.git
cd lotto111

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate --seed

# Start development
php artisan serve
npm run dev
```

### Docker Alternative

```bash
docker compose up -d
docker compose exec app php artisan migrate --seed
```

---

## Development Workflow

### Branch Strategy

We follow **Git Flow**:

| Branch | Purpose |
|--------|---------|
| `main` | Production-ready code |
| `develop` | Integration branch for features |
| `feature/*` | New features |
| `bugfix/*` | Bug fixes |
| `hotfix/*` | Emergency production fixes |
| `release/*` | Release preparation |

### Creating a Feature

```bash
# Create feature branch from develop
git checkout develop
git pull origin develop
git checkout -b feature/your-feature-name

# Work on your feature...

# Push and create PR
git push -u origin feature/your-feature-name
gh pr create --base develop
```

### Commit Messages

We follow [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>(<scope>): <description>

[optional body]

[optional footer]
```

**Types:**
- `feat` - New feature
- `fix` - Bug fix
- `docs` - Documentation
- `style` - Code style (formatting, no logic change)
- `refactor` - Code refactoring
- `perf` - Performance improvement
- `test` - Tests
- `build` - Build system changes
- `ci` - CI/CD changes
- `chore` - Maintenance tasks

**Examples:**
```
feat(deposit): add SMS auto-deposit via smschecker
fix(risk): correct win rate calculation for whale users
docs(api): update deposit endpoint documentation
test(gamification): add VIP level progression tests
```

---

## Code Standards

### PHP

- Follow PSR-12 coding standard
- Use PHP 8.3+ features (enums, readonly, fibers)
- Use strict types (`declare(strict_types=1)`)
- Use Laravel Pint for formatting

```bash
# Format code
./vendor/bin/pint

# Check without fixing
./vendor/bin/pint --test
```

### Static Analysis

```bash
# Run PHPStan (level 6)
./vendor/bin/phpstan analyse

# Run Rector (dry-run)
./vendor/bin/rector process --dry-run
```

### All Quality Checks

```bash
# Run all at once
composer quality
```

---

## Testing

### Running Tests

```bash
# All tests
php artisan test

# With coverage
php artisan test --coverage --min=70

# Specific suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Specific test file
php artisan test tests/Unit/Services/RiskEngineServiceTest.php

# Parallel execution
php artisan test --parallel
```

### Writing Tests

- Place unit tests in `tests/Unit/`
- Place feature tests in `tests/Feature/`
- Place integration tests in `tests/Integration/`
- Use snake_case for test method names
- Each test should test one behavior
- Use factories for model creation

```php
public function test_deposit_below_minimum_throws_exception(): void
{
    $user = User::factory()->create();

    $this->expectException(\InvalidArgumentException::class);

    $this->service->createDeposit($user, 10);
}
```

---

## Pull Request Process

1. **Update documentation** if you change any public API
2. **Add tests** for new features
3. **Run quality checks**: `composer quality`
4. **Run tests**: `php artisan test`
5. **Create PR** with clear title and description
6. **Request review** from at least one maintainer
7. **Address feedback** promptly

### PR Template

```markdown
## Summary
Brief description of changes.

## Changes
- Change 1
- Change 2

## Testing
- [ ] Unit tests pass
- [ ] Feature tests pass
- [ ] Manual testing completed

## Screenshots (if UI changes)
```

---

## Reporting Issues

### Bug Reports

Include:
1. Steps to reproduce
2. Expected behavior
3. Actual behavior
4. PHP/Laravel version
5. Error logs (if applicable)

### Feature Requests

Include:
1. Problem statement
2. Proposed solution
3. Alternative solutions considered
4. Impact assessment

---

## Code of Conduct

- Be respectful and constructive
- Focus on the code, not the person
- Help others learn and grow
- Keep discussions professional
