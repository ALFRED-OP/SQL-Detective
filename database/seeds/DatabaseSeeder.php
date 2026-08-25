<?php

namespace Database\Seeds;

use PDO;

class DatabaseSeeder
{
    private PDO $db;

    public function __construct()
    {
        $config = require base_path('config/database.php');
        $conn = $config['connections'][$config['default']];

        $dsn = "{$conn['driver']}:host={$conn['host']};port={$conn['port']};dbname={$conn['database']};charset={$conn['charset']}";
        $this->db = new PDO($dsn, $conn['username'], $conn['password'], $conn['options']);
    }

    public function run(): void
    {
        $this->seedUsers();
        $this->seedCases();
        $this->seedChallenges();
        $this->seedHints();
        $this->seedAchievements();
        echo "Database seeding completed\n";
    }

    private function seedUsers(): void
    {
        $adminEmail = $_ENV['ADMIN_EMAIL'] ?? 'admin@sqldetective.local';
        $adminPassword = $_ENV['ADMIN_PASSWORD'] ?? 'SecurePass123!';
        $demoEmail = $_ENV['DEMO_EMAIL'] ?? 'demo@sqldetective.local';
        $demoPassword = $_ENV['DEMO_PASSWORD'] ?? 'DemoPass123!';

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$adminEmail]);
        if ($stmt->fetchColumn() === 0) {
            $this->db->prepare("
                INSERT INTO users (username, email, password_hash, display_name, xp, level, detective_rank, role, email_verified_at, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'active')
            ")->execute([
                'admin',
                $adminEmail,
                password_hash($adminPassword, PASSWORD_DEFAULT),
                'Administrator',
                0,
                1,
                'SQL Rookie',
                'admin',
            ]);
            echo "Admin user created\n";
        }

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$demoEmail]);
        if ($stmt->fetchColumn() === 0) {
            $this->db->prepare("
                INSERT INTO users (username, email, password_hash, display_name, xp, level, detective_rank, role, email_verified_at, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'active')
            ")->execute([
                'demo',
                $demoEmail,
                password_hash($demoPassword, PASSWORD_DEFAULT),
                'Demo Detective',
                0,
                1,
                'SQL Rookie',
                'user',
            ]);
            echo "Demo user created\n";
        }
    }

    private function seedCases(): void
    {
        $cases = [
            // ─── BEGINNER (Cases 1-10) ─────────────────────────
            [
                'case_code' => 'CASE-001',
                'title' => 'The Missing Million',
                'description' => 'A company reports an unauthorized transfer of ₹10,00,000. Investigate the transaction logs to find the culprit.',
                'difficulty' => 'beginner',
                'category' => 'Financial Crime',
                'briefing' => 'On August 5th, 2026, at 02:15, an unauthorized wire transfer of ₹10,00,000 was initiated from the company\'s main operating account to an external account. Multiple similar transfers followed over the next two weeks. Your task is to analyze the transaction logs, employee access records, and login history to identify who initiated these transfers and how they gained access.',
                'objective' => 'Write a query to find all transactions initiated by employee 5 (Sunita Reddy) that occurred between 2:00 AM and 4:00 AM. Return the transaction_id, amount, and transaction_date.',
                'expected_result_description' => 'The query should return suspicious late-night transactions with amounts greater than ₹500,000.',
                'xp_reward' => 150,
                'estimated_minutes' => 20,
                'investigation_db' => 'corporate_finance',
            ],
            [
                'case_code' => 'CASE-002',
                'title' => 'The Digital Trail',
                'description' => 'A cyberattack has compromised company systems. Trace the intrusion through network and access logs.',
                'difficulty' => 'intermediate',
                'category' => 'Cyber Security',
                'briefing' => 'The security team discovered unauthorized access to the company\'s HR database. An external IP address was used to access sensitive files including salary data and employee records. Investigate the activities table, network logs, and file access records to determine the attack vector and what data was compromised.',
                'objective' => 'Find all activities performed from external IP addresses (not starting with 10.0 or 192.168) on August 12, 2026. Return the user, action_type, and timestamp.',
                'expected_result_description' => 'The query should identify the suspicious activities from unknown external IPs.',
                'xp_reward' => 200,
                'estimated_minutes' => 30,
                'investigation_db' => 'digital_forensics',
            ],
            [
                'case_code' => 'CASE-003',
                'title' => 'Employee Portal Breach',
                'description' => 'An employee has been escalating their own privileges. Investigate the permission changes.',
                'difficulty' => 'beginner',
                'category' => 'Insider Threat',
                'briefing' => 'The IT security team noticed suspicious permission changes in the employee portal. Someone has been self-elevating their access levels and exporting sensitive data. Investigate the permission_changes, access_logs, and login_records tables to identify the culprit and the extent of the breach.',
                'objective' => 'Find all permission_changes where the employee changed their own permissions (employee_id = changed_by). Return the employee_id, permission_type, old_value, and new_value.',
                'expected_result_description' => 'The query should reveal self-granted permission escalations.',
                'xp_reward' => 150,
                'estimated_minutes' => 20,
                'investigation_db' => 'employee_portal',
            ],
            [
                'case_code' => 'CASE-004',
                'title' => 'The Suspicious Login',
                'description' => 'Multiple failed login attempts were detected from an unusual location. Identify the source.',
                'difficulty' => 'beginner',
                'category' => 'Authentication',
                'briefing' => 'The security team noticed a spike in failed login attempts from an IP address that has never been seen before. The attempts were targeting admin accounts. Investigate the login_logs to determine which accounts were targeted and whether any attempts succeeded.',
                'objective' => 'Write a query to find all login attempts (successful and failed) from IP addresses not in the office range (192.168.x.x). Return the employee_id, login_time, ip_address, and status.',
                'expected_result_description' => 'Return non-office login attempts showing suspicious patterns.',
                'xp_reward' => 100,
                'estimated_minutes' => 15,
                'investigation_db' => 'corporate_finance',
            ],
            [
                'case_code' => 'CASE-005',
                'title' => 'Ghost in the System',
                'description' => 'A system account is performing actions at odd hours. Investigate the anomaly.',
                'difficulty' => 'beginner',
                'category' => 'Anomaly Detection',
                'briefing' => 'The monitoring system flagged unusual activity from a service account. The account normally runs backups at 3 AM but recently started accessing files and databases outside its normal scope. Investigate the activities and logs to determine what the account has been doing.',
                'objective' => 'Find all activities by service accounts (user_id IN (9, 10)) that occurred outside normal business hours (before 6 AM or after 10 PM). Return the user_id, action_type, and timestamp.',
                'expected_result_description' => 'Identify service account activities during unusual hours.',
                'xp_reward' => 100,
                'estimated_minutes' => 15,
                'investigation_db' => 'digital_forensics',
            ],
            [
                'case_code' => 'CASE-006',
                'title' => 'The Missing Records',
                'description' => 'Several employee records have been deleted from the portal. Find out who did it.',
                'difficulty' => 'beginner',
                'category' => 'Data Tampering',
                'briefing' => 'The HR department discovered that several employee records were modified or deleted from the portal. The audit trail shows changes were made but the responsible party claims innocence. Investigate the audit_trail and system_events to find who performed these actions.',
                'objective' => 'Query the audit_trail table to find all delete and update operations on the employees table. Return the record_id, action, performed_by, and performed_at.',
                'expected_result_description' => 'Return all modification operations on employee records.',
                'xp_reward' => 100,
                'estimated_minutes' => 15,
                'investigation_db' => 'employee_portal',
            ],
            [
                'case_code' => 'CASE-007',
                'title' => 'Budget Leaks',
                'description' => 'Department budgets don\'t match the actual spending. Find the discrepancies.',
                'difficulty' => 'beginner',
                'category' => 'Accounting',
                'briefing' => 'The annual audit revealed that several departments have spent more than their allocated budgets. However, the spending records don\'t show any overages. Someone may have manipulated the budget records. Compare department budgets with actual transaction totals.',
                'objective' => 'Write a query comparing each department\'s budget with the total payments from their department accounts. Return department code, budget, and total spending.',
                'expected_result_description' => 'Show departments where actual spending exceeds their budget.',
                'xp_reward' => 120,
                'estimated_minutes' => 20,
                'investigation_db' => 'corporate_finance',
            ],
            [
                'case_code' => 'CASE-008',
                'title' => 'Phishing Campaign',
                'description' => 'Employees received suspicious emails. Identify the source and targets.',
                'difficulty' => 'beginner',
                'category' => 'Email Security',
                'briefing' => 'Several employees reported receiving suspicious emails claiming to be from IT support. The emails asked for login credentials. The security team needs to identify which employees received these emails and whether any fell for the phishing attempt.',
                'objective' => 'Find all emails sent from external addresses (sender_id IS NULL or sender not in users table) or with suspicious subjects. Return the message_id, subject, and sent_at.',
                'expected_result_description' => 'Identify suspicious emails in the system.',
                'xp_reward' => 100,
                'estimated_minutes' => 15,
                'investigation_db' => 'digital_forensics',
            ],
            [
                'case_code' => 'CASE-009',
                'title' => 'The Night Shift',
                'description' => 'Someone is accessing the system during night shifts when they shouldn\'t be.',
                'difficulty' => 'beginner',
                'category' => 'Access Control',
                'briefing' => 'The security team noticed that certain employees are logging into the system during night hours (11 PM to 5 AM) even though they don\'t have night shift assignments. Investigate the login_records to identify these employees and their access patterns.',
                'objective' => 'Find all successful logins between 11 PM and 5 AM. Return the employee_id, login_time, ip_address, and location.',
                'expected_result_description' => 'Show night-time login patterns for non-shift employees.',
                'xp_reward' => 100,
                'estimated_minutes' => 15,
                'investigation_db' => 'employee_portal',
            ],
            [
                'case_code' => 'CASE-010',
                'title' => 'Duplicate Payments',
                'description' => 'The finance team found duplicate payments to a vendor. Investigate the transaction history.',
                'difficulty' => 'beginner',
                'category' => 'Financial Crime',
                'briefing' => 'The accounts payable team discovered that several invoices from the same vendor were paid multiple times. The duplicate payments total over ₹5 lakhs. Investigate the transactions table to identify all duplicate payments to external accounts.',
                'objective' => 'Find transactions where the same amount was sent to the same to_account_id on the same date. Return the transaction_id, amount, to_account_id, and transaction_date.',
                'expected_result_description' => 'Identify duplicate payment patterns.',
                'xp_reward' => 120,
                'estimated_minutes' => 20,
                'investigation_db' => 'corporate_finance',
            ],

            // ─── INTERMEDIATE (Cases 11-20) ────────────────────
            [
                'case_code' => 'CASE-011',
                'title' => 'The Insider Trading Ring',
                'description' => 'Stock prices changed before major announcements. Find who leaked the information.',
                'difficulty' => 'intermediate',
                'category' => 'Financial Crime',
                'briefing' => 'The company\'s stock price showed unusual movement before three major announcements this quarter. Someone with inside knowledge must have traded ahead of the news. Cross-reference employee access to confidential documents with trading activity timestamps.',
                'objective' => 'Find employees who accessed confidential files (resource_type = "file") within 48 hours before major announcements. Return the employee_id, file accessed, and access_time.',
                'expected_result_description' => 'Identify employees with suspicious file access timing.',
                'xp_reward' => 250,
                'estimated_minutes' => 35,
                'investigation_db' => 'digital_forensics',
            ],
            [
                'case_code' => 'CASE-012',
                'title' => 'The Shadow Network',
                'description' => 'A hidden network of compromised devices is communicating. Map the connections.',
                'difficulty' => 'intermediate',
                'category' => 'Network Security',
                'briefing' => 'The security operations center detected unusual network traffic between internal devices and known malicious IPs. The traffic appears to be using covert channels. Investigate the network_logs to map the communication patterns and identify compromised devices.',
                'objective' => 'Find all network connections with status = "suspicious" and group them by source IP. Return the source_ip, count of connections, and total bytes transferred.',
                'expected_result_description' => 'Show suspicious network communication patterns by source.',
                'xp_reward' => 250,
                'estimated_minutes' => 30,
                'investigation_db' => 'digital_forensics',
            ],
            [
                'case_code' => 'CASE-013',
                'title' => 'The Fund Diversion',
                'description' => 'Company funds are being diverted through a chain of accounts. Trace the money flow.',
                'difficulty' => 'intermediate',
                'category' => 'Money Laundering',
                'briefing' => 'The forensic accountant discovered that ₹50 lakhs have been diverted through a series of transactions involving multiple accounts. The money starts from the company operating account, goes through several intermediary accounts, and ends up at an offshore account. Trace the complete money trail.',
                'objective' => 'Write a query to trace all transactions from account 1 (Main Operating) where the amount is greater than ₹5,00,000. Join with bank_accounts to show the source and destination account names. Return the transaction chain.',
                'expected_result_description' => 'Map the complete money flow from source to destination.',
                'xp_reward' => 300,
                'estimated_minutes' => 40,
                'investigation_db' => 'corporate_finance',
            ],
            [
                'case_code' => 'CASE-014',
                'title' => 'The Data Hoarder',
                'description' => 'An employee has been accumulating sensitive data. Find what they\'ve collected.',
                'difficulty' => 'intermediate',
                'category' => 'Data Theft',
                'briefing' => 'The DLP system flagged an employee who has been downloading large amounts of sensitive data over the past month. The downloads span multiple file types and departments. Investigate the file access logs and email records to determine the full scope of data collected.',
                'objective' => 'Find all file_access activities where the action was "read" and the resource_type was "file", grouped by employee. Return the employee_id, count of files accessed, and total file sizes.',
                'expected_result_description' => 'Identify employees with excessive file access patterns.',
                'xp_reward' => 250,
                'estimated_minutes' => 35,
                'investigation_db' => 'digital_forensics',
            ],
            [
                'case_code' => 'CASE-015',
                'title' => 'The Permission Cascade',
                'description' => 'One compromised account led to a chain of privilege escalations. Map the attack path.',
                'difficulty' => 'intermediate',
                'category' => 'Privilege Escalation',
                'briefing' => 'The security audit revealed that a junior employee somehow gained admin access. The investigation shows multiple permission changes over time, each building on the previous one. Map the complete chain of permission escalations to understand how the attack progressed.',
                'objective' => 'Find the complete chain of permission_changes ordered by change_time. Return the employee_id, changed_by, permission_type, old_value, new_value, and change_time.',
                'expected_result_description' => 'Show the progression of permission escalations over time.',
                'xp_reward' => 250,
                'estimated_minutes' => 30,
                'investigation_db' => 'employee_portal',
            ],
            [
                'case_code' => 'CASE-016',
                'title' => 'The Speed Demon',
                'description' => 'A user is running automated scripts against the database. Identify the pattern.',
                'difficulty' => 'intermediate',
                'category' => 'Automated Attacks',
                'briefing' => 'The database performance has degraded significantly. Analysis shows one user is executing hundreds of queries per minute, far beyond human capability. The queries appear to be automated and are targeting sensitive tables. Investigate the access_logs to identify the automated attack pattern.',
                'objective' => 'Find users who made more than 10 database queries in a single minute. Return the user_id, the minute (truncated timestamp), and query count.',
                'expected_result_description' => 'Identify automated high-frequency database access patterns.',
                'xp_reward' => 200,
                'estimated_minutes' => 25,
                'investigation_db' => 'digital_forensics',
            ],
            [
                'case_code' => 'CASE-017',
                'title' => 'The Money Mule',
                'description' => 'A recently joined employee\'s account is being used to funnel money. Investigate.',
                'difficulty' => 'intermediate',
                'category' => 'Financial Crime',
                'briefing' => 'A junior employee who joined 6 months ago suddenly has transactions worth ₹20 lakhs flowing through their personal account. The employee claims ignorance. Investigate the employee\'s transaction history, login patterns, and device usage to determine if they are a willing participant or an unwitting mule.',
                'objective' => 'Find all transactions involving employees hired in 2025 or later, where the amount exceeds ₹1,00,000. Join with employees and bank_accounts. Return the employee name, transaction amount, and account details.',
                'expected_result_description' => 'Identify suspicious transactions by recent hires.',
                'xp_reward' => 250,
                'estimated_minutes' => 35,
                'investigation_db' => 'corporate_finance',
            ],
            [
                'case_code' => 'CASE-018',
                'title' => 'The Certificate Heist',
                'description' => 'Digital certificates were copied from the secure server. Trace the access.',
                'difficulty' => 'intermediate',
                'category' => 'Data Theft',
                'briefing' => 'The PKI team discovered that several digital certificates were exported from the secure server without authorization. These certificates could be used to impersonate the company. Investigate the file access and system logs to determine who accessed the certificates and where they were sent.',
                'objective' => 'Find all file_access activities for certificate files (file_name containing "cert" or "pem" or "key"). Return the employee_id, file_name, action, and access_time.',
                'expected_result_description' => 'Trace access to digital certificate files.',
                'xp_reward' => 200,
                'estimated_minutes' => 25,
                'investigation_db' => 'digital_forensics',
            ],
            [
                'case_code' => 'CASE-019',
                'title' => 'The Access Anomaly',
                'description' => 'An employee\'s access patterns have changed dramatically. Investigate the shift.',
                'difficulty' => 'intermediate',
                'category' => 'Behavioral Analysis',
                'briefing' => 'The UEBA system flagged an employee whose access patterns have changed significantly over the past month. They used to access only finance-related resources but now frequently access engineering, HR, and admin resources. This pattern shift could indicate compromised credentials or malicious intent.',
                'objective' => 'Compare each employee\'s resource_type access in the first half vs second half of August 2026. Find employees who accessed more than 3 different resource types. Return the employee_id and distinct resource types accessed.',
                'expected_result_description' => 'Identify employees with abnormal access pattern changes.',
                'xp_reward' => 250,
                'estimated_minutes' => 35,
                'investigation_db' => 'employee_portal',
            ],
            [
                'case_code' => 'CASE-020',
                'title' => 'The Vendor Fraud',
                'description' => 'Fake vendor invoices are being submitted and paid. Find the connection.',
                'difficulty' => 'intermediate',
                'category' => 'Fraud Investigation',
                'briefing' => 'The internal audit discovered a scheme where fake vendor invoices are being submitted and approved for payment. The invoices come from vendors that don\'t exist in the approved vendor list. Investigate the transactions and bank accounts to identify the fraud pattern.',
                'objective' => 'Find payments to bank accounts where the owner_type = "external" that were initiated by employees in the Finance department. Return the employee name, account name, and transaction amount.',
                'expected_result_description' => 'Identify payments to external accounts initiated by finance staff.',
                'xp_reward' => 250,
                'estimated_minutes' => 35,
                'investigation_db' => 'corporate_finance',
            ],

            // ─── ADVANCED (Cases 21-30) ────────────────────────
            [
                'case_code' => 'CASE-021',
                'title' => 'TheAPT Attack',
                'description' => 'A sophisticated attack has been ongoing for months. Uncover the full kill chain.',
                'difficulty' => 'advanced',
                'category' => 'Advanced Persistent Threat',
                'briefing' => 'Threat intelligence indicates the company has been targeted by an APT group for the past 6 months. The attack has progressed through multiple stages: initial compromise, lateral movement, privilege escalation, and data exfiltration. Map the complete attack lifecycle using all available logs.',
                'objective' => 'Create a timeline of all suspicious activities (status = "suspicious" or "compromised") across all tables, ordered by timestamp. Return the event_type, timestamp, source_ip, and description.',
                'expected_result_description' => 'Build a complete APT timeline from initial compromise to exfiltration.',
                'xp_reward' => 500,
                'estimated_minutes' => 60,
                'investigation_db' => 'digital_forensics',
            ],
            [
                'case_code' => 'CASE-022',
                'title' => 'The Money Puzzle',
                'description' => '₹1 crore has been moved through 15+ transactions. Decode the complex laundering scheme.',
                'difficulty' => 'advanced',
                'category' => 'Money Laundering',
                'briefing' => 'The financial intelligence unit has flagged a complex money laundering scheme involving ₹1 crore. The money has been moved through 15+ transactions across multiple accounts, with each transaction splitting and recombining amounts. Use recursive queries or multi-level joins to trace the complete flow.',
                'objective' => 'Starting from the main operating account (account_id = 1), trace all outgoing transfers greater than ₹5,00,000 for at least 3 levels deep. Show each hop in the money trail with amounts and timestamps.',
                'expected_result_description' => 'Reconstruct the complete multi-hop money laundering chain.',
                'xp_reward' => 500,
                'estimated_minutes' => 60,
                'investigation_db' => 'corporate_finance',
            ],
            [
                'case_code' => 'CASE-023',
                'title' => 'The Zero Day',
                'description' => 'An unknown vulnerability was exploited. Piece together the attack from fragments.',
                'difficulty' => 'advanced',
                'category' => 'Exploit Analysis',
                'briefing' => 'The security team discovered evidence of a zero-day exploit being used against the company. The attack left fragments across multiple systems: unusual process starts, abnormal network connections, and modified file timestamps. Correlate all anomalies to reconstruct the exploit chain.',
                'objective' => 'Find all events where the severity is "critical" or "high", cross-reference with suspicious network connections and file modifications on the same dates. Return a correlated timeline of the attack.',
                'expected_result_description' => 'Reconstruct the zero-day exploit chain from multiple data sources.',
                'xp_reward' => 500,
                'estimated_minutes' => 60,
                'investigation_db' => 'digital_forensics',
            ],
            [
                'case_code' => 'CASE-024',
                'title' => 'The Chain of Command',
                'description' => 'A manager has been abusing their authority. Map the complete abuse pattern.',
                'difficulty' => 'advanced',
                'category' => 'Abuse of Authority',
                'briefing' => 'Multiple employees have complained about a manager who has been granting themselves excessive permissions and accessing resources outside their department. The manager has also been approving their own requests and bypassing normal approval workflows. Map the complete abuse pattern.',
                'objective' => 'Find all instances where a manager (level IN ("manager", "director", "vp")) performed actions that should have required approval from a higher level. Return the action, timestamp, and any self-approvals.',
                'expected_result_description' => 'Identify the complete pattern of authority abuse.',
                'xp_reward' => 400,
                'estimated_minutes' => 50,
                'investigation_db' => 'employee_portal',
            ],
            [
                'case_code' => 'CASE-025',
                'title' => 'The Silent Exfiltration',
                'description' => 'Data is being slowly leaked over months. Detect the drip-feed pattern.',
                'difficulty' => 'advanced',
                'category' => 'Data Exfiltration',
                'briefing' => 'The DLP system missed a slow data exfiltration because the volumes were small and spread over months. The attacker has been sending small amounts of data through email attachments, file uploads, and network transfers. Detect the cumulative pattern that individual events missed.',
                'objective' => 'Calculate the total data exported by each user over the past 3 months, broken down by method (email, file, network). Return the user, method, total bytes, and event count.',
                'expected_result_description' => 'Detect cumulative data exfiltration patterns over time.',
                'xp_reward' => 400,
                'estimated_minutes' => 50,
                'investigation_db' => 'digital_forensics',
            ],
            [
                'case_code' => 'CASE-026',
                'title' => 'The Phantom Transactions',
                'description' => 'Transactions exist in the ledger but not in the bank. Find the ghost money.',
                'difficulty' => 'advanced',
                'category' => 'Financial Forensics',
                'briefing' => 'The reconciliation team discovered discrepancies between the company\'s internal ledger and bank statements. Several transactions show as completed in the company system but the bank has no record of them. These phantom transactions total ₹75 lakhs. Investigate the transaction records to identify which ones are fabricated.',
                'objective' => 'Find transactions with status = "completed" but where the IP address is from an external range (not 192.168.x.x) and occurred between 2-4 AM. Return the transaction details and compare with normal transaction patterns.',
                'expected_result_description' => 'Identify phantom transactions that don\'t match bank records.',
                'xp_reward' => 450,
                'estimated_minutes' => 55,
                'investigation_db' => 'corporate_finance',
            ],
            [
                'case_code' => 'CASE-027',
                'title' => 'The Identity Thief',
                'description' => 'Someone is using stolen credentials. Identify the impersonation pattern.',
                'difficulty' => 'advanced',
                'category' => 'Identity Fraud',
                'briefing' => 'The authentication system logs show that two different IP addresses are logging into the same account within short time windows. This suggests stolen credentials being used from multiple locations. Identify all accounts showing this concurrent usage pattern and determine which sessions are legitimate.',
                'objective' => 'Find pairs of login records where the same employee_id logged in from different IP addresses within a 30-minute window. Return the employee_id, both IP addresses, and both login times.',
                'expected_result_description' => 'Identify credential sharing or theft through concurrent session analysis.',
                'xp_reward' => 400,
                'estimated_minutes' => 50,
                'investigation_db' => 'employee_portal',
            ],
            [
                'case_code' => 'CASE-028',
                'title' => 'The Corporate Spy',
                'description' => 'A competitor seems to know internal strategies. Find the information leak.',
                'difficulty' => 'advanced',
                'category' => 'Corporate Espionage',
                'briefing' => 'The company suspects a competitor has been receiving confidential information. Marketing strategy documents, product roadmaps, and bid proposals have appeared in the competitor\'s materials. Investigate email, file access, and network logs to find the leak vector.',
                'objective' => 'Find all instances where confidential files (files with "confidential", "strategy", "roadmap", or "bid" in the name) were accessed and then emailed externally within 24 hours. Return the employee, file, email details, and timeline.',
                'expected_result_description' => 'Trace the information flow from internal files to external emails.',
                'xp_reward' => 450,
                'estimated_minutes' => 55,
                'investigation_db' => 'digital_forensics',
            ],
            [
                'case_code' => 'CASE-029',
                'title' => 'The Cover-Up',
                'description' => 'Logs have been tampered with to hide evidence. Detect the alterations.',
                'difficulty' => 'advanced',
                'category' => 'Forensic Analysis',
                'briefing' => 'The security team suspects that logs have been modified to cover up malicious activity. Timestamps don\'t align, some entries are missing, and certain records show editing patterns inconsistent with normal system behavior. Use forensic analysis to detect the log tampering.',
                'objective' => 'Find anomalies in the logs table: entries where timestamps are out of sequence, duplicate entries, or gaps in the log sequence. Also cross-reference with the timestamps table for inconsistencies. Return all detected anomalies.',
                'expected_result_description' => 'Detect log tampering through timestamp and sequence analysis.',
                'xp_reward' => 500,
                'estimated_minutes' => 60,
                'investigation_db' => 'digital_forensics',
            ],
            [
                'case_code' => 'CASE-030',
                'title' => 'The Mastermind',
                'description' => 'All previous cases are connected. Uncover the person behind everything.',
                'difficulty' => 'advanced',
                'category' => 'Criminal Mastermind',
                'briefing' => 'This is the final investigation. All the previous cases — the financial fraud, the data breaches, the insider threats — are connected. The mastermind has been orchestrating everything while staying hidden behind layers of indirection. Using all the evidence gathered from cases 001-029, identify the common links and expose the criminal mastermind.',
                'objective' => 'Write a query that finds the common IP addresses, devices, or employees that appear across multiple suspicious activities in the corporate_finance database. Return the connecting elements and their frequency across different types of suspicious activities.',
                'expected_result_description' => 'Connect the dots across all cases to identify the mastermind.',
                'xp_reward' => 1000,
                'estimated_minutes' => 90,
                'investigation_db' => 'corporate_finance',
            ],
        ];

        foreach ($cases as $case) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM cases WHERE case_code = ?");
            $stmt->execute([$case['case_code']]);
            if ($stmt->fetchColumn() === 0) {
                $this->db->prepare("
                    INSERT INTO cases (case_code, title, description, difficulty, category, briefing, objective, expected_result_description, xp_reward, estimated_minutes, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
                ")->execute([
                    $case['case_code'],
                    $case['title'],
                    $case['description'],
                    $case['difficulty'],
                    $case['category'],
                    $case['briefing'],
                    $case['objective'],
                    $case['expected_result_description'],
                    $case['xp_reward'],
                    $case['estimated_minutes'],
                ]);
                echo "Case {$case['case_code']} created\n";
            }
        }
    }

    private function seedChallenges(): void
    {
        $challenges = [
            // CASE-001 challenges
            ['CASE-001', 'Identify Suspicious Transactions', 'Find all transactions that occurred between 2:00 AM and 4:00 AM with amounts greater than ₹5,00,000.', 'easy', 50, 'SELECT * FROM transactions WHERE HOUR(transaction_date) BETWEEN 2 AND 4 AND amount > 500000', '{"type": "row_count", "min": 5}'],
            ['CASE-001', 'Find the Culprit', 'Which employee initiated the most suspicious (late-night) transactions? Return their employee_code and name.', 'medium', 100, 'SELECT e.employee_code, e.first_name, e.last_name FROM employees e JOIN transactions t ON e.id = t.initiated_by WHERE HOUR(t.transaction_date) BETWEEN 2 AND 4 GROUP BY e.id ORDER BY COUNT(*) DESC LIMIT 1', '{"type": "value_check", "column": "employee_code", "expected": "EMP005"}'],
            ['CASE-001', 'Track the Device', 'What device was used for the suspicious transactions? Return the device_id.', 'hard', 150, 'SELECT DISTINCT device_id FROM transactions WHERE HOUR(transaction_date) BETWEEN 2 AND 4 AND amount > 500000', '{"type": "row_count", "min": 1}'],

            // CASE-002 challenges
            ['CASE-002', 'External Access', 'Find all activities from IPs not in the 10.0.x.x or 192.168.x.x range on August 12, 2026.', 'easy', 50, 'SELECT * FROM activities WHERE timestamp LIKE "2026-08-12%" AND ip_address NOT LIKE "10.0.%" AND ip_address NOT LIKE "192.168.%"', '{"type": "row_count", "min": 3}'],
            ['CASE-002', 'Compromised Files', 'Which files were accessed from the compromised device (DEV-UNKNOWN-01)?', 'medium', 100, 'SELECT f.file_name, f.file_path FROM files f JOIN activities a ON f.device_id = a.device_id WHERE a.device_id = (SELECT id FROM devices WHERE device_id = "DEV-UNKNOWN-01")', '{"type": "row_count", "min": 1}'],

            // CASE-003 challenges
            ['CASE-003', 'Self-Escalation', 'Find all permission changes where employee_id = changed_by.', 'easy', 50, 'SELECT * FROM permission_changes WHERE employee_id = changed_by', '{"type": "row_count", "min": 2}'],
            ['CASE-003', 'Data Exports', 'Which employees exported data from suspicious IPs?', 'medium', 100, 'SELECT DISTINCT employee_id FROM access_logs WHERE ip_address NOT LIKE "192.168.%" AND action = "export"', '{"type": "row_count", "min": 1}'],
        ];

        $caseMap = [];
        $stmt = $this->db->query("SELECT id, case_code FROM cases");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $caseMap[$row['case_code']] = $row['id'];
        }

        foreach ($challenges as $ch) {
            $caseId = $caseMap[$ch[0]] ?? null;
            if (!$caseId) continue;

            $stmt = $this->db->prepare("SELECT COUNT(*) FROM challenges WHERE case_id = ? AND title = ?");
            $stmt->execute([$caseId, $ch[1]]);
            if ($stmt->fetchColumn() === 0) {
                $this->db->prepare("
                    INSERT INTO challenges (case_id, title, description, difficulty, xp_reward, validation_rules, display_order)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ")->execute([
                    $caseId,
                    $ch[1],
                    $ch[2],
                    $ch[3],
                    $ch[4],
                    $ch[6],
                    $ch[4] / 50,
                ]);
                echo "Challenge '{$ch[1]}' created for {$ch[0]}\n";
            }
        }
    }

    private function seedHints(): void
    {
        $hints = [
            // CASE-001 hints
            ['CASE-001', 'Identify Suspicious Transactions', 1, 'Think about what hours most people are sleeping. Transactions between 2 AM and 4 AM are unusual.', 5],
            ['CASE-001', 'Identify Suspicious Transactions', 2, 'Use the HOUR() function to extract the hour from transaction_date.', 10],
            ['CASE-001', 'Find the Culprit', 1, 'You need to join the transactions table with the employees table using the initiated_by foreign key.', 10],
            ['CASE-001', 'Find the Culprit', 2, 'GROUP BY the employee and ORDER BY COUNT(*) DESC to find the most frequent offender.', 20],
            ['CASE-001', 'Track the Device', 1, 'The suspicious transactions all came from the same device. Look at the device_id column.', 15],

            // CASE-002 hints
            ['CASE-002', 'External Access', 1, 'Internal IPs start with 10.0.x.x or 192.168.x.x. Use NOT LIKE to filter them out.', 5],
            ['CASE-002', 'Compromised Files', 1, 'First find the device ID for DEV-UNKNOWN-01, then find which files were associated with that device.', 10],

            // CASE-003 hints
            ['CASE-003', 'Self-Escalation', 1, 'Compare the employee_id and changed_by columns. If they are the same, the employee changed their own permissions.', 5],
            ['CASE-003', 'Data Exports', 1, 'External IPs do not start with 192.168. Filter for export actions.', 10],
        ];

        $caseMap = [];
        $stmt = $this->db->query("SELECT id, case_code FROM cases");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $caseMap[$row['case_code']] = $row['id'];
        }

        $challengeMap = [];
        $stmt = $this->db->query("SELECT id, case_id, title FROM challenges");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $challengeMap[$row['case_id'] . '|' . $row['title']] = $row['id'];
        }

        foreach ($hints as $h) {
            $caseId = $caseMap[$h[0]] ?? null;
            if (!$caseId) continue;
            $challengeId = $challengeMap[$caseId . '|' . $h[1]] ?? null;
            if (!$challengeId) continue;

            $stmt = $this->db->prepare("SELECT COUNT(*) FROM hints WHERE challenge_id = ? AND hint_level = ?");
            $stmt->execute([$challengeId, $h[2]]);
            if ($stmt->fetchColumn() === 0) {
                $this->db->prepare("
                    INSERT INTO hints (challenge_id, hint_level, hint_text, xp_penalty)
                    VALUES (?, ?, ?, ?)
                ")->execute([$challengeId, $h[2], $h[3], $h[4]]);
            }
        }
        echo "Hints seeded\n";
    }

    private function seedAchievements(): void
    {
        $achievements = [
            ['First Investigation', 'Complete your first case.', '🔍', 'first_case', 1, 50],
            ['Clean Query', 'Solve a challenge without using any hints.', '💎', 'no_hints', 1, 100],
            ['SQL Master', 'Complete 10 advanced challenges.', '🏆', 'challenges_solved', 10, 500],
            ['No Errors', 'Complete a case without any incorrect queries.', '🎯', 'perfect_cases', 1, 200],
            ['Speed Detective', 'Solve a case under the target time.', '⚡', 'speed', 1, 150],
            ['Database Explorer', 'Inspect every table in a case.', '🗄️', 'explorer', 1, 100],
            ['Persistent Investigator', 'Complete 7 cases.', '📋', 'cases_completed', 7, 300],
            ['Perfect Investigation', 'Complete a case with maximum XP.', '✨', 'perfect_cases', 1, 250],
            ['Level 5 Reached', 'Reach level 5.', '⭐', 'level_milestone', 5, 100],
            ['Level 10 Reached', 'Reach level 10.', '⭐⭐', 'level_milestone', 10, 250],
            ['Level 20 Reached', 'Reach level 20.', '⭐⭐⭐', 'level_milestone', 20, 500],
            ['XP Milestone: 1000', 'Earn 1000 total XP.', '💰', 'xp_milestone', 1000, 100],
            ['XP Milestone: 5000', 'Earn 5000 total XP.', '💰💰', 'xp_milestone', 5000, 500],
            ['7-Day Streak', 'Maintain a 7-day investigation streak.', '🔥', 'streak', 7, 200],
            ['30-Day Streak', 'Maintain a 30-day investigation streak.', '🔥🔥', 'streak', 30, 1000],
            ['Beginner Graduated', 'Complete all 10 beginner cases.', '🎓', 'difficulty_complete', 10, 500],
            ['Intermediate Graduate', 'Complete all 10 intermediate cases.', '🎓🎓', 'difficulty_complete', 20, 750],
            ['Master Detective', 'Complete all 30 cases.', '👑', 'cases_completed', 30, 2000],
            ['Query Artist', 'Write 100 queries total.', '🎨', 'total_queries', 100, 300],
            ['SQL Guru', 'Write 500 queries total.', '🧙', 'total_queries', 500, 1000],
        ];

        foreach ($achievements as $achievement) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM achievements WHERE name = ?");
            $stmt->execute([$achievement[0]]);
            if ($stmt->fetchColumn() === 0) {
                $this->db->prepare("
                    INSERT INTO achievements (name, description, icon, requirement_type, requirement_value, xp_reward)
                    VALUES (?, ?, ?, ?, ?, ?)
                ")->execute($achievement);
                echo "Achievement '{$achievement[0]}' created\n";
            }
        }
    }
}