<?php
define('PROJECT_ROOT', __DIR__);
require_once PROJECT_ROOT . '/includes/init.php';

$command = $argv[1] ?? '';

switch ($command) {
    case 'migrate':
        run_migrations();
        break;
    case 'migrate:fresh':
        drop_all_tables();
        run_migrations();
        break;
    case 'seed':
        require PROJECT_ROOT . '/database/seeds/DatabaseSeeder.php';
        $seeder = new \Database\Seeds\DatabaseSeeder();
        $seeder->run();
        break;
    case 'setup':
        run_migrations();
        require PROJECT_ROOT . '/database/seeds/DatabaseSeeder.php';
        $seeder = new \Database\Seeds\DatabaseSeeder();
        $seeder->run();
        echo "Setup complete!\n";
        break;
    default:
        echo "Usage: php setup.php [migrate|seed|setup|migrate:fresh]\n";
        echo "  migrate       - Run pending migrations\n";
        echo "  seed          - Seed the database\n";
        echo "  setup         - Run migrations + seed\n";
        echo "  migrate:fresh - Drop all tables and re-run migrations\n";
}

function run_migrations(): void {
    $db = db();
    $db->exec("CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL,
        batch INT NOT NULL DEFAULT 1,
        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $executed = [];
    $stmt = $db->query("SELECT migration FROM migrations ORDER BY id");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $executed[] = $row['migration'];
    }

    $migrationDir = PROJECT_ROOT . '/database/migrations';
    $files = glob($migrationDir . '/*.php');
    sort($files);

    $batch = count(array_unique(array_column($executed, 'batch'))) + 1;
    $count = 0;

    foreach ($files as $file) {
        $name = basename($file, '.php');
        if (in_array($name, $executed)) continue;

        require_once $file;
        $className = get_class_name_from_file($file);
        if (!$className) continue;

        $migration = new $className($db);
        echo "Migrating: $name\n";
        $migration->up();

        $db->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)")->execute([$name, $batch]);
        $count++;
    }

    echo "$count migration(s) completed.\n";
}

function drop_all_tables(): void {
    $db = db();
    $tables = [];
    $stmt = $db->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    foreach ($tables as $table) {
        $db->exec("DROP TABLE `$table`");
        echo "Dropped: $table\n";
    }
}

function get_class_name_from_file(string $file): ?string {
    $content = file_get_contents($file);
    $namespace = '';
    $className = '';
    if (preg_match('/namespace\s+([\w\\\\]+);/', $content, $m)) {
        $namespace = $m[1];
    }
    if (preg_match('/class\s+(\w+)/', $content, $m)) {
        $className = $m[1];
    }
    if (!$className) return null;
    return $namespace ? $namespace . '\\' . $className : $className;
}
