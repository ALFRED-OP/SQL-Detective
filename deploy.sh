#!/bin/bash
# SQL Detective - VPS Deployment Script
# Run from project root on the VPS: bash deploy.sh

set -e

echo "=== SQL Detective Deployment ==="
echo ""

# 1. Ensure storage directories exist with proper permissions
echo "[1/6] Creating storage directories..."
mkdir -p storage/sessions storage/logs storage/cache
chmod -R 775 storage/
echo "  Done."

# 2. Check .env exists
echo "[2/6] Checking .env configuration..."
if [ ! -f .env ]; then
    echo "  ERROR: .env file not found! Copy .env.example to .env and configure it."
    exit 1
fi
echo "  .env found."

# 3. Ensure correct PHP version
echo "[3/6] Checking PHP version..."
PHP_VER=$(php -r 'echo PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION;')
echo "  PHP version: $PHP_VER"
if [ $(php -r 'echo PHP_MAJOR_VERSION;') -lt 8 ] || [ $(php -r 'echo PHP_MINOR_VERSION;') -lt 1 ]; then
    echo "  WARNING: PHP 8.1+ recommended. Current: $PHP_VER"
fi

# 4. Ensure correct MySQL user permissions
echo "[4/6] Checking MySQL user permissions..."
source .env 2>/dev/null || true
DB_USER="${DB_USER:-sqldetective}"
DB_ROOT_USER="${DB_ROOT_USER:-root}"
echo "  App DB user: $DB_USER"
echo "  Root DB user: $DB_ROOT_USER"

# 5. Run setup (migrations + seed + investigation data)
echo "[5/6] Running setup (migrations + seed + investigation DBs)..."
php setup.php setup

# 6. Fix file permissions for web server
echo "[6/6] Fixing file permissions..."
SITE_USER=""
# Detect CloudPanel site user from /home/<user>/htdocs/ path
PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"
if [[ "$PROJECT_DIR" =~ ^/home/([^/]+)/htdocs/ ]]; then
    SITE_USER="${BASH_REMATCH[1]}"
fi

if [ -n "$SITE_USER" ] && id "$SITE_USER" >/dev/null 2>&1; then
    chown -R "$SITE_USER":"$SITE_USER" storage/
    chmod -R 775 storage/
    echo "  Ownership set to $SITE_USER (CloudPanel site user)."
elif id "www-data" >/dev/null 2>&1; then
    chown -R www-data:www-data storage/
    chmod -R 775 storage/
    echo "  Ownership set to www-data."
elif id "nginx" >/dev/null 2>&1; then
    chown -R nginx:nginx storage/
    chmod -R 775 storage/
    echo "  Ownership set to nginx."
else
    chmod -R 777 storage/
    echo "  Storage set to 777 (no web server user found)."
fi

echo ""
echo "=== Deployment Complete ==="
echo ""
echo "IMPORTANT: Verify CloudPanel site settings:"
echo "  1. Document Root must be: /home/sqldetective/htdocs/sqldetective.dipteshdey.in/public"
echo "  2. SSL must be active (Let's Encrypt)"
echo "  3. PHP version: 8.1+"
echo ""
echo "Test URLs:"
echo "  https://sqldetective.dipteshdey.in/       (homepage)"
echo "  https://sqldetective.dipteshdey.in/diag   (diagnostic)"
echo "  https://sqldetective.dipteshdey.in/health (health check)"
echo "  https://sqldetective.dipteshdey.in/auth/login"
echo ""
echo "Login: admin@sqldetective.local / SecurePass123!"
