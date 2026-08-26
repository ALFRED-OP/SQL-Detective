USE `employeeportal`;

-- Seed departments
INSERT INTO `departments` (`id`, `name`, `code`, `budget`) VALUES
(1, 'Engineering', 'ENG', 12000000.00),
(2, 'Human Resources', 'HR', 3000000.00),
(3, 'Finance', 'FIN', 4000000.00),
(4, 'Marketing', 'MKT', 5000000.00),
(5, 'Sales', 'SLS', 6000000.00),
(6, 'Operations', 'OPS', 3500000.00),
(7, 'Legal', 'LEG', 2500000.00),
(8, 'Executive', 'EXE', 8000000.00);

-- Seed employees
INSERT INTO `employees` (`id`, `emp_code`, `first_name`, `last_name`, `email`, `department_id`, `position`, `level`, `hire_date`, `status`, `last_login`, `failed_logins`) VALUES
(1, 'E001', 'Raj', 'Malhotra', 'raj.malhotra@corp.com', 8, 'CEO', 'c_suite', '2015-01-01', 'active', '2026-08-20 09:00:00', 0),
(2, 'E002', 'Priya', 'Nair', 'priya.nair@corp.com', 1, 'VP Engineering', 'vp', '2016-03-15', 'active', '2026-08-20 08:45:00', 0),
(3, 'E003', 'Amit', 'Tiwari', 'amit.tiwari@corp.com', 3, 'Finance Director', 'director', '2017-06-20', 'active', '2026-08-20 09:15:00', 0),
(4, 'E004', 'Sneha', 'Kapoor', 'sneha.kapoor@corp.com', 1, 'Senior Developer', 'senior', '2019-02-10', 'active', '2026-08-20 09:30:00', 0),
(5, 'E005', 'Vikram', 'Singh', 'vikram.singh@corp.com', 1, 'Junior Developer', 'junior', '2022-07-01', 'active', '2026-08-20 10:00:00', 0),
(6, 'E006', 'Neha', 'Gupta', 'neha.gupta@corp.com', 2, 'HR Manager', 'manager', '2018-04-15', 'active', '2026-08-20 08:30:00', 0),
(7, 'E007', 'Arjun', 'Reddy', 'arjun.reddy@corp.com', 4, 'Marketing Lead', 'lead', '2020-01-10', 'active', '2026-08-20 09:45:00', 0),
(8, 'E008', 'Deepa', 'Menon', 'deepa.menon@corp.com', 5, 'Sales Executive', 'mid', '2021-05-20', 'active', '2026-08-20 10:15:00', 0),
(9, 'E009', 'Sanjay', 'Patel', 'sanjay.patel@corp.com', 1, 'DevOps Engineer', 'mid', '2020-08-01', 'active', '2026-08-20 08:00:00', 0),
(10, 'E010', 'Kavita', 'Joshi', 'kavita.joshi@corp.com', 6, 'Operations Coordinator', 'mid', '2021-09-15', 'active', '2026-08-20 09:00:00', 0),
(11, 'E011', 'Rohan', 'Mehta', 'rohan.mehta@corp.com', 3, 'Accountant', 'mid', '2020-03-10', 'active', '2026-08-20 09:30:00', 0),
(12, 'E012', 'Pooja', 'Sharma', 'pooja.sharma@corp.com', 7, 'Legal Counsel', 'senior', '2019-11-01', 'active', '2026-08-20 08:15:00', 0),
(13, 'E013', 'Manish', 'Yadav', 'manish.yadav@corp.com', 1, 'Security Analyst', 'senior', '2019-07-15', 'active', '2026-08-20 07:30:00', 0),
(14, 'E014', 'Sunita', 'Rao', 'sunita.rao@corp.com', 3, 'Financial Analyst', 'junior', '2023-01-10', 'active', '2026-08-20 09:45:00', 0),
(15, 'E015', 'Kiran', 'Deshmukh', 'kiran.deshmukh@corp.com', 1, 'Lead Developer', 'lead', '2018-06-01', 'active', '2026-08-20 08:30:00', 0),
(16, 'E016', 'Anita', 'Chauhan', 'anita.chauhan@corp.com', 4, 'Content Writer', 'junior', '2023-06-15', 'active', '2026-08-20 10:00:00', 0),
(17, 'E017', 'Deepak', 'Jain', 'deepak.jain@corp.com', 5, 'Sales Manager', 'manager', '2019-04-20', 'active', '2026-08-20 09:00:00', 0),
(18, 'E018', 'Meera', 'Iyer', 'meera.iyer@corp.com', 8, 'Executive Assistant', 'mid', '2020-02-15', 'active', '2026-08-20 08:00:00', 0),
(19, 'E019', 'Suresh', 'Banerjee', 'suresh.banerjee@corp.com', 6, 'Operations Manager', 'manager', '2018-09-01', 'active', '2026-08-20 08:45:00', 0),
(20, 'E020', 'Nitin', 'Kulkarni', 'nitin.kulkarni@corp.com', 1, 'Intern', 'junior', '2026-01-05', 'active', '2026-08-20 10:30:00', 0);

-- Seed IP addresses
INSERT INTO `ip_addresses` (`ip_address`, `ip_type`, `location`, `country`, `isp`, `is_vpn`, `is_tor`, `is_blacklisted`, `threat_score`, `first_seen`, `last_seen`) VALUES
('192.168.1.0/24', 'ipv4', 'Mumbai Office', 'India', 'Corporate LAN', FALSE, FALSE, FALSE, 0, '2026-01-01 00:00:00', '2026-08-20 23:59:59'),
('10.0.0.0/8', 'ipv4', 'Internal Network', 'India', 'Corporate VPN', FALSE, FALSE, FALSE, 0, '2026-01-01 00:00:00', '2026-08-20 23:59:59'),
('203.0.113.1', 'ipv4', 'Mumbai', 'India', 'Jio Fiber', FALSE, FALSE, FALSE, 5, '2026-01-01 00:00:00', '2026-08-20 23:59:59'),
('203.0.113.50', 'ipv4', 'Unknown', 'India', 'Airtel', FALSE, FALSE, FALSE, 15, '2026-07-01 00:00:00', '2026-08-15 23:59:59'),
('198.51.100.10', 'ipv4', 'Delhi', 'India', 'BSNL', FALSE, FALSE, FALSE, 10, '2026-06-01 00:00:00', '2026-08-10 23:59:59'),
('198.51.100.75', 'ipv4', 'Unknown', 'Unknown', 'Tor Exit Node', FALSE, TRUE, TRUE, 85, '2026-08-10 00:00:00', '2026-08-18 23:59:59'),
('100.64.0.1', 'ipv4', 'Bangalore', 'India', 'Corporate VPN', TRUE, FALSE, FALSE, 0, '2026-01-01 00:00:00', '2026-08-20 23:59:59'),
('45.33.32.156', 'ipv4', 'Unknown', 'USA', 'DigitalOcean', FALSE, FALSE, TRUE, 60, '2026-08-01 00:00:00', '2026-08-12 23:59:59');

-- Seed login records
INSERT INTO `login_records` (`employee_id`, `login_time`, `logout_time`, `ip_address`, `location`, `device_info`, `mfa_used`, `status`, `vpn_used`, `geo_location`) VALUES
-- Normal logins
(1, '2026-08-20 09:00:00', '2026-08-20 18:00:00', '192.168.1.10', 'Mumbai Office', 'Chrome/Windows 11', TRUE, 'success', FALSE, 'Mumbai, Maharashtra, India'),
(2, '2026-08-20 08:45:00', '2026-08-20 18:30:00', '192.168.1.11', 'Mumbai Office', 'Chrome/Windows 11', TRUE, 'success', FALSE, 'Mumbai, Maharashtra, India'),
(3, '2026-08-20 09:15:00', '2026-08-20 17:45:00', '192.168.1.12', 'Mumbai Office', 'Edge/Windows 11', TRUE, 'success', FALSE, 'Mumbai, Maharashtra, India'),
(4, '2026-08-20 09:30:00', '2026-08-20 19:00:00', '192.168.1.13', 'Mumbai Office', 'Chrome/macOS', TRUE, 'success', FALSE, 'Mumbai, Maharashtra, India'),
(5, '2026-08-20 10:00:00', '2026-08-20 18:00:00', '192.168.1.14', 'Mumbai Office', 'Firefox/Ubuntu', FALSE, 'success', FALSE, 'Mumbai, Maharashtra, India'),
(6, '2026-08-20 08:30:00', '2026-08-20 17:30:00', '192.168.1.15', 'Mumbai Office', 'Chrome/Windows 11', TRUE, 'success', FALSE, 'Mumbai, Maharashtra, India'),
(7, '2026-08-20 09:45:00', '2026-08-20 18:15:00', '192.168.1.16', 'Mumbai Office', 'Chrome/Windows 10', TRUE, 'success', FALSE, 'Mumbai, Maharashtra, India'),
(8, '2026-08-20 10:15:00', '2026-08-20 19:00:00', '100.64.0.50', 'Remote - Bangalore', 'Chrome/Windows 11', TRUE, 'success', TRUE, 'Bangalore, Karnataka, India'),
(9, '2026-08-20 08:00:00', '2026-08-20 20:00:00', '192.168.1.17', 'Mumbai Office', 'Terminal/Linux', TRUE, 'success', FALSE, 'Mumbai, Maharashtra, India'),
(10, '2026-08-20 09:00:00', '2026-08-20 17:00:00', '192.168.1.18', 'Mumbai Office', 'Chrome/Windows 11', TRUE, 'success', FALSE, 'Mumbai, Maharashtra, India'),
-- Suspicious logins
(5, '2026-08-12 02:15:00', '2026-08-12 02:45:00', '198.51.100.75', 'Unknown', 'Chrome/Windows 10', FALSE, 'suspicious', FALSE, 'Unknown'),
(5, '2026-08-14 03:20:00', '2026-08-14 03:50:00', '198.51.100.75', 'Unknown', 'Chrome/Windows 10', FALSE, 'suspicious', FALSE, 'Unknown'),
(5, '2026-08-16 02:30:00', '2026-08-16 03:00:00', '203.0.113.50', 'Unknown', 'Chrome/Windows 10', FALSE, 'suspicious', FALSE, 'Unknown'),
-- Failed login attempts
(5, '2026-08-12 02:10:00', NULL, '198.51.100.75', 'Unknown', 'Chrome/Windows 10', FALSE, 'failed', FALSE, 'Unknown'),
(5, '2026-08-14 03:15:00', NULL, '198.51.100.75', 'Unknown', 'Chrome/Windows 10', FALSE, 'failed', FALSE, 'Unknown'),
(6, '2026-08-15 14:00:00', NULL, '45.33.32.156', 'Unknown', 'curl/7.68', FALSE, 'failed', FALSE, 'Unknown'),
-- Normal continued
(11, '2026-08-20 09:30:00', '2026-08-20 17:00:00', '192.168.1.19', 'Mumbai Office', 'Chrome/Windows 10', TRUE, 'success', FALSE, 'Mumbai, Maharashtra, India'),
(12, '2026-08-20 08:15:00', '2026-08-20 18:00:00', '192.168.1.20', 'Mumbai Office', 'Safari/macOS', TRUE, 'success', FALSE, 'Mumbai, Maharashtra, India'),
(13, '2026-08-20 07:30:00', '2026-08-20 19:00:00', '192.168.1.21', 'Mumbai Office', 'Chrome/Windows 11', TRUE, 'success', FALSE, 'Mumbai, Maharashtra, India');

-- Seed access logs
INSERT INTO `access_logs` (`employee_id`, `resource`, `resource_type`, `action`, `access_time`, `ip_address`, `session_id`, `success`, `response_code`) VALUES
-- Normal access
(1, '/dashboard', 'page', 'view', '2026-08-20 09:00:05', '192.168.1.10', 'sess_001_abc', TRUE, 200),
(2, '/api/projects', 'api', 'view', '2026-08-20 08:45:10', '192.168.1.11', 'sess_002_def', TRUE, 200),
(3, '/reports/financial', 'page', 'view', '2026-08-20 09:15:15', '192.168.1.12', 'sess_003_ghi', TRUE, 200),
(4, '/api/code/commit', 'api', 'create', '2026-08-20 09:30:20', '192.168.1.13', 'sess_004_jkl', TRUE, 201),
(5, '/dashboard', 'page', 'view', '2026-08-20 10:00:05', '192.168.1.14', 'sess_005_mno', TRUE, 200),
(6, '/hr/employees', 'page', 'view', '2026-08-20 08:30:10', '192.168.1.15', 'sess_006_pqr', TRUE, 200),
(7, '/marketing/campaigns', 'page', 'view', '2026-08-20 09:45:15', '192.168.1.16', 'sess_007_stu', TRUE, 200),
-- Suspicious access from junior developer
(5, '/admin/users', 'admin', 'view', '2026-08-12 02:20:00', '198.51.100.75', 'sess_susp_001', TRUE, 200),
(5, '/admin/users/role', 'admin', 'update', '2026-08-12 02:25:00', '198.51.100.75', 'sess_susp_001', TRUE, 200),
(5, '/api/salary/export', 'api', 'export', '2026-08-12 02:30:00', '198.51.100.75', 'sess_susp_001', TRUE, 200),
(5, '/admin/system/config', 'admin', 'view', '2026-08-12 02:35:00', '198.51.100.75', 'sess_susp_001', TRUE, 200),
(5, '/admin/users', 'admin', 'view', '2026-08-14 03:25:00', '198.51.100.75', 'sess_susp_002', TRUE, 200),
(5, '/api/financial/export', 'api', 'export', '2026-08-14 03:30:00', '198.51.100.75', 'sess_susp_002', TRUE, 200),
(5, '/admin/audit_logs', 'admin', 'view', '2026-08-16 02:35:00', '203.0.113.50', 'sess_susp_003', TRUE, 200),
(5, '/api/database/query', 'database', 'execute', '2026-08-16 02:40:00', '203.0.113.50', 'sess_susp_003', TRUE, 200),
-- Failed access attempts
(5, '/admin/super_admin', 'admin', 'view', '2026-08-12 02:22:00', '198.51.100.75', 'sess_susp_001', FALSE, 403),
(6, '/admin/users', 'admin', 'view', '2026-08-15 14:05:00', '45.33.32.156', 'sess_fail_001', FALSE, 401);

-- Seed permission changes
INSERT INTO `permission_changes` (`employee_id`, `changed_by`, `permission_type`, `old_value`, `new_value`, `change_time`, `reason`, `ip_address`) VALUES
(5, 5, 'role', 'junior', 'admin', '2026-08-12 02:25:00', 'Self-escalated privileges', '198.51.100.75'),
(5, 5, 'data_access', 'basic', 'full', '2026-08-12 02:26:00', 'Self-modified access level', '198.51.100.75'),
(5, 5, 'admin', 'none', 'full', '2026-08-12 02:27:00', 'Self-granted admin access', '198.51.100.75'),
(2, 1, 'role', 'senior', 'vp', '2026-03-15 10:00:00', 'Promoted to VP Engineering', '192.168.1.10'),
(3, 1, 'role', 'senior', 'director', '2026-06-20 10:00:00', 'Promoted to Finance Director', '192.168.1.10');

-- Seed audit trail
INSERT INTO `audit_trail` (`table_name`, `record_id`, `action`, `old_values`, `new_values`, `performed_by`, `performed_at`, `ip_address`) VALUES
('employees', 5, 'update', '{"role": "junior"}', '{"role": "admin"}', 5, '2026-08-12 02:25:00', '198.51.100.75'),
('employees', 5, 'update', '{"data_access": "basic"}', '{"data_access": "full"}', 5, '2026-08-12 02:26:00', '198.51.100.75'),
('employees', 2, 'update', '{"role": "senior"}', '{"role": "vp"}', 1, '2026-03-15 10:00:00', '192.168.1.10'),
('employees', 3, 'update', '{"role": "senior"}', '{"role": "director"}', 1, '2026-06-20 10:00:00', '192.168.1.10');

-- Seed system events
INSERT INTO `system_events` (`event_type`, `employee_id`, `event_time`, `ip_address`, `description`, `severity`, `details`) VALUES
('login', 1, '2026-08-20 09:00:00', '192.168.1.10', 'Normal login', 'info', '{"method": "password+mfa"}'),
('login', 2, '2026-08-20 08:45:00', '192.168.1.11', 'Normal login', 'info', '{"method": "password+mfa"}'),
('login', 5, '2026-08-20 10:00:00', '192.168.1.14', 'Normal login', 'info', '{"method": "password"}'),
('security_alert', 5, '2026-08-12 02:20:00', '198.51.100.75', 'Privilege escalation detected', 'critical', '{"from_role": "junior", "to_role": "admin", "method": "self"}'),
('security_alert', 5, '2026-08-12 02:25:00', '198.51.100.75', 'Unauthorized admin access attempt', 'critical', '{"resource": "/admin/users", "result": "success"}'),
('data_export', 5, '2026-08-12 02:30:00', '198.51.100.75', 'Salary data exported', 'high', '{"file": "salary_export.csv", "records": 500}'),
('data_export', 5, '2026-08-14 03:30:00', '198.51.100.75', 'Financial data exported', 'high', '{"file": "financial_export.csv", "records": 1200}'),
('security_alert', 5, '2026-08-16 02:40:00', '203.0.113.50', 'Database query from suspicious IP', 'critical', '{"query": "SELECT * FROM salary_data", "records": 500}'),
('password_change', 5, '2026-08-10 14:00:00', '192.168.1.14', 'Password changed', 'info', '{"method": "self_service"}'),
('login', 6, '2026-08-15 14:00:00', '45.33.32.156', 'Failed login attempt from blacklisted IP', 'high', '{"attempts": 3, "result": "failed"}'),
('file_upload', 9, '2026-08-20 11:00:00', '192.168.1.17', 'Configuration file uploaded', 'info', '{"file": "nginx.conf", "size": 4096}'),
('api_call', 4, '2026-08-20 09:35:00', '192.168.1.13', 'API commit created', 'info', '{"repo": "main-app", "commits": 3}');