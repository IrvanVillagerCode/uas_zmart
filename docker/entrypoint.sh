#!/bin/bash
set -e

# 1. Environment file setup
if [ ! -f .env ]; then
    echo "Creating .env file from .env.example..."
    cp .env.example .env
fi

# 2. Composer install check (if vendor directory is missing)
if [ ! -d vendor ]; then
    echo "Vendor directory not found. Running composer install..."
    composer install --no-interaction --optimize-autoloader --no-dev
else
    echo "Vendor directory exists. Skipping composer install."
fi

# 3. Laravel application key setup
if ! grep -q "APP_KEY=base" .env || [ -z "$(grep APP_KEY= .env | cut -d '=' -f2)" ]; then
    echo "Generating Laravel application key..."
    php artisan key:generate --force
fi

# 4. Wait for database connection
echo "Checking database connection..."
php -r "
\$dbHost = getenv('DB_HOST') ?: 'db';
\$dbName = getenv('DB_DATABASE') ?: 'zmart';
\$dbUser = getenv('DB_USERNAME') ?: 'root';
\$dbPass = getenv('DB_PASSWORD') ?: '';
\$dbPort = getenv('DB_PORT') ?: '3306';

echo \"Target Database details: host=\$dbHost, port=\$dbPort, dbname=\$dbName, user=\$dbUser\n\";

for (\$i = 0; \$i < 30; \$i++) {
    try {
        \$pdo = new PDO(\"mysql:host=\$dbHost;port=\$dbPort;dbname=\$dbName\", \$dbUser, \$dbPass);
        \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo \"Database connection established successfully.\n\";
        exit(0);
    } catch (Exception \$e) {
        echo \"Database connection failed (Attempt \$i/30): \" . \$e->getMessage() . \"\n\";
        sleep(2);
    }
}
exit(1);
"

# 4.5. Cleanup incompatible migrations table if it exists (e.g. from legacy CodeIgniter database)
echo "Checking migrations table compatibility..."
php -r "
\$dbHost = getenv('DB_HOST') ?: 'db';
\$dbName = getenv('DB_DATABASE') ?: 'zmart';
\$dbUser = getenv('DB_USERNAME') ?: 'root';
\$dbPass = getenv('DB_PASSWORD') ?: '';
\$dbPort = getenv('DB_PORT') ?: '3306';
try {
    \$pdo = new PDO(\"mysql:host=\$dbHost;port=\$dbPort;dbname=\$dbName\", \$dbUser, \$dbPass);
    \$stmt = \$pdo->query(\"SHOW TABLES LIKE 'migrations'\");
    if (\$stmt->rowCount() > 0) {
        \$colStmt = \$pdo->query(\"SHOW COLUMNS FROM migrations LIKE 'migration'\");
        if (\$colStmt->rowCount() == 0) {
            echo \"Incompatible legacy migrations table found. Dropping it...\n\";
            \$pdo->exec(\"DROP TABLE migrations\");
        }
    }
} catch (Exception \$e) {
    echo \"Migrations check failed: \" . \$e->getMessage() . \"\n\";
}
"

# 5. Clear application caches to pick up container environment variables
echo "Clearing configuration cache..."
php artisan config:clear

# 6. Database Schema Initialization (Wipe and Import zmart.sql if products table doesn't exist)
echo "Checking if database schema needs initialization..."
DB_INITIALIZED=$(php -r "
\$dbHost = getenv('DB_HOST') ?: 'db';
\$dbName = getenv('DB_DATABASE') ?: 'zmart';
\$dbUser = getenv('DB_USERNAME') ?: 'root';
\$dbPass = getenv('DB_PASSWORD') ?: '';
\$dbPort = getenv('DB_PORT') ?: '3306';
try {
    \$pdo = new PDO(\"mysql:host=\$dbHost;port=\$dbPort;dbname=\$dbName\", \$dbUser, \$dbPass);
    \$stmt = \$pdo->query(\"SHOW TABLES LIKE 'products'\");
    if (\$stmt->rowCount() > 0) {
        echo 'YES';
    } else {
        echo 'NO';
    }
} catch (Exception \$e) {
    echo 'NO';
}
")

DB_HOST=${DB_HOST:-db}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE:-zmart}
DB_USERNAME=${DB_USERNAME:-root}
DB_PASSWORD=${DB_PASSWORD:-}

if [ "$DB_INITIALIZED" = "NO" ]; then
    echo "Database schema is empty or incomplete. Wiping any existing tables..."
    php artisan db:wipe --force
    
    echo "Importing clean schema from zmart.sql..."
    MYSQL_PWD="$DB_PASSWORD" mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" "$DB_DATABASE" < zmart.sql
    echo "Database initialized successfully from zmart.sql."

    echo "Running migrations for missing system tables (sessions, cache, jobs)..."
    php artisan migrate --force
else
    echo "Database schema already initialized."
    echo "Ensuring migrations are up to date..."
    php artisan migrate --force
fi

# 7. Database Seeding (to ensure latest products and users from DatabaseSeeder are present)
echo "Seeding database with latest records..."
php artisan db:seed --force

# 7.5. Clear application cache now that database is fully initialized
echo "Clearing application cache..."
php artisan cache:clear

# 8. Start the main container command (defaults to php-fpm)
echo "Starting PHP-FPM..."
exec "$@"
