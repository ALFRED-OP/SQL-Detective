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

- **Backend**: PHP 8.1+ (plain PHP, zero external dependencies)
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, Vanilla JavaScript (ES6+)
- **Server**: Apache with mod_rewrite or Nginx with try_files

## Requirements

- PHP 8.1+
- MySQL 8.0+ or MariaDB 10.5+
- Web server (Nginx/Apache) with PHP-FPM

## Installation

### 1. Clone Repository

```bash
git clone <repository-url>
cd SQL-Detective
```

### 2. Configure Environment

```bash
cp .env.example .env
# Edit .env with your database credentials
```

### 3. Create Databases

```sql
CREATE DATABASE sqldetective CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE corporatefinance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE digitalforensics CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE employeeportal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create app user (full access for seeding, restrict after)
CREATE USER 'sqldetectiveapp'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON sqldetective.* TO 'sqldetectiveapp'@'localhost';
GRANT ALL PRIVILEGES ON corporatefinance.* TO 'sqldetectiveapp'@'localhost';
GRANT ALL PRIVILEGES ON digitalforensics.* TO 'sqldetectiveapp'@'localhost';
GRANT ALL PRIVILEGES ON employeeportal.* TO 'sqldetectiveapp'@'localhost';
FLUSH PRIVILEGES;
```

### 4. Run Setup

```bash
php setup.php setup
```

This runs migrations and seeds all 30 cases, challenges, suspects, evidence, and achievements.

### 5. Secure Investigation Databases (Post-Setup)

```sql
-- Revoke write access on investigation DBs
REVOKE ALL PRIVILEGES ON corporatefinance.* FROM 'sqldetectiveapp'@'localhost';
REVOKE ALL PRIVILEGES ON digitalforensics.* FROM 'sqldetectiveapp'@'localhost';
REVOKE ALL PRIVILEGES ON employeeportal.* FROM 'sqldetectiveapp'@'localhost';
GRANT SELECT ON corporatefinance.* TO 'sqldetectiveapp'@'localhost';
GRANT SELECT ON digitalforensics.* TO 'sqldetectiveapp'@'localhost';
GRANT SELECT ON employeeportal.* TO 'sqldetectiveapp'@'localhost';
FLUSH PRIVILEGES;
```

### 6. Configure Web Server

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
├── public/              # Web Root
│   ├── index.php        # Front Controller (all routing)
│   ├── .htaccess        # URL Rewriting
│   └── assets/          # CSS, JS, Images
├── includes/            # Core Functions & Config
│   ├── init.php         # Bootstrap (env, session, DB)
│   ├── helpers.php      # Global Helper Functions
│   ├── db.php           # Database Connections
│   ├── auth.php         # Authentication Middleware
│   ├── csrf.php         # CSRF Protection
│   ├── rate_limit.php   # Rate Limiting
│   ├── validator.php    # Input Validation
│   ├── query_validator.php  # SQL Query Safety
│   ├── challenge_validator.php  # Challenge Validation
│   └── game.php         # XP, Levels, Achievements
├── controllers/         # Request Handlers (plain functions)
│   ├── home.php
│   ├── auth.php
│   ├── dashboard.php
│   ├── cases.php
│   ├── detective.php
│   ├── profile.php
│   ├── leaderboard.php
│   ├── achievements.php
│   ├── admin.php
│   └── api.php
├── views/               # PHP Templates
│   ├── layouts/app.php  # Main Layout
│   └── ...              # View Files
├── config/              # Configuration Files
├── database/
│   ├── migrations/      # Schema Migrations
│   ├── seeds/           # Seed Data (30 cases)
│   └── investigation_databases/  # Case DB Schemas
├── storage/             # Logs, Sessions, Cache
├── setup.php            # CLI Setup (replaces artisan)
├── tests/               # PHPUnit Tests
└── docs/                # Documentation
```

## Security Features

- **SQL Injection Prevention**: PDO prepared statements, query validation, separate read-only investigation DB user
- **XSS Protection**: Output escaping via `e()`, CSP headers
- **CSRF Protection**: Token validation on all state-changing requests
- **Rate Limiting**: Login, query execution, challenge submissions (file-based)
- **Session Security**: HttpOnly, Secure, SameSite cookies; regeneration on login
- **Password Security**: bcrypt with configurable cost
- **Audit Logging**: Authentication events, suspicious activity
- **Anti-Cheat**: Server-side game state validation, separate investigation DBs

## Development

### Run Tests

```bash
php tests/run.php
```

### Setup Commands

```bash
php setup.php migrate       # Run pending migrations
php setup.php seed          # Seed the database
php setup.php setup         # Migrate + Seed
php setup.php migrate:fresh # Drop all tables and re-run
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
- Front Controller Architecture (single entry point)
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
- Zero External Dependencies

---

**SQL Detective** - Built for learning, designed for investigation.