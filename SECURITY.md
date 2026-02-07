# Security Policy

## Supported Versions

| Version | Supported          |
|---------|--------------------|
| 1.x     | :white_check_mark: |
| < 1.0   | :x:                |

## Reporting a Vulnerability

If you discover a security vulnerability within Lotto Platform, please send an email to **security@example.com**.

**Please do NOT open a public GitHub issue for security vulnerabilities.**

### What to Include

1. Description of the vulnerability
2. Steps to reproduce
3. Potential impact
4. Suggested fix (if any)

### Response Timeline

- **Acknowledgment**: Within 24 hours
- **Assessment**: Within 72 hours
- **Fix**: Depending on severity
  - Critical: Within 24 hours
  - High: Within 1 week
  - Medium: Within 2 weeks
  - Low: Next scheduled release

### Disclosure Policy

- We will coordinate disclosure with you
- We will credit you (unless you prefer anonymity)
- We will not take legal action against good-faith researchers

## Security Measures

This project implements the following security measures:

### Authentication & Authorization
- Laravel Sanctum for API authentication
- SMS OTP verification
- Role-based access control (RBAC)
- Admin IP whitelist

### Data Protection
- AES-256-GCM encryption for SMS payloads
- HMAC-SHA256 request signing
- Nonce-based replay attack prevention
- bcrypt password hashing
- CSRF protection on all forms

### Infrastructure
- HTTPS enforced in production
- Rate limiting on sensitive endpoints
- SQL injection prevention (Eloquent ORM)
- XSS prevention (Blade auto-escaping)
- Security headers (CSP, X-Frame-Options, etc.)
- Audit logging for admin actions
