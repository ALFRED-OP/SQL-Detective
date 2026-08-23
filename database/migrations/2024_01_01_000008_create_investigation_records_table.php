<?php

namespace Database\Migrations;

use PDO;

class CreateInvestigationRecordsTable
{
    public function __construct(private PDO $db) {}

    public function up(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS investigation_records (
                id INT AUTO_INCREMENT PRIMARY KEY,
                table_id INT NOT NULL,
                record_data JSON NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (table_id) REFERENCES database_tables(id) ON DELETE CASCADE,
                INDEX idx_table_id (table_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->db->exec("DROP TABLE IF EXISTS investigation_records");
    }
}