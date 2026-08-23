<?php

namespace Database\Migrations;

use PDO;

class CreateDatabaseTablesTable
{
    public function __construct(private PDO $db) {}

    public function up(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS database_tables (
                id INT AUTO_INCREMENT PRIMARY KEY,
                case_database_id INT NOT NULL,
                table_name VARCHAR(100) NOT NULL,
                description TEXT,
                display_order INT UNSIGNED NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (case_database_id) REFERENCES case_databases(id) ON DELETE CASCADE,
                INDEX idx_case_database_id (case_database_id),
                UNIQUE KEY unique_database_table (case_database_id, table_name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->db->exec("DROP TABLE IF EXISTS database_tables");
    }
}