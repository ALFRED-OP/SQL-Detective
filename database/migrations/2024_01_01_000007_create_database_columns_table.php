<?php

namespace Database\Migrations;

use PDO;

class CreateDatabaseColumnsTable
{
    public function __construct(private PDO $db) {}

    public function up(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS database_columns (
                id INT AUTO_INCREMENT PRIMARY KEY,
                table_id INT NOT NULL,
                column_name VARCHAR(100) NOT NULL,
                data_type VARCHAR(50) NOT NULL,
                is_primary_key BOOLEAN NOT NULL DEFAULT FALSE,
                is_nullable BOOLEAN NOT NULL DEFAULT TRUE,
                description TEXT,
                display_order INT UNSIGNED NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (table_id) REFERENCES database_tables(id) ON DELETE CASCADE,
                INDEX idx_table_id (table_id),
                UNIQUE KEY unique_table_column (table_id, column_name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->db->exec("DROP TABLE IF EXISTS database_columns");
    }
}