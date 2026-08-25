USE `digital_forensics`;

-- Seed devices
INSERT INTO `devices` (`id`, `device_name`, `device_type`, `ip_address`, `mac_address`, `os`, `location`, `owner_id`, `status`, `first_seen`, `last_seen`) VALUES
(1, 'DC-PROD-01', 'server', '10.0.1.10', '00:1A:2B:3C:01:01', 'Ubuntu 22.04 LTS', 'Data Center - Rack A1', NULL, 'active', '2024-01-01 00:00:00', '2026-08-20 23:59:59'),
(2, 'DC-PROD-02', 'server', '10.0.1.11', '00:1A:2B:3C:01:02', 'Ubuntu 22.04 LTS', 'Data Center - Rack A1', NULL, 'active', '2024-01-01 00:00:00', '2026-08-20 23:59:59'),
(3, 'MAIL-SRV', 'server', '10.0.1.20', '00:1A:2B:3C:01:03', 'Windows Server 2022', 'Data Center - Rack A2', NULL, 'active', '2024-01-01 00:00:00', '2026-08-20 23:59:59'),
(4, 'WS-SHARMA-01', 'workstation', '10.0.2.101', '00:1A:2B:3C:02:01', 'Windows 11 Pro', 'Office Floor 3 - Desk 45', 1, 'active', '2025-06-15 09:00:00', '2026-08-20 18:30:00'),
(5, 'WS-KUMAR-01', 'workstation', '10.0.2.102', '00:1A:2B:3C:02:02', 'Windows 11 Pro', 'Office Floor 3 - Desk 46', 2, 'active', '2025-06-15 09:00:00', '2026-08-20 18:30:00'),
(6, 'WS-PATEL-01', 'workstation', '10.0.2.103', '00:1A:2B:3C:02:03', 'Windows 11 Pro', 'Office Floor 3 - Desk 47', 3, 'compromised', '2025-06-15 09:00:00', '2026-08-15 04:22:00'),
(7, 'LAPTOP-VERMA-01', 'laptop', '10.0.3.50', '00:1A:2B:3C:03:01', 'macOS Sonoma', 'Remote - Bangalore', 4, 'active', '2025-09-01 09:00:00', '2026-08-20 19:00:00'),
(8, 'MOB-SHARMA-01', 'mobile', '192.168.1.50', '00:1A:2B:3C:04:01', 'iOS 17.5', 'Mobile Network', 1, 'active', '2025-06-15 09:00:00', '2026-08-20 20:00:00'),
(9, 'FW-CORE-01', 'network_device', '10.0.0.1', '00:1A:2B:3C:00:01', 'Fortinet FortiOS', 'Data Center - Rack A0', NULL, 'active', '2024-01-01 00:00:00', '2026-08-20 23:59:59'),
(10, 'SW-CORE-01', 'network_device', '10.0.0.2', '00:1A:2B:3C:00:02', 'Cisco IOS', 'Data Center - Rack A0', NULL, 'active', '2024-01-01 00:00:00', '2026-08-20 23:59:59'),
(11, 'DEV-UNKNOWN-01', 'workstation', '203.0.113.50', NULL, 'Windows 10', 'Unknown', NULL, 'compromised', '2026-08-10 03:15:00', '2026-08-18 04:30:00'),
(12, 'TOR-EXIT-01', 'network_device', '198.51.100.75', NULL, NULL, 'External - Tor Network', NULL, 'compromised', '2026-08-12 02:00:00', '2026-08-17 05:00:00');

-- Seed users
INSERT INTO `users` (`id`, `username`, `full_name`, `email`, `department`, `role`, `status`) VALUES
(1, 'psharma', 'Priya Sharma', 'priya.sharma@corp.com', 'Executive', 'admin', 'active'),
(2, 'rkumar', 'Rajesh Kumar', 'rajesh.kumar@corp.com', 'IT', 'admin', 'active'),
(3, 'apatel', 'Amit Patel', 'amit.patel@corp.com', 'Finance', 'user', 'active'),
(4, 'sverma', 'Sanjay Verma', 'sanjay.verma@corp.com', 'Engineering', 'user', 'active'),
(5, 'ldesai', 'Leena Desai', 'leena.desai@corp.com', 'HR', 'user', 'active'),
(6, 'mgupta', 'Manoj Gupta', 'manoj.gupta@corp.com', 'Finance', 'user', 'locked'),
(7, 'nreddy', 'Nisha Reddy', 'nisha.reddy@corp.com', 'Marketing', 'user', 'active'),
(8, 'tsingh', 'Tarun Singh', 'tarun.singh@corp.com', 'Engineering', 'user', 'active'),
(9, 'backup_svc', 'Backup Service Account', 'backup@corp.com', 'IT', 'service_account', 'active'),
(10, 'monitor_svc', 'Monitoring Service', 'monitor@corp.com', 'IT', 'service_account', 'active');

-- Seed activities
INSERT INTO `activities` (`user_id`, `device_id`, `action_type`, `description`, `timestamp`, `ip_address`, `success`, `details`) VALUES
-- Normal activities
(1, 4, 'login', 'User login', '2026-08-15 09:00:00', '10.0.2.101', TRUE, '{"browser": "Chrome 118", "os": "Windows 11"}'),
(2, 5, 'login', 'User login', '2026-08-15 09:05:00', '10.0.2.102', TRUE, '{"browser": "Chrome 118", "os": "Windows 11"}'),
(3, 6, 'login', 'User login', '2026-08-15 09:10:00', '10.0.2.103', TRUE, '{"browser": "Chrome 118", "os": "Windows 11"}'),
(4, 7, 'login', 'VPN login from remote', '2026-08-15 08:30:00', '103.21.58.1', TRUE, '{"vpn": "Corporate VPN", "location": "Bangalore"}'),
(3, 6, 'file_access', 'Accessed financial_report_q2.xlsx', '2026-08-15 09:30:00', '10.0.2.103', TRUE, '{"path": "/shared/finance/Q2_report.xlsx", "action": "read"}'),
(3, 6, 'database_query', 'SELECT query on employees table', '2026-08-15 10:00:00', '10.0.2.103', TRUE, '{"query": "SELECT * FROM employees WHERE department = '\''Finance'\''", "rows": 12}'),
(1, 4, 'email_send', 'Email to board@corp.com', '2026-08-15 14:00:00', '10.0.2.101', TRUE, '{"subject": "Q3 Budget Review", "recipients": 5}'),
-- Suspicious activities - after hours from compromised device
(6, 11, 'login', 'Login from unknown device', '2026-08-12 02:15:00', '203.0.113.50', TRUE, '{"browser": "Chrome 118", "os": "Windows 10", "vpn": false}'),
(6, 11, 'privilege_escalation', 'Attempted admin access', '2026-08-12 02:20:00', '203.0.113.50', FALSE, '{"target_role": "admin", "reason": "insufficient_privileges"}'),
(6, 11, 'database_query', 'Unauthorized database query', '2026-08-12 02:25:00', '203.0.113.50', TRUE, '{"query": "SELECT * FROM users", "rows": 10}'),
(6, 11, 'file_access', 'Accessed sensitive files', '2026-08-12 02:30:00', '203.0.113.50', TRUE, '{"path": "/shared/hr/confidential/", "action": "read", "files": 5}'),
-- More suspicious - Tor network
(NULL, 12, 'network_connect', 'Connection from Tor exit node', '2026-08-12 02:45:00', '198.51.100.75', TRUE, '{"protocol": "SSH", "destination_port": 22}'),
(NULL, 12, 'process_start', 'Suspicious process detected', '2026-08-12 02:50:00', '198.51.100.75', TRUE, '{"process": "mimikatz.exe", "command_line": "sekurlsa::logonpasswords"}'),
(6, 11, 'email_send', 'External email with attachment', '2026-08-12 03:00:00', '203.0.113.50', TRUE, '{"subject": "Project Files", "recipients": "external", "attachment_size": "15MB"}'),
-- Normal activities continued
(7, NULL, 'login', 'Login from mobile', '2026-08-15 10:00:00', '192.168.1.50', TRUE, '{"browser": "Safari Mobile", "os": "iOS 17"}'),
(8, 7, 'login', 'Login from laptop', '2026-08-15 09:15:00', '10.0.3.50', TRUE, '{"browser": "Chrome 118", "os": "macOS Sonoma"}'),
(9, 1, 'config_change', 'Backup configuration updated', '2026-08-15 03:00:00', '10.0.1.10', TRUE, '{"config": "retention_policy", "old": "30_days", "new": "90_days"}'),
(10, 2, 'process_start', 'Monitoring check', '2026-08-15 00:00:00', '10.0.1.11', TRUE, '{"check": "health_status", "result": "all_systems_normal"}');

-- Seed files
INSERT INTO `files` (`file_path`, `file_name`, `file_type`, `file_size`, `owner_id`, `device_id`, `created_at`, `modified_at`, `accessed_at`, `is_deleted`, `hash_md5`, `hash_sha256`, `permissions`, `content_preview`) VALUES
('/shared/finance/Q2_report.xlsx', 'Q2_report.xlsx', 'spreadsheet', 2457600, 3, 6, '2026-07-15 10:00:00', '2026-08-01 14:30:00', '2026-08-15 09:30:00', FALSE, 'a1b2c3d4e5f678901234567890abcdef', 'abcdef1234567890abcdef1234567890abcdef1234567890abcdef1234567890', 'rw-r--r--', 'Financial Report Q2 2026 - Revenue: ₹45.2Cr'),
('/shared/hr/confidential/salary_data.xlsx', 'salary_data.xlsx', 'spreadsheet', 1048576, 5, NULL, '2026-06-01 09:00:00', '2026-08-01 11:00:00', '2026-08-12 02:32:00', FALSE, 'b2c3d4e5f678901234567890abcdef01', 'bcdef1234567890abcdef1234567890abcdef1234567890abcdef1234567890a', 'rw-r-----', 'Employee Salary Data - Confidential'),
('/shared/hr/confidential/employee_records.db', 'employee_records.db', 'database', 52428800, 5, NULL, '2026-01-01 00:00:00', '2026-08-15 08:00:00', '2026-08-12 02:33:00', FALSE, 'c3d4e5f678901234567890abcdef0123', 'cdef1234567890abcdef1234567890abcdef1234567890abcdef1234567890ab', 'rw-------', 'Employee database backup'),
('/home/psharma/documents/board_presentation.pptx', 'board_presentation.pptx', 'presentation', 15728640, 1, 4, '2026-08-10 09:00:00', '2026-08-15 13:00:00', '2026-08-15 14:00:00', FALSE, 'd4e5f678901234567890abcdef012345', 'def1234567890abcdef1234567890abcdef1234567890abcdef1234567890abc', 'rw-r--r--', 'Q3 Board Presentation Draft'),
('/shared/IT/security/incident_report.docx', 'incident_report.docx', 'document', 524288, 2, NULL, '2026-08-18 10:00:00', '2026-08-18 14:00:00', '2026-08-18 14:00:00', FALSE, 'e5f678901234567890abcdef0123456', 'ef1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcde', 'rw-rw----', 'Security Incident Report - August 2026'),
('/home/apatel/documents/tax_returns_2025.pdf', 'tax_returns_2025.pdf', 'document', 2097152, 3, 6, '2026-03-15 10:00:00', '2026-03-15 10:00:00', '2026-08-12 02:35:00', FALSE, 'f678901234567890abcdef012345678', 'f1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcdef', 'rw-------', 'Tax Returns FY 2025-26'),
('/tmp/suspicious_malware.exe', 'suspicious_malware.exe', 'executable', 1048576, NULL, 11, '2026-08-12 02:45:00', '2026-08-12 02:45:00', '2026-08-12 02:45:00', TRUE, 'malware_hash_1234567890123456789', 'malware_sha256_12345678901234567890123456789012345678901234567890123456', 'rwx------', 'Known malware signature - Cobalt Strike beacon'),
('/shared/IT/logs/access_log_20260815.log', 'access_log_20260815.log', 'log', 10485760, NULL, 1, '2026-08-15 00:00:00', '2026-08-15 23:59:59', '2026-08-18 10:00:00', FALSE, 'log_hash_123456789012345678901234', 'log_sha_123456789012345678901234567890123456789012345678901234567890', 'rw-r--r--', 'Server access log for August 15, 2026');

-- Seed network logs
INSERT INTO `network_logs` (`source_ip`, `destination_ip`, `source_port`, `destination_port`, `protocol`, `timestamp`, `bytes_sent`, `bytes_received`, `duration`, `status`, `device_id`, `user_id`) VALUES
-- Normal traffic
('10.0.2.101', '10.0.1.10', 49832, 443, 'https', '2026-08-15 09:00:05', 1024, 2048, 2, 'allowed', 4, 1),
('10.0.2.102', '10.0.1.10', 49833, 443, 'https', '2026-08-15 09:05:05', 1024, 2048, 2, 'allowed', 5, 2),
('10.0.2.103', '10.0.1.10', 49834, 443, 'https', '2026-08-15 09:10:05', 1024, 2048, 2, 'allowed', 6, 3),
('10.0.2.103', '10.0.1.20', 49835, 25, 'smtp', '2026-08-15 14:00:10', 5120, 1024, 5, 'allowed', 6, 3),
-- Suspicious traffic from external
('203.0.113.50', '10.0.1.20', 54321, 22, 'ssh', '2026-08-12 02:15:05', 2048, 4096, 10, 'suspicious', NULL, NULL),
('203.0.113.50', '10.0.1.10', 54322, 443, 'https', '2026-08-12 02:20:05', 4096, 8192, 15, 'suspicious', NULL, NULL),
('203.0.113.50', '10.0.1.11', 54323, 3306, 'tcp', '2026-08-12 02:25:05', 8192, 16384, 20, 'suspicious', NULL, NULL),
-- Tor exit node traffic
('198.51.100.75', '10.0.0.1', 45678, 22, 'ssh', '2026-08-12 02:45:05', 1024, 2048, 30, 'suspicious', NULL, NULL),
('198.51.100.75', '10.0.1.20', 45679, 443, 'https', '2026-08-12 02:50:05', 102400, 51200, 60, 'suspicious', NULL, NULL),
-- More suspicious - data exfiltration pattern
('10.0.2.103', '203.0.113.50', 49900, 8080, 'http', '2026-08-12 03:00:05', 15728640, 1024, 120, 'suspicious', 6, 3),
-- Normal
('10.0.3.50', '10.0.0.1', 51234, 1194, 'tcp', '2026-08-15 08:30:05', 512, 512, 1, 'allowed', 7, 4),
('10.0.2.101', '8.8.8.8', 49840, 53, 'dns', '2026-08-15 09:00:10', 128, 256, 1, 'allowed', 4, 1);

-- Seed emails
INSERT INTO `emails` (`message_id`, `sender_id`, `recipient_ids`, `subject`, `body`, `sent_at`, `folder`, `importance', `has_attachments`, `size_bytes`) VALUES
('MSG-2026-001', 1, '[2, 3, 4]', 'Q3 Budget Review Meeting', 'Hi team, please review the Q3 budget documents before our meeting on Friday.', '2026-08-15 14:00:00', 'sent', 'normal', TRUE, 2048000),
('MSG-2026-002', 3, '[1]', 'Re: Q3 Budget Review', 'I have reviewed the numbers and they look good. Attaching my analysis.', '2026-08-15 15:30:00', 'sent', 'normal', TRUE, 1048576),
('MSG-2026-003', 6, '[external:unknown@protonmail.com]', 'Project Files', 'As discussed, here are the files you requested.', '2026-08-12 03:05:00', 'sent', 'urgent', TRUE, 15728640),
('MSG-2026-004', 2, '[5, 6, 7]', 'Security Policy Update', 'Please review the updated security policy document.', '2026-08-14 11:00:00', 'sent', 'high', FALSE, 4096),
('MSG-2026-005', 5, '[3]', 'Employee Survey Results', 'Hi Amit, here are the survey results for your department.', '2026-08-13 16:00:00', 'sent', 'normal', FALSE, 8192),
('MSG-2026-006', 4, '[8]', 'Code Review Request', 'Can you review my pull request when you get a chance?', '2026-08-15 10:00:00', 'sent', 'normal', FALSE, 2048),
('MSG-2026-007', 9, '[1, 2]', 'Backup Complete', 'Nightly backup completed successfully. 15GB compressed.', '2026-08-15 03:15:00', 'sent', 'low', FALSE, 1024);

-- Seed system logs
INSERT INTO `logs` (`log_type`, `level`, `source`, `message`, `user_id`, `device_id`, `ip_address`, `timestamp`, `details`) VALUES
('security', 'warning', 'Firewall', 'Connection attempt from blocked IP range', NULL, 9, '203.0.113.50', '2026-08-12 02:14:50', '{"action": "logged", "rule": "geo_block"}'),
('security', 'error', 'IDS', 'Intrusion detected: privilege escalation attempt', 6, 11, '203.0.113.50', '2026-08-12 02:20:05', '{"signature": "PRIV_ESC_001", "severity": "high"}'),
('security', 'critical', 'SIEM', 'Malware detected on endpoint', NULL, 11, '203.0.113.50', '2026-08-12 02:45:10', '{"malware_type": "trojan", "file": "suspicious_malware.exe", "action": "quarantined"}'),
('security', 'warning', 'DLP', 'Large data transfer detected', 3, 6, '10.0.2.103', '2026-08-12 03:00:10', '{"bytes": 15728640, "destination": "203.0.113.50", "protocol": "HTTP"}'),
('application', 'info', 'Database', 'User login successful', 3, 6, '10.0.2.103', '2026-08-15 09:10:05', '{"database": "hr_system", "query_count": 0}'),
('application', 'info', 'Email', 'Email sent successfully', 1, 4, '10.0.2.101', '2026-08-15 14:00:15', '{"recipients": 3, "size": 2048000}'),
('system', 'info', 'Backup', 'Nightly backup completed', 9, 1, '10.0.1.10', '2026-08-15 03:15:00', '{"size_gb": 15, "duration_minutes": 45}'),
('security', 'info', 'Auth', 'Account locked after failed attempts', 6, NULL, '203.0.113.50', '2026-08-18 04:30:00', '{"reason": "multiple_failed_logins", "attempts": 10}'),
('audit', 'info', 'FileServer', 'File accessed', 3, 6, '10.0.2.103', '2026-08-15 09:30:05', '{"file": "/shared/finance/Q2_report.xlsx", "action": "read"}'),
('audit', 'warning', 'FileServer', 'Unauthorized file access attempt', 6, 11, '203.0.113.50', '2026-08-12 02:32:05', '{"file": "/shared/hr/confidential/salary_data.xlsx", "action": "read", "result": "denied"}');

-- Seed timestamps for forensic timeline
INSERT INTO `timestamps` (`event_type`, `event_data`, `recorded_at`, `source`) VALUES
('system_boot', '{"device": "WS-PATEL-01", "boot_type": "cold"}', '2026-08-10 08:55:00.000', 'System'),
('user_login', '{"user": "apatel", "method": "password"}', '2026-08-15 09:10:00.123', 'Auth'),
('file_access', '{"file": "Q2_report.xlsx", "user": "apatel"}', '2026-08-15 09:30:00.456', 'FileServer'),
('email_sent', '{"to": "external", "subject": "Project Files"}', '2026-08-12 03:05:00.789', 'Email'),
('network_anomaly', '{"src": "203.0.113.50", "dst": "10.0.1.20", "port": 22}', '2026-08-12 02:15:05.012', 'Firewall'),
('malware_detected', '{"file": "suspicious_malware.exe", "action": "quarantined"}', '2026-08-12 02:45:10.345', 'Antivirus'),
('data_exfiltration', '{"bytes": 15728640, "dest": "203.0.113.50"}', '2026-08-12 03:00:10.678', 'DLP'),
('account_locked', '{"user": "mgupta", "reason": "failed_logins"}', '2026-08-18 04:30:00.901', 'Auth');