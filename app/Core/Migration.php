<?php

namespace App\Core;

use PDO;

class Migration
{
    private PDO $db;
    private string $migrationsPath;
    private string $migrationsTable = 'migrations';

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->migrationsPath = base_path('database/migrations/');
        $this->ensureMigrationsTable();
    }

    private function ensureMigrationsTable(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS {$this->migrationsTable} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INT NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function run(): int
    {
        $executed = $this->getExecutedMigrations();
        $files = glob($this->migrationsPath . '*.php');
        sort($files);

        $batch = $this->getNextBatchNumber();
        $count = 0;

        foreach ($files as $file) {
            $migration = basename($file, '.php');
            if (in_array($migration, $executed)) {
                continue;
            }

            echo "Running migration: $migration\n";
            require_once $file;
            $className = 'Database\\Migrations\\' . $migration;

            if (!class_exists($className)) {
                throw new \RuntimeException("Migration class $className not found in $file");
            }

            $instance = new $className($this->db);
            $instance->up();

            $this->recordMigration($migration, $batch);
            $count++;
        }

        return $count;
    }

    public function rollback(int $steps = 1): int
    {
        $executed = $this->getExecutedMigrationsWithBatch();
        $batches = array_unique(array_column($executed, 'batch'));
        rsort($batches);

        $count = 0;
        foreach ($batches as $batch) {
            if ($count >= $steps) break;

            $batchMigrations = array_filter($executed, fn($m) => $m['batch'] === $batch);
            $batchMigrations = array_reverse($batchMigrations);

            foreach ($batchMigrations as $migration) {
                echo "Rolling back: {$migration['migration']}\n";
                $className = 'Database\\Migrations\\' . $migration['migration'];
                if (class_exists($className)) {
                    $instance = new $className($this->db);
                    $instance->down();
                }
                $this->removeMigrationRecord($migration['migration']);
                $count++;
            }
        }

        return $count;
    }

    public function refresh(): void
    {
        $this->rollback(PHP_INT_MAX);
        $this->run();
    }

    public function status(): void
    {
        $executed = $this->getExecutedMigrations();
        $files = glob($this->migrationsPath . '*.php');
        sort($files);

        echo "Migration Status:\n";
        echo str_repeat('-', 80) . "\n";
        printf("%-50s %s\n", 'Migration', 'Status');
        echo str_repeat('-', 80) . "\n";

        foreach ($files as $file) {
            $migration = basename($file, '.php');
            $status = in_array($migration, $executed) ? 'Ran' : 'Pending';
            printf("%-50s %s\n", $migration, $status);
        }
    }

    private function getExecutedMigrations(): array
    {
        $stmt = $this->db->query("SELECT migration FROM {$this->migrationsTable} ORDER BY batch, id");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function getExecutedMigrationsWithBatch(): array
    {
        $stmt = $this->db->query("SELECT migration, batch FROM {$this->migrationsTable} ORDER BY batch, id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getNextBatchNumber(): int
    {
        $stmt = $this->db->query("SELECT COALESCE(MAX(batch), 0) + 1 FROM {$this->migrationsTable}");
        return (int)$stmt->fetchColumn();
    }

    private function recordMigration(string $migration, int $batch): void
    {
        $stmt = $this->db->prepare("INSERT INTO {$this->migrationsTable} (migration, batch) VALUES (?, ?)");
        $stmt->execute([$migration, $batch]);
    }

    private function removeMigrationRecord(string $migration): void
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->migrationsTable} WHERE migration = ?");
        $stmt->execute([$migration]);
    }
}