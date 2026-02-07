# Setup Guide
# ระบบหวยออนไลน์ - Lotto Platform

## Prerequisites

- PHP 8.3+
- Composer 2.x
- Node.js 20+ & NPM
- MySQL 8.0+ / MariaDB 10.6+
- Redis 7.x
- Git

## Installation

### 1. Clone & Install Dependencies

```bash
cd D:\Code\lotto

# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### 2. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Database Setup

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE lotto CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Update .env with database credentials
# DB_DATABASE=lotto
# DB_USERNAME=root
# DB_PASSWORD=your_password

# Run migrations
php artisan migrate

# Seed initial data
php artisan db:seed
```

### 4. Additional Setup

```bash
# Create storage link
php artisan storage:link

# Install Livewire
php artisan livewire:publish --config

# Install Reverb (WebSocket)
php artisan reverb:install

# Clear caches
php artisan optimize:clear
```

### 5. Build Frontend

```bash
# Development
npm run dev

# Production
npm run build
```

### 6. Start Services

```bash
# Start Laravel development server
php artisan serve

# Start queue worker (in separate terminal)
php artisan queue:work redis --queue=high,default,low

# Start WebSocket server (in separate terminal)
php artisan reverb:start

# Start Vite dev server (in separate terminal)
npm run dev
```

## Access

- **Web App:** http://localhost:8000
- **Admin Panel:** http://localhost:8000/admin
- **API:** http://localhost:8000/api

## Default Admin Account

```
Phone: 0999999999
Password: (set in ADMIN_DEFAULT_PASSWORD env)
```

## Production Deployment

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/lotto/public;

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Supervisor for Queue Workers

```ini
[program:lotto-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/lotto/artisan queue:work redis --queue=high,default,low --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=3
redirect_stderr=true
stdout_logfile=/var/www/lotto/storage/logs/worker.log
stopwaitsecs=3600
```

### Cron for Scheduler

```cron
* * * * * cd /var/www/lotto && php artisan schedule:run >> /dev/null 2>&1
```

### Production Optimization

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan icons:cache
npm run build
```
