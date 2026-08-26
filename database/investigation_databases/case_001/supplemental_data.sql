-- Supplemental seed data for corporatefinance investigation database
-- Supports cases 004, 007, 010, 013, 017, 020, 022, 026, 030

USE `corporatefinance`;

-- ============================================================
-- Additional employees hired in 2025-2026 (for CASE-017 Money Mule)
-- ============================================================
INSERT INTO `employees` (`id`, `employee_code`, `first_name`, `last_name`, `email`, `phone`, `department_id`, `position`, `hire_date`, `salary`, `manager_id`, `status`) VALUES
(21, 'EMP021', 'Tanya', 'Malhotra', 'tanya.malhotra@corp.com', '+91-9876543221', 5, 'Sales Executive', '2025-03-10', 320000.00, 10, 'active'),
(22, 'EMP022', 'Vivek', 'Sharma', 'vivek.sharma@corp.com', '+91-9876543222', 3, 'Junior Developer', '2025-06-15', 350000.00, 6, 'active'),
(23, 'EMP023', 'Pallavi', 'Reddy', 'pallavi.reddy@corp.com', '+91-9876543223', 1, 'Accounts Assistant', '2026-01-20', 280000.00, 3, 'active');

-- Additional employees hired before 2025 (normal employees for contrast)
INSERT INTO `employees` (`id`, `employee_code`, `first_name`, `last_name`, `email`, `phone`, `department_id`, `position`, `hire_date`, `salary`, `manager_id`, `status`) VALUES
(24, 'EMP024', 'Nikhil', 'Bose', 'nikhil.bose@corp.com', '+91-9876543224', 5, 'Regional Sales Manager', '2019-04-01', 850000.00, 10, 'active'),
(25, 'EMP025', 'Aisha', 'Khan', 'aisha.khan@corp.com', '+91-9876543225', 4, 'Supply Chain Analyst', '2020-08-12', 450000.00, 8, 'active');

-- ============================================================
-- Additional bank accounts (for CASE-007, 013, 017, 022, 026)
-- ============================================================
INSERT INTO `bank_accounts` (`account_number`, `account_type`, `account_name`, `owner_type`, `owner_id`, `bank_name`, `branch`, `currency`, `balance`, `is_active`) VALUES
-- Department-specific accounts (for CASE-007 budget leaks)
('CORP-HDFC-FIN', 'operating', 'Finance Department Account', 'company', NULL, 'HDFC Bank', 'Mumbai HQ', 'INR', 8000000.00, TRUE),
('CORP-HDFC-IT', 'operating', 'IT Department Account', 'company', NULL, 'HDFC Bank', 'Mumbai HQ', 'INR', 12000000.00, TRUE),
('CORP-HDFC-HR', 'operating', 'HR Department Account', 'company', NULL, 'HDFC Bank', 'Delhi Branch', 'INR', 3500000.00, TRUE),
('CORP-HDFC-OPS', 'operating', 'Operations Department Account', 'company', NULL, 'HDFC Bank', 'Chennai Branch', 'INR', 5000000.00, TRUE),
-- Intermediary accounts for money laundering trail (CASE-013, 022)
('EXT-CAYMAN-001', 'checking', 'Global Trade Solutions Ltd', 'external', NULL, 'HSBC Cayman', 'Grand Cayman', 'USD', 2500000.00, TRUE),
('EXT-MAURITIUS-001', 'checking', 'Apex Holdings Pty Ltd', 'external', NULL, 'BCP Mauritius', 'Port Louis', 'USD', 1800000.00, TRUE),
('EXT-PANAMA-001', 'checking', 'Pacific Ventures SA', 'external', NULL, 'Banistmo Panama', 'Panama City', 'USD', 950000.00, TRUE),
('EXT-DUBAI-001', 'checking', 'Gulf Investments LLC', 'external', NULL, 'Emirates NBD', 'Dubai', 'AED', 4200000.00, TRUE),
-- Additional employee accounts (for CASE-017 money mule)
('EMP-021-SAL', 'checking', 'Tanya Malhotra Salary', 'employee', 21, 'SBI', 'Andheri', 'INR', 160000.00, TRUE),
('EMP-021-SAV', 'savings', 'Tanya Malhotra Savings', 'employee', 21, 'SBI', 'Andheri', 'INR', 2400000.00, TRUE),
('EMP-022-SAL', 'checking', 'Vivek Sharma Salary', 'employee', 22, 'HDFC Bank', 'Whitefield', 'INR', 175000.00, TRUE),
('EMP-023-SAL', 'checking', 'Pallavi Reddy Salary', 'employee', 23, 'ICICI Bank', 'Bandra', 'INR', 140000.00, TRUE),
('EMP-023-SAV', 'savings', 'Pallavi Reddy Savings', 'employee', 23, 'ICICI Bank', 'Bandra', 'INR', 85000.00, TRUE),
-- Suspicious external accounts (for CASE-026 phantom transactions)
('EXT-UNKNOWN-001', 'checking', 'Skyward Enterprises Ltd', 'external', NULL, 'Deutsche Bank', 'London', 'GBP', 670000.00, TRUE),
('EXT-UNKNOWN-002', 'checking', 'Northern Lights Trading Co', 'external', NULL, 'UBS Zurich', 'Zurich', 'CHF', 1200000.00, TRUE);

-- ============================================================
-- Additional devices (for CASE-004, 030)
-- ============================================================
INSERT INTO `devices` (`device_id`, `employee_id`, `device_type`, `os`, `browser`, `mac_address`, `is_company_issued`, `first_seen`, `last_seen`, `status`) VALUES
('DEV-LAPTOP-009', 21, 'laptop', 'Windows 11', 'Chrome', 'AA:BB:CC:DD:01:09', TRUE, '2025-03-15 09:00:00', '2026-08-20 18:00:00', 'active'),
('DEV-LAPTOP-010', 22, 'laptop', 'macOS Sonoma', 'Safari', 'AA:BB:CC:DD:01:10', TRUE, '2025-06-20 09:00:00', '2026-08-20 17:30:00', 'active'),
('DEV-LAPTOP-011', 23, 'laptop', 'Windows 11', 'Chrome', 'AA:BB:CC:DD:01:11', TRUE, '2026-01-25 09:00:00', '2026-08-20 18:15:00', 'active'),
('DEV-UNKNOWN-002', NULL, 'desktop', 'Windows 10', 'Chrome', 'DD:EE:FF:00:01:02', FALSE, '2026-07-28 01:30:00', '2026-08-22 03:45:00', 'active'),
('DEV-UNKNOWN-003', NULL, 'laptop', 'Ubuntu 22.04', 'Firefox', 'DD:EE:FF:00:01:03', FALSE, '2026-06-10 02:15:00', '2026-08-20 02:50:00', 'active');

-- ============================================================
-- Additional locations
-- ============================================================
INSERT INTO `locations` (`id`, `name`, `address`, `city`, `state`, `country`, `ip_range_start`, `ip_range_end`, `timezone`, `is_office`) VALUES
(6, 'Singapore Branch', '1 Raffles Place, Tower 2', 'Singapore', NULL, 'Singapore', '10.10.1.1', '10.10.1.254', 'Asia/Singapore', TRUE),
(7, 'Dubai Office', 'Business Bay, Sheikh Zayed Road', 'Dubai', NULL, 'UAE', '10.20.1.1', '10.20.1.254', 'Asia/Dubai', TRUE);

-- ============================================================
-- CASE-004: Additional login attempts from external IPs (various employees)
-- ============================================================
INSERT INTO `login_logs` (`employee_id`, `login_time`, `logout_time`, `ip_address`, `device_id`, `location_id`, `status`) VALUES
-- EMP001 from Singapore office (legitimate but external to India)
(1, '2026-08-03 08:00:00', '2026-08-03 18:30:00', '10.10.1.45', 'DEV-LAPTOP-001', 6, 'success'),
(1, '2026-08-04 07:45:00', '2026-08-04 19:00:00', '10.10.1.45', 'DEV-LAPTOP-001', 6, 'success'),
-- EMP010 from Dubai (legitimate)
(10, '2026-08-06 09:00:00', '2026-08-06 17:00:00', '10.20.1.30', 'DEV-LAPTOP-005', 7, 'success'),
-- Suspicious external attempts on admin accounts
(2, '2026-08-10 03:15:00', NULL, '45.33.98.77', NULL, 5, 'failed'),
(2, '2026-08-10 03:16:00', NULL, '45.33.98.77', NULL, 5, 'failed'),
(2, '2026-08-10 03:17:00', NULL, '45.33.98.77', NULL, 5, 'failed'),
(1, '2026-08-10 03:20:00', NULL, '45.33.98.77', NULL, 5, 'failed'),
(1, '2026-08-10 03:21:00', NULL, '45.33.98.77', NULL, 5, 'failed'),
(6, '2026-08-10 03:22:00', NULL, '45.33.98.77', NULL, 5, 'failed'),
(6, '2026-08-10 03:23:00', NULL, '45.33.98.77', NULL, 5, 'failed'),
-- One success from attacker IP (compromised account)
(14, '2026-08-10 03:25:00', '2026-08-10 03:45:00', '45.33.98.77', 'DEV-UNKNOWN-002', 5, 'success'),
-- Another attacker IP
(3, '2026-08-12 02:50:00', NULL, '185.220.101.42', NULL, 5, 'failed'),
(4, '2026-08-12 02:51:00', NULL, '185.220.101.42', NULL, 5, 'failed'),
(5, '2026-08-12 02:52:00', NULL, '185.220.101.42', NULL, 5, 'failed'),
(7, '2026-08-12 02:53:00', NULL, '185.220.101.42', NULL, 5, 'failed'),
-- Normal logins from office IPs
(3, '2026-08-01 09:05:00', '2026-08-01 18:00:00', '192.168.1.50', 'DEV-LAPTOP-002', 1, 'success'),
(4, '2026-08-01 09:10:00', '2026-08-01 17:45:00', '192.168.1.51', 'DEV-LAPTOP-003', 1, 'success'),
(6, '2026-08-01 08:30:00', '2026-08-01 19:00:00', '192.168.1.52', 'DEV-LAPTOP-005', 1, 'success'),
(7, '2026-08-01 09:00:00', '2026-08-01 18:30:00', '192.168.1.53', 'DEV-LAPTOP-006', 1, 'success'),
(8, '2026-08-01 08:45:00', '2026-08-01 17:30:00', '192.168.4.20', 'DEV-LAPTOP-005', 4, 'success'),
(10, '2026-08-01 09:00:00', '2026-08-01 18:15:00', '192.168.1.55', 'DEV-LAPTOP-005', 1, 'success'),
(12, '2026-08-01 09:15:00', '2026-08-01 18:00:00', '192.168.1.56', 'DEV-LAPTOP-005', 1, 'success');

-- ============================================================
-- CASE-007: Department budgets vs spending
-- Add transactions initiated by employees from specific departments
-- ============================================================
INSERT INTO `transactions` (`transaction_id`, `transaction_date`, `amount`, `currency`, `transaction_type`, `status`, `from_account_id`, `to_account_id`, `initiated_by`, `approved_by`, `description`, `reference`, `ip_address`, `device_id`, `location_id`) VALUES
-- Finance department spending (dept 1: employees 3,4,5,13,19,23)
('TXN-2026-0016', '2026-08-03 10:00:00', 1200000.00, 'INR', 'payment', 'completed', 6, 11, 4, 1, 'Finance software license renewal', 'FIN-SW-001', '192.168.1.46', 'DEV-LAPTOP-003', 1),
('TXN-2026-0017', '2026-08-05 11:00:00', 800000.00, 'INR', 'payment', 'completed', 6, 11, 3, 1, 'Audit consulting fees Q3', 'FIN-AUD-001', '192.168.1.45', 'DEV-LAPTOP-002', 1),
('TXN-2026-0018', '2026-08-08 14:30:00', 1500000.00, 'INR', 'payment', 'completed', 6, 12, 19, 1, 'Tax advisory services', 'FIN-TAX-001', '192.168.1.58', 'DEV-LAPTOP-002', 1),
('TXN-2026-0019', '2026-08-12 09:45:00', 950000.00, 'INR', 'payment', 'completed', 6, 11, 13, 1, 'Financial modeling training', 'FIN-TRN-001', '192.168.1.60', 'DEV-LAPTOP-002', 1),
-- IT department spending (dept 3: employees 6,7,14,20,22)
('TXN-2026-0020', '2026-08-02 10:30:00', 3500000.00, 'INR', 'payment', 'completed', 7, 11, 6, 2, 'Cloud infrastructure Q3', 'IT-CLOUD-001', '192.168.1.52', 'DEV-LAPTOP-005', 1),
('TXN-2026-0021', '2026-08-06 15:00:00', 2800000.00, 'INR', 'payment', 'completed', 7, 12, 7, 6, 'Database server upgrade', 'IT-DB-001', '192.168.1.53', 'DEV-LAPTOP-006', 1),
('TXN-2026-0022', '2026-08-10 11:15:00', 1200000.00, 'INR', 'payment', 'completed', 7, 11, 14, 6, 'Security software renewal', 'IT-SEC-001', '192.168.1.61', 'DEV-LAPTOP-007', 1),
('TXN-2026-0023', '2026-08-14 16:00:00', 900000.00, 'INR', 'payment', 'completed', 7, 11, 20, 6, 'Penetration testing service', 'IT-PEN-001', '192.168.1.62', 'DEV-LAPTOP-008', 1),
-- HR department spending (dept 2: employee 9)
('TXN-2026-0024', '2026-08-04 13:00:00', 600000.00, 'INR', 'payment', 'completed', 8, 11, 9, 2, 'Recruitment platform annual', 'HR-REC-001', '192.168.1.54', 'DEV-LAPTOP-002', 1),
('TXN-2026-0025', '2026-08-09 10:00:00', 450000.00, 'INR', 'payment', 'completed', 8, 12, 9, 2, 'Employee training program', 'HR-TRN-001', '192.168.1.54', 'DEV-LAPTOP-002', 1),
-- Operations dept spending (dept 4: employees 8,15,25)
('TXN-2026-0026', '2026-08-07 14:00:00', 1800000.00, 'INR', 'payment', 'completed', 9, 11, 8, 2, 'Logistics platform upgrade', 'OPS-LOG-001', '192.168.4.20', 'DEV-LAPTOP-005', 4),
('TXN-2026-0027', '2026-08-11 11:30:00', 750000.00, 'INR', 'payment', 'completed', 9, 11, 25, 8, 'Supply chain consulting', 'OPS-SC-001', '192.168.4.21', 'DEV-LAPTOP-005', 4),
-- Over-budget: Finance dept spending exceeds budget (budget=50L, spending=~44.5L total via dept 6)
-- Add one more big transaction to push Finance over budget
('TXN-2026-0028', '2026-08-16 10:00:00', 800000.00, 'INR', 'payment', 'completed', 6, 11, 5, 3, 'Emergency compliance audit', 'FIN-COMP-001', '192.168.1.47', 'DEV-LAPTOP-004', 1);

-- ============================================================
-- CASE-010: Duplicate payments
-- Same amount, same to_account, same date
-- ============================================================
INSERT INTO `transactions` (`transaction_id`, `transaction_date`, `amount`, `currency`, `transaction_type`, `status`, `from_account_id`, `to_account_id`, `initiated_by`, `approved_by`, `description`, `reference`, `ip_address`, `device_id`, `location_id`) VALUES
-- Duplicate pair 1: Same amount to same vendor on same day
('TXN-2026-0029', '2026-08-05 10:30:00', 475000.00, 'INR', 'payment', 'completed', 1, 11, 5, NULL, 'Vendor payment - Forge Consulting', 'INV-FC-2026-078', '203.0.113.42', 'DEV-UNKNOWN-001', 5),
('TXN-2026-0030', '2026-08-05 15:45:00', 475000.00, 'INR', 'payment', 'completed', 1, 11, 5, NULL, 'Vendor payment - Forge Consulting (duplicate)', 'INV-FC-2026-078-DUP', '203.0.113.42', 'DEV-UNKNOWN-001', 5),
-- Duplicate pair 2: Same amount to same account, different date (near-duplicate)
('TXN-2026-0031', '2026-08-10 11:00:00', 350000.00, 'INR', 'payment', 'completed', 1, 12, 5, NULL, 'Consulting fees', 'CON-MER-2026-012', '198.51.100.15', 'DEV-UNKNOWN-001', 5),
('TXN-2026-0032', '2026-08-10 16:20:00', 350000.00, 'INR', 'payment', 'completed', 1, 12, 5, NULL, 'Consulting fees - correction', 'CON-MER-2026-012-REV', '198.51.100.15', 'DEV-UNKNOWN-001', 5),
-- Duplicate pair 3: Same amount to same external account
('TXN-2026-0033', '2026-08-15 09:15:00', 625000.00, 'INR', 'payment', 'completed', 1, 13, 3, 1, 'Board meeting logistics', 'BOD-LOG-001', '192.168.1.45', 'DEV-LAPTOP-002', 1),
('TXN-2026-0034', '2026-08-15 14:30:00', 625000.00, 'INR', 'payment', 'completed', 1, 13, 5, NULL, 'Board meeting logistics (repost)', 'BOD-LOG-001-RE', '203.0.113.42', 'DEV-UNKNOWN-001', 5),
-- Single payment (non-duplicate, for noise)
('TXN-2026-0035', '2026-08-18 10:00:00', 280000.00, 'INR', 'payment', 'completed', 1, 11, 4, 1, 'Software maintenance', 'SWM-AUG-001', '192.168.1.46', 'DEV-LAPTOP-003', 1);

-- ============================================================
-- CASE-013 & CASE-022: Multi-hop money trail
-- Account 1 → Intermediaries → External (3 levels deep)
-- ============================================================
INSERT INTO `transactions` (`transaction_id`, `transaction_date`, `amount`, `currency`, `transaction_type`, `status`, `from_account_id`, `to_account_id`, `initiated_by`, `approved_by`, `description`, `reference`, `ip_address`, `device_id`, `location_id`) VALUES
-- Level 1: Main account → Cayman intermediary
('TXN-2026-0036', '2026-08-01 02:30:00', 8500000.00, 'INR', 'transfer', 'completed', 1, 14, 5, NULL, 'International settlement', 'INT-SET-001', '203.0.113.42', 'DEV-UNKNOWN-001', 5),
-- Level 1: Main account → Mauritius intermediary
('TXN-2026-0037', '2026-08-03 03:00:00', 6200000.00, 'INR', 'transfer', 'completed', 1, 15, 5, NULL, 'Overseas consultancy', 'INT-CON-001', '198.51.100.15', 'DEV-UNKNOWN-001', 5),
-- Level 2: Cayman → Panama
('TXN-2026-0038', '2026-08-04 14:00:00', 5500000.00, 'USD', 'transfer', 'completed', 14, 16, NULL, NULL, 'Subsidiary funding', 'SUB-FUND-001', NULL, NULL, NULL),
-- Level 2: Mauritius → Dubai
('TXN-2026-0039', '2026-08-05 10:30:00', 4800000.00, 'USD', 'transfer', 'completed', 15, 17, NULL, NULL, 'Investment distribution', 'INV-DIST-001', NULL, NULL, NULL),
-- Level 2: Cayman → Void Technologies
('TXN-2026-0040', '2026-08-06 09:00:00', 3000000.00, 'USD', 'transfer', 'completed', 14, 13, NULL, NULL, 'Service payment', 'SVC-PAY-001', NULL, NULL, NULL),
-- Level 3: Panama → external offshore
('TXN-2026-0041', '2026-08-07 16:00:00', 4200000.00, 'USD', 'transfer', 'completed', 16, 18, NULL, NULL, 'Final settlement', 'FIN-SET-001', NULL, NULL, NULL),
-- Level 3: Dubai → external offshore
('TXN-2026-0042', '2026-08-08 11:00:00', 3800000.00, 'USD', 'transfer', 'completed', 17, 18, NULL, NULL, 'Dividend payment', 'DIV-PAY-001', NULL, NULL, NULL),
-- Level 3: Void → external
('TXN-2026-0043', '2026-08-09 15:30:00', 2500000.00, 'USD', 'transfer', 'completed', 13, 18, NULL, NULL, 'Settlement transfer', 'SET-TRN-001', NULL, NULL, NULL),
-- Additional levels for deeper tracing
('TXN-2026-0044', '2026-08-10 02:45:00', 7200000.00, 'INR', 'transfer', 'completed', 1, 14, 5, NULL, 'Quarterly remittance', 'QTR-REM-001', '203.0.113.42', 'DEV-UNKNOWN-001', 5),
('TXN-2026-0045', '2026-08-12 03:15:00', 5800000.00, 'INR', 'transfer', 'completed', 1, 15, 5, NULL, 'Overseas investment', 'OVS-INV-001', '198.51.100.15', 'DEV-UNKNOWN-001', 5),
('TXN-2026-0046', '2026-08-14 14:30:00', 4100000.00, 'USD', 'transfer', 'completed', 14, 16, NULL, NULL, 'Capital transfer', 'CAP-TRN-001', NULL, NULL, NULL),
('TXN-2026-0047', '2026-08-16 10:00:00', 3200000.00, 'USD', 'transfer', 'completed', 15, 17, NULL, NULL, 'Investment return', 'INV-RET-001', NULL, NULL, NULL);

-- ============================================================
-- CASE-017: Transactions involving employees hired in 2025+
-- Tanya Malhotra (EMP021), Vivek Sharma (EMP022), Pallavi Reddy (EMP023)
-- ============================================================
INSERT INTO `transactions` (`transaction_id`, `transaction_date`, `amount`, `currency`, `transaction_type`, `status`, `from_account_id`, `to_account_id`, `initiated_by`, `approved_by`, `description`, `reference`, `ip_address`, `device_id`, `location_id`) VALUES
-- Tanya Malhotra suspicious transactions
('TXN-2026-0048', '2026-07-15 14:30:00', 1500000.00, 'INR', 'transfer', 'completed', 1, 19, 21, NULL, 'Client payment processing', 'SLS-CLI-001', '192.168.1.70', 'DEV-LAPTOP-009', 1),
('TXN-2026-0049', '2026-07-22 11:00:00', 2200000.00, 'INR', 'transfer', 'completed', 1, 20, 21, NULL, 'Sales commission advance', 'SLS-COM-001', '192.168.1.70', 'DEV-LAPTOP-009', 1),
('TXN-2026-0050', '2026-08-01 16:00:00', 1800000.00, 'INR', 'transfer', 'completed', 1, 19, 21, NULL, 'Marketing event payment', 'MKT-EVT-001', '192.168.1.70', 'DEV-LAPTOP-009', 1),
-- Pallavi Reddy suspicious transactions
('TXN-2026-0051', '2026-08-08 09:30:00', 1200000.00, 'INR', 'transfer', 'completed', 1, 21, 23, NULL, 'Vendor reconciliation', 'FIN-VEN-001', '192.168.1.71', 'DEV-LAPTOP-011', 1),
('TXN-2026-0052', '2026-08-15 13:45:00', 1100000.00, 'INR', 'transfer', 'completed', 1, 21, 23, NULL, 'Emergency expense', 'FIN-EMR-001', '192.168.1.71', 'DEV-LAPTOP-011', 1),
-- Normal transactions by 2025+ hires (noise)
('TXN-2026-0053', '2026-08-05 10:00:00', 50000.00, 'INR', 'payment', 'completed', 11, 22, 22, 6, 'Training material purchase', 'IT-TRN-001', '192.168.1.72', 'DEV-LAPTOP-010', 1);

-- ============================================================
-- CASE-026: More phantom transactions to reach ~75 lakhs
-- Need ~36.5L more from the current 38.5L
-- ============================================================
INSERT INTO `transactions` (`transaction_id`, `transaction_date`, `amount`, `currency`, `transaction_type`, `status`, `from_account_id`, `to_account_id`, `initiated_by`, `approved_by`, `description`, `reference`, `ip_address`, `device_id`, `location_id`) VALUES
-- More phantom transactions (completed, external IP, 2-4 AM)
('TXN-2026-0054', '2026-08-06 03:10:00', 850000.00, 'INR', 'transfer', 'completed', 1, 13, 5, NULL, 'Emergency vendor settlement', 'EMRG-VEN-002', '203.0.113.42', 'DEV-UNKNOWN-001', 5),
('TXN-2026-0055', '2026-08-09 02:40:00', 720000.00, 'INR', 'transfer', 'completed', 1, 13, 5, NULL, 'IT infrastructure payment', 'IT-INF-002', '198.51.100.15', 'DEV-UNKNOWN-001', 5),
('TXN-2026-0056', '2026-08-14 02:20:00', 930000.00, 'INR', 'transfer', 'completed', 1, 13, 5, NULL, 'Compliance processing fee', 'COMP-FEE-001', '203.0.113.42', 'DEV-UNKNOWN-001', 5),
('TXN-2026-0057', '2026-08-20 03:30:00', 1250000.00, 'INR', 'transfer', 'completed', 1, 18, 5, NULL, 'Offshore settlement', 'OFS-SET-001', '198.51.100.15', 'DEV-UNKNOWN-001', 5),
-- Legitimate late-night transactions (noise - from office IP)
('TXN-2026-0058', '2026-08-11 02:15:00', 200000.00, 'INR', 'transfer', 'completed', 2, 5, 3, 1, 'Salary advance processing', 'SAL-ADV-001', '192.168.1.45', 'DEV-LAPTOP-002', 1);

-- ============================================================
-- CASE-030: Cross-cutting connections
-- Add transactions that share IPs/devices with other suspicious activities
-- ============================================================
INSERT INTO `transactions` (`transaction_id`, `transaction_date`, `amount`, `currency`, `transaction_type`, `status`, `from_account_id`, `to_account_id`, `initiated_by`, `approved_by`, `description`, `reference`, `ip_address`, `device_id`, `location_id`) VALUES
-- Transaction using the same IP as the attacker from CASE-004
('TXN-2026-0059', '2026-08-10 04:00:00', 450000.00, 'INR', 'transfer', 'flagged', 1, 18, 14, NULL, 'Late night transfer', 'LNT-001', '45.33.98.77', 'DEV-UNKNOWN-002', 5),
-- Transaction using device DEV-UNKNOWN-003 (new suspicious device)
('TXN-2026-0060', '2026-08-15 02:55:00', 380000.00, 'INR', 'transfer', 'flagged', 1, 13, 5, NULL, 'Processing fee', 'PRO-FEE-001', '185.220.101.42', 'DEV-UNKNOWN-003', 5),
-- Transaction linking EMP005 to the Singapore IP (cross-case connection)
('TXN-2026-0061', '2026-08-03 02:40:00', 650000.00, 'INR', 'transfer', 'completed', 1, 13, 5, NULL, 'International wire', 'INT-WIR-001', '10.10.1.45', 'DEV-UNKNOWN-001', 6);

-- ============================================================
-- Additional access logs for cross-referencing (CASE-030)
-- ============================================================
INSERT INTO `access_logs` (`employee_id`, `resource_type`, `resource_name`, `action`, `access_time`, `ip_address`, `device_id`, `location_id`, `success`) VALUES
-- Employee 14 (Pooja Thakur - System Admin) suspicious access
(14, 'database', 'corporatefinance', 'write', '2026-08-10 03:30:00', '45.33.98.77', 'DEV-UNKNOWN-002', 5, TRUE),
(14, 'file', 'bank_accounts_backup.sql', 'export', '2026-08-10 03:35:00', '45.33.98.77', 'DEV-UNKNOWN-002', 5, TRUE),
(14, 'database', 'corporatefinance', 'read', '2026-08-10 03:40:00', '45.33.98.77', 'DEV-UNKNOWN-002', 5, TRUE),
-- Employee 5 accessing from Singapore IP (cross-case link)
(5, 'database', 'corporatefinance', 'write', '2026-08-03 02:45:00', '10.10.1.45', 'DEV-UNKNOWN-001', 6, TRUE),
-- Employee 5 accessing from DEV-UNKNOWN-003
(5, 'system', 'Finance Management System', 'read', '2026-08-15 02:50:00', '185.220.101.42', 'DEV-UNKNOWN-003', 5, TRUE),
(5, 'database', 'corporatefinance', 'write', '2026-08-15 02:55:00', '185.220.101.42', 'DEV-UNKNOWN-003', 5, TRUE);

-- ============================================================
-- Additional login logs for employee 14 (cross-case connection)
-- ============================================================
INSERT INTO `login_logs` (`employee_id`, `login_time`, `logout_time`, `ip_address`, `device_id`, `location_id`, `status`) VALUES
(14, '2026-08-10 03:25:00', '2026-08-10 03:50:00', '45.33.98.77', 'DEV-UNKNOWN-002', 5, 'success'),
(5, '2026-08-15 02:30:00', '2026-08-15 03:00:00', '185.220.101.42', 'DEV-UNKNOWN-003', 5, 'success'),
(14, '2026-08-15 02:40:00', '2026-08-15 03:10:00', '185.220.101.42', 'DEV-UNKNOWN-003', 5, 'success');

-- ============================================================
-- Transaction audit trail for new transactions
-- ============================================================
INSERT INTO `transaction_audit` (`transaction_id`, `action`, `performed_by`, `new_values`, `notes`) VALUES
(16, 'created', 4, '{"amount": 1200000, "vendor": "Forge Consulting"}', 'Software license payment'),
(20, 'created', 6, '{"amount": 3500000, "vendor": "Cloud provider"}', 'Cloud infrastructure payment'),
(29, 'created', 5, '{"amount": 475000, "vendor": "Forge Consulting"}', 'Vendor payment processed'),
(30, 'created', 5, '{"amount": 475000, "vendor": "Forge Consulting", "duplicate": true}', 'Duplicate payment detected'),
(33, 'created', 3, '{"amount": 625000, "description": "Board meeting"}', 'Board meeting logistics'),
(34, 'created', 5, '{"amount": 625000, "description": "Board meeting (repost)"}', 'Duplicate board meeting payment'),
(36, 'created', 5, '{"amount": 8500000, "destination": "Cayman"}', 'International settlement initiated'),
(37, 'created', 5, '{"amount": 6200000, "destination": "Mauritius"}', 'Overseas consultancy transfer'),
(48, 'created', 21, '{"amount": 1500000, "type": "sales_payment"}', 'Client payment processed'),
(49, 'created', 21, '{"amount": 2200000, "type": "commission"}', 'Sales commission advance'),
(59, 'flagged', 20, '{"flagged_reason": "external_ip_used", "ip": "45.33.98.77"}', 'Automated flag: suspicious IP address'),
(60, 'flagged', 20, '{"flagged_reason": "external_ip_and_device", "ip": "185.220.101.42"}', 'Automated flag: unknown device + external IP');
