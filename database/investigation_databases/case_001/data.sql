USE `corporate_finance`;

-- Seed departments
INSERT INTO `departments` (`id`, `name`, `code`, `description`, `budget`) VALUES
(1, 'Finance', 'FIN', 'Financial operations and accounting', 5000000.00),
(2, 'Human Resources', 'HR', 'Employee management and recruitment', 2000000.00),
(3, 'Information Technology', 'IT', 'Technology infrastructure and development', 8000000.00),
(4, 'Operations', 'OPS', 'Day-to-day business operations', 4000000.00),
(5, 'Sales', 'SLS', 'Revenue generation and client relations', 3500000.00),
(6, 'Marketing', 'MKT', 'Brand management and advertising', 3000000.00),
(7, 'Legal', 'LEG', 'Legal affairs and compliance', 2500000.00),
(8, 'Executive', 'EXE', 'Senior leadership team', 10000000.00);

-- Seed employees
INSERT INTO `employees` (`id`, `employee_code`, `first_name`, `last_name`, `email`, `phone`, `department_id`, `position`, `hire_date`, `salary`, `manager_id`, `status`) VALUES
(1, 'EMP001', 'Priya', 'Sharma', 'priya.sharma@corp.com', '+91-9876543201', 8, 'Chief Financial Officer', '2015-03-15', 2500000.00, NULL, 'active'),
(2, 'EMP002', 'Rajesh', 'Kumar', 'rajesh.kumar@corp.com', '+91-9876543202', 8, 'Chief Executive Officer', '2014-01-10', 3000000.00, NULL, 'active'),
(3, 'EMP003', 'Anita', 'Desai', 'anita.desai@corp.com', '+91-9876543203', 1, 'Senior Accountant', '2018-06-20', 800000.00, 1, 'active'),
(4, 'EMP004', 'Vikram', 'Patel', 'vikram.patel@corp.com', '+91-9876543204', 1, 'Financial Analyst', '2020-02-15', 600000.00, 3, 'active'),
(5, 'EMP005', 'Sunita', 'Reddy', 'sunita.reddy@corp.com', '+91-9876543205', 1, 'Accounts Clerk', '2021-08-01', 350000.00, 3, 'active'),
(6, 'EMP006', 'Amit', 'Singh', 'amit.singh@corp.com', '+91-9876543206', 3, 'IT Director', '2017-04-12', 1200000.00, 2, 'active'),
(7, 'EMP007', 'Deepa', 'Nair', 'deepa.nair@corp.com', '+91-9876543207', 3, 'Database Administrator', '2019-09-01', 750000.00, 6, 'active'),
(8, 'EMP008', 'Ravi', 'Verma', 'ravi.verma@corp.com', '+91-9876543208', 4, 'Operations Manager', '2016-11-20', 900000.00, 2, 'active'),
(9, 'EMP009', 'Kavita', 'Joshi', 'kavita.joshi@corp.com', '+91-9876543209', 2, 'HR Manager', '2018-03-10', 700000.00, 2, 'active'),
(10, 'EMP010', 'Manish', 'Gupta', 'manish.gupta@corp.com', '+91-9876543210', 5, 'Sales Director', '2017-07-15', 1100000.00, 2, 'active'),
(11, 'EMP011', 'Neha', 'Mishra', 'neha.mishra@corp.com', '+91-9876543211', 6, 'Marketing Manager', '2019-01-05', 650000.00, 2, 'active'),
(12, 'EMP012', 'Suresh', 'Iyer', 'suresh.iyer@corp.com', '+91-9876543212', 7, 'Legal Counsel', '2016-05-18', 1500000.00, 2, 'active'),
(13, 'EMP013', 'Rohan', 'Mehta', 'rohan.mehta@corp.com', '+91-9876543213', 1, 'Junior Accountant', '2022-01-10', 300000.00, 3, 'active'),
(14, 'EMP014', 'Pooja', 'Thakur', 'pooja.thakur@corp.com', '+91-9876543214', 3, 'System Administrator', '2020-06-15', 550000.00, 6, 'active'),
(15, 'EMP015', 'Arjun', 'Rao', 'arjun.rao@corp.com', '+91-9876543215', 4, 'Logistics Coordinator', '2021-03-20', 320000.00, 8, 'active'),
(16, 'EMP016', 'Meera', 'Pillai', 'meera.pillai@corp.com', '+91-9876543216', 5, 'Sales Executive', '2020-09-01', 400000.00, 10, 'active'),
(17, 'EMP017', 'Sanjay', 'Bhatt', 'sanjay.bhatt@corp.com', '+91-9876543217', 8, 'Executive Assistant', '2019-04-15', 450000.00, 2, 'active'),
(18, 'EMP018', 'Divya', 'Chopra', 'divya.chopra@corp.com', '+91-9876543218', 6, 'Content Strategist', '2021-07-10', 380000.00, 11, 'active'),
(19, 'EMP019', 'Kiran', 'Deshpande', 'kiran.deshpande@corp.com', '+91-9876543219', 1, 'Tax Consultant', '2020-11-01', 500000.00, 1, 'active'),
(20, 'EMP020', 'Nitin', 'Kulkarni', 'nitin.kulkarni@corp.com', '+91-9876543220', 3, 'Security Analyst', '2019-08-20', 680000.00, 6, 'active');

-- Seed locations
INSERT INTO `locations` (`id`, `name`, `address`, `city`, `state`, `country`, `ip_range_start`, `ip_range_end`, `timezone`, `is_office`) VALUES
(1, 'Mumbai HQ', '45th Floor, One World Center, Senapati Bapat Marg, Lower Parel', 'Mumbai', 'Maharashtra', 'India', '192.168.1.1', '192.168.1.254', 'Asia/Kolkata', TRUE),
(2, 'Delhi Branch', '12th Floor, DLF Place, Saket', 'New Delhi', 'Delhi', 'India', '192.168.2.1', '192.168.2.254', 'Asia/Kolkata', TRUE),
(3, 'Bangalore Office', '8th Floor, Prestige Tech Park, Outer Ring Road', 'Bangalore', 'Karnataka', 'India', '192.168.3.1', '192.168.3.254', 'Asia/Kolkata', TRUE),
(4, 'Chennai Branch', '6th Floor, Tidel Park, Taramani', 'Chennai', 'Tamil Nadu', 'India', '192.168.4.1', '192.168.4.254', 'Asia/Kolkata', TRUE),
(5, 'Offsite Location', NULL, NULL, NULL, 'India', NULL, NULL, 'Asia/Kolkata', FALSE);

-- Seed devices
INSERT INTO `devices` (`device_id`, `employee_id`, `device_type`, `os`, `browser`, `mac_address`, `is_company_issued`, `first_seen`, `last_seen`, `status`) VALUES
('DEV-LAPTOP-001', 1, 'laptop', 'Windows 11', 'Chrome', 'AA:BB:CC:DD:01:01', TRUE, '2023-01-15 09:00:00', '2026-08-20 18:30:00', 'active'),
('DEV-LAPTOP-002', 3, 'laptop', 'Windows 11', 'Chrome', 'AA:BB:CC:DD:01:02', TRUE, '2023-01-15 09:00:00', '2026-08-20 17:45:00', 'active'),
('DEV-LAPTOP-003', 4, 'laptop', 'Windows 10', 'Edge', 'AA:BB:CC:DD:01:03', TRUE, '2023-01-15 09:00:00', '2026-08-20 18:00:00', 'active'),
('DEV-LAPTOP-004', 5, 'laptop', 'Windows 10', 'Chrome', 'AA:BB:CC:DD:01:04', TRUE, '2023-01-15 09:00:00', '2026-08-20 17:30:00', 'active'),
('DEV-LAPTOP-005', 6, 'laptop', 'macOS Ventura', 'Safari', 'AA:BB:CC:DD:01:05', TRUE, '2023-01-15 09:00:00', '2026-08-20 19:00:00', 'active'),
('DEV-LAPTOP-006', 7, 'laptop', 'Windows 11', 'Chrome', 'AA:BB:CC:DD:01:06', TRUE, '2023-01-15 09:00:00', '2026-08-20 18:15:00', 'active'),
('DEV-MOBILE-001', 1, 'mobile', 'Android 14', 'Chrome', 'BB:CC:DD:EE:01:01', FALSE, '2023-01-15 09:00:00', '2026-08-20 20:00:00', 'active'),
('DEV-MOBILE-002', 5, 'mobile', 'iOS 17', 'Safari', 'BB:CC:DD:EE:01:02', FALSE, '2023-01-15 09:00:00', '2026-08-20 19:30:00', 'active'),
('DEV-LAPTOP-007', 14, 'laptop', 'Ubuntu 22.04', 'Firefox', 'AA:BB:CC:DD:01:07', TRUE, '2023-01-15 09:00:00', '2026-08-20 18:00:00', 'active'),
('DEV-LAPTOP-008', 20, 'laptop', 'Windows 11', 'Chrome', 'AA:BB:CC:DD:01:08', TRUE, '2023-01-15 09:00:00', '2026-08-20 22:00:00', 'active'),
('DEV-SERVER-001', NULL, 'server', 'Windows Server 2022', NULL, 'CC:DD:EE:FF:01:01', TRUE, '2023-01-15 09:00:00', '2026-08-20 23:59:00', 'active'),
('DEV-UNKNOWN-001', NULL, 'unknown', 'Windows 10', 'Chrome', 'DD:EE:FF:00:01:01', FALSE, '2026-08-01 03:22:00', '2026-08-15 04:15:00', 'active');

-- Seed bank accounts
INSERT INTO `bank_accounts` (`account_number`, `account_type`, `account_name`, `owner_type`, `owner_id`, `bank_name`, `branch`, `currency`, `balance`, `is_active`) VALUES
('CORP-HDFC-001', 'operating', 'Main Operating Account', 'company', NULL, 'HDFC Bank', 'Mumbai HQ', 'INR', 125000000.00, TRUE),
('CORP-HDFC-002', 'payroll', 'Payroll Account', 'company', NULL, 'HDFC Bank', 'Mumbai HQ', 'INR', 15000000.00, TRUE),
('CORP-ICICI-001', 'investment', 'Investment Portfolio', 'company', NULL, 'ICICI Bank', 'Mumbai HQ', 'INR', 85000000.00, TRUE),
('CORP-AXIS-001', 'savings', 'Reserve Fund', 'company', NULL, 'Axis Bank', 'Delhi Branch', 'INR', 45000000.00, TRUE),
('EMP-001-SAL', 'checking', 'Priya Sharma Salary', 'employee', 1, 'HDFC Bank', 'Lower Parel', 'INR', 1250000.00, TRUE),
('EMP-003-SAL', 'checking', 'Anita Desai Salary', 'employee', 3, 'ICICI Bank', 'Bandra', 'INR', 400000.00, TRUE),
('EMP-005-SAL', 'checking', 'Sunita Reddy Salary', 'employee', 5, 'SBI', 'Andheri', 'INR', 175000.00, TRUE),
('EMP-005-SAV', 'savings', 'Sunita Reddy Savings', 'employee', 5, 'SBI', 'Andheri', 'INR', 850000.00, TRUE),
('EMP-013-SAL', 'checking', 'Rohan Mehta Salary', 'employee', 13, 'HDFC Bank', 'Borivali', 'INR', 150000.00, TRUE),
('EMP-014-SAL', 'checking', 'Pooja Thakur Salary', 'employee', 14, 'Kotak Bank', 'Whitefield', 'INR', 275000.00, TRUE),
('EXT-FORGE-001', 'checking', 'Forge Consulting Pvt Ltd', 'vendor', NULL, 'Yes Bank', 'Bandra Kurla Complex', 'INR', 5200000.00, TRUE),
('EXT-MERIDIAN-001', 'checking', 'Meridian Services LLC', 'external', NULL, 'Citibank', 'Nariman Point', 'INR', 3100000.00, TRUE),
('EXT-VOID-001', 'checking', 'Void Technologies Inc', 'external', NULL, 'Standard Chartered', 'Worli', 'INR', 9800000.00, TRUE);

-- Seed transactions (suspicious activity around Aug 2026)
INSERT INTO `transactions` (`transaction_id`, `transaction_date`, `amount`, `currency`, `transaction_type`, `status`, `from_account_id`, `to_account_id`, `initiated_by`, `approved_by`, `description`, `reference`, `ip_address`, `device_id`, `location_id`) VALUES
-- Normal transactions
('TXN-2026-0001', '2026-08-01 10:15:00', 2500000.00, 'INR', 'transfer', 'completed', 1, 2, 3, 1, 'Monthly payroll processing', 'PAY-AUG-001', '192.168.1.45', 'DEV-LAPTOP-002', 1),
('TXN-2026-0002', '2026-08-01 14:30:00', 500000.00, 'INR', 'payment', 'completed', 1, 11, 8, 1, 'Vendor payment - Forge Consulting', 'INV-FC-2026-045', '192.168.1.88', 'DEV-LAPTOP-005', 1),
('TXN-2026-0003', '2026-08-02 09:00:00', 1500000.00, 'INR', 'transfer', 'completed', 3, 4, 4, 1, 'Investment allocation Q3', 'INV-Q3-001', '192.168.1.46', 'DEV-LAPTOP-003', 1),
('TXN-2026-0004', '2026-08-03 11:45:00', 75000.00, 'INR', 'payment', 'completed', 1, 11, 5, 3, 'Office supplies procurement', 'PO-2026-0892', '192.168.1.47', 'DEV-LAPTOP-004', 1),
-- Suspicious transactions
('TXN-2026-0005', '2026-08-05 02:15:00', 1000000.00, 'INR', 'transfer', 'completed', 1, 13, 5, NULL, 'Emergency vendor payment', 'EMRG-2026-001', '203.0.113.42', 'DEV-UNKNOWN-001', 5),
('TXN-2026-0006', '2026-08-07 02:30:00', 750000.00, 'INR', 'transfer', 'completed', 1, 13, 5, NULL, 'Urgent processing fee', 'UPF-2026-001', '203.0.113.42', 'DEV-UNKNOWN-001', 5),
('TXN-2026-0007', '2026-08-10 02:45:00', 500000.00, 'INR', 'transfer', 'completed', 1, 13, 5, NULL, 'Consulting payment', 'CONS-2026-001', '198.51.100.15', 'DEV-UNKNOWN-001', 5),
-- More suspicious
('TXN-2026-0008', '2026-08-12 02:00:00', 1250000.00, 'INR', 'transfer', 'completed', 1, 13, 5, NULL, 'IT infrastructure upgrade', 'ITUP-2026-001', '198.51.100.15', 'DEV-UNKNOWN-001', 5),
('TXN-2026-0009', '2026-08-15 02:20:00', 350000.00, 'INR', 'transfer', 'completed', 1, 13, 5, NULL, 'Legal compliance fee', 'LCF-2026-001', '203.0.113.42', 'DEV-UNKNOWN-001', 5),
-- Normal
('TXN-2026-0010', '2026-08-15 10:00:00', 300000.00, 'INR', 'payment', 'completed', 1, 12, 8, 1, 'Marketing campaign payment', 'MKT-AUG-001', '192.168.1.88', 'DEV-LAPTOP-005', 1),
('TXN-2026-0011', '2026-08-16 09:30:00', 250000.00, 'INR', 'transfer', 'completed', 2, 5, 3, 1, 'Salary credit - Anita Desai', 'SAL-AUG-003', '192.168.1.45', 'DEV-LAPTOP-002', 1),
-- Flagged
('TXN-2026-0012', '2026-08-18 02:35:00', 2200000.00, 'INR', 'transfer', 'flagged', 1, 13, 5, NULL, 'Board-approved acquisition', 'ACQ-2026-001', '192.168.1.47', 'DEV-UNKNOWN-001', 5),
-- Normal
('TXN-2026-0013', '2026-08-19 11:15:00', 150000.00, 'INR', 'payment', 'completed', 1, 11, 3, 1, 'Office rent - September', 'RENT-SEP-001', '192.168.1.45', 'DEV-LAPTOP-002', 1),
('TXN-2026-0014', '2026-08-20 14:00:00', 50000.00, 'INR', 'refund', 'completed', 12, 1, 12, NULL, 'Refund from Meridian Services', 'REF-MER-001', '192.168.2.15', 'DEV-LAPTOP-005', 2),
-- Another suspicious one
('TXN-2026-0015', '2026-08-22 02:50:00', 950000.00, 'INR', 'transfer', 'flagged', 1, 13, 5, NULL, 'Emergency infrastructure', 'EMRG-INF-001', '192.168.1.47', 'DEV-UNKNOWN-001', 5);

-- Seed login logs (showing suspicious patterns)
INSERT INTO `login_logs` (`employee_id`, `login_time`, `logout_time`, `ip_address`, `device_id`, `location_id`, `status`) VALUES
-- Sunita Reddy (employee 5) - suspicious logins
(5, '2026-08-05 01:50:00', '2026-08-05 02:30:00', '203.0.113.42', 'DEV-UNKNOWN-001', 5, 'success'),
(5, '2026-08-07 02:10:00', '2026-08-07 02:45:00', '203.0.113.42', 'DEV-UNKNOWN-001', 5, 'success'),
(5, '2026-08-10 02:25:00', '2026-08-10 03:00:00', '198.51.100.15', 'DEV-UNKNOWN-001', 5, 'success'),
(5, '2026-08-12 01:40:00', '2026-08-12 02:20:00', '198.51.100.15', 'DEV-UNKNOWN-001', 5, 'success'),
(5, '2026-08-15 02:00:00', '2026-08-15 02:35:00', '203.0.113.42', 'DEV-UNKNOWN-001', 5, 'success'),
(5, '2026-08-18 02:15:00', '2026-08-18 02:55:00', '203.0.113.42', 'DEV-UNKNOWN-001', 5, 'success'),
(5, '2026-08-22 02:30:00', '2026-08-22 03:10:00', '192.168.1.47', 'DEV-UNKNOWN-001', 1, 'success'),
-- Normal logins
(5, '2026-08-01 09:00:00', '2026-08-01 17:30:00', '192.168.1.47', 'DEV-LAPTOP-004', 1, 'success'),
(5, '2026-08-02 09:15:00', '2026-08-02 17:45:00', '192.168.1.47', 'DEV-LAPTOP-004', 1, 'success'),
(5, '2026-08-03 08:55:00', '2026-08-03 17:20:00', '192.168.1.47', 'DEV-LAPTOP-004', 1, 'success'),
(5, '2026-08-04 09:05:00', '2026-08-04 17:35:00', '192.168.1.47', 'DEV-LAPTOP-004', 1, 'success'),
-- Failed attempts
(5, '2026-08-05 01:45:00', NULL, '203.0.113.42', 'DEV-UNKNOWN-001', 5, 'failed'),
(5, '2026-08-07 02:05:00', NULL, '203.0.113.42', 'DEV-UNKNOWN-001', 5, 'failed'),
-- Other employees normal
(1, '2026-08-01 08:30:00', '2026-08-01 18:00:00', '192.168.1.45', 'DEV-LAPTOP-001', 1, 'success'),
(3, '2026-08-01 09:00:00', '2026-08-01 17:45:00', '192.168.1.45', 'DEV-LAPTOP-002', 1, 'success'),
(4, '2026-08-01 09:15:00', '2026-08-01 18:00:00', '192.168.1.46', 'DEV-LAPTOP-003', 1, 'success');

-- Seed access logs
INSERT INTO `access_logs` (`employee_id`, `resource_type`, `resource_name`, `action`, `access_time`, `ip_address`, `device_id`, `location_id`, `success`) VALUES
-- Sunita Reddy accessing finance system
(5, 'system', 'Finance Management System', 'read', '2026-08-05 01:55:00', '203.0.113.42', 'DEV-UNKNOWN-001', 5, TRUE),
(5, 'database', 'corporate_finance', 'read', '2026-08-05 01:57:00', '203.0.113.42', 'DEV-UNKNOWN-001', 5, TRUE),
(5, 'database', 'corporate_finance', 'write', '2026-08-05 02:00:00', '203.0.113.42', 'DEV-UNKNOWN-001', 5, TRUE),
(5, 'system', 'Finance Management System', 'read', '2026-08-07 02:15:00', '203.0.113.42', 'DEV-UNKNOWN-001', 5, TRUE),
(5, 'database', 'corporate_finance', 'write', '2026-08-07 02:18:00', '203.0.113.42', 'DEV-UNKNOWN-001', 5, TRUE),
(5, 'system', 'Finance Management System', 'read', '2026-08-10 02:30:00', '198.51.100.15', 'DEV-UNKNOWN-001', 5, TRUE),
(5, 'database', 'corporate_finance', 'write', '2026-08-10 02:33:00', '198.51.100.15', 'DEV-UNKNOWN-001', 5, TRUE),
(5, 'system', 'Finance Management System', 'read', '2026-08-12 01:45:00', '198.51.100.15', 'DEV-UNKNOWN-001', 5, TRUE),
(5, 'database', 'corporate_finance', 'write', '2026-08-12 01:48:00', '198.51.100.15', 'DEV-UNKNOWN-001', 5, TRUE),
(5, 'system', 'Finance Management System', 'read', '2026-08-15 02:05:00', '203.0.113.42', 'DEV-UNKNOWN-001', 5, TRUE),
(5, 'database', 'corporate_finance', 'write', '2026-08-15 02:08:00', '203.0.113.42', 'DEV-UNKNOWN-001', 5, TRUE),
-- Normal access
(5, 'system', 'Finance Management System', 'read', '2026-08-01 09:05:00', '192.168.1.47', 'DEV-LAPTOP-004', 1, TRUE),
(5, 'database', 'corporate_finance', 'read', '2026-08-01 09:10:00', '192.168.1.47', 'DEV-LAPTOP-004', 1, TRUE),
(5, 'file', 'monthly_report_aug.xlsx', 'read', '2026-08-01 14:30:00', '192.168.1.47', 'DEV-LAPTOP-004', 1, TRUE),
-- Others normal
(3, 'system', 'Finance Management System', 'read', '2026-08-01 09:05:00', '192.168.1.45', 'DEV-LAPTOP-002', 1, TRUE),
(3, 'database', 'corporate_finance', 'read', '2026-08-01 09:10:00', '192.168.1.45', 'DEV-LAPTOP-002', 1, TRUE),
(4, 'system', 'Finance Management System', 'read', '2026-08-01 09:20:00', '192.168.1.46', 'DEV-LAPTOP-003', 1, TRUE),
(4, 'database', 'corporate_finance', 'read', '2026-08-01 09:25:00', '192.168.1.46', 'DEV-LAPTOP-003', 1, TRUE);

-- Transaction audit logs
INSERT INTO `transaction_audit` (`transaction_id`, `action`, `performed_by`, `new_values`, `notes`) VALUES
(1, 'created', 3, '{"amount": 2500000, "to_account": "EMP-002-SAL"}', 'Monthly payroll batch initiated'),
(2, 'created', 8, '{"amount": 500000, "vendor": "Forge Consulting"}', 'Vendor payment processed'),
(5, 'created', 5, '{"amount": 1000000, "to_account": "EXT-VOID-001"}', 'Emergency payment processed'),
(12, 'flagged', 20, '{"flagged_reason": "unusual_amount", "flagged_by": "system"}', 'Automated flag: transaction exceeds threshold'),
(15, 'flagged', 20, '{"flagged_reason": "unusual_time_and_amount", "flagged_by": "system"}', 'Automated flag: transaction pattern anomaly');