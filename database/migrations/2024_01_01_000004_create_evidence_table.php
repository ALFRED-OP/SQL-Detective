<?php

namespace Database\Migrations;

use PDO;

class CreateEvidenceTable
{
    public function __construct(private PDO $db) {}

    public function up(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS evidence (
                id INT AUTO_INCREMENT PRIMARY KEY,
                case_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                evidence_type ENUM('document', 'log', 'record', 'image', 'audio', 'video', 'other') NOT NULL DEFAULT 'document',
                evidence_data JSON,
                importance ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
                INDEX idx_case_id (case_id),
                INDEX idx_type (evidence_type),
                INDEX idx_importance (importance)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->db->exec("DROP TABLE IF EXISTS evidence");
    }
}