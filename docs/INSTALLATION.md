# SQL Detective — Installation Guide

## Requirements

- **PHP** 8.1 or higher
- **MySQL** 8.0 or MariaDB 10.6+
- **Apache** 2.4+ with `mod_rewrite` enabled
- Composer (for dependency installation)

## Step 1: Clone the Repository

```bash
git clone https://github.com/your-org/sql-detective.git
cd sql-detective
```

## Step 2: Install Dependencies

```bash
composer install
```

## Step 3: Environment Configuration

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

INV_DB_HOST=127.0.0.1
INV_DB_PORT=3306
INV_DB_USERNAME=sql_detective_readonly
INV_DB_PASSWORD=readonly_password_here
```

## Step 4: Generate Application Key

```bash
php artisan key:generate
```

## Step 5: Create Database

```sql
CREATE DATABASE sql_detective CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE corporate_finance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE digital_forensics CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE employee_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## Step 6: Create Database Users

```sql
-- Application user (read/write for app tables only)
CREATE USER 'sql_detective_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON sql_detective.* TO 'sql_detective_user'@'localhost';

-- Investigation user (read-only for investigation databases)
CREATE USER 'sql_detective_readonly'@'localhost' IDENTIFIED BY 'readonly_password_here';
GRANT SELECT ON corporate_finance.* TO 'sql_detective_readonly'@'localhost';
GRANT SELECT ON digital_forensics.* TO 'sql_detective_readonly'@'localhost';
GRANT SELECT ON employee_portal.* TO 'sql_detective_readonly'@'localhost';

FLUSH PRIVILEGES;
```

## Step 7: Run Migrations

```bash
php artisan migrate
```

## Step 8: Seed Database

```bash
php artisan db:seed
```

## Step 9: Configure Apache Virtual Host

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

## Step 10: Test the Application

Visit `http://sql-detective.local` in your browser.

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
- Run `php artisan db:seed` if tables are empty