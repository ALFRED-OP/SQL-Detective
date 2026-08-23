# SQL Detective

**Investigate. Query. Discover the Truth.**

An interactive database investigation game designed around SQL, relational databases, logical reasoning, and investigative problem solving. Built as a NIELIT A-Level Major Project.

## Features

- **30 Investigation Cases** across Beginner, Intermediate, and Advanced levels
- **Professional SQL Editor** with syntax highlighting, line numbers, and query history
- **Database Explorer** with schema visualization and relationship diagrams
- **Evidence System** with documents, logs, and digital artifacts
- **Progressive Challenges** with XP rewards and hint system
- **Achievement System** with 15+ unlockable achievements
- **Global Leaderboard** with anti-cheat protection
- **Admin Panel** for case and content management
- **Dark/Light Mode** with persistent theme preference
- **Responsive Design** for desktop, tablet, and mobile

## Tech Stack

- **Backend**: PHP 8.1+ (no framework, pure MVC)
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, Vanilla JavaScript (ES6+)
- **Routing**: FastRoute
- **HTTP**: Laminas Diactoros
- **Environment**: vlucas/phpdotenv

## Requirements

- PHP 8.1+
- MySQL 8.0+ or MariaDB 10.5+
- Composer 2.x
- Web server (Nginx/Apache) with PHP-FPM

## Installation

### 1. Clone and Install Dependencies

```bash
git clone <repository-url>
cd SQL-Detective
composer install
```

### 2. Configure Environment

```bash
cp .env.example .env
# Edit .env with your database credentials
```

### 3. Create Databases

```sql
CREATE DATABASE sql_detective CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE sql_detective_investigation CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create read-only user for investigation queries
CREATE USER 'investigation_readonly'@'localhost' IDENTIFIED BY 'secure_password';
GRANT SELECT ON sql_detective_investigation.* TO 'investigation_readonly'@'localhost';
FLUSH PRIVILEGES;
```

### 4. Run Migrations

```bash
php artisan migrate
```

### 5. Seed Database

```bash
php artisan db:seed
```

### 6. Generate Application Key

```bash
php artisan key:generate
```

### 7. Configure Web Server

**Nginx (recommended):**

```nginx
server {
    listen 80;
    server_name sqldetective.local;
    root /path/to/SQL-Detective/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

**Apache:**

```apache
<VirtualHost *:80>
    ServerName sqldetective.local
    DocumentRoot /path/to/SQL-Detective/public

    <Directory /path/to/SQL-Detective/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 8. Development Server (Quick Start)

```bash
cd public
php -S localhost:8000
```

## Default Accounts

After seeding:
- **Admin**: admin@sqldetective.local / SecurePass123!
- **Demo**: demo@sqldetective.local / DemoPass123!

## Project Structure

```
sql-detective/
├── app/
│   ├── Controllers/     # HTTP Controllers
│   ├── Models/          # Data Models
│   ├── Services/        # Business Logic
│   ├── Repositories/    # Data Access
│   ├── Middleware/      # HTTP Middleware
│   ├── Validators/      # Input Validation
│   ├── Helpers/         # Global Helpers
│   └── Core/            # Core Classes
├── config/              # Configuration Files
├── public/              # Web Root
│   ├── assets/          # CSS, JS, Images
│   └── index.php        # Entry Point
├── routes/              # Route Definitions
├── views/               # Blade-style Templates
├── database/
│   ├── migrations/      # Schema Migrations
│   ├── seeds/           # Seed Data
│   └── investigation_databases/  # Case Databases
├── storage/             # Logs, Cache
├── tests/               # PHPUnit Tests
└── docs/                # Documentation
```

## Security Features

- **SQL Injection Prevention**: PDO prepared statements, query validation, read-only investigation DB user
- **XSS Protection**: Output escaping, CSP headers
- **CSRF Protection**: Token validation on all state-changing requests
- **Rate Limiting**: Login, query execution, challenge submissions
- **Session Security**: HttpOnly, Secure, SameSite cookies; regeneration on login
- **Password Security**: bcrypt with cost 12
- **Audit Logging**: Authentication events, suspicious activity
- **Anti-Cheat**: Server-side game state validation

## Development

### Run Tests

```bash
composer test
```

### Code Style

```bash
composer cs        # Check
composer cs:fix    # Fix
```

### Create Migration

```bash
# Create file in database/migrations/ with timestamp prefix
# e.g., 2024_01_15_120000_create_new_table.php
```

## Deployment

### Production Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate strong `APP_KEY`
- [ ] Use HTTPS with valid SSL certificate
- [ ] Set `SESSION_SECURE=true`
- [ ] Configure strong database passwords
- [ ] Disable database root access
- [ ] Set up log rotation
- [ ] Configure backups
- [ ] Enable OPcache

### VPS Requirements (Minimum)

- 1 CPU Core
- 1 GB RAM
- Ubuntu 22.04+
- Nginx + PHP-FPM
- MySQL/MariaDB

## Documentation

See `/docs` directory for:
- Project Proposal
- System Design
- Database Design (ER Diagrams)
- Data Flow Diagrams
- Use Case Diagrams
- Security Design
- Testing Plan
- User Manual
- Installation Guide
- Viva Questions

## License

MIT License - See LICENSE file for details.

## Academic Use

This project is designed to meet NIELIT A-Level Major Project requirements. The codebase demonstrates:
- MVC Architecture
- Database Normalization (1NF, 2NF, 3NF)
- Relational Database Design
- CRUD Operations
- Authentication & Authorization
- Session Management
- Form Validation
- Security Best Practices
- Error Handling & Logging
- Responsive Web Design
- REST-style API Endpoints
- Database Transactions
- Access Control

---

**SQL Detective** - Built for learning, designed for investigation.