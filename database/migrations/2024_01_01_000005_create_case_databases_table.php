<?php

namespace Database\Migrations;

use PDO;

class CreateCaseDatabasesTable
{
    public function __construct(private PDO $db) {}

    public function up(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS case_databases (
                id INT AUTO_INCREMENT PRIMARY KEY,
                case_id INT NOT NULL,
                database_name VARCHAR(100) NOT NULL,
                database_description TEXT,
                schema_description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
                INDEX idx_case_id (case_id),
                UNIQUE KEY unique_case_database (case_id, database_name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->db->exec("DROP TABLE IF EXISTS case_databases");
    }
}