-- Supplemental seed data for employeeportal investigation database
-- Supports cases 006, 009, 015, 019, 024, 027

USE `employeeportal`;

-- ============================================================
-- CASE-006: The Missing Records
-- Audit trail entries with DELETE actions on employees table
-- ============================================================
INSERT INTO `audit_trail` (`table_name`, `record_id`, `action`, `old_values`, `new_values`, `performed_by`, `performed_at`, `ip_address`) VALUES
-- Deleted employee records
('employees', 21, 'delete', '{"emp_code": "E021", "first_name": "Aakash", "last_name": "Dutta", "department_id": 5, "position": "Sales Trainee"}', NULL, 5, '2026-08-12 02:40:00', '198.51.100.75'),
('employees', 22, 'delete', '{"emp_code": "E022", "first_name": "Rina", "last_name": "Ghosh", "department_id": 3, "position": "Junior Accountant"}', NULL, 5, '2026-08-14 03:35:00', '198.51.100.75'),
-- Updated records (existing pattern)
('employees', 5, 'update', '{"position": "Junior Developer"}', '{"position": "Junior Developer", "access_level": "admin"}', 5, '2026-08-12 02:28:00', '198.51.100.75'),
('employees', 5, 'update', '{"failed_logins": 0}', '{"failed_logins": 0, "last_login": "2026-08-12 02:15:00"}', 5, '2026-08-12 02:16:00', '198.51.100.75'),
-- Legitimate updates (noise)
('employees', 2, 'update', '{"position": "Senior Developer"}', '{"position": "VP Engineering"}', 1, '2026-03-15 10:05:00', '192.168.1.10'),
('employees', 3, 'update', '{"position": "Senior Accountant"}', '{"position": "Finance Director"}', 1, '2026-06-20 10:05:00', '192.168.1.10'),
('employees', 4, 'update', '{"salary": 800000}', '{"salary": 1200000}', 1, '2026-07-01 10:00:00', '192.168.1.10');

-- ============================================================
-- CASE-009: The Night Shift
-- Successful logins between 11 PM and 5 AM
-- ============================================================
INSERT INTO `login_records` (`employee_id`, `login_time`, `logout_time`, `ip_address`, `location`, `device_info`, `mfa_used`, `status`, `vpn_used`, `geo_location`) VALUES
-- Night shift successful logins (various employees)
(5, '2026-08-05 23:30:00', '2026-08-06 01:00:00', '192.168.1.14', 'Mumbai Office', 'Firefox/Ubuntu', FALSE, 'success', FALSE, 'Mumbai, Maharashtra, India'),
(4, '2026-08-06 00:15:00', '2026-08-06 02:30:00', '192.168.1.13', 'Mumbai Office', 'Chrome/macOS', TRUE, 'success', FALSE, 'Mumbai, Maharashtra, India'),
(15, '2026-08-07 23:45:00', '2026-08-08 03:00:00', '192.168.1.25', 'Mumbai Office', 'VSCode/Linux', TRUE, 'success', FALSE, 'Mumbai, Maharashtra, India'),
(5, '2026-08-08 01:00:00', '2026-08-08 04:30:00', '192.168.1.14', 'Mumbai Office', 'Firefox/Ubuntu', FALSE, 'success', FALSE, 'Mumbai, Maharashtra, India'),
(9, '2026-08-09 23:15:00', '2026-08-10 05:00:00', '192.168.1.17', 'Mumbai Office', 'Terminal/Linux', TRUE, 'success', FALSE, 'Mumbai, Maharashtra, India'),
(4, '2026-08-10 00:30:00', '2026-08-10 03:15:00', '100.64.0.100', 'Remote - Pune', 'Chrome/macOS', TRUE, 'success', TRUE, 'Pune, Maharashtra, India'),
(5, '2026-08-11 23:00:00', '2026-08-12 01:30:00', '198.51.100.75', 'Unknown', 'Chrome/Windows 10', FALSE, 'success', FALSE, 'Unknown'),
(15, '2026-08-12 00:00:00', '2026-08-12 02:00:00', '192.168.1.25', 'Mumbai Office', 'VSCode/Linux', TRUE, 'success', FALSE, 'Mumbai, Maharashtra, India'),
(5, '2026-08-13 23:30:00', '2026-08-14 02:00:00', '192.168.1.14', 'Mumbai Office', 'Firefox/Ubuntu', FALSE, 'success', FALSE, 'Mumbai, Maharashtra, India'),
-- Normal day logins (noise)
(1, '2026-08-05 09:00:00', '2026-08-05 18:00:00', '192.168.1.10', 'Mumbai Office', 'Chrome/Windows 11', TRUE, 'success', FALSE, 'Mumbai, Maharashtra, India'),
(2, '2026-08-05 08:30:00', '2026-08-05 18:30:00', '192.168.1.11', 'Mumbai Office', 'Chrome/Windows 11', TRUE, 'success', FALSE, 'Mumbai, Maharashtra, India'),
(3, '2026-08-06 09:15:00', '2026-08-06 17:45:00', '192.168.1.12', 'Mumbai Office', 'Edge/Windows 11', TRUE, 'success', FALSE, 'Mumbai, Maharashtra, India'),
(6, '2026-08-07 08:30:00', '2026-08-07 17:30:00', '192.168.1.15', 'Mumbai Office', 'Chrome/Windows 11', TRUE, 'success', FALSE, 'Mumbai, Maharashtra, India'),
(7, '2026-08-08 09:45:00', '2026-08-08 18:15:00', '192.168.1.16', 'Mumbai Office', 'Chrome/Windows 10', TRUE, 'success', FALSE, 'Mumbai, Maharashtra, India'),
(13, '2026-08-09 07:30:00', '2026-08-09 19:00:00', '192.168.1.21', 'Mumbai Office', 'Chrome/Windows 11', TRUE, 'success', FALSE, 'Mumbai, Maharashtra, India');

-- ============================================================
-- CASE-015: The Permission Cascade
-- Longer permission_changes chain (multi-step escalation)
-- ============================================================
INSERT INTO `permission_changes` (`employee_id`, `changed_by`, `permission_type`, `old_value`, `new_value`, `change_time`, `reason`, `ip_address`) VALUES
-- Employee 5 escalation chain over multiple days
(5, 5, 'data_access', 'basic', 'elevated', '2026-08-10 22:00:00', 'Testing access requirements', '192.168.1.14'),
(5, 5, 'api_key', 'none', 'read_only', '2026-08-11 23:30:00', 'API access for development', '192.168.1.14'),
(5, 5, 'access_level', 'junior', 'senior', '2026-08-12 01:00:00', 'Role adjustment', '198.51.100.75'),
(5, 5, 'vpn', 'none', 'full', '2026-08-12 01:30:00', 'VPN access for remote work', '198.51.100.75'),
(5, 5, 'role', 'junior', 'admin', '2026-08-12 02:25:00', 'Self-escalated privileges', '198.51.100.75'),
(5, 5, 'data_access', 'basic', 'full', '2026-08-12 02:26:00', 'Self-modified access level', '198.51.100.75'),
(5, 5, 'admin', 'none', 'full', '2026-08-12 02:27:00', 'Self-granted admin access', '198.51.100.75'),
(5, 5, 'api_key', 'read_only', 'full', '2026-08-12 02:28:00', 'Elevated API access', '198.51.100.75'),
-- Legitimate permission changes by managers (noise)
(4, 2, 'role', 'mid', 'senior', '2026-07-01 10:00:00', 'Promoted to Senior Developer', '192.168.1.11'),
(15, 2, 'role', 'mid', 'lead', '2026-06-15 10:00:00', 'Promoted to Lead Developer', '192.168.1.11'),
(7, 6, 'data_access', 'basic', 'marketing_full', '2026-05-01 10:00:00', 'Marketing data access grant', '192.168.1.15'),
(12, 1, 'access_level', 'mid', 'senior', '2026-04-01 10:00:00', 'Seniority promotion', '192.168.1.10'),
(13, 2, 'access_level', 'mid', 'senior', '2026-03-01 10:00:00', 'Security team expansion', '192.168.1.11');

-- ============================================================
-- CASE-019: The Access Anomaly
-- Access logs spanning full August from multiple employees
-- ============================================================
INSERT INTO `access_logs` (`employee_id`, `resource`, `resource_type`, `action`, `access_time`, `ip_address`, `session_id`, `success`, `response_code`) VALUES
-- Employee 5 (Vikram) - First half of August (limited access pattern)
(5, '/dashboard', 'page', 'view', '2026-08-01 10:00:00', '192.168.1.14', 'sess_0801_001', TRUE, 200),
(5, '/api/code/list', 'api', 'view', '2026-08-01 10:05:00', '192.168.1.14', 'sess_0801_001', TRUE, 200),
(5, '/api/code/commit', 'api', 'create', '2026-08-02 09:30:00', '192.168.1.14', 'sess_0802_001', TRUE, 201),
(5, '/dashboard', 'page', 'view', '2026-08-03 10:00:00', '192.168.1.14', 'sess_0803_001', TRUE, 200),
(5, '/api/code/list', 'api', 'view', '2026-08-04 09:15:00', '192.168.1.14', 'sess_0804_001', TRUE, 200),
(5, '/api/code/commit', 'api', 'create', '2026-08-05 14:00:00', '192.168.1.14', 'sess_0805_001', TRUE, 201),
(5, '/dashboard', 'page', 'view', '2026-08-06 10:00:00', '192.168.1.14', 'sess_0806_001', TRUE, 200),
(5, '/api/code/list', 'api', 'view', '2026-08-07 09:00:00', '192.168.1.14', 'sess_0807_001', TRUE, 200),
(5, '/api/code/commit', 'api', 'create', '2026-08-08 11:00:00', '192.168.1.14', 'sess_0808_001', TRUE, 201),
(5, '/dashboard', 'page', 'view', '2026-08-09 10:00:00', '192.168.1.14', 'sess_0809_001', TRUE, 200),
(5, '/api/code/list', 'api', 'view', '2026-08-10 09:30:00', '192.168.1.14', 'sess_0810_001', TRUE, 200),
(5, '/api/code/commit', 'api', 'create', '2026-08-11 15:00:00', '192.168.1.14', 'sess_0811_001', TRUE, 201),
-- Employee 5 - Second half (access pattern changes dramatically)
(5, '/admin/users', 'admin', 'view', '2026-08-12 02:20:00', '198.51.100.75', 'sess_0812_001', TRUE, 200),
(5, '/admin/audit_logs', 'admin', 'view', '2026-08-12 02:22:00', '198.51.100.75', 'sess_0812_001', TRUE, 200),
(5, '/hr/employees', 'page', 'view', '2026-08-13 14:00:00', '192.168.1.14', 'sess_0813_001', TRUE, 200),
(5, '/hr/employees', 'page', 'export', '2026-08-13 14:05:00', '192.168.1.14', 'sess_0813_001', TRUE, 200),
(5, '/finance/reports', 'page', 'view', '2026-08-14 10:00:00', '192.168.1.14', 'sess_0814_001', TRUE, 200),
(5, '/finance/reports', 'page', 'export', '2026-08-14 10:05:00', '192.168.1.14', 'sess_0814_001', TRUE, 200),
(5, '/admin/system/config', 'admin', 'view', '2026-08-15 03:00:00', '203.0.113.50', 'sess_0815_001', TRUE, 200),
(5, '/api/database/query', 'database', 'execute', '2026-08-16 02:40:00', '203.0.113.50', 'sess_0816_001', TRUE, 200),
(5, '/admin/users', 'admin', 'view', '2026-08-17 01:30:00', '198.51.100.75', 'sess_0817_001', TRUE, 200),
(5, '/admin/roles', 'admin', 'update', '2026-08-17 01:35:00', '198.51.100.75', 'sess_0817_001', TRUE, 200),
(5, '/system/logs', 'system', 'view', '2026-08-18 02:00:00', '203.0.113.50', 'sess_0818_001', TRUE, 200),
(5, '/database/export', 'database', 'export', '2026-08-19 03:00:00', '203.0.113.50', 'sess_0819_001', TRUE, 200),
(5, '/dashboard', 'page', 'view', '2026-08-20 10:00:00', '192.168.1.14', 'sess_0820_001', TRUE, 200),
-- Other employees - normal patterns for comparison
(4, '/api/code/commit', 'api', 'create', '2026-08-01 09:30:00', '192.168.1.13', 'sess_s4_0801', TRUE, 201),
(4, '/api/code/review', 'api', 'view', '2026-08-05 10:00:00', '192.168.1.13', 'sess_s4_0805', TRUE, 200),
(4, '/api/code/commit', 'api', 'create', '2026-08-10 14:00:00', '192.168.1.13', 'sess_s4_0810', TRUE, 201),
(4, '/api/code/review', 'api', 'view', '2026-08-15 11:00:00', '192.168.1.13', 'sess_s4_0815', TRUE, 200),
(4, '/api/code/commit', 'api', 'create', '2026-08-20 09:30:00', '192.168.1.13', 'sess_s4_0820', TRUE, 201),
(6, '/hr/employees', 'page', 'view', '2026-08-01 08:30:00', '192.168.1.15', 'sess_s6_0801', TRUE, 200),
(6, '/hr/employees', 'page', 'view', '2026-08-08 09:00:00', '192.168.1.15', 'sess_s6_0808', TRUE, 200),
(6, '/hr/employees', 'page', 'update', '2026-08-15 14:00:00', '192.168.1.15', 'sess_s6_0815', TRUE, 200),
(6, '/hr/employees', 'page', 'view', '2026-08-20 08:30:00', '192.168.1.15', 'sess_s6_0820', TRUE, 200),
(7, '/marketing/campaigns', 'page', 'view', '2026-08-01 09:45:00', '192.168.1.16', 'sess_s7_0801', TRUE, 200),
(7, '/marketing/campaigns', 'page', 'create', '2026-08-05 10:00:00', '192.168.1.16', 'sess_s7_0805', TRUE, 201),
(7, '/marketing/analytics', 'page', 'view', '2026-08-10 14:00:00', '192.168.1.16', 'sess_s7_0810', TRUE, 200),
(7, '/marketing/campaigns', 'page', 'view', '2026-08-15 11:00:00', '192.168.1.16', 'sess_s7_0815', TRUE, 200),
(7, '/marketing/campaigns', 'page', 'view', '2026-08-20 09:45:00', '192.168.1.16', 'sess_s7_0820', TRUE, 200);

-- ============================================================
-- CASE-024: The Chain of Command
-- Manager/director/VP self-approvals and out-of-department access
-- ============================================================
INSERT INTO `access_logs` (`employee_id`, `resource`, `resource_type`, `action`, `access_time`, `ip_address`, `session_id`, `success`, `response_code`) VALUES
-- Manager self-approvals
(6, '/hr/employees', 'admin', 'update', '2026-08-10 10:00:00', '192.168.1.15', 'sess_mgr_001', TRUE, 200),
(6, '/hr/employees', 'admin', 'update', '2026-08-10 10:05:00', '192.168.1.15', 'sess_mgr_001', TRUE, 200),
(6, '/hr/employees', 'admin', 'update', '2026-08-10 10:10:00', '192.168.1.15', 'sess_mgr_001', TRUE, 200),
(6, '/hr/salary/review', 'admin', 'view', '2026-08-12 11:00:00', '192.168.1.15', 'sess_mgr_002', TRUE, 200),
(6, '/hr/salary/approve', 'admin', 'update', '2026-08-12 11:05:00', '192.168.1.15', 'sess_mgr_002', TRUE, 200),
-- Director out-of-department access
(3, '/hr/employees', 'database', 'view', '2026-08-08 15:00:00', '192.168.1.12', 'sess_dir_001', TRUE, 200),
(3, '/hr/salary/data', 'database', 'export', '2026-08-08 15:05:00', '192.168.1.12', 'sess_dir_001', TRUE, 200),
(3, '/engineering/code', 'file', 'view', '2026-08-12 16:00:00', '192.168.1.12', 'sess_dir_002', TRUE, 200),
(3, '/engineering/config', 'system', 'view', '2026-08-15 14:00:00', '192.168.1.12', 'sess_dir_003', TRUE, 200),
(3, '/marketing/campaigns', 'page', 'view', '2026-08-18 10:00:00', '192.168.1.12', 'sess_dir_004', TRUE, 200),
-- VP self-approval pattern
(2, '/engineering/budget', 'admin', 'view', '2026-08-05 10:00:00', '192.168.1.11', 'sess_vp_001', TRUE, 200),
(2, '/engineering/budget', 'admin', 'update', '2026-08-05 10:10:00', '192.168.1.11', 'sess_vp_001', TRUE, 200),
(2, '/engineering/hiring', 'admin', 'create', '2026-08-10 11:00:00', '192.168.1.11', 'sess_vp_002', TRUE, 201),
(2, '/engineering/hiring', 'admin', 'approve', '2026-08-10 11:05:00', '192.168.1.11', 'sess_vp_002', TRUE, 200),
(2, '/finance/budget', 'database', 'view', '2026-08-15 14:00:00', '192.168.1.11', 'sess_vp_003', TRUE, 200),
(2, '/finance/budget', 'database', 'update', '2026-08-15 14:10:00', '192.168.1.11', 'sess_vp_003', TRUE, 200);

-- Permission changes showing self-approvals
INSERT INTO `permission_changes` (`employee_id`, `changed_by`, `permission_type`, `old_value`, `new_value`, `change_time`, `reason`, `approved_by`, `ip_address`) VALUES
(6, 6, 'role', 'manager', 'manager', '2026-08-10 10:00:00', 'Self-approved salary review access', NULL, '192.168.1.15'),
(6, 6, 'data_access', 'hr_only', 'hr_finance', '2026-08-12 11:00:00', 'Self-approved cross-department access', NULL, '192.168.1.15'),
(3, 3, 'data_access', 'finance', 'all', '2026-08-08 15:00:00', 'Self-approved full access for audit', NULL, '192.168.1.12'),
(2, 2, 'data_access', 'engineering', 'all', '2026-08-15 14:00:00', 'Self-approved budget modification access', NULL, '192.168.1.11'),
(2, 2, 'admin', 'engineering', 'full', '2026-08-10 11:05:00', 'Self-approved hiring authority', NULL, '192.168.1.11');

-- ============================================================
-- CASE-027: The Identity Thief
-- Concurrent logins: same employee, different IPs, within 30 minutes
-- ============================================================
INSERT INTO `login_records` (`employee_id`, `login_time`, `logout_time`, `ip_address`, `location`, `device_info`, `mfa_used`, `status`, `vpn_used`, `geo_location`) VALUES
-- Employee 5 - concurrent sessions from different IPs
(5, '2026-08-12 10:00:00', '2026-08-12 18:00:00', '192.168.1.14', 'Mumbai Office', 'Firefox/Ubuntu', FALSE, 'success', FALSE, 'Mumbai, Maharashtra, India'),
(5, '2026-08-12 10:15:00', '2026-08-12 10:45:00', '203.0.113.50', 'Unknown', 'Chrome/Windows 10', FALSE, 'suspicious', FALSE, 'Unknown'),
-- Employee 4 - concurrent sessions
(4, '2026-08-15 09:30:00', '2026-08-15 19:00:00', '192.168.1.13', 'Mumbai Office', 'Chrome/macOS', TRUE, 'success', FALSE, 'Mumbai, Maharashtra, India'),
(4, '2026-08-15 09:45:00', '2026-08-15 10:15:00', '198.51.100.10', 'Delhi', 'Chrome/Windows 11', FALSE, 'suspicious', FALSE, 'Delhi, India'),
-- Employee 7 - concurrent sessions
(7, '2026-08-18 14:00:00', '2026-08-18 18:00:00', '192.168.1.16', 'Mumbai Office', 'Chrome/Windows 10', TRUE, 'success', FALSE, 'Mumbai, Maharashtra, India'),
(7, '2026-08-18 14:20:00', '2026-08-18 14:50:00', '45.33.32.156', 'Unknown', 'curl/7.68', FALSE, 'suspicious', FALSE, 'Unknown'),
-- Employee 13 - concurrent sessions
(13, '2026-08-19 07:30:00', '2026-08-19 19:00:00', '192.168.1.21', 'Mumbai Office', 'Chrome/Windows 11', TRUE, 'success', FALSE, 'Mumbai, Maharashtra, India'),
(13, '2026-08-19 07:45:00', '2026-08-19 08:15:00', '203.0.113.50', 'Unknown', 'Chrome/Windows 10', FALSE, 'suspicious', FALSE, 'Unknown'),
-- Legitimate concurrent sessions via VPN (noise - not suspicious)
(8, '2026-08-20 10:15:00', '2026-08-20 19:00:00', '100.64.0.50', 'Remote - Bangalore', 'Chrome/Windows 11', TRUE, 'success', TRUE, 'Bangalore, Karnataka, India'),
(8, '2026-08-20 10:20:00', '2026-08-20 10:25:00', '192.168.1.30', 'Mumbai Office', 'Chrome/Windows 11', TRUE, 'success', FALSE, 'Mumbai, Maharashtra, India'),
-- Normal non-concurrent logins (noise)
(1, '2026-08-12 09:00:00', '2026-08-12 18:00:00', '192.168.1.10', 'Mumbai Office', 'Chrome/Windows 11', TRUE, 'success', FALSE, 'Mumbai, Maharashtra, India'),
(2, '2026-08-15 08:45:00', '2026-08-15 18:30:00', '192.168.1.11', 'Mumbai Office', 'Chrome/Windows 11', TRUE, 'success', FALSE, 'Mumbai, Maharashtra, India'),
(3, '2026-08-18 09:15:00', '2026-08-18 17:45:00', '192.168.1.12', 'Mumbai Office', 'Edge/Windows 11', TRUE, 'success', FALSE, 'Mumbai, Maharashtra, India'),
(6, '2026-08-19 08:30:00', '2026-08-19 17:30:00', '192.168.1.15', 'Mumbai Office', 'Chrome/Windows 11', TRUE, 'success', FALSE, 'Mumbai, Maharashtra, India');
