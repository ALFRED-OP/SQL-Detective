-- Supplemental seed data for digitalforensics investigation database
-- Supports cases 005, 008, 011, 012, 014, 016, 018, 021, 023, 025, 028, 029

USE `digitalforensics`;

-- ============================================================
-- CASE-005: Ghost in the System
-- More service account activities (odd hours + normal hours contrast)
-- ============================================================
INSERT INTO `activities` (`user_id`, `device_id`, `action_type`, `description`, `timestamp`, `ip_address`, `success`, `details`) VALUES
-- Normal hours activities (contrast)
(9, 1, 'config_change', 'Backup schedule configuration', '2026-08-10 10:00:00', '10.0.1.10', TRUE, '{"schedule": "daily_3am", "retention": "90_days"}'),
(10, 2, 'process_start', 'Health check - all systems normal', '2026-08-10 12:00:00', '10.0.1.11', TRUE, '{"uptime": "99.99%", "cpu_usage": "35%"}'),
(9, 1, 'database_query', 'Backup integrity verification', '2026-08-10 10:15:00', '10.0.1.10', TRUE, '{"tables_checked": 45, "integrity": "ok"}'),
(10, 2, 'process_start', 'Daily metrics collection', '2026-08-10 09:00:00', '10.0.1.11', TRUE, '{"metrics_collected": 150}'),
-- Suspicious odd-hour activities
(9, 1, 'file_access', 'Accessed /etc/shadow backup', '2026-08-05 02:30:00', '10.0.1.10', TRUE, '{"path": "/etc/shadow.bak", "action": "read"}'),
(9, 1, 'database_query', 'SELECT on user_credentials table', '2026-08-05 02:35:00', '10.0.1.10', TRUE, '{"query": "SELECT * FROM user_credentials", "rows": 120}'),
(9, 1, 'email_send', 'External email with database dump', '2026-08-05 02:40:00', '10.0.1.10', TRUE, '{"to": "external", "subject": "System Report", "attachment_size": "5MB"}'),
(10, 2, 'file_access', 'Read /opt/secrets/api_keys.json', '2026-08-08 23:45:00', '10.0.1.11', TRUE, '{"path": "/opt/secrets/api_keys.json", "action": "read"}'),
(10, 2, 'config_change', 'Modified firewall rules', '2026-08-08 23:50:00', '10.0.1.11', TRUE, '{"rule_added": "allow_203.0.113.0/24", "port": "all"}'),
(10, 2, 'process_start', 'Started reverse shell listener', '2026-08-09 01:15:00', '10.0.1.11', FALSE, '{"process": "nc", "args": "-lvp 4444", "result": "blocked_by_policy"}'),
(9, 1, 'database_query', 'Export of full HR database', '2026-08-11 03:00:00', '10.0.1.10', TRUE, '{"query": "SELECT * FROM employees", "rows": 500, "exported": true}'),
(9, 1, 'file_access', 'Copied database dump to temp', '2026-08-11 03:10:00', '10.0.1.10', TRUE, '{"source": "/tmp/hr_dump.sql", "size": "25MB"}'),
(10, 2, 'privilege_escalation', 'Attempted sudo escalation', '2026-08-14 00:30:00', '10.0.1.11', FALSE, '{"command": "sudo su -", "result": "denied"}'),
(9, 1, 'config_change', 'Disabled audit logging', '2026-08-14 02:00:00', '10.0.1.10', TRUE, '{"setting": "audit_enabled", "old": "true", "new": "false"}'),
-- More normal activities (contrast noise)
(9, 1, 'process_start', 'Scheduled backup job', '2026-08-12 03:00:00', '10.0.1.10', TRUE, '{"job": "full_backup", "size_gb": 15}'),
(10, 2, 'process_start', 'Log rotation', '2026-08-12 04:00:00', '10.0.1.11', TRUE, '{"rotated_files": 120}'),
(9, 1, 'config_change', 'Certificate renewal', '2026-08-13 11:00:00', '10.0.1.10', TRUE, '{"cert": "*.corp.com", "expires": "2027-08-13"}'),
(10, 2, 'process_start', 'Security scan completed', '2026-08-13 14:00:00', '10.0.1.11', TRUE, '{"vulnerabilities_found": 2, "severity": "low"}');

-- ============================================================
-- CASE-008: Phishing Campaign
-- Phishing emails with suspicious subjects and external senders
-- ============================================================
INSERT INTO `emails` (`message_id`, `sender_id`, `recipient_ids`, `subject`, `body`, `sent_at`, `folder`, `importance`, `has_attachments`, `size_bytes`) VALUES
-- External phishing emails (sender_id = NULL means external/inbound)
(NULL, NULL, '[1, 2, 3]', 'URGENT: Verify Your Password Immediately', 'Dear Employee, Your password will expire in 24 hours. Click the link below to reset your password: http://corp-verify.security-reset.xyz/verify', '2026-08-10 08:30:00', 'inbox', 'urgent', TRUE, 1024),
(NULL, NULL, '[4, 5, 6, 7]', 'IT Security Alert - Account Compromised', 'We have detected unauthorized access to your account. Please verify your identity by providing your credentials at: http://10.0.2.103/security/login', '2026-08-10 09:15:00', 'inbox', 'urgent', FALSE, 2048),
(NULL, NULL, '[8, 3, 1]', 'Monthly Payroll Statement - Action Required', 'Your salary slip for August 2026 is ready. Download from: http://payroll-corp.insecure-site.com/slip.php?id=42', '2026-08-11 10:00:00', 'inbox', 'high', TRUE, 51200),
-- Phishing emails sent by compromised account
(6, 6, '[3, 4, 5]', 'FW: Invoice Payment Urgently Needed', 'Please process the attached invoice for immediate payment. Account details: 9876543210, IFSC: UTIB0000001', '2026-08-12 10:30:00', 'sent', 'urgent', TRUE, 204800),
(6, 6, '[1]', 'Re: Board Meeting Agenda - Confidential', 'Attached are the confidential board documents. Please review before Friday.', '2026-08-12 14:00:00', 'sent', 'urgent', TRUE, 5242880),
-- More external phishing attempts
(NULL, NULL, '[2, 6, 8]', 'Claim Your Festival Bonus Now!', 'Congratulations! You have been selected for a special festival bonus. Verify your bank details at: http://bonus-festival.linkedaccount.com/claim', '2026-08-13 11:00:00', 'inbox', 'normal', FALSE, 4096),
(NULL, NULL, '[9, 10]', 'System Upgrade Required - Login Required', 'Our systems have been upgraded. All users must re-authenticate at: http://portal-corp-update.xyz/auth', '2026-08-14 09:00:00', 'inbox', 'high', FALSE, 1024),
-- Legitimate emails (noise)
(1, 1, '[3, 4, 5]', 'Q3 Budget Review Meeting', 'Hi team, please review the Q3 budget documents before our meeting on Friday.', '2026-08-15 14:00:00', 'sent', 'normal', TRUE, 2048000),
(2, 5, '[3]', 'Employee Survey Results', 'Hi Amit, here are the survey results for your department.', '2026-08-13 16:00:00', 'sent', 'normal', FALSE, 8192),
(4, 2, '[5, 6, 7]', 'Security Policy Update', 'Please review the updated security policy document.', '2026-08-14 11:00:00', 'sent', 'high', FALSE, 4096),
(8, 8, '[4]', 'Code Review Request', 'Can you review my pull request when you get a chance?', '2026-08-15 10:00:00', 'sent', 'normal', FALSE, 2048);

-- ============================================================
-- CASE-011: Insider Trading Ring
-- File access activities for confidential files before announcements
-- ============================================================
INSERT INTO `activities` (`user_id`, `device_id`, `action_type`, `description`, `timestamp`, `ip_address`, `success`, `details`) VALUES
-- Confidential file accesses by various employees before announcement dates
-- Announcement 1: Q3 results (Aug 5) - employee accessed strategy doc July 30
(3, 6, 'file_access', 'Accessed Q3 strategy roadmap', '2026-07-30 11:00:00', '10.0.2.103', TRUE, '{"path": "/shared/exec/Q3_strategy_roadmap.pptx", "action": "read"}'),
(3, 6, 'file_access', 'Accessed bid proposal document', '2026-07-30 14:30:00', '10.0.2.103', TRUE, '{"path": "/shared/sales/confidential_bid_clientX.docx", "action": "read"}'),
-- Announcement 2: Merger news (Aug 8) - employee accessed docs Aug 5-6
(5, NULL, 'file_access', 'Accessed M&A confidential brief', '2026-08-05 16:00:00', '192.168.1.50', TRUE, '{"path": "/shared/legal/merger_confidential.pdf", "action": "read"}'),
(7, NULL, 'file_access', 'Accessed merger financial projections', '2026-08-06 10:30:00', '192.168.1.50', TRUE, '{"path": "/shared/finance/merger_projections.xlsx", "action": "read"}'),
(3, 6, 'file_access', 'Accessed board meeting minutes', '2026-08-05 09:00:00', '10.0.2.103', TRUE, '{"path": "/shared/exec/board_minutes_aug.docx", "action": "read"}'),
-- Announcement 3: Product launch (Aug 14) - employee accessed docs Aug 10-12
(4, 7, 'file_access', 'Accessed product launch strategy', '2026-08-10 15:00:00', '10.0.3.50', TRUE, '{"path": "/shared/product/launch_strategy_confidential.pdf", "action": "read"}'),
(8, 7, 'file_access', 'Accessed patent filing documents', '2026-08-11 11:30:00', '10.0.3.50', TRUE, '{"path": "/shared/legal/patent_filing_2026.pdf", "action": "read"}'),
(3, 6, 'file_access', 'Accessed marketing bid document', '2026-08-12 09:00:00', '10.0.2.103', TRUE, '{"path": "/shared/marketing/confidential_bid_2026.docx", "action": "read"}'),
(5, NULL, 'file_access', 'Accessed HR restructuring plan', '2026-08-10 14:00:00', '192.168.1.50', TRUE, '{"path": "/shared/hr/restructuring_plan_q3.pdf", "action": "read"}'),
-- Normal file accesses (noise)
(1, 4, 'file_access', 'Accessed quarterly report', '2026-08-01 09:00:00', '10.0.2.101', TRUE, '{"path": "/shared/finance/Q2_report.xlsx", "action": "read"}'),
(2, 5, 'file_access', 'Accessed IT policy document', '2026-08-02 10:00:00', '10.0.2.102', TRUE, '{"path": "/shared/IT/policy_2026.pdf", "action": "read"}'),
(4, 7, 'file_access', 'Accessed code repository', '2026-08-03 11:00:00', '10.0.3.50', TRUE, '{"path": "/repos/main/README.md", "action": "read"}');

-- ============================================================
-- CASE-012: Shadow Network
-- More suspicious network connections from additional source IPs
-- ============================================================
INSERT INTO `network_logs` (`source_ip`, `destination_ip`, `source_port`, `destination_port`, `protocol`, `timestamp`, `bytes_sent`, `bytes_received`, `duration`, `status`, `device_id`, `user_id`) VALUES
-- Additional suspicious sources
('45.33.98.100', '10.0.1.10', 55001, 443, 'https', '2026-08-13 01:30:00', 2048, 4096, 15, 'suspicious', NULL, NULL),
('45.33.98.100', '10.0.1.20', 55002, 22, 'ssh', '2026-08-13 01:35:00', 4096, 8192, 30, 'suspicious', NULL, NULL),
('45.33.98.100', '10.0.2.103', 55003, 3306, 'tcp', '2026-08-13 01:40:00', 8192, 16384, 45, 'suspicious', NULL, NULL),
('198.51.100.200', '10.0.1.11', 56001, 443, 'https', '2026-08-14 02:00:00', 1024, 2048, 10, 'suspicious', NULL, NULL),
('198.51.100.200', '10.0.0.1', 56002, 80, 'http', '2026-08-14 02:05:00', 512, 1024, 5, 'suspicious', NULL, NULL),
('103.21.58.200', '10.0.1.10', 57001, 443, 'https', '2026-08-15 03:15:00', 2048, 4096, 20, 'suspicious', NULL, NULL),
('103.21.58.200', '10.0.1.20', 57002, 587, 'smtp', '2026-08-15 03:20:00', 10240, 2048, 15, 'suspicious', NULL, NULL),
('10.0.2.103', '203.0.113.50', 49901, 4444, 'tcp', '2026-08-15 03:25:00', 5242880, 1024, 90, 'suspicious', 6, 3),
('10.0.2.103', '198.51.100.75', 49902, 4444, 'tcp', '2026-08-16 02:10:00', 3145728, 512, 60, 'suspicious', 6, 3),
-- Normal traffic (noise)
('10.0.2.101', '10.0.1.10', 49845, 443, 'https', '2026-08-15 09:15:00', 1024, 2048, 2, 'allowed', 4, 1),
('10.0.2.102', '10.0.1.10', 49846, 443, 'https', '2026-08-15 09:20:00', 1024, 2048, 2, 'allowed', 5, 2),
('10.0.3.50', '10.0.0.1', 51235, 1194, 'tcp', '2026-08-15 08:35:00', 512, 512, 1, 'allowed', 7, 4),
('10.0.2.103', '8.8.8.8', 49847, 53, 'dns', '2026-08-15 09:25:00', 128, 256, 1, 'allowed', 6, 3),
('10.0.1.20', '10.0.1.10', 60001, 443, 'https', '2026-08-15 10:00:00', 512, 1024, 1, 'allowed', 3, NULL),
-- Blocked legitimate attempts
('10.0.3.50', '45.33.98.100', 51240, 80, 'http', '2026-08-15 14:30:00', 256, 0, 5, 'blocked', 7, 4);

-- ============================================================
-- CASE-014: The Data Hoarder
-- Many file_access activities grouped by employee
-- ============================================================
INSERT INTO `activities` (`user_id`, `device_id`, `action_type`, `description`, `timestamp`, `ip_address`, `success`, `details`) VALUES
-- Employee 3 (Amit Patel) - Suspicious high-volume file access (THE HOARDER)
(3, 6, 'file_access', 'Read financial_report_q2.xlsx', '2026-08-10 09:00:00', '10.0.2.103', TRUE, '{"path": "/shared/finance/Q2_report.xlsx", "action": "read", "size_bytes": 2457600}'),
(3, 6, 'file_access', 'Read employee_salaries_2026.xlsx', '2026-08-10 09:15:00', '10.0.2.103', TRUE, '{"path": "/shared/hr/salaries_2026.xlsx", "action": "read", "size_bytes": 1048576}'),
(3, 6, 'file_access', 'Read client_database.csv', '2026-08-10 09:30:00', '10.0.2.103', TRUE, '{"path": "/shared/sales/client_database.csv", "action": "read", "size_bytes": 5242880}'),
(3, 6, 'file_access', 'Read board_presentation_aug.pptx', '2026-08-10 10:00:00', '10.0.2.103', TRUE, '{"path": "/shared/exec/board_presentation_aug.pptx", "action": "read", "size_bytes": 15728640}'),
(3, 6, 'file_access', 'Read pricing_strategy_2026.docx', '2026-08-10 10:30:00', '10.0.2.103', TRUE, '{"path": "/shared/sales/pricing_strategy_2026.docx", "action": "read", "size_bytes": 1048576}'),
(3, 6, 'file_access', 'Read merger_confidential.pdf', '2026-08-10 11:00:00', '10.0.2.103', TRUE, '{"path": "/shared/legal/merger_confidential.pdf", "action": "read", "size_bytes": 3145728}'),
(3, 6, 'file_access', 'Read product_roadmap_q4.xlsx', '2026-08-11 09:00:00', '10.0.2.103', TRUE, '{"path": "/shared/product/roadmap_q4.xlsx", "action": "read", "size_bytes": 2097152}'),
(3, 6, 'file_access', 'Read vendor_contracts_2026.pdf', '2026-08-11 09:30:00', '10.0.2.103', TRUE, '{"path": "/shared/procurement/vendor_contracts.pdf", "action": "read", "size_bytes": 4194304}'),
(3, 6, 'file_access', 'Read budget_allocation_fy27.xlsx', '2026-08-11 10:00:00', '10.0.2.103', TRUE, '{"path": "/shared/finance/budget_fy27.xlsx", "action": "read", "size_bytes": 1572864}'),
(3, 6, 'file_access', 'Read patent_filing_draft.pdf', '2026-08-11 10:30:00', '10.0.2.103', TRUE, '{"path": "/shared/legal/patent_draft.pdf", "action": "read", "size_bytes": 2621440}'),
(3, 6, 'file_access', 'Read reorganization_plan.docx', '2026-08-12 09:00:00', '10.0.2.103', TRUE, '{"path": "/shared/hr/reorg_plan.docx", "action": "read", "size_bytes": 786432}'),
(3, 6, 'file_access', 'Read server_architecture.pdf', '2026-08-12 09:30:00', '10.0.2.103', TRUE, '{"path": "/shared/IT/server_arch.pdf", "action": "read", "size_bytes": 3670016}'),
(3, 6, 'file_access', 'Read ip_portfolio.xlsx', '2026-08-12 10:00:00', '10.0.2.103', TRUE, '{"path": "/shared/legal/ip_portfolio.xlsx", "action": "read", "size_bytes": 1048576}'),
(3, 6, 'file_access', 'Read marketing_campaign_q3.pptx', '2026-08-12 10:30:00', '10.0.2.103', TRUE, '{"path": "/shared/marketing/campaign_q3.pptx", "action": "read", "size_bytes": 8388608}'),
(3, 6, 'file_access', 'Read customer_feedback_analysis.xlsx', '2026-08-13 09:00:00', '10.0.2.103', TRUE, '{"path": "/shared/sales/feedback_analysis.xlsx", "action": "read", "size_bytes": 2097152}'),
(3, 6, 'file_access', 'Read compliance_audit_2026.pdf', '2026-08-13 09:30:00', '10.0.2.103', TRUE, '{"path": "/shared/legal/compliance_audit.pdf", "action": "read", "size_bytes": 1572864}'),
(3, 6, 'file_access', 'Read employee_performance_ratings.xlsx', '2026-08-13 10:00:00', '10.0.2.103', TRUE, '{"path": "/shared/hr/performance_ratings.xlsx", "action": "read", "size_bytes": 524288}'),
(3, 6, 'file_access', 'Read competitive_analysis.docx', '2026-08-13 10:30:00', '10.0.2.103', TRUE, '{"path": "/shared/marketing/competitive_analysis.docx", "action": "read", "size_bytes": 1048576}'),
(3, 6, 'file_access', 'Read api_keys_production.json', '2026-08-14 09:00:00', '10.0.2.103', TRUE, '{"path": "/opt/secrets/api_keys.json", "action": "read", "size_bytes": 32768}'),
(3, 6, 'file_access', 'Read database_credentials.enc', '2026-08-14 09:30:00', '10.0.2.103', TRUE, '{"path": "/opt/secrets/db_creds.enc", "action": "read", "size_bytes": 16384}'),
-- Employee 4 (Sanjay Verma) - Normal file access (contrast)
(4, 7, 'file_access', 'Read codebase_readme.md', '2026-08-10 09:00:00', '10.0.3.50', TRUE, '{"path": "/repos/main/README.md", "action": "read", "size_bytes": 4096}'),
(4, 7, 'file_access', 'Read api_documentation.pdf', '2026-08-11 10:00:00', '10.0.3.50', TRUE, '{"path": "/docs/api_ref.pdf", "action": "read", "size_bytes": 524288}'),
(4, 7, 'file_access', 'Read deployment_guide.md', '2026-08-12 11:00:00', '10.0.3.50', TRUE, '{"path": "/docs/deploy.md", "action": "read", "size_bytes": 8192}'),
-- Employee 5 (Leena Desai) - Normal HR file access
(5, NULL, 'file_access', 'Read onboarding_checklist.pdf', '2026-08-10 14:00:00', '192.168.1.50', TRUE, '{"path": "/shared/hr/onboarding.pdf", "action": "read", "size_bytes": 262144}'),
(5, NULL, 'file_access', 'Read policy_handbook.docx', '2026-08-11 15:00:00', '192.168.1.50', TRUE, '{"path": "/shared/hr/policy.docx", "action": "read", "size_bytes": 1048576}'),
(5, NULL, 'file_access', 'Read training_materials.pdf', '2026-08-12 09:00:00', '192.168.1.50', TRUE, '{"path": "/shared/hr/training.pdf", "action": "read", "size_bytes": 5242880}'),
-- Employee 7 (Nisha Reddy) - Normal marketing access
(7, NULL, 'file_access', 'Read campaign_brief.docx', '2026-08-10 11:00:00', '192.168.1.50', TRUE, '{"path": "/shared/marketing/brief.docx", "action": "read", "size_bytes": 524288}'),
(7, NULL, 'file_access', 'Read brand_guidelines.pdf', '2026-08-11 14:00:00', '192.168.1.50', TRUE, '{"path": "/shared/marketing/brand.pdf", "action": "read", "size_bytes": 3145728}');

-- ============================================================
-- CASE-016: The Speed Demon
-- 10+ database_query activities within a single minute for one user
-- ============================================================
INSERT INTO `activities` (`user_id`, `device_id`, `action_type`, `description`, `timestamp`, `ip_address`, `success`, `details`) VALUES
-- Employee 6 (Manoj Gupta - locked account) running automated queries
(6, 11, 'database_query', 'Automated query batch 1', '2026-08-12 02:30:00', '203.0.113.50', TRUE, '{"query": "SELECT * FROM users WHERE role = '\''admin'\''", "rows": 2}'),
(6, 11, 'database_query', 'Automated query batch 2', '2026-08-12 02:30:03', '203.0.113.50', TRUE, '{"query": "SELECT * FROM users WHERE status = '\''active'\''", "rows": 8}'),
(6, 11, 'database_query', 'Automated query batch 3', '2026-08-12 02:30:06', '203.0.113.50', TRUE, '{"query": "SELECT * FROM employees", "rows": 500}'),
(6, 11, 'database_query', 'Automated query batch 4', '2026-08-12 02:30:09', '203.0.113.50', TRUE, '{"query": "SELECT * FROM salary_data", "rows": 500}'),
(6, 11, 'database_query', 'Automated query batch 5', '2026-08-12 02:30:12', '203.0.113.50', TRUE, '{"query": "SELECT * FROM access_logs WHERE action = '\''admin'\''", "rows": 25}'),
(6, 11, 'database_query', 'Automated query batch 6', '2026-08-12 02:30:15', '203.0.113.50', TRUE, '{"query": "SELECT * FROM transactions WHERE amount > 100000", "rows": 45}'),
(6, 11, 'database_query', 'Automated query batch 7', '2026-08-12 02:30:18', '203.0.113.50', TRUE, '{"query": "SELECT * FROM bank_accounts", "rows": 14}'),
(6, 11, 'database_query', 'Automated query batch 8', '2026-08-12 02:30:21', '203.0.113.50', TRUE, '{"query": "SELECT * FROM login_logs WHERE status = '\''failed'\''", "rows": 120}'),
(6, 11, 'database_query', 'Automated query batch 9', '2026-08-12 02:30:24', '203.0.113.50', TRUE, '{"query": "SELECT * FROM permission_changes", "rows": 5}'),
(6, 11, 'database_query', 'Automated query batch 10', '2026-08-12 02:30:27', '203.0.113.50', TRUE, '{"query": "SELECT * FROM system_events WHERE severity = '\''critical'\''", "rows": 3}'),
(6, 11, 'database_query', 'Automated query batch 11', '2026-08-12 02:30:30', '203.0.113.50', TRUE, '{"query": "SELECT * FROM email_templates", "rows": 15}'),
(6, 11, 'database_query', 'Automated query batch 12', '2026-08-12 02:30:33', '203.0.113.50', TRUE, '{"query": "SELECT * FROM config_settings", "rows": 42}'),
(6, 11, 'database_query', 'Automated query batch 13', '2026-08-12 02:30:36', '203.0.113.50', TRUE, '{"query": "SHOW TABLES", "rows": 25}'),
(6, 11, 'database_query', 'Automated query batch 14', '2026-08-12 02:30:39', '203.0.113.50', TRUE, '{"query": "SHOW DATABASES", "rows": 8}'),
(6, 11, 'database_query', 'Automated query batch 15', '2026-08-12 02:30:42', '203.0.113.50', TRUE, '{"query": "SELECT @@version", "rows": 1}'),
-- Normal query activity (contrast)
(3, 6, 'database_query', 'Monthly financial query', '2026-08-15 10:00:00', '10.0.2.103', TRUE, '{"query": "SELECT SUM(amount) FROM transactions", "rows": 1}'),
(1, 4, 'database_query', 'Admin dashboard query', '2026-08-15 14:00:00', '10.0.2.101', TRUE, '{"query": "SELECT COUNT(*) FROM users WHERE status = '\''active'\''", "rows": 1}'),
(8, 7, 'database_query', 'Engineering metrics query', '2026-08-15 11:00:00', '10.0.3.50', TRUE, '{"query": "SELECT COUNT(*) FROM deployments", "rows": 1}');

-- ============================================================
-- CASE-018: The Certificate Heist
-- Certificate files and access activities
-- ============================================================
INSERT INTO `files` (`file_path`, `file_name`, `file_type`, `file_size`, `owner_id`, `device_id`, `created_at`, `modified_at`, `accessed_at`, `is_deleted`, `hash_md5`, `hash_sha256`, `permissions`, `content_preview`) VALUES
('/etc/ssl/certs/server_cert.pem', 'server_cert.pem', 'certificate', 4096, 2, 1, '2025-01-15 10:00:00', '2026-01-15 10:00:00', '2026-08-10 14:00:00', FALSE, 'cert_hash_123456789012345678901234', 'cert_sha_1234567890123456789012345678901234567890123456789012345678901234', 'rw-r--r--', 'BEGIN CERTIFICATE Corp Server Certificate'),
('/etc/ssl/private/server_key.pem', 'server_key.pem', 'private_key', 32768, 2, 1, '2025-01-15 10:00:00', '2026-01-15 10:00:00', '2026-08-10 14:00:00', FALSE, 'key_hash_1234567890123456789012345', 'key_sha_12345678901234567890123456789012345678901234567890123456789012345', 'rw-------', 'BEGIN RSA PRIVATE KEY Corp Server Key'),
('/etc/ssl/certs/root_ca.crt', 'root_ca.crt', 'certificate', 2048, 2, 1, '2024-06-01 10:00:00', '2025-06-01 10:00:00', '2026-08-10 14:00:00', FALSE, 'ca_hash_12345678901234567890123456', 'ca_sha_123456789012345678901234567890123456789012345678901234567890123456', 'rw-r--r--', 'BEGIN CERTIFICATE Root CA Certificate'),
('/etc/ssl/certs/client_cert.pfx', 'client_cert.pfx', 'pkcs12', 6144, 2, 1, '2025-03-01 10:00:00', '2026-03-01 10:00:00', '2026-08-10 14:00:00', FALSE, 'pfx_hash_1234567890123456789012345', 'pfx_sha_12345678901234567890123456789012345678901234567890123456789012345', 'rw-------', 'PKCS12 Client Certificate Bundle'),
('/opt/certs/api_signing_key.pem', 'api_signing_key.pem', 'private_key', 16384, 2, 1, '2025-06-15 10:00:00', '2026-06-15 10:00:00', '2026-08-10 14:00:00', FALSE, 'api_key_hash_12345678901234567890', 'api_key_sha_1234567890123456789012345678901234567890123456789012345678901234', 'rw-------', 'BEGIN RSA PRIVATE KEY API Signing Key');

-- Certificate file access activities
INSERT INTO `activities` (`user_id`, `device_id`, `action_type`, `description`, `timestamp`, `ip_address`, `success`, `details`) VALUES
-- Employee 6 (Manoj Gupta) accessing certificates
(6, 11, 'file_access', 'Downloaded server certificate', '2026-08-10 14:00:00', '203.0.113.50', TRUE, '{"path": "/etc/ssl/certs/server_cert.pem", "action": "read", "size_bytes": 4096}'),
(6, 11, 'file_access', 'Downloaded server private key', '2026-08-10 14:05:00', '203.0.113.50', TRUE, '{"path": "/etc/ssl/private/server_key.pem", "action": "read", "size_bytes": 32768}'),
(6, 11, 'file_access', 'Downloaded root CA certificate', '2026-08-10 14:10:00', '203.0.113.50', TRUE, '{"path": "/etc/ssl/certs/root_ca.crt", "action": "read", "size_bytes": 2048}'),
(6, 11, 'file_access', 'Downloaded client certificate', '2026-08-10 14:15:00', '203.0.113.50', TRUE, '{"path": "/etc/ssl/certs/client_cert.pfx", "action": "read", "size_bytes": 6144}'),
(6, 11, 'file_access', 'Downloaded API signing key', '2026-08-10 14:20:00', '203.0.113.50', TRUE, '{"path": "/opt/certs/api_signing_key.pem", "action": "read", "size_bytes": 16384}'),
(6, 11, 'email_send', 'Emailed certificate files externally', '2026-08-10 14:30:00', '203.0.113.50', TRUE, '{"subject": "SSL Certificates Backup", "to": "external", "attachments": 5, "total_size": 61440}'),
-- Legitimate certificate access (noise)
(2, 5, 'file_access', 'Certificate renewal check', '2026-08-01 10:00:00', '10.0.2.102', TRUE, '{"path": "/etc/ssl/certs/server_cert.pem", "action": "read", "size_bytes": 4096}'),
(8, 7, 'file_access', 'SSL verification for deployment', '2026-08-05 11:00:00', '10.0.3.50', TRUE, '{"path": "/etc/ssl/certs/root_ca.crt", "action": "read", "size_bytes": 2048}');

-- ============================================================
-- CASE-021: APT Attack Timeline
-- Activities spanning 6 months with suspicious events
-- ============================================================
INSERT INTO `activities` (`user_id`, `device_id`, `action_type`, `description`, `timestamp`, `ip_address`, `success`, `details`) VALUES
-- February 2026 - Initial reconnaissance
(NULL, 11, 'login', 'First appearance of unknown device', '2026-02-15 03:00:00', '203.0.113.50', TRUE, '{"method": "password_spray", "accounts_targeted": 5}'),
(NULL, 11, 'database_query', 'Reconnaissance query on user table', '2026-02-15 03:05:00', '203.0.113.50', TRUE, '{"query": "SELECT username, email FROM users", "rows": 10}'),
-- March 2026 - Credential harvesting
(6, 11, 'login', 'Compromised account first login', '2026-03-20 02:30:00', '203.0.113.50', TRUE, '{"method": "stolen_credentials", "mfa_bypassed": false}'),
(6, 11, 'file_access', 'Accessed password policy document', '2026-03-20 02:35:00', '203.0.113.50', TRUE, '{"path": "/shared/IT/password_policy.pdf", "action": "read"}'),
-- April 2026 - Lateral movement
(NULL, 11, 'privilege_escalation', 'Attempted privilege escalation', '2026-04-10 01:45:00', '203.0.113.50', FALSE, '{"method": "exploit_cve_2026_1234", "target": "system_admin"}'),
(6, 11, 'database_query', 'Modified own user role', '2026-04-10 01:50:00', '203.0.113.50', TRUE, '{"query": "UPDATE users SET role = '\''admin'\'' WHERE id = 6", "affected_rows": 1}'),
-- May 2026 - Data staging
(6, 11, 'file_access', 'Created staging directory', '2026-05-15 02:00:00', '203.0.113.50', TRUE, '{"path": "/tmp/.cache_backup/", "action": "create_dir"}'),
(6, 11, 'database_query', 'Exported user credentials', '2026-05-15 02:10:00', '203.0.113.50', TRUE, '{"query": "SELECT * INTO OUTFILE", "rows": 500, "exported": true}'),
(6, 11, 'database_query', 'Exported financial data', '2026-05-15 02:20:00', '203.0.113.50', TRUE, '{"query": "SELECT * FROM transactions INTO OUTFILE", "rows": 5000, "exported": true}'),
-- June 2026 - Data collection
(6, 11, 'file_access', 'Collected HR database dump', '2026-06-10 01:30:00', '203.0.113.50', TRUE, '{"path": "/tmp/.cache_backup/hr_dump.sql", "size_bytes": 52428800}'),
(6, 11, 'file_access', 'Collected finance database dump', '2026-06-10 01:45:00', '203.0.113.50', TRUE, '{"path": "/tmp/.cache_backup/finance_dump.sql", "size_bytes": 26214400}'),
-- July 2026 - Exfiltration begins
(6, 11, 'email_send', 'Exfiltrated HR data via email', '2026-07-05 02:00:00', '203.0.113.50', TRUE, '{"to": "external", "subject": "Backup Report", "attachment_size": "10MB"}'),
(6, 11, 'email_send', 'Exfiltrated finance data via email', '2026-07-20 03:00:00', '203.0.113.50', TRUE, '{"to": "external", "subject": "Monthly Summary", "attachment_size": "8MB"}'),
-- August 2026 - Final exfiltration and cleanup
(6, 11, 'email_send', 'Final data exfiltration', '2026-08-12 03:00:00', '203.0.113.50', TRUE, '{"to": "external", "subject": "Project Files", "attachment_size": "15MB"}'),
(6, 11, 'file_access', 'Attempted log cleanup', '2026-08-12 03:10:00', '203.0.113.50', TRUE, '{"path": "/tmp/.cache_backup/", "action": "delete"}'),
(6, 11, 'database_query', 'Attempted to clear audit logs', '2026-08-12 03:15:00', '203.0.113.50', FALSE, '{"query": "DELETE FROM audit_logs", "result": "permission_denied"}');

-- ============================================================
-- CASE-023: The Zero Day
-- More critical/high logs + correlated network/file modifications
-- ============================================================
INSERT INTO `logs` (`log_type`, `level`, `source`, `message`, `user_id`, `device_id`, `ip_address`, `timestamp`, `details`) VALUES
-- Critical events on Aug 12
('security', 'critical', 'EDR', 'Exploit detected: buffer overflow in web server', NULL, 11, '203.0.113.50', '2026-08-12 02:10:00', '{"cve": "CVE-2026-1234", "service": "nginx", "severity": "critical"}'),
('security', 'critical', 'EDR', 'Shellcode execution detected', NULL, 11, '203.0.113.50', '2026-08-12 02:11:00', '{"process": "nginx", "injected": true}'),
('security', 'error', 'HIDS', 'Suspicious process spawned from web server', NULL, 11, '203.0.113.50', '2026-08-12 02:12:00', '{"parent": "nginx", "child": "bash", "pid": 45678}'),
-- Network connections on same dates
-- (supplemental_network_logs will be added separately)
('security', 'high', 'Firewall', 'Reverse shell connection attempt', NULL, 11, '203.0.113.50', '2026-08-12 02:15:00', '{"dest_ip": "203.0.113.50", "port": 4444}'),
('security', 'high', 'DLP', 'Sensitive file staging detected', NULL, 11, '203.0.113.50', '2026-08-12 02:20:00', '{"directory": "/tmp/.cache", "files": 5, "total_size": "80MB"}'),
-- Critical events on Aug 14
('security', 'critical', 'EDR', 'Rootkit signature detected', NULL, 11, '203.0.113.50', '2026-08-14 01:30:00', '{"type": "rootkit", "location": "/usr/lib/modules/"}'),
('security', 'high', 'HIDS', 'System binary modification detected', NULL, 11, '203.0.113.50', '2026-08-14 01:35:00', '{"binary": "/usr/bin/ls", "hash_changed": true}'),
('security', 'high', 'Audit', 'SSH key added to authorized_keys', 6, 11, '203.0.113.50', '2026-08-14 01:40:00', '{"user": "www-data", "key_type": "RSA"}'),
-- Normal events (noise)
('application', 'info', 'WebServer', 'Request processed successfully', NULL, 3, '10.0.2.101', '2026-08-12 10:00:00', '{"method": "GET", "path": "/api/health", "status": 200}'),
('system', 'info', 'Backup', 'Nightly backup completed', 9, 1, '10.0.1.10', '2026-08-14 03:00:00', '{"size_gb": 15}');

-- ============================================================
-- CASE-025: Silent Exfiltration
-- Export activities spanning May-August 2026 (drip-feed pattern)
-- ============================================================
INSERT INTO `activities` (`user_id`, `device_id`, `action_type`, `description`, `timestamp`, `ip_address`, `success`, `details`) VALUES
-- May 2026 - Small email exports
(6, NULL, 'email_send', 'Monthly report to external contact', '2026-05-05 14:00:00', '192.168.1.50', TRUE, '{"to": "external", "subject": "Monthly Update", "attachment_size": 524288}'),
(6, NULL, 'email_send', 'Client data to personal email', '2026-05-18 16:30:00', '192.168.1.50', TRUE, '{"to": "personal_external", "subject": "Client List", "attachment_size": 262144}'),
-- June 2026 - Small file downloads
(6, NULL, 'file_access', 'Downloaded customer database excerpt', '2026-06-08 10:00:00', '192.168.1.50', TRUE, '{"path": "/shared/sales/customers.csv", "action": "read", "size_bytes": 1048576}'),
(6, NULL, 'email_send', 'Customer list via email', '2026-06-08 14:00:00', '192.168.1.50', TRUE, '{"to": "external", "subject": "Customer Review", "attachment_size": 1048576}'),
(6, NULL, 'file_access', 'Downloaded pricing sheet', '2026-06-22 11:00:00', '192.168.1.50', TRUE, '{"path": "/shared/sales/pricing_2026.xlsx", "action": "read", "size_bytes": 524288}'),
(6, NULL, 'email_send', 'Pricing data externally', '2026-06-22 15:00:00', '192.168.1.50', TRUE, '{"to": "external", "subject": "Pricing Analysis", "attachment_size": 524288}'),
-- July 2026 - Network transfers
(6, 6, 'file_access', 'Staged financial projections', '2026-07-10 09:00:00', '10.0.2.103', TRUE, '{"path": "/shared/finance/projections.xlsx", "action": "read", "size_bytes": 2097152}'),
(6, NULL, 'email_send', 'Financial projections externally', '2026-07-10 14:00:00', '192.168.1.50', TRUE, '{"to": "external", "subject": "Q3 Forecast", "attachment_size": 2097152}'),
(6, 6, 'file_access', 'Downloaded employee records', '2026-07-25 10:00:00', '10.0.2.103', TRUE, '{"path": "/shared/hr/employees.xlsx", "action": "read", "size_bytes": 786432}'),
(6, NULL, 'email_send', 'Employee data externally', '2026-07-25 15:00:00', '192.168.1.50', TRUE, '{"to": "external", "subject": "HR Update", "attachment_size": 786432}'),
-- August 2026 - Larger but still small individual transfers
(6, 6, 'file_access', 'Downloaded strategy document', '2026-08-02 09:30:00', '10.0.2.103', TRUE, '{"path": "/shared/exec/strategy_2026.docx", "action": "read", "size_bytes": 1572864}'),
(6, NULL, 'email_send', 'Strategy doc externally', '2026-08-02 14:30:00', '192.168.1.50', TRUE, '{"to": "external", "subject": "Strategy Review", "attachment_size": 1572864}'),
(6, 6, 'file_access', 'Downloaded IP portfolio', '2026-08-10 10:00:00', '10.0.2.103', TRUE, '{"path": "/shared/legal/ip_portfolio.pdf", "action": "read", "size_bytes": 3145728}'),
(6, NULL, 'email_send', 'IP portfolio externally', '2026-08-10 14:00:00', '192.168.1.50', TRUE, '{"to": "external", "subject": "Legal Documents", "attachment_size": 3145728}'),
-- Network exfiltration (small amounts)
(6, 6, 'network_connect', 'Small network transfer to external', '2026-05-15 02:00:00', '10.0.2.103', TRUE, '{"dest": "45.33.98.100", "bytes": 262144, "protocol": "HTTPS"}'),
(6, 6, 'network_connect', 'Small network transfer to external', '2026-06-15 02:30:00', '10.0.2.103', TRUE, '{"dest": "45.33.98.100", "bytes": 524288, "protocol": "HTTPS"}'),
(6, 6, 'network_connect', 'Small network transfer to external', '2026-07-15 03:00:00', '10.0.2.103', TRUE, '{"dest": "45.33.98.100", "bytes": 786432, "protocol": "HTTPS"}'),
(6, 6, 'network_connect', 'Small network transfer to external', '2026-08-15 02:45:00', '10.0.2.103', TRUE, '{"dest": "45.33.98.100", "bytes": 1048576, "protocol": "HTTPS"}'),
-- Normal activities (noise)
(3, 6, 'email_send', 'Monthly report to manager', '2026-08-01 10:00:00', '10.0.2.103', TRUE, '{"to": "internal", "subject": "Monthly Finance Report", "attachment_size": 1048576}'),
(4, 7, 'email_send', 'Code review notification', '2026-08-01 11:00:00', '10.0.3.50', TRUE, '{"to": "internal", "subject": "PR #1234 Ready", "attachment_size": 0}'),
(8, 7, 'email_send', 'Deployment notification', '2026-08-01 14:00:00', '10.0.3.50', TRUE, '{"to": "internal", "subject": "Deploy v2.5.1", "attachment_size": 0}');

-- ============================================================
-- CASE-028: The Corporate Spy
-- Confidential files + file_access + external emails within 24h
-- ============================================================
INSERT INTO `files` (`file_path`, `file_name`, `file_type`, `file_size`, `owner_id`, `device_id`, `created_at`, `modified_at`, `accessed_at`, `is_deleted`, `hash_md5`, `hash_sha256`, `permissions`, `content_preview`) VALUES
('/shared/marketing/confidential_bid_2026.docx', 'confidential_bid_2026.docx', 'document', 3145728, 7, NULL, '2026-08-01 10:00:00', '2026-08-10 14:00:00', '2026-08-10 11:00:00', FALSE, 'bid_hash_1234567890123456789012345', 'bid_sha_12345678901234567890123456789012345678901234567890123456789012345', 'rw-r-----', 'Confidential Bid Proposal - Project Alpha'),
('/shared/exec/strategy_2026.docx', 'strategy_2026.docx', 'document', 1572864, 1, 4, '2026-07-15 09:00:00', '2026-08-05 14:00:00', '2026-08-10 15:00:00', FALSE, 'strat_hash_12345678901234567890123', 'strat_sha_1234567890123456789012345678901234567890123456789012345678901234', 'rw-r-----', 'Annual Strategy Plan 2026 - Confidential'),
('/shared/product/roadmap_q4.xlsx', 'roadmap_q4.xlsx', 'spreadsheet', 2097152, 4, 7, '2026-08-01 11:00:00', '2026-08-08 10:00:00', '2026-08-11 09:00:00', FALSE, 'road_hash_123456789012345678901234', 'road_sha_12345678901234567890123456789012345678901234567890123456789012345', 'rw-r-----', 'Product Roadmap Q4 2026 - Confidential'),
('/shared/sales/confidential_bid_clientX.docx', 'confidential_bid_clientX.docx', 'document', 4194304, 7, NULL, '2026-08-05 09:00:00', '2026-08-12 11:00:00', '2026-08-12 10:30:00', FALSE, 'bx_hash_1234567890123456789012345', 'bx_sha_123456789012345678901234567890123456789012345678901234567890123456', 'rw-r-----', 'Client X Bid Proposal - Highly Confidential');

-- File access activities for confidential files
INSERT INTO `activities` (`user_id`, `device_id`, `action_type`, `description`, `timestamp`, `ip_address`, `success`, `details`) VALUES
(6, 11, 'file_access', 'Accessed confidential bid document', '2026-08-10 11:00:00', '203.0.113.50', TRUE, '{"path": "/shared/marketing/confidential_bid_2026.docx", "action": "read"}'),
(6, 11, 'email_send', 'Emailed bid document externally', '2026-08-10 23:00:00', '203.0.113.50', TRUE, '{"to": "external", "subject": "Marketing Materials", "attachment": "confidential_bid_2026.docx", "size": 3145728}'),
(6, 11, 'file_access', 'Accessed strategy document', '2026-08-10 15:00:00', '203.0.113.50', TRUE, '{"path": "/shared/exec/strategy_2026.docx", "action": "read"}'),
(6, 11, 'email_send', 'Emailed strategy doc externally', '2026-08-11 10:00:00', '203.0.113.50', TRUE, '{"to": "external", "subject": "Strategy Overview", "attachment": "strategy_2026.docx", "size": 1572864}'),
(6, 11, 'file_access', 'Accessed product roadmap', '2026-08-11 09:00:00', '203.0.113.50', TRUE, '{"path": "/shared/product/roadmap_q4.xlsx", "action": "read"}'),
(6, 11, 'email_send', 'Emailed roadmap externally', '2026-08-12 08:00:00', '203.0.113.50', TRUE, '{"to": "external", "subject": "Product Planning", "attachment": "roadmap_q4.xlsx", "size": 2097152}'),
(6, 11, 'file_access', 'Accessed Client X bid', '2026-08-12 10:30:00', '203.0.113.50', TRUE, '{"path": "/shared/sales/confidential_bid_clientX.docx", "action": "read"}'),
(6, 11, 'email_send', 'Emailed Client X bid externally', '2026-08-13 06:00:00', '203.0.113.50', TRUE, '{"to": "external", "subject": "Sales Documentation", "attachment": "confidential_bid_clientX.docx", "size": 4194304}'),
-- Legitimate accesses (noise)
(7, NULL, 'file_access', 'Created bid document', '2026-08-05 09:00:00', '192.168.1.50', TRUE, '{"path": "/shared/sales/confidential_bid_clientX.docx", "action": "create"}'),
(1, 4, 'file_access', 'Reviewed strategy document', '2026-08-06 10:00:00', '10.0.2.101', TRUE, '{"path": "/shared/exec/strategy_2026.docx", "action": "read"}'),
(4, 7, 'file_access', 'Updated product roadmap', '2026-08-08 10:00:00', '10.0.3.50', TRUE, '{"path": "/shared/product/roadmap_q4.xlsx", "action": "write"}');

-- ============================================================
-- CASE-029: The Cover-Up
-- Anomalous log entries (out-of-sequence, duplicates, gaps, contradictory timestamps)
-- ============================================================
INSERT INTO `logs` (`log_type`, `level`, `source`, `message`, `user_id`, `device_id`, `ip_address`, `timestamp`, `details`) VALUES
-- Normal sequence
('security', 'info', 'Auth', 'User login successful', 3, 6, '10.0.2.103', '2026-08-15 09:00:00', '{"user": "apatel"}'),
('security', 'info', 'Auth', 'User login successful', 1, 4, '10.0.2.101', '2026-08-15 09:01:00', '{"user": "psharma"}'),
-- ANOMALY 1: Out-of-sequence timestamp (timestamp before predecessor)
('application', 'info', 'WebServer', 'Request processed', NULL, 3, '10.0.2.101', '2026-08-15 08:55:00', '{"method": "GET", "path": "/api/status"}'),
-- ANOMALY 2: Duplicate entries (identical log within seconds)
('security', 'info', 'Auth', 'User login successful', 4, 7, '10.0.3.50', '2026-08-15 09:15:00', '{"user": "sverma"}'),
('security', 'info', 'Auth', 'User login successful', 4, 7, '10.0.3.50', '2026-08-15 09:15:01', '{"user": "sverma"}'),
-- ANOMALY 3: Large gap in log sequence (missing hours)
-- (gap from 09:15 to 14:30)
('application', 'info', 'WebServer', 'Request processed', NULL, 3, '10.0.2.101', '2026-08-15 14:30:00', '{"method": "POST", "path": "/api/data"}'),
-- ANOMALY 4: Modified timestamp (changed after creation)
('security', 'warning', 'IDS', 'Suspicious pattern detected', NULL, NULL, '203.0.113.50', '2026-08-15 15:00:00', '{"signature": "SCAN_001", "note": "timestamp appears modified"}'),
-- ANOMALY 5: Log entry with future timestamp
('application', 'info', 'Scheduler', 'Task completed', 9, 1, '10.0.1.10', '2026-08-15 23:59:59', '{"task": "cleanup", "status": "completed"}'),
('system', 'info', 'Backup', 'Backup started', 9, 1, '10.0.1.10', '2026-08-15 08:00:00', '{"type": "incremental"}'),
-- ANOMALY 6: Near-duplicate with slight modification
('security', 'error', 'HIDS', 'File integrity violation', NULL, 6, '203.0.113.50', '2026-08-15 16:00:00', '{"file": "/etc/passwd", "change": "modified"}'),
('security', 'error', 'HIDS', 'File integrity violation', NULL, 6, '203.0.113.50', '2026-08-15 16:00:02', '{"file": "/etc/passwd", "change": "modified", "extra_field": true}'),
-- Normal continuation
('application', 'info', 'WebServer', 'Request processed', NULL, 3, '10.0.2.101', '2026-08-15 17:00:00', '{"method": "GET", "path": "/api/health"}'),
('security', 'info', 'Auth', 'User logout', 3, 6, '10.0.2.103', '2026-08-15 18:00:00', '{"user": "apatel"}');

-- ============================================================
-- Additional timestamps for CASE-029 (contradictory with logs)
-- ============================================================
INSERT INTO `timestamps` (`event_type`, `event_data`, `recorded_at`, `source`) VALUES
-- ANOMALY: Timestamp contradicts log entry (different times for same event)
('user_login', '{"user": "sverma", "source_ip": "10.0.3.50"}', '2026-08-15 09:14:58.000', 'NTP'),
('user_login', '{"user": "sverma", "source_ip": "10.0.3.50"}', '2026-08-15 09:15:03.000', 'Auth'),
-- ANOMALY: Out-of-sequence timestamps
('file_access', '{"file": "confidential_bid.docx"}', '2026-08-15 15:30:00.000', 'FileServer'),
('file_access', '{"file": "strategy_doc.docx"}', '2026-08-15 14:00:00.000', 'FileServer'),
-- ANOMALY: Gap in timestamps
('system_event', '{"type": "scheduled_task"}', '2026-08-15 09:16:00.000', 'Scheduler'),
('system_event', '{"type": "scheduled_task"}', '2026-08-15 14:29:00.000', 'Scheduler'),
-- Normal timestamps
('system_boot', '{"device": "DC-PROD-01"}', '2026-08-15 06:00:00.000', 'System'),
('config_change', '{"setting": "backup_schedule"}', '2026-08-15 06:05:00.000', 'ConfigManager');
