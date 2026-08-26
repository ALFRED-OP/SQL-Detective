# SQL Detective — Installation Guide

## Requirements

- **PHP** 8.1 or higher (no Composer needed)
- **MySQL** 8.0 or MariaDB 10.6+
- **Apache** 2.4+ with `mod_rewrite` enabled, or **Nginx**

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
DB_NAME=sqldetective
DB_USER=sqldetectiveapp
DB_PASSWORD=your_secure_password

DB_INVESTIGATION_HOST=127.0.0.1
DB_INVESTIGATION_PORT=3306
DB_INVESTIGATION_NAME=corporatefinance
DB_INVESTIGATION_USER=sqldetectivereadonly
DB_INVESTIGATION_PASSWORD=readonly_password_here
```

## Step 3: Create Databases

```sql
CREATE DATABASE sqldetective CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE corporatefinance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE digitalforensics CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE employeeportal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## Step 4: Create Database Users

```sql
-- Application user (read/write for app tables, full access during seeding)
CREATE USER 'sqldetectiveapp'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON sqldetective.* TO 'sqldetectiveapp'@'localhost';
GRANT ALL PRIVILEGES ON corporatefinance.* TO 'sqldetectiveapp'@'localhost';
GRANT ALL PRIVILEGES ON digitalforensics.* TO 'sqldetectiveapp'@'localhost';
GRANT ALL PRIVILEGES ON employeeportal.* TO 'sqldetectiveapp'@'localhost';

-- Investigation user (read-only for investigation databases)
CREATE USER 'sqldetectivereadonly'@'localhost' IDENTIFIED BY 'readonly_password_here';
GRANT SELECT ON corporatefinance.* TO 'sqldetectivereadonly'@'localhost';
GRANT SELECT ON digitalforensics.* TO 'sqldetectivereadonly'@'localhost';
GRANT SELECT ON employeeportal.* TO 'sqldetectivereadonly'@'localhost';

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

**Nginx:**
```nginx
server {
    listen 80;
    server_name sql-detective.local;
    root /path/to/sql-detective/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
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
