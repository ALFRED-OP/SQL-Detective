# SQL Detective — Architecture

## Overview

SQL Detective follows the MVC (Model-View-Controller) pattern with a custom PHP framework. No heavy frameworks — everything is hand-built for learning and performance.

## Directory Structure

```
sql-detective/
├── app/
│   ├── Controllers/       # Request handlers
│   ├── Core/              # Framework core (Application, Router, View, Migration)
│   ├── Helpers/           # Utility functions
│   ├── Middleware/         # Request middleware (Auth, CSRF, Rate Limit)
│   ├── Models/            # Database models (base + specific)
│   ├── Services/          # Business logic (QueryValidator, ChallengeValidator)
│   └── Validators/        # Input validation
├── config/                # Configuration files
│   ├── app.php            # App settings (name, URL, debug)
│   ├── database.php       # Database connections
│   └── security.php       # Security settings (CSRF, rate limits)
├── database/
│   ├── migrations/        # Database schema (17 migration files)
│   ├── seeds/             # Database seeders
│   └── investigation_databases/  # Case-specific databases
├── docs/                  # Project documentation
├── public/                # Web root
│   ├── .htaccess          # Apache URL rewriting
│   ├── index.php          # Front controller
│   └── assets/            # Static assets (CSS, JS)
├── routes/
│   └── web.php            # Route definitions
├── storage/               # Logs, cache, sessions
└── views/                 # PHP templates
    ├── layouts/           # Master layout
    ├── admin/             # Admin panel views
    ├── cases/             # Investigation case views
    ├── detective/         # SQL workspace view
    └── ...
```

## Request Flow

```
HTTP Request
    ↓
public/index.php (entry point)
    ↓
Application::bootstrap()
    ↓
Router::dispatch() → matches route
    ↓
Middleware pipeline:
  1. RateLimitMiddleware
  2. CsrfMiddleware
  3. AuthMiddleware / GuestMiddleware
    ↓
Controller::action()
    ↓
View::render() → HTML response
```

## Database Architecture

### Two Database Connections

1. **Application Database** (`sql_detective`)
   - Stores users, cases, challenges, achievements, progress
   - Connected via `DB_USERNAME` env variable
   - Full CRUD permissions

2. **Investigation Databases** (`corporate_finance`, `digital_forensics`, `employee_portal`)
   - Read-only databases that players query
   - Connected via `INV_DB_USERNAME` env variable
   - SELECT-only permissions
   - **Player queries NEVER execute against the application database**

### Security Model

```
Player Query
    ↓
QueryValidator::validate() — checks SQL safety
    ↓
ChallengeValidator::executeAndValidate() — runs query
    ↓
PDO (read-only connection) → Investigation Database
    ↓
Result returned to player
```

## Key Components

### QueryValidator
- Validates SQL queries for safety
- Blocks: DROP, DELETE, INSERT, UPDATE, ALTER, TRUNCATE, GRANT, etc.
- Blocks: Subqueries, UNION-based injection attempts
- Blocks: System tables, stored procedures
- Max query length: 10,000 characters
- Max execution time: 5 seconds

### ChallengeValidator
- Compares player query results against expected answers
- Supports: exact match, row count, value check, column existence
- Awards XP on correct answers
- Manages achievement triggers
- Tracks progress per user per case

### Middleware Stack
- **RateLimitMiddleware**: Limits requests per minute (configurable)
- **CsrfMiddleware**: Validates CSRF tokens on POST/PUT/DELETE
- **AuthMiddleware**: Requires authenticated session
- **GuestMiddleware**: Redirects authenticated users away from login/register
- **AdminMiddleware**: Requires admin role

## CSS Architecture

- CSS Custom Properties (variables) for theming
- Dark/light mode via `data-theme` attribute on `<html>`
- Two CSS files:
  - `app.css` — Design system, layout, typography, variables
  - `components.css` — Component-specific styles

## JavaScript Architecture

- Vanilla JavaScript (no frameworks)
- Three JS files:
  - `app.js` — Global functionality (theme, menu, AJAX forms)
  - `detective.js` — SQL workspace (editor, execution, schema)
  - `admin.js` — Admin panel (confirmations, inline editing)
- Page-specific scripts loaded via `$extraScripts` in layout