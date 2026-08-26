# SQL Detective — Installation Guide

## Requirements

- **PHP** 8.1 or higher (no Composer needed)
- **MySQL** 8.0 or MariaDB 10.6+
- **Apache** 2.4+ with `mod_rewrite` enabled

## Step 1: Clone the Repository

```bash
git clone https://github.com/your-org/sql-detective.git
cd sql-detective
```

## Step 2: Environment Configuration

```bash
cp .env.example .env
```

Edit `.env` and configure:

```env
APP_NAME="SQL Detective"
APP_URL=http://localhost:8000
APP_KEY=base64:your-generated-key-here
APP_ENV=local
APP_DEBUG=true

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sql_detective
DB_USERNAME=sql_detective_user
DB_PASSWORD=your_secure_password

DB_INVESTIGATION_HOST=127.0.0.1
DB_INVESTIGATION_PORT=3306
DB_INVESTIGATION_NAME=corporate_finance
DB_INVESTIGATION_USER=sql_detective_readonly
DB_INVESTIGATION_PASSWORD=readonly_password_here
```

## Step 3: Create Databases

```sql
CREATE DATABASE sql_detective CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE corporate_finance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE digital_forensics CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE employee_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## Step 4: Create Database Users

```sql
-- Application user (read/write for app tables, full access during seeding)
CREATE USER 'sql_detective_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON sql_detective.* TO 'sql_detective_user'@'localhost';
GRANT ALL PRIVILEGES ON corporate_finance.* TO 'sql_detective_user'@'localhost';
GRANT ALL PRIVILEGES ON digital_forensics.* TO 'sql_detective_user'@'localhost';
GRANT ALL PRIVILEGES ON employee_portal.* TO 'sql_detective_user'@'localhost';

-- Investigation user (read-only for investigation databases)
CREATE USER 'sql_detective_readonly'@'localhost' IDENTIFIED BY 'readonly_password_here';
GRANT SELECT ON corporate_finance.* TO 'sql_detective_readonly'@'localhost';
GRANT SELECT ON digital_forensics.* TO 'sql_detective_readonly'@'localhost';
GRANT SELECT ON employee_portal.* TO 'sql_detective_readonly'@'localhost';

FLUSH PRIVILEGES;
```

## Step 5: Run Setup

```bash
php setup.php setup
```

This runs all migrations and seeds the database with users, 30 cases, 60 challenges, hints, achievements, and automatically creates + loads the investigation databases with schema and data.

**Alternative commands:**
- `php setup.php migrate` — Run migrations only
- `php setup.php seed` — Run seeds only
- `php setup.php migrate:fresh` — Drop all tables and re-run everything

## Step 6: Configure Web Server

Point your web server document root to the `public/` directory.

**Apache Virtual Host:**
```apache
<VirtualHost *:80>
    ServerName sql-detective.local
    DocumentRoot /path/to/sql-detective/public

    <Directory /path/to/sql-detective/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/sql-detective-error.log
    CustomLog ${APACHE_LOG_DIR}/sql-detective-access.log combined
</VirtualHost>
```

## Step 7: Set Permissions

Ensure the `storage/` directory is writable by the web server:

```bash
chmod -R 775 storage/
chown -R www-data:www-data storage/
```

## Step 8: Verify Installation

Visit `http://sql-detective.local/health` in your browser. You should see a JSON response confirming the application is running.

### Default Accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@sqldetective.com | password |
| Demo | demo@sqldetective.com | password |

## Troubleshooting

### 500 Internal Server Error
- Check `.env` configuration
- Ensure `storage/` directory is writable
- Check PHP error logs

### Database Connection Failed
- Verify MySQL is running
- Check credentials in `.env`
- Ensure database users exist with correct permissions

### Query Execution Fails
- Verify the investigation database user has SELECT permission
- Check that investigation database tables exist
- Run `php setup.php seed` if tables are empty
