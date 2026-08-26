# SQL Detective — Architecture

## Overview

SQL Detective is built with plain PHP — no framework, no Composer dependencies, no classes except validators. Everything is functions, arrays, and simple PHP templates.

## Directory Structure

```
sql-detective/
├── config/                # Configuration files
│   ├── app.php            # App settings (name, URL, debug)
│   ├── database.php       # Database connections
│   └── security.php       # Security settings (CSRF, rate limits)
├── controllers/           # Plain PHP function files
│   ├── home.php           # Home page
│   ├── auth.php           # Login, register, logout
│   ├── dashboard.php      # User dashboard
│   ├── cases.php          # Case listing and details
│   ├── detective.php      # SQL workspace
│   ├── profile.php        # User profile
│   ├── leaderboard.php    # Rankings
│   ├── achievements.php   # Achievements display
│   ├── admin.php          # Admin panel
│   └── api.php            # JSON API endpoints
├── database/
│   ├── migrations/        # Database schema (17 migration files)
│   ├── seeds/             # Database seeders
│   └── investigation_databases/  # Case-specific databases
├── docs/                  # Project documentation
├── includes/              # Shared infrastructure
│   ├── init.php           # Bootstrap, autoloading, session
│   ├── helpers.php        # Utility functions (view, redirect, json, etc.)
│   ├── db.php             # Database connection and query helpers
│   ├── auth.php           # Authentication functions
│   ├── csrf.php           # CSRF token generation and validation
│   ├── rate_limit.php     # Rate limiting
│   ├── validator.php      # Input validation
│   ├── query_validator.php    # SQL query safety validation
│   ├── challenge_validator.php # Challenge answer validation
│   └── game.php           # XP, levels, achievements logic
├── public/                # Web root
│   ├── .htaccess          # Apache URL rewriting
│   ├── index.php          # Front controller (all routes)
│   └── assets/            # Static assets (CSS, JS, images)
├── storage/               # Logs, cache, sessions
├── views/                 # PHP templates
│   ├── layouts/           # Master layout (app.php)
│   ├── admin/             # Admin panel views
│   ├── cases/             # Investigation case views
│   ├── detective/         # SQL workspace view
│   └── ...
└── setup.php              # CLI tool (migrate, seed, setup)
```

## Request Flow

```
HTTP Request
    ↓
public/index.php (front controller — all routes)
    ↓
Route matching (if/else + regex on REQUEST_URI)
    ↓
include controller file → call function
    ↓
Business logic (includes/ helpers, validators)
    ↓
View rendering (ob_start / view() helper / ob_get_clean)
    ↓
HTML response
```

## Key Principles

- **No namespaces, no autoloader** — everything is `require`'d via `includes/init.php`
- **No classes except validators** — controllers are plain functions, config is arrays
- **No Composer** — zero external dependencies
- **No ORM** — raw PDO with prepared statements
- **No template engine** — plain PHP with `ob_start()`/`ob_get_clean()` for layout wrapping

## Routing

All routes are defined in `public/index.php` using simple pattern matching:

```php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

if ($uri === '/' && $method === 'GET') {
    require CONTROLLERS_PATH . '/home.php';
    home_page();
} elseif (preg_match('#^/cases/(\d+)$#', $uri, $matches) && $method === 'GET') {
    require CONTROLLERS_PATH . '/cases.php';
    show_case($matches[1]);
}
// ... etc
```

## Controller Functions

Controllers are plain PHP functions in separate files:

```php
// controllers/home.php
function home_page() {
    $user = current_user();
    view('home', ['user' => $user]);
}

// controllers/cases.php
function show_case($id) {
    $case = db_query("SELECT * FROM cases WHERE id = ?", [$id])->fetch();
    view('cases.show', ['case' => $case]);
}
```

## View System

Views are plain PHP templates with a layout wrapper:

```php
// Using the view() helper from includes/helpers.php
view('cases.show', ['case' => $case]);

// Inside view('cases.show'):
// 1. ob_start()
// 2. require views/cases/show.php
// 3. $content = ob_get_clean()
// 4. require views/layouts/app.php (which echoes $content)
```

Variables passed to `view()` are extracted into the template scope.

## Database Architecture

### Two Database Connections

1. **Application Database** (`sqldetective`)
   - Stores users, cases, challenges, achievements, progress
   - Connected via `DB_*` env variables
   - Full CRUD permissions

2. **Investigation Databases** (`corporatefinance`, `digitalforensics`, `employeeportal`)
   - Read-only databases that players query
   - Connected via `DB_INVESTIGATION_*` env variables
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

### QueryValidator (`includes/query_validator.php`)
- Validates SQL queries for safety
- Blocks: DROP, DELETE, INSERT, UPDATE, ALTER, TRUNCATE, GRANT, etc.
- Blocks: Subqueries, UNION-based injection attempts
- Blocks: System tables, stored procedures
- Max query length: 10,000 characters
- Max execution time: 5 seconds

### ChallengeValidator (`includes/challenge_validator.php`)
- Compares player query results against expected answers
- Supports: exact match, row count, value check, column existence
- Awards XP on correct answers
- Manages achievement triggers
- Tracks progress per user per case

### Security Functions (`includes/csrf.php`, `includes/rate_limit.php`)
- CSRF token generation and validation on all POST/PUT/DELETE requests
- Rate limiting per IP/endpoint
- Session security

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
