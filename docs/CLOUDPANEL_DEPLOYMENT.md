# SQL Detective — CloudPanel Deployment Guide (Zero to Live)

Complete step-by-step guide to deploy SQL Detective on a VPS running CloudPanel with Nginx.

**Target:** `sqldetective.dipteshdey.in`

---

## Prerequisites

- VPS with CloudPanel installed (Debian/Ubuntu recommended)
- Root SSH access to your VPS
- A domain (`dipteshdey.in`) with DNS management access
- CloudPanel admin login (usually `https://YOUR_VPS_IP:8443`)

---

## Phase 1: DNS Setup

1. Log into your domain registrar (wherever `dipteshdey.in` is managed)
2. Add an **A Record**:

| Type | Name | Value | TTL |
|------|------|-------|-----|
| A | `sqldetective` | `YOUR_VPS_IP_ADDRESS` | 300 |

3. Wait 5-10 minutes for propagation. Verify:

```bash
dig sqldetective.dipteshdey.in
# or
nslookup sqldetective.dipteshdey.in
```

Both should return your VPS IP.

---

## Phase 2: Create Site in CloudPanel

### Option A — Web UI (Recommended)

1. Log into CloudPanel: `https://YOUR_VPS_IP:8443`
2. Click **+ Add Site** (top right)
3. Select **Create a PHP Site**
4. Fill in:
   - **Application:** Generic
   - **Domain Name:** `sqldetective.dipteshdey.in`
   - **PHP Version:** 8.1+ (select highest available, e.g. 8.4)
   - **Site User:** `sqldetective`
   - **Site User Password:** create a strong password (save it!)
5. Click **Create**
6. CloudPanel will auto-provision SSL via Let's Encrypt

### Option B — CLI (SSH as root)

```bash
clpctl site:add:php \
  --domainName=sqldetective.dipteshdey.in \
  --phpVersion=8.4 \
  --vhostTemplate='Generic' \
  --siteUser=sqldetective \
  --siteUserPassword='YOUR_STRONG_PASSWORD'
```

After creation, the site user's home directory will be at:
```
/home/sqldetective/
```

---

## Phase 3: Upload Project Files

SSH into your VPS as root.

### Option A — Git (Recommended)

```bash
cd /home/sqldetective/htdocs/sqldetective.dipteshdey.in/

# If your repo is on GitHub/GitLab:
git clone <your-repo-url> .

# Or clone into a temp folder and move:
git clone <your-repo-url> temp
mv temp/* temp/.* . 2>/dev/null
rm -rf temp
```

### Option B — SCP from Windows

Open PowerShell on your local machine:

```powershell
scp -r "D:\personal\A LVL PMM Project\SQL-Detective\SQL-Detective\*" `
  sqldetective@YOUR_VPS_IP:/home/sqldetective/htdocs/sqldetective.dipteshdey.in/
```

### Option C — SFTP

Use WinSCP or FileZilla to upload the entire project to:
```
/home/sqldetective/htdocs/sqldetective.dipteshdey.in/
```

---

## Phase 4: Set Document Root to `public/`

CloudPanel's default document root is the site directory. Our app needs `public/`.

### Web UI

1. Go to **Sites** → `sqldetective.dipteshdey.in` → **Settings**
2. Find **Root Directory** and change it to:
   ```
   /home/sqldetective/htdocs/sqldetective.dipteshdey.in/public
   ```
3. Click **Save**

### CLI

```bash
# Edit the vhost config directly
nano /etc/nginx/sites-enabled/sqldetective.dipteshdey.in.conf
```

Find the `root` line and change it to:
```nginx
root /home/sqldetective/htdocs/sqldetective.dipteshdey.in/public;
```

Then reload Nginx:
```bash
clpctl reload:nginx
```

---

## Phase 5: Configure `.env`

SSH into your VPS:

```bash
cd /home/sqldetective/htdocs/sqldetective.dipteshdey.in
cp .env.example .env
nano .env
```

Set these values:

```env
APP_NAME="SQL Detective"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sqldetective.dipteshdey.in

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=sqldetective
DB_USER=sqldetectiveapp
DB_PASSWORD=YOUR_APP_DB_PASSWORD_HERE

DB_INVESTIGATION_HOST=127.0.0.1
DB_INVESTIGATION_PORT=3306
DB_INVESTIGATION_NAME=corporatefinance
DB_INVESTIGATION_USER=sqldetectivereadonly
DB_INVESTIGATION_PASSWORD=YOUR_READONLY_DB_PASSWORD_HERE

SESSION_LIFETIME=120
SESSION_COOKIE=sqldetectivesession
SESSION_DOMAIN=sqldetective.dipteshdey.in
SESSION_SECURE=true
```

**Important:** Replace the password placeholders with real strong passwords. Save both passwords somewhere safe.

---

## Phase 6: Create MySQL Databases & Users

Log into MySQL as root:

```bash
mysql -u root -p
```

Enter your MySQL root password, then paste this entire block:

```sql
-- ============================================================
-- 1. Create Databases
-- ============================================================
CREATE DATABASE sqldetective CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE corporatefinance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE digitalforensics CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE employeeportal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ============================================================
-- 2. Create App User (full access for seeding)
-- ============================================================
CREATE USER 'sqldetectiveapp'@'127.0.0.1' IDENTIFIED BY 'YOUR_APP_DB_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON sqldetective.* TO 'sqldetectiveapp'@'127.0.0.1';
GRANT ALL PRIVILEGES ON corporatefinance.* TO 'sqldetectiveapp'@'127.0.0.1';
GRANT ALL PRIVILEGES ON digitalforensics.* TO 'sqldetectiveapp'@'127.0.0.1';
GRANT ALL PRIVILEGES ON employeeportal.* TO 'sqldetectiveapp'@'127.0.0.1';

-- ============================================================
-- 3. Create Read-Only User (for investigation queries)
-- ============================================================
CREATE USER 'sqldetectivereadonly'@'127.0.0.1' IDENTIFIED BY 'YOUR_READONLY_DB_PASSWORD_HERE';
GRANT SELECT ON corporatefinance.* TO 'sqldetectivereadonly'@'127.0.0.1';
GRANT SELECT ON digitalforensics.* TO 'sqldetectivereadonly'@'127.0.0.1';
GRANT SELECT ON employeeportal.* TO 'sqldetectivereadonly'@'127.0.0.1';

FLUSH PRIVILEGES;
```

**Replace** `YOUR_APP_DB_PASSWORD_HERE` and `YOUR_READONLY_DB_PASSWORD_HERE` with the same passwords you used in `.env`.

Type `EXIT` to leave MySQL.

---

## Phase 7: Run Setup Script

```bash
cd /home/sqldetective/htdocs/sqldetective.dipteshdey.in
php setup.php setup
```

This will:
1. Run all 17 database migrations (creates app tables)
2. Seed 30 cases, 60 challenges, suspects, evidence, achievements
3. Create the 3 investigation databases with schemas and data
4. Create default admin and demo user accounts

You should see output like:
```
Migrating: 001_create_users_table
Migrating: 002_create_cases_table
...
Migration(s) completed.
Setup complete!
```

---

## Phase 8: Secure Investigation Databases (Post-Seed)

After seeding is complete, revoke write access on investigation databases:

```bash
mysql -u root -p
```

```sql
-- Revoke write access on investigation DBs
REVOKE ALL PRIVILEGES ON corporatefinance.* FROM 'sqldetectiveapp'@'127.0.0.1';
REVOKE ALL PRIVILEGES ON digitalforensics.* FROM 'sqldetectiveapp'@'127.0.0.1';
REVOKE ALL PRIVILEGES ON employeeportal.* FROM 'sqldetectiveapp'@'127.0.0.1';

-- Grant read-only access (app still needs to route queries)
GRANT SELECT ON corporatefinance.* TO 'sqldetectiveapp'@'127.0.0.1';
GRANT SELECT ON digitalforensics.* TO 'sqldetectiveapp'@'127.0.0.1';
GRANT SELECT ON employeeportal.* TO 'sqldetectiveapp'@'127.0.0.1';

FLUSH PRIVILEGES;
```

Type `EXIT` to leave MySQL.

---

## Phase 9: Set File Permissions

```bash
cd /home/sqldetective/htdocs/sqldetective.dipteshdey.in

# Ensure the site user owns all files
chown -R sqldetective:www-data .

# Make storage writable by web server
chmod -R 775 storage/
chmod -R 775 public/assets/

# Protect .env from web access
chmod 640 .env

# Ensure setup.php can't be accessed from web (already protected by CloudPanel)
```

---

## Phase 10: Configure Nginx (If Needed)

CloudPanel's **Generic** vhost template already includes:

```nginx
try_files $uri $uri/ /index.php?$query_string;
```

This is all our app needs. **No changes should be required.**

### Optional: Add Security Headers

Edit the vhost config:

```bash
nano /etc/nginx/sites-enabled/sqldetective.dipteshdey.in.conf
```

Add inside the `server { }` block:

```nginx
add_header X-Content-Type-Options "nosniff" always;
add_header X-Frame-Options "DENY" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';" always;
```

Reload Nginx:

```bash
clpctl reload:nginx
```

---

## Phase 11: Test the Deployment

1. Visit `https://sqldetective.dipteshdey.in`
2. You should see the SQL Detective homepage
3. Try logging in with:

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@sqldetective.local` | `SecurePass123!` |
| Demo | `demo@sqldetective.local` | `DemoPass123!` |

4. Navigate to **Cases** → select a case → **Enter Investigation**
5. Try running a simple query:
   ```sql
   SELECT * FROM employees LIMIT 5;
   ```
6. Verify the schema viewer works
7. Test challenge submission

---

## Troubleshooting

### 500 Internal Server Error

```bash
# Check PHP error log
tail -50 /home/sqldetective/logs/php_errors.log

# Or check CloudPanel error log
tail -50 /var/log/nginx/error.log
```

Common causes:
- `.env` file missing or misconfigured
- `storage/` directory not writable
- PHP version too old (must be 8.1+)

### Database Connection Failed

```bash
# Test MySQL connection
mysql -u sqldetectiveapp -p sqldetective -h 127.0.0.1

# Verify databases exist
mysql -u root -p -e "SHOW DATABASES;"
```

### Page Not Found (404)

- Verify document root is set to `.../public`
- Check Nginx config has `try_files $uri $uri/ /index.php?$query_string;`

### SSL Issues

CloudPanel auto-provisions Let's Encrypt certificates. If it fails:

```bash
clpctl site:renew:letsencrypt:certificate --domainName=sqldetective.dipteshdey.in
```

### Setup Script Fails

```bash
# Check PHP CLI is available
php --version

# Run with verbose output
php setup.php migrate
php setup.php seed
```

---

## Post-Deployment Checklist

- [ ] DNS A record pointing to VPS IP
- [ ] Site created in CloudPanel
- [ ] Document root set to `public/`
- [ ] `.env` configured with production values
- [ ] 4 MySQL databases created
- [ ] 2 MySQL users created (app + readonly)
- [ ] `php setup.php setup` completed successfully
- [ ] Investigation databases secured (read-only for app user)
- [ ] File permissions set correctly
- [ ] SSL certificate active
- [ ] Homepage loads at `https://sqldetective.dipteshdey.in`
- [ ] Login works with admin account
- [ ] Cases list loads
- [ ] Investigation workspace loads
- [ ] SQL query execution works
- [ ] Challenge submission works

---

## Summary of Naming Convention

| Resource | Name |
|----------|------|
| App Database | `sqldetective` |
| Investigation DB 1 | `corporatefinance` |
| Investigation DB 2 | `digitalforensics` |
| Investigation DB 3 | `employeeportal` |
| App DB User | `sqldetectiveapp` |
| Read-Only DB User | `sqldetectivereadonly` |
| Session Cookie | `sqldetectivesession` |
| Domain | `sqldetective.dipteshdey.in` |
