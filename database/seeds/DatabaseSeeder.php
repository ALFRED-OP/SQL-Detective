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
        $this->seedCaseDatabases();
        $this->seedChallenges();
        $this->seedHints();
        $this->seedSuspects();
        $this->seedEvidence();
        $this->seedAchievements();
        $this->loadInvestigationData();
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
                'investigation_db' => 'corporatefinance',
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
                'investigation_db' => 'digitalforensics',
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
                'investigation_db' => 'employeeportal',
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
                'investigation_db' => 'corporatefinance',
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
                'investigation_db' => 'digitalforensics',
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
                'investigation_db' => 'employeeportal',
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
                'investigation_db' => 'corporatefinance',
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
                'investigation_db' => 'digitalforensics',
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
                'investigation_db' => 'employeeportal',
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
                'investigation_db' => 'corporatefinance',
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
                'investigation_db' => 'digitalforensics',
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
                'investigation_db' => 'digitalforensics',
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
                'investigation_db' => 'corporatefinance',
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
                'investigation_db' => 'digitalforensics',
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
                'investigation_db' => 'employeeportal',
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
                'investigation_db' => 'digitalforensics',
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
                'investigation_db' => 'corporatefinance',
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
                'investigation_db' => 'digitalforensics',
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
                'investigation_db' => 'employeeportal',
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
                'investigation_db' => 'corporatefinance',
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
                'investigation_db' => 'digitalforensics',
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
                'investigation_db' => 'corporatefinance',
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
                'investigation_db' => 'digitalforensics',
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
                'investigation_db' => 'employeeportal',
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
                'investigation_db' => 'digitalforensics',
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
                'investigation_db' => 'corporatefinance',
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
                'investigation_db' => 'employeeportal',
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
                'investigation_db' => 'digitalforensics',
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
                'investigation_db' => 'digitalforensics',
            ],
            [
                'case_code' => 'CASE-030',
                'title' => 'The Mastermind',
                'description' => 'All previous cases are connected. Uncover the person behind everything.',
                'difficulty' => 'advanced',
                'category' => 'Criminal Mastermind',
                'briefing' => 'This is the final investigation. All the previous cases — the financial fraud, the data breaches, the insider threats — are connected. The mastermind has been orchestrating everything while staying hidden behind layers of indirection. Using all the evidence gathered from cases 001-029, identify the common links and expose the criminal mastermind.',
                'objective' => 'Write a query that finds the common IP addresses, devices, or employees that appear across multiple suspicious activities in the corporatefinance database. Return the connecting elements and their frequency across different types of suspicious activities.',
                'expected_result_description' => 'Connect the dots across all cases to identify the mastermind.',
                'xp_reward' => 1000,
                'estimated_minutes' => 90,
                'investigation_db' => 'corporatefinance',
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

    private function seedCaseDatabases(): void
    {
        $caseDbMap = [
            'CASE-001' => ['corporatefinance', 'Financial transactions, employee accounts, and audit logs'],
            'CASE-002' => ['digitalforensics', 'User activities, files, network logs, and communications'],
            'CASE-003' => ['employeeportal', 'Employee records, access logs, and permission changes'],
            'CASE-004' => ['corporatefinance', 'Login attempts and IP tracking data'],
            'CASE-005' => ['digitalforensics', 'Service account activities and scheduling data'],
            'CASE-006' => ['employeeportal', 'Audit trail and record modifications'],
            'CASE-007' => ['corporatefinance', 'Department budgets and employee spending'],
            'CASE-008' => ['digitalforensics', 'Email communications and network logs'],
            'CASE-009' => ['employeeportal', 'Login records and geographic data'],
            'CASE-010' => ['corporatefinance', 'Transaction patterns and duplicate detection'],
            'CASE-011' => ['digitalforensics', 'File access patterns and timestamps'],
            'CASE-012' => ['digitalforensics', 'Network traffic and data transfers'],
            'CASE-013' => ['corporatefinance', 'Money flow through bank accounts'],
            'CASE-014' => ['digitalforensics', 'File access volumes and user activities'],
            'CASE-015' => ['employeeportal', 'Permission change chains and escalation'],
            'CASE-016' => ['digitalforensics', 'Database queries and automation patterns'],
            'CASE-017' => ['corporatefinance', 'Recent hires and transaction patterns'],
            'CASE-018' => ['digitalforensics', 'Certificate and key file access'],
            'CASE-019' => ['employeeportal', 'Access pattern analysis across resources'],
            'CASE-020' => ['corporatefinance', 'External payments and fraud detection'],
            'CASE-021' => ['digitalforensics', 'APT timeline and attack reconstruction'],
            'CASE-022' => ['corporatefinance', 'Multi-hop money laundering trails'],
            'CASE-023' => ['digitalforensics', 'System logs and security events'],
            'CASE-024' => ['employeeportal', 'Manager self-approvals and access abuse'],
            'CASE-025' => ['digitalforensics', 'Data exfiltration methods and totals'],
            'CASE-026' => ['corporatefinance', 'Phantom transactions and fraud patterns'],
            'CASE-027' => ['employeeportal', 'Concurrent sessions and identity theft'],
            'CASE-028' => ['digitalforensics', 'Info leaks and email-file correlations'],
            'CASE-029' => ['digitalforensics', 'Log integrity and tampering detection'],
            'CASE-030' => ['corporatefinance', 'Cross-database correlation and mastermind identification'],
        ];

        $caseMap = [];
        $stmt = $this->db->query("SELECT id, case_code FROM cases");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $caseMap[$row['case_code']] = $row['id'];
        }

        $descriptions = [
            'corporatefinance' => 'Financial database containing employee records, transactions, bank accounts, and audit logs',
            'digitalforensics' => 'Forensic database with user activities, files, network logs, emails, and system events',
            'employeeportal' => 'Employee portal database with access logs, permission changes, and audit trails',
        ];

        foreach ($caseDbMap as $caseCode => [$dbName, $description]) {
            $caseId = $caseMap[$caseCode] ?? null;
            if (!$caseId) continue;

            $stmt = $this->db->prepare("SELECT COUNT(*) FROM case_databases WHERE case_id = ?");
            $stmt->execute([$caseId]);
            if ($stmt->fetchColumn() === 0) {
                $this->db->prepare("
                    INSERT INTO case_databases (case_id, database_name, database_description)
                    VALUES (?, ?, ?)
                ")->execute([$caseId, $dbName, $descriptions[$dbName] ?? $description]);
                echo "Case database mapping created for {$caseCode} -> {$dbName}\n";
            }
        }
    }

    private function loadInvestigationData(): void
    {
        $config = require base_path('config/database.php');
        $conn = $config['connections'][$config['default']];
        $rootUser = env('DB_ROOT_USER', $conn['username']);
        $rootPass = env('DB_ROOT_PASSWORD', $conn['password']);
        $dsn = "{$conn['driver']}:host={$conn['host']};port={$conn['port']};charset={$conn['charset']}";
        $pdo = new PDO($dsn, $rootUser, $rootPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $dbMap = [
            'corporatefinance' => base_path('database/investigation_databases/case_001'),
            'digitalforensics' => base_path('database/investigation_databases/case_002'),
            'employeeportal'   => base_path('database/investigation_databases/case_003'),
        ];

        foreach ($dbMap as $dbName => $dir) {
            try {
                $pdo->exec("DROP DATABASE IF EXISTS `" . $dbName . "`");
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . $dbName . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo->exec("USE `" . $dbName . "`");
            } catch (\Throwable $e) {
                echo "ERROR preparing database {$dbName}: " . $e->getMessage() . "\n";
                continue;
            }

            $sqlFiles = ['schema.sql', 'data.sql', 'supplemental_data.sql'];
            foreach ($sqlFiles as $file) {
                $path = $dir . '/' . $file;
                if (!file_exists($path)) continue;

                echo "Loading {$file} into {$dbName}...\n";
                $sql = file_get_contents($path);
                $statements = $this->splitSqlStatements($sql);
                $stmtCount = 0;
                foreach ($statements as $statement) {
                    $statement = trim($statement);
                    if (!empty($statement) && !str_starts_with($statement, '--')) {
                        $stmtCount++;
                        try {
                            $pdo->exec($statement);
                        } catch (\Throwable $e) {
                            echo "  WARNING (stmt #{$stmtCount}): " . $e->getMessage() . "\n";
                            echo "  SQL: " . substr($statement, 0, 200) . "\n";
                        }
                    }
                }
                echo "  Executed {$stmtCount} statements from {$file}\n";
            }
        }

        echo "Investigation databases loaded successfully\n";

        $invConfig = $config['connections']['investigation'];
        $invUser = $invConfig['username'];
        $invPass = $invConfig['password'];
        if ($invUser && $invPass) {
            echo "Setting up read-only user: {$invUser}\n";
            try {
                $pdo->exec("CREATE USER IF NOT EXISTS '" . $invUser . "'@'%' IDENTIFIED BY '" . addslashes($invPass) . "'");
                foreach (array_keys($dbMap) as $dbName) {
                    $pdo->exec("GRANT SELECT ON `" . $dbName . "`.* TO '" . $invUser . "'@'%'");
                }
                $pdo->exec("FLUSH PRIVILEGES");
                echo "Read-only user {$invUser} configured with SELECT on investigation databases\n";
            } catch (\Throwable $e) {
                echo "WARNING: Could not create read-only user: " . $e->getMessage() . "\n";
            }
        }
    }

    private function splitSqlStatements(string $sql): array
    {
        $statements = [];
        $current = '';
        $inString = false;
        $stringChar = '';
        $len = strlen($sql);

        for ($i = 0; $i < $len; $i++) {
            $char = $sql[$i];

            if ($inString) {
                $current .= $char;
                if ($char === '\\' && $i + 1 < $len) {
                    $current .= $sql[++$i];
                } elseif ($char === $stringChar) {
                    $inString = false;
                }
            } else {
                if ($char === "'" || $char === '"') {
                    $inString = true;
                    $stringChar = $char;
                    $current .= $char;
                } elseif ($char === '-' && $i + 1 < $len && $sql[$i + 1] === '-') {
                    while ($i < $len && $sql[$i] !== "\n") {
                        $i++;
                    }
                } elseif ($char === '/' && $i + 1 < $len && $sql[$i + 1] === '*') {
                    $i += 2;
                    while ($i < $len - 1 && !($sql[$i] === '*' && $sql[$i + 1] === '/')) {
                        $i++;
                    }
                    $i++;
                } elseif ($char === ';') {
                    $trimmed = trim($current);
                    if (!empty($trimmed)) {
                        $statements[] = $current;
                    }
                    $current = '';
                } else {
                    $current .= $char;
                }
            }
        }

        $trimmed = trim($current);
        if (!empty($trimmed)) {
            $statements[] = $current;
        }

        return $statements;
    }

    private function seedChallenges(): void
    {
        $challenges = [
            // CASE-001 challenges
            ['CASE-001', 'Identify Suspicious Transactions', 'Find all transactions that occurred between 2:00 AM and 4:00 AM with amounts greater than ₹5,00,000.', 'beginner', 50, 'SELECT * FROM transactions WHERE HOUR(transaction_date) BETWEEN 2 AND 4 AND amount > 500000', '{"type": "row_count", "min": 5}'],
            ['CASE-001', 'Find the Culprit', 'Which employee initiated the most suspicious (late-night) transactions? Return their employee_code and name.', 'intermediate', 100, 'SELECT e.employee_code, e.first_name, e.last_name FROM employees e JOIN transactions t ON e.id = t.initiated_by WHERE HOUR(t.transaction_date) BETWEEN 2 AND 4 GROUP BY e.id ORDER BY COUNT(*) DESC LIMIT 1', '{"type": "value_check", "column": "employee_code", "expected": "EMP005"}'],
            ['CASE-001', 'Track the Device', 'What device was used for the suspicious transactions? Return the device_id.', 'advanced', 150, 'SELECT DISTINCT device_id FROM transactions WHERE HOUR(transaction_date) BETWEEN 2 AND 4 AND amount > 500000', '{"type": "row_count", "min": 1}'],

            // CASE-002 challenges
            ['CASE-002', 'External Access', 'Find all activities from IPs not in the 10.0.x.x or 192.168.x.x range on August 12, 2026.', 'beginner', 50, 'SELECT * FROM activities WHERE timestamp LIKE "2026-08-12%" AND ip_address NOT LIKE "10.0.%" AND ip_address NOT LIKE "192.168.%"', '{"type": "row_count", "min": 3}'],
            ['CASE-002', 'Compromised Files', 'Which files were accessed from the compromised device (DEV-UNKNOWN-01)?', 'intermediate', 100, 'SELECT f.file_name, f.file_path FROM files f JOIN activities a ON f.device_id = a.device_id WHERE a.device_id = (SELECT id FROM devices WHERE device_id = "DEV-UNKNOWN-01")', '{"type": "row_count", "min": 1}'],

            // CASE-003 challenges
            ['CASE-003', 'Self-Escalation', 'Find all permission changes where employee_id = changed_by.', 'beginner', 50, 'SELECT * FROM permission_changes WHERE employee_id = changed_by', '{"type": "row_count", "min": 2}'],
            ['CASE-003', 'Data Exports', 'Which employees exported data from suspicious IPs?', 'intermediate', 100, 'SELECT DISTINCT employee_id FROM access_logs WHERE ip_address NOT LIKE "192.168.%" AND action = "export"', '{"type": "row_count", "min": 1}'],

            // CASE-004 challenges
            ['CASE-004', 'Non-Office Logins', 'Find all login attempts from IPs not in the 192.168.x.x office range.', 'beginner', 50, 'SELECT employee_id, login_time, ip_address, status FROM login_logs WHERE ip_address NOT LIKE "192.168.%"', '{"type": "row_count", "min": 10}'],
            ['CASE-004', 'Brute Force Attack', 'Which admin account was targeted with the most failed login attempts from external IPs?', 'intermediate', 100, 'SELECT employee_id, COUNT(*) as attempts FROM login_logs WHERE ip_address NOT LIKE "192.168.%" AND status = "failed" GROUP BY employee_id ORDER BY attempts DESC LIMIT 1', '{"type": "value_check", "column": "employee_id", "expected": "2"}'],

            // CASE-005 challenges
            ['CASE-005', 'Odd Hour Activity', 'Find all activities by service accounts (user_id IN (9, 10)) that occurred outside business hours (before 6 AM or after 10 PM).', 'beginner', 50, 'SELECT user_id, action_type, timestamp FROM activities WHERE user_id IN (9, 10) AND (HOUR(timestamp) < 6 OR HOUR(timestamp) >= 22)', '{"type": "row_count", "min": 8}'],
            ['CASE-005', 'Service Account Abuse', 'Which service account performed the most suspicious activities outside business hours?', 'intermediate', 100, 'SELECT user_id, COUNT(*) as suspicious_count FROM activities WHERE user_id IN (9, 10) AND (HOUR(timestamp) < 6 OR HOUR(timestamp) >= 22) GROUP BY user_id ORDER BY suspicious_count DESC LIMIT 1', '{"type": "value_check", "column": "user_id", "expected": "9"}'],

            // CASE-006 challenges
            ['CASE-006', 'Deleted Records', 'Find all delete operations on the employees table from the audit_trail.', 'beginner', 50, 'SELECT record_id, performed_by, performed_at FROM audit_trail WHERE table_name = "employees" AND action = "delete"', '{"type": "row_count", "min": 2}'],
            ['CASE-006', 'Who Did It', 'Which employee performed the most delete operations on employee records?', 'intermediate', 100, 'SELECT performed_by, COUNT(*) as deletions FROM audit_trail WHERE table_name = "employees" AND action = "delete" GROUP BY performed_by ORDER BY deletions DESC LIMIT 1', '{"type": "value_check", "column": "performed_by", "expected": "5"}'],

            // CASE-007 challenges
            ['CASE-007', 'Budget vs Spending', 'Compare each department\'s budget with the total spending initiated by their employees. Return department code, budget, and total spending.', 'beginner', 60, 'SELECT d.code, d.budget, SUM(t.amount) as total_spending FROM departments d JOIN employees e ON d.id = e.department_id JOIN transactions t ON e.id = t.initiated_by GROUP BY d.id', '{"type": "row_count", "min": 4}'],
            ['CASE-007', 'Over Budget', 'Which department exceeded its budget? Return the department code and the amount over budget.', 'intermediate', 120, 'SELECT d.code, SUM(t.amount) - d.budget as over_budget FROM departments d JOIN employees e ON d.id = e.department_id JOIN transactions t ON e.id = t.initiated_by GROUP BY d.id HAVING SUM(t.amount) > d.budget', '{"type": "row_count", "min": 1}'],

            // CASE-008 challenges
            ['CASE-008', 'Phishing Detection', 'Find all emails with suspicious subjects or from external senders (sender_id IS NULL).', 'beginner', 50, 'SELECT message_id, subject, sent_at FROM emails WHERE sender_id IS NULL OR subject LIKE "%password%" OR subject LIKE "%verify%" OR subject LIKE "%bonus%"', '{"type": "row_count", "min": 5}'],
            ['CASE-008', 'Compromised Account', 'Which internal account was used to forward phishing emails externally?', 'intermediate', 100, 'SELECT sender_id, COUNT(*) as phishing_count FROM emails WHERE sender_id IS NOT NULL AND (recipient_ids LIKE "%external%" OR subject LIKE "%FW:%") GROUP BY sender_id ORDER BY phishing_count DESC LIMIT 1', '{"type": "value_check", "column": "sender_id", "expected": "6"}'],

            // CASE-009 challenges
            ['CASE-009', 'Night Logins', 'Find all successful logins between 11 PM and 5 AM.', 'beginner', 50, 'SELECT employee_id, login_time, ip_address, location FROM login_records WHERE status = "success" AND (HOUR(login_time) >= 23 OR HOUR(login_time) < 5)', '{"type": "row_count", "min": 5}'],
            ['CASE-009', 'Night Owl Employees', 'Which employee logged in at night the most times? Return employee_id and count.', 'intermediate', 100, 'SELECT employee_id, COUNT(*) as night_logins FROM login_records WHERE status = "success" AND (HOUR(login_time) >= 23 OR HOUR(login_time) < 5) GROUP BY employee_id ORDER BY night_logins DESC LIMIT 1', '{"type": "value_check", "column": "employee_id", "expected": "5"}'],

            // CASE-010 challenges
            ['CASE-010', 'Find Duplicates', 'Find transactions where the same amount was sent to the same to_account_id on the same date.', 'beginner', 60, 'SELECT amount, to_account_id, DATE(transaction_date) as txn_date, COUNT(*) as occurrences FROM transactions GROUP BY amount, to_account_id, DATE(transaction_date) HAVING COUNT(*) > 1', '{"type": "row_count", "min": 3}'],
            ['CASE-010', 'Total Fraud', 'What is the total amount of duplicate payments?', 'intermediate', 120, 'SELECT SUM(amount) as total_duplicate FROM (SELECT amount, to_account_id, DATE(transaction_date) as txn_date FROM transactions GROUP BY amount, to_account_id, DATE(transaction_date) HAVING COUNT(*) > 1) duplicates JOIN transactions t ON t.amount = duplicates.amount AND DATE(t.transaction_date) = duplicates.txn_date', '{"type": "row_count", "min": 1}'],

            // CASE-011 challenges
            ['CASE-011', 'File Access Timing', 'Find employees who accessed files with "confidential", "strategy", "roadmap", or "bid" in the name.', 'beginner', 75, 'SELECT DISTINCT user_id, description, timestamp FROM activities WHERE action_type = "file_access" AND (description LIKE "%confidential%" OR description LIKE "%strategy%" OR description LIKE "%roadmap%" OR description LIKE "%bid%")', '{"type": "row_count", "min": 5}'],
            ['CASE-011', 'Insider Pattern', 'Which user accessed the most confidential-sounding files?', 'intermediate', 125, 'SELECT user_id, COUNT(*) as file_count FROM activities WHERE action_type = "file_access" AND (description LIKE "%confidential%" OR description LIKE "%strategy%" OR description LIKE "%roadmap%" OR description LIKE "%bid%") GROUP BY user_id ORDER BY file_count DESC LIMIT 1', '{"type": "value_check", "column": "user_id", "expected": "3"}'],

            // CASE-012 challenges
            ['CASE-012', 'Suspicious Traffic', 'Find all network connections with status = "suspicious", grouped by source IP.', 'beginner', 75, 'SELECT source_ip, COUNT(*) as connection_count, SUM(bytes_sent) as total_bytes FROM network_logs WHERE status = "suspicious" GROUP BY source_ip', '{"type": "row_count", "min": 3}'],
            ['CASE-012', 'Top Exfiltrator', 'Which source IP transferred the most data over suspicious connections?', 'intermediate', 125, 'SELECT source_ip, SUM(bytes_sent) as total_bytes FROM network_logs WHERE status = "suspicious" GROUP BY source_ip ORDER BY total_bytes DESC LIMIT 1', '{"type": "value_check", "column": "source_ip", "expected": "203.0.113.50"}'],

            // CASE-013 challenges
            ['CASE-013', 'Trace the Money', 'Find all transactions from account 1 where amount > 500000. Join with bank_accounts to show source and destination names.', 'beginner', 75, 'SELECT t.transaction_id, t.amount, ba_from.account_name as from_account, ba_to.account_name as to_account, t.transaction_date FROM transactions t JOIN bank_accounts ba_from ON t.from_account_id = ba_from.id JOIN bank_accounts ba_to ON t.to_account_id = ba_to.id WHERE t.from_account_id = 1 AND t.amount > 500000', '{"type": "row_count", "min": 8}'],
            ['CASE-013', 'Follow the Trail', 'Trace the money flow from the main operating account through intermediary accounts to external accounts. Show each hop.', 'advanced', 150, 'SELECT t.transaction_id, ba_from.account_name as from_acct, ba_to.account_name as to_acct, t.amount, t.transaction_date FROM transactions t JOIN bank_accounts ba_from ON t.from_account_id = ba_from.id JOIN bank_accounts ba_to ON t.to_account_id = ba_to.id WHERE t.from_account_id IN (1, 14, 15) AND t.amount > 500000 ORDER BY t.transaction_date', '{"type": "row_count", "min": 5}'],

            // CASE-014 challenges
            ['CASE-014', 'Data Hoarder', 'Find all file_access activities grouped by employee. Which employee accessed the most files?', 'beginner', 75, 'SELECT user_id, COUNT(*) as files_accessed FROM activities WHERE action_type = "file_access" GROUP BY user_id ORDER BY files_accessed DESC', '{"type": "value_check", "column": "user_id", "expected": "3"}'],
            ['CASE-014', 'Volume Check', 'How many total bytes of data did the data hoarder access?', 'advanced', 150, 'SELECT user_id, SUM(CAST(JSON_UNQUOTE(JSON_EXTRACT(details, "$.size_bytes")) AS UNSIGNED)) as total_bytes FROM activities WHERE action_type = "file_access" GROUP BY user_id ORDER BY total_bytes DESC LIMIT 1', '{"type": "row_count", "min": 1}'],

            // CASE-015 challenges
            ['CASE-015', 'Escalation Chain', 'Find the complete chain of permission_changes ordered by change_time.', 'beginner', 75, 'SELECT employee_id, changed_by, permission_type, old_value, new_value, change_time FROM permission_changes ORDER BY change_time', '{"type": "row_count", "min": 10}'],
            ['CASE-015', 'Self-Escalator', 'Which employee made the most self-changes (employee_id = changed_by)?', 'intermediate', 125, 'SELECT employee_id, COUNT(*) as self_changes FROM permission_changes WHERE employee_id = changed_by GROUP BY employee_id ORDER BY self_changes DESC LIMIT 1', '{"type": "value_check", "column": "employee_id", "expected": "5"}'],

            // CASE-016 challenges
            ['CASE-016', 'Query Storm', 'Find users who made more than 10 database queries in a single minute.', 'beginner', 75, 'SELECT user_id, DATE_FORMAT(timestamp, "%Y-%m-%d %H:%i") as minute_window, COUNT(*) as query_count FROM activities WHERE action_type = "database_query" GROUP BY user_id, DATE_FORMAT(timestamp, "%Y-%m-%d %H:%i") HAVING query_count > 10', '{"type": "row_count", "min": 1}'],
            ['CASE-016', 'Bot Detection', 'How many automated queries did the suspect user execute in total during the attack window?', 'intermediate', 125, 'SELECT COUNT(*) as total_queries FROM activities WHERE action_type = "database_query" AND user_id = 6 AND timestamp BETWEEN "2026-08-12 02:30:00" AND "2026-08-12 02:31:00"', '{"type": "value_check", "column": "total_queries", "expected": "15"}'],

            // CASE-017 challenges
            ['CASE-017', 'Recent Hires', 'Find all transactions involving employees hired in 2025 or later where the amount exceeds 100000.', 'beginner', 75, 'SELECT e.first_name, e.last_name, t.amount, t.transaction_date FROM transactions t JOIN employees e ON t.initiated_by = e.id WHERE e.hire_date >= "2025-01-01" AND t.amount > 100000', '{"type": "row_count", "min": 4}'],
            ['CASE-017', 'Money Mule Total', 'What is the total amount transacted by employees hired in 2025 or later?', 'intermediate', 125, 'SELECT SUM(t.amount) as total FROM transactions t JOIN employees e ON t.initiated_by = e.id WHERE e.hire_date >= "2025-01-01"', '{"type": "row_count", "min": 1}'],

            // CASE-018 challenges
            ['CASE-018', 'Certificate Access', 'Find all activities involving certificate files (containing "cert", "pem", or "key").', 'beginner', 75, 'SELECT user_id, description, timestamp FROM activities WHERE action_type = "file_access" AND (description LIKE "%cert%" OR description LIKE "%pem%" OR description LIKE "%key%")', '{"type": "row_count", "min": 5}'],
            ['CASE-018', 'Certificate Theft', 'Which user downloaded the most certificate files?', 'intermediate', 125, 'SELECT user_id, COUNT(*) as cert_downloads FROM activities WHERE action_type = "file_access" AND (description LIKE "%cert%" OR description LIKE "%pem%" OR description LIKE "%key%") GROUP BY user_id ORDER BY cert_downloads DESC LIMIT 1', '{"type": "value_check", "column": "user_id", "expected": "6"}'],

            // CASE-019 challenges
            ['CASE-019', 'Access Pattern', 'Find employees who accessed more than 3 different resource_types in the access_logs.', 'beginner', 75, 'SELECT employee_id, COUNT(DISTINCT resource_type) as resource_types FROM access_logs GROUP BY employee_id HAVING resource_types > 3', '{"type": "row_count", "min": 1}'],
            ['CASE-019', 'Pattern Shift', 'Which employee showed the biggest change in access patterns between early and late August?', 'advanced', 150, 'SELECT a.employee_id, COUNT(DISTINCT a.resource_type) as total_types FROM access_logs a GROUP BY a.employee_id HAVING total_types > 3', '{"type": "row_count", "min": 1}'],

            // CASE-020 challenges
            ['CASE-020', 'External Payments', 'Find payments to external accounts initiated by Finance department employees.', 'beginner', 75, 'SELECT e.first_name, e.last_name, ba.account_name, t.amount FROM transactions t JOIN employees e ON t.initiated_by = e.id JOIN bank_accounts ba ON t.to_account_id = ba.id WHERE e.department_id = 1 AND ba.owner_type = "external"', '{"type": "row_count", "min": 5}'],
            ['CASE-020', 'Fraud Total', 'What is the total amount paid to external accounts by Finance employees?', 'intermediate', 125, 'SELECT SUM(t.amount) as total_fraud FROM transactions t JOIN employees e ON t.initiated_by = e.id JOIN bank_accounts ba ON t.to_account_id = ba.id WHERE e.department_id = 1 AND ba.owner_type = "external"', '{"type": "row_count", "min": 1}'],

            // CASE-021 challenges
            ['CASE-021', 'APT Timeline', 'Create a timeline of all suspicious/compromised activities ordered by timestamp.', 'advanced', 250, 'SELECT action_type, timestamp, ip_address, description FROM activities WHERE (description LIKE "%unknown%" OR description LIKE "%compromised%" OR description LIKE "%suspicious%" OR description LIKE "%unauthorized%" OR description LIKE "%exfiltrat%") ORDER BY timestamp', '{"type": "row_count", "min": 10}'],
            ['CASE-021', 'Attack Duration', 'How many months did the APT attack span from first to last suspicious activity?', 'advanced', 250, 'SELECT TIMESTAMPDIFF(MONTH, MIN(timestamp), MAX(timestamp)) as attack_months FROM activities WHERE (description LIKE "%unknown%" OR description LIKE "%compromised%" OR description LIKE "%suspicious%" OR description LIKE "%unauthorized%")', '{"type": "row_count", "min": 1}'],

            // CASE-022 challenges
            ['CASE-022', 'Multi-Hop Trace', 'Starting from the main operating account (account_id = 1), trace outgoing transfers greater than 500000 for at least 2 levels.', 'advanced', 250, 'SELECT t1.transaction_id, ba1.account_name as hop1_from, ba2.account_name as hop1_to, t1.amount as hop1_amount, t2.transaction_id as hop2_txn, ba3.account_name as hop2_from, ba4.account_name as hop2_to, t2.amount as hop2_amount FROM transactions t1 JOIN bank_accounts ba1 ON t1.from_account_id = ba1.id JOIN bank_accounts ba2 ON t1.to_account_id = ba2.id JOIN transactions t2 ON t2.from_account_id = t1.to_account_id AND t2.amount > 500000 JOIN bank_accounts ba3 ON t2.from_account_id = ba3.id JOIN bank_accounts ba4 ON t2.to_account_id = ba4.id WHERE t1.from_account_id = 1 AND t1.amount > 500000', '{"type": "row_count", "min": 3}'],

            // CASE-023 challenges
            ['CASE-023', 'Critical Events', 'Find all log entries with severity "critical" or "high" from security sources.', 'advanced', 250, 'SELECT log_type, level, source, message, timestamp FROM logs WHERE level IN ("critical", "high") AND log_type = "security" ORDER BY timestamp', '{"type": "row_count", "min": 8}'],
            ['CASE-023', 'Exploit Chain', 'Cross-reference critical logs with suspicious network connections on the same dates to reconstruct the attack.', 'advanced', 250, 'SELECT l.timestamp as log_time, l.message as log_event, n.source_ip as net_src, n.destination_ip as net_dst, n.status as net_status FROM logs l LEFT JOIN network_logs n ON DATE(l.timestamp) = DATE(n.timestamp) AND n.status = "suspicious" WHERE l.level IN ("critical", "high") ORDER BY l.timestamp', '{"type": "row_count", "min": 5}'],

            // CASE-024 challenges
            ['CASE-024', 'Manager Abuse', 'Find access logs where managers/directors/VPs performed admin actions.', 'advanced', 200, 'SELECT a.employee_id, e.first_name, e.last_name, e.level, a.resource, a.action, a.access_time FROM access_logs a JOIN employees e ON a.employee_id = e.id WHERE e.level IN ("manager", "director", "vp") AND a.resource_type = "admin"', '{"type": "row_count", "min": 10}'],
            ['CASE-024', 'Self-Approvals', 'Find permission_changes where the employee both changed and approved their own permissions.', 'advanced', 200, 'SELECT employee_id, permission_type, change_time FROM permission_changes WHERE employee_id = changed_by', '{"type": "row_count", "min": 3}'],

            // CASE-025 challenges
            ['CASE-025', 'Exfiltration Methods', 'Calculate the total data exported by each user over the past 3 months, broken down by activity type.', 'advanced', 200, 'SELECT user_id, action_type, COUNT(*) as event_count FROM activities WHERE action_type IN ("email_send", "file_access", "network_connect") AND timestamp >= "2026-05-01" GROUP BY user_id, action_type', '{"type": "row_count", "min": 5}'],
            ['CASE-025', 'Top Leaker', 'Which user exported the most data by event count across all methods?', 'advanced', 200, 'SELECT user_id, COUNT(*) as total_events FROM activities WHERE action_type IN ("email_send", "file_access", "network_connect") AND timestamp >= "2026-05-01" GROUP BY user_id ORDER BY total_events DESC LIMIT 1', '{"type": "value_check", "column": "user_id", "expected": "6"}'],

            // CASE-026 challenges
            ['CASE-026', 'Phantom Transactions', 'Find transactions with status = "completed" from IPs not in the 192.168.x.x range between 2 AM and 4 AM.', 'advanced', 225, 'SELECT transaction_id, amount, ip_address, transaction_date FROM transactions WHERE status = "completed" AND ip_address NOT LIKE "192.168.%" AND HOUR(transaction_date) BETWEEN 2 AND 4', '{"type": "row_count", "min": 8}'],
            ['CASE-026', 'Total Phantom', 'What is the total value of phantom transactions?', 'advanced', 225, 'SELECT SUM(amount) as total_phantom FROM transactions WHERE status = "completed" AND ip_address NOT LIKE "192.168.%" AND HOUR(transaction_date) BETWEEN 2 AND 4', '{"type": "row_count", "min": 1}'],

            // CASE-027 challenges
            ['CASE-027', 'Concurrent Sessions', 'Find pairs of login records where the same employee logged in from different IP addresses within a 30-minute window.', 'advanced', 200, 'SELECT a.employee_id, a.ip_address as ip1, a.login_time as time1, b.ip_address as ip2, b.login_time as time2 FROM login_records a JOIN login_records b ON a.employee_id = b.employee_id AND a.ip_address != b.ip_address AND ABS(TIMESTAMPDIFF(MINUTE, a.login_time, b.login_time)) <= 30 WHERE a.login_time < b.login_time', '{"type": "row_count", "min": 3}'],
            ['CASE-027', 'Identity Thief', 'How many employees show concurrent session patterns from different IPs?', 'advanced', 200, 'SELECT COUNT(DISTINCT a.employee_id) as compromised_count FROM login_records a JOIN login_records b ON a.employee_id = b.employee_id AND a.ip_address != b.ip_address AND ABS(TIMESTAMPDIFF(MINUTE, a.login_time, b.login_time)) <= 30 WHERE a.login_time < b.login_time', '{"type": "row_count", "min": 1}'],

            // CASE-028 challenges
            ['CASE-028', 'Info Leak', 'Find activities where confidential files were accessed and then emailed externally within 24 hours.', 'advanced', 225, 'SELECT a.user_id, a.description as file_accessed, a.timestamp as access_time, e.description as email_desc, e.timestamp as email_time FROM activities a JOIN activities e ON a.user_id = e.user_id AND e.action_type = "email_send" AND e.description LIKE "%external%" AND TIMESTAMPDIFF(HOUR, a.timestamp, e.timestamp) BETWEEN 0 AND 24 WHERE a.action_type = "file_access" AND (a.description LIKE "%confidential%" OR a.description LIKE "%strategy%" OR a.description LIKE "%roadmap%" OR a.description LIKE "%bid%")', '{"type": "row_count", "min": 3}'],
            ['CASE-028', 'Spy Identified', 'Which employee leaked the most confidential documents via email?', 'advanced', 225, 'SELECT a.user_id, COUNT(*) as leaks FROM activities a JOIN activities e ON a.user_id = e.user_id AND e.action_type = "email_send" AND e.description LIKE "%external%" WHERE a.action_type = "file_access" AND (a.description LIKE "%confidential%" OR a.description LIKE "%strategy%" OR a.description LIKE "%roadmap%" OR a.description LIKE "%bid%") GROUP BY a.user_id ORDER BY leaks DESC LIMIT 1', '{"type": "value_check", "column": "user_id", "expected": "6"}'],

            // CASE-029 challenges
            ['CASE-029', 'Log Anomalies', 'Find log entries with timestamps that appear out of sequence (a log entry with a timestamp earlier than the previous entry).', 'advanced', 250, 'SELECT l1.id, l1.timestamp as current_time, l2.timestamp as previous_time, l1.message FROM logs l1 JOIN logs l2 ON l1.id > l2.id AND l1.timestamp < l2.timestamp LIMIT 10', '{"type": "row_count", "min": 1}'],
            ['CASE-029', 'Tampering Evidence', 'Find duplicate log entries (same message within 5 seconds) and cross-reference with timestamps table for inconsistencies.', 'advanced', 250, 'SELECT l1.id as log1_id, l1.timestamp as log1_time, l2.id as log2_id, l2.timestamp as log2_time, l1.message FROM logs l1 JOIN logs l2 ON l1.message = l2.message AND l1.id != l2.id AND ABS(TIMESTAMPDIFF(SECOND, l1.timestamp, l2.timestamp)) <= 5', '{"type": "row_count", "min": 1}'],

            // CASE-030 challenges
            ['CASE-030', 'Connected Dots', 'Find common IP addresses that appear across both login_logs and transactions tables in suspicious contexts.', 'advanced', 500, 'SELECT ll.ip_address, COUNT(DISTINCT \'login\') as login_count, COUNT(DISTINCT \'transaction\') as txn_count FROM login_logs ll LEFT JOIN transactions t ON ll.ip_address = t.ip_address WHERE ll.ip_address NOT LIKE "192.168.%" GROUP BY ll.ip_address HAVING login_count > 0 AND txn_count > 0', '{"type": "row_count", "min": 2}'],
            ['CASE-030', 'Mastermind', 'Find the employee who appears most frequently across suspicious activities (transactions from external IPs, logins from external IPs, access from unknown devices).', 'advanced', 500, 'SELECT employee_id, COUNT(*) as suspicious_count FROM (SELECT initiated_by as employee_id FROM transactions WHERE ip_address NOT LIKE "192.168.%" UNION ALL SELECT employee_id FROM login_logs WHERE ip_address NOT LIKE "192.168.%" UNION ALL SELECT employee_id FROM access_logs WHERE device_id LIKE "DEV-UNKNOWN%") combined GROUP BY employee_id ORDER BY suspicious_count DESC LIMIT 1', '{"type": "value_check", "column": "employee_id", "expected": "5"}'],
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

    private function seedSuspects(): void
    {
        $suspects = [
            // CASE-001: The Missing Million
            ['CASE-001', 'Rajesh Kumar', 34, 'Junior Accountant', 'Recently transferred to the finance department. Has access to wire transfer systems. Known gambling debts.', 'Claims to have been on approved leave during the transfers. Verified by HR records.', 'critical'],
            ['CASE-001', 'Priya Sharma', 42, 'Senior Finance Manager', 'Supervises the accounts payable team. Has authority to approve transfers up to ₹5,00,000.', 'Was in a client meeting in Delhi during the time of the unauthorized transfers. Hotel records confirm.', 'medium'],
            ['CASE-001', 'Vikram Patel', 28, 'IT Administrator', 'Has root access to the finance server and transaction database. Recently passed over for promotion.', 'Was working on a server migration project. Logs show he was logged into the IT management console.', 'low'],

            // CASE-002: The Digital Trail
            ['CASE-002', 'Unknown External Actor', NULL, 'N/A', 'Attacker using IP range 203.0.113.x. High technical sophistication. Custom malware detected.', 'No known identity. IP addresses trace to a VPS provider in Eastern Europe.', 'critical'],
            ['CASE-002', 'DevOps Service Account', NULL, 'Service Account', 'The compromised service account (svc-backup-01) was used as the initial entry point.', 'Service account credentials were found in a phishing email sent to a sysadmin.', 'high'],

            // CASE-003: Employee Portal Breach
            ['CASE-003', 'Amit Joshi', 29, 'Junior Developer', 'Employee ID 5. Has been self-approving permission changes and accessing HR data.', 'Claims the IT department told him to test the portal permissions for a security audit.', 'high'],
            ['CASE-003', 'Neha Gupta', 35, 'HR Manager', 'Has legitimate access to employee records. Could have been the initial victim.', 'Was on maternity leave during the suspicious activity period. Leave records verified.', 'low'],

            // CASE-004: The Suspicious Login
            ['CASE-004', 'Unidentified Attacker', NULL, 'N/A', 'Source IP 45.33.98.77 — external to corporate network. Attempted brute force on admin accounts.', 'IP traces to a VPN exit node in the Netherlands. No identity possible.', 'critical'],
            ['CASE-004', 'Rajesh Kumar', 34, 'Junior Accountant', 'Employee ID 2. Targeted by 3 failed login attempts from the external IP.', 'Was away from desk during the attack. Badge records show he was in the cafeteria.', 'medium'],

            // CASE-005: Ghost in the System
            ['CASE-005', 'svc-backup-01 (Compromised)', NULL, 'Service Account', 'Service account normally runs nightly backups. Recently performing unauthorized file access.', 'Account credentials were compromised via a phishing attack on the backup administrator.', 'critical'],
            ['CASE-005', 'svc-monitor-02', NULL, 'Service Account', 'Monitoring service account. Has read access to most systems for health checks.', 'Behaving normally. Access patterns match its defined scope.', 'low'],

            // CASE-006: The Missing Records
            ['CASE-006', 'Amit Joshi', 29, 'Junior Developer', 'Employee ID 5. Audit trail shows multiple delete operations on the employees table.', 'Claims he was performing a data cleanup authorized by his manager. No written authorization found.', 'high'],

            // CASE-007: Budget Leaks
            ['CASE-007', 'Finance Department', NULL, 'Department', 'The Finance department (code FIN) shows the largest budget overage at ₹45,00,000.', 'Finance claims the overage was due to unplanned regulatory compliance costs.', 'medium'],
            ['CASE-007', 'Manoj Singh', 38, 'Finance Director', 'Authorizes all budget transfers exceeding ₹10,00,000. Approved several high-value transactions.', 'Claims all transfers were properly documented and approved by the CFO.', 'medium'],

            // CASE-008: Phishing Campaign
            ['CASE-008', 'External Phisher', NULL, 'N/A', 'Unknown party sending phishing emails impersonating IT support. Credentials harvested.', 'Emails originate from compromised accounts and external mail servers.', 'critical'],
            ['CASE-008', 'Vikram Patel', 28, 'IT Administrator', 'Employee ID 6. May have inadvertently forwarded phishing emails to colleagues.', 'Claims he flagged the emails to the security team immediately.', 'low'],

            // CASE-009: The Night Shift
            ['CASE-009', 'Amit Joshi', 29, 'Junior Developer', 'Employee ID 5. Multiple night-time logins from external IPs. No night shift assignment.', 'Claims he was working overtime on a deadline. No project records support this.', 'high'],
            ['CASE-009', 'Rohit Verma', 31, 'Senior Developer', 'Employee ID 13. Night logins from multiple locations including non-office IPs.', 'Was on-call for a production issue. On-call schedule confirms.', 'low'],

            // CASE-010: Duplicate Payments
            ['CASE-010', 'Rajesh Kumar', 34, 'Junior Accountant', 'Processes vendor payments. Multiple duplicate payments routed to external accounts.', 'Claims the duplicates were system errors in the payment gateway.', 'high'],
            ['CASE-010', 'TechServe Solutions', NULL, 'Vendor', 'External vendor receiving duplicate payments. Company exists but owner is unknown.', 'Vendor refuses to return duplicate payments citing contractual terms.', 'medium'],

            // CASE-011: The Insider Trading Ring
            ['CASE-011', 'Employee 3', NULL, 'Confidential', 'Accessed confidential strategy documents and bid proposals before major announcements.', 'Claims access was for routine work. Timing is coincidental.', 'high'],

            // CASE-012: The Shadow Network
            ['CASE-012', 'Unknown External Actor', NULL, 'N/A', 'Source IP 203.0.113.50 — known malicious IP. Thousands of bytes transferred.', 'IP is associated with a known APT group. Identity not confirmed.', 'critical'],

            // CASE-013: The Fund Diversion
            ['CASE-013', 'Employee 5 (Sunita Reddy)', 38, 'Senior Accountant', 'Initiated transfers from the main operating account. Used DEV-UNKNOWN-01 device.', 'Claims her credentials were stolen. Device was not company-issued.', 'critical'],

            // CASE-014: The Data Hoarder
            ['CASE-014', 'Employee 3', NULL, 'Confidential', 'Accessed the most files with confidential, strategy, roadmap, or bid in the name.', 'Cannot provide justification for accessing files outside their department.', 'high'],

            // CASE-015: The Permission Cascade
            ['CASE-015', 'Amit Joshi', 29, 'Junior Developer', 'Employee ID 5. Started with junior access, escalated to manager-level permissions.', 'Claims each permission was granted by a legitimate approval. Self-approvals found.', 'critical'],

            // CASE-016: The Speed Demon
            ['CASE-016', 'Employee 6', NULL, 'Service Account', 'Executed 15 automated database queries in 42 seconds. Far beyond human capability.', 'Queries were from an automated script targeting sensitive tables. No legitimate use case.', 'critical'],

            // CASE-017: The Money Mule
            ['CASE-017', 'Rajesh Kumar', 34, 'Junior Accountant', 'Recently hired employee with ₹20 lakhs in unexplained transactions.', 'Claims the money was from a personal loan. No loan documentation found.', 'high'],

            // CASE-018: The Certificate Heist
            ['CASE-018', 'Employee 6', NULL, 'Confidential', 'Downloaded multiple certificate and key files from the secure server.', 'Claims it was for a server migration project. No migration project exists.', 'critical'],

            // CASE-019: The Access Anomaly
            ['CASE-019', 'Employee 6', NULL, 'Confidential', 'Access patterns shifted from finance-only to cross-department access (HR, engineering, admin).', 'No role change or project assignment justifies the pattern shift.', 'high'],

            // CASE-020: The Vendor Fraud
            ['CASE-020', 'Finance Employee', NULL, 'Finance Dept', 'Initiated payments to external accounts not in the approved vendor list.', 'Claims the vendors were approved verbally by management. No documentation.', 'high'],

            // CASE-021: The APT Attack
            ['CASE-021', 'APT Actor (APT-28)', NULL, 'N/A', 'Known APT group using custom tools. Initial compromise via phishing email in February 2026.', 'IPs and TTPs match known APT-28 infrastructure. No individual identified.', 'critical'],

            // CASE-022: The Money Puzzle
            ['CASE-022', 'Employee 5 (Sunita Reddy)', 38, 'Senior Accountant', 'Initiated transfers from the main operating account through intermediary accounts.', 'Claims she was following CFO instructions. CFO denies involvement.', 'critical'],

            // CASE-023: The Zero Day
            ['CASE-023', 'Unknown Exploiter', NULL, 'N/A', 'Used an unknown vulnerability to gain initial access. Exploited multiple systems.', 'Zero-day exploit. No attribution possible with current intelligence.', 'critical'],

            // CASE-024: The Chain of Command
            ['CASE-024', 'Management Team', NULL, 'Management', 'Managers, directors, and VP all show self-approval patterns.', 'Each manager claims their actions were within their authority.', 'high'],

            // CASE-025: The Silent Exfiltration
            ['CASE-025', 'Employee 6', NULL, 'Confidential', 'Top data exfiltrator. Sent data via email, file access, and network transfers.', 'Claims the file transfers were for legitimate backup purposes.', 'critical'],

            // CASE-026: The Phantom Transactions
            ['CASE-026', 'Employee 5 (Sunita Reddy)', 38, 'Senior Accountant', 'Initiated phantom transactions from external IPs at 2-4 AM.', 'Claims her VPN was compromised. No VPN logs support this.', 'critical'],

            // CASE-027: The Identity Thief
            ['CASE-027', 'Credential Thief', NULL, 'N/A', 'Stolen credentials used from different IPs within 30-minute windows.', 'Multiple employees show concurrent session patterns. Identity of thief unknown.', 'high'],

            // CASE-028: The Corporate Spy
            ['CASE-028', 'Employee 6', NULL, 'Confidential', 'Accessed confidential files and emailed them externally within 24 hours.', 'Claims the emails were to a personal account for remote work purposes.', 'critical'],

            // CASE-029: The Cover-Up
            ['CASE-029', 'Unknown Tamperer', NULL, 'N/A', 'Modified log entries and timestamps to hide malicious activity.', 'Tampering detected through timestamp analysis. Identity not confirmed.', 'critical'],

            // CASE-030: The Mastermind
            ['CASE-030', 'Employee 5 (Sunita Reddy)', 38, 'Senior Accountant', 'Connected to the most suspicious activities across multiple cases. Common IP addresses and devices.', 'Claims to be a victim of identity theft. Investigation ongoing.', 'critical'],
        ];

        $caseMap = [];
        $stmt = $this->db->query("SELECT id, case_code FROM cases");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $caseMap[$row['case_code']] = $row['id'];
        }

        foreach ($suspects as $s) {
            $caseId = $caseMap[$s[0]] ?? null;
            if (!$caseId) continue;

            $stmt = $this->db->prepare("SELECT COUNT(*) FROM suspects WHERE case_id = ? AND name = ?");
            $stmt->execute([$caseId, $s[1]]);
            if ($stmt->fetchColumn() === 0) {
                $this->db->prepare("
                    INSERT INTO suspects (case_id, name, age, occupation, description, alibi, risk_level)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ")->execute([$caseId, $s[1], $s[2], $s[3], $s[4], $s[5], $s[6]]);
                echo "Suspect '{$s[1]}' created for {$s[0]}\n";
            }
        }
    }

    private function seedEvidence(): void
    {
        $evidence = [
            // CASE-001: The Missing Million
            ['CASE-001', 'Transaction Log Extract', 'Bank transfer records showing 13 suspicious transactions from the main operating account to external accounts between Aug 1-22, 2026. Total: ₹42,85,000.', 'log', 'critical'],
            ['CASE-001', 'Login Session Records', 'Login records showing employee 5 (Sunita Reddy) logged in from IP 203.0.113.42 using device DEV-UNKNOWN-001 during all suspicious transactions.', 'log', 'critical'],
            ['CASE-001', 'Unknown Device Report', 'Three unregistered devices (DEV-UNKNOWN-001, 002, 003) found on the network. None are company-issued. First seen dates coincide with the start of suspicious activity.', 'record', 'high'],
            ['CASE-001', 'Employee Access Audit', 'Access logs showing employee 5 accessed the finance database write operations at 2-3 AM on 7 separate occasions.', 'log', 'high'],

            // CASE-002: The Digital Trail
            ['CASE-002', 'External IP Activity Log', 'Activities from IP 203.0.113.50 on August 12, 2026. Multiple file access, email, and database operations from an unknown external address.', 'log', 'critical'],
            ['CASE-002', 'Compromised Device Inventory', 'DEV-UNKNOWN-01 identified as the compromised device. Owner unknown. Device first seen Aug 1, 2026.', 'record', 'high'],
            ['CASE-002', 'Email Forwarding Rules', 'Email forwarding rules set up to automatically send copies of certain emails to external addresses.', 'document', 'high'],

            // CASE-003: Employee Portal Breach
            ['CASE-003', 'Permission Change Audit Trail', 'Complete record of all permission changes. Employee 5 self-approved role escalation from junior to manager-level access.', 'log', 'critical'],
            ['CASE-003', 'Data Export Records', 'Three data export operations initiated by employee 5 from IP 192.168.1.14. Exports included salary data and employee records.', 'log', 'high'],

            // CASE-004: The Suspicious Login
            ['CASE-004', 'Brute Force Attack Log', '20+ failed login attempts from IP 45.33.98.77 targeting admin accounts between Aug 10-12, 2026.', 'log', 'critical'],
            ['CASE-004', 'Successful External Login', 'One successful login from the attacker IP 45.33.98.77 to employee 14 (Sunita Reddy) account on Aug 10 at 03:25 AM.', 'log', 'critical'],

            // CASE-005: Ghost in the System
            ['CASE-005', 'Service Account Activity Anomaly', 'svc-backup-01 performed 12 activities outside business hours between Aug 5-15, 2026. Normal scope is limited to nightly backup tasks.', 'log', 'high'],
            ['CASE-005', 'Unauthorized File Access', 'svc-backup-01 accessed /etc/shadow.bak and user_credentials table on Aug 5 at 2:30 AM. These are outside its normal scope.', 'log', 'critical'],

            // CASE-006: The Missing Records
            ['CASE-006', 'Audit Trail - Employee Deletions', 'Three DELETE operations on the employees table performed by employee 5 on Aug 8-12, 2026.', 'log', 'critical'],
            ['CASE-006', 'System Event Logs', 'Security alerts triggered by the employee record modifications. No corresponding authorization forms found.', 'log', 'high'],

            // CASE-007: Budget Leaks
            ['CASE-007', 'Department Budget Summary', 'Finance department spending exceeds budget by ₹45,00,000. Engineering also over budget by ₹12,00,000.', 'document', 'high'],
            ['CASE-007', 'Transaction Audit Report', 'High-value transactions initiated by Finance employees totaling ₹1,25,00,000 against a budget of ₹80,00,000.', 'document', 'critical'],

            // CASE-008: Phishing Campaign
            ['CASE-008', 'Phishing Email Samples', 'Five suspicious emails with subjects about password reset, bonus payment, and account verification. Sent from external addresses.', 'document', 'critical'],
            ['CASE-008', 'External Sender Analysis', 'Emails from NULL sender IDs or unknown external addresses targeting employees in Finance and HR departments.', 'log', 'high'],

            // CASE-009: The Night Shift
            ['CASE-009', 'Night Login Records', '12 successful logins between 11 PM and 5 AM from employees without night shift assignments.', 'log', 'high'],
            ['CASE-009', 'Geo-Location Mismatch', 'Employee 5 (Amit Joshi) logged in from IP 185.220.101.42 — a known Tor exit node — at 2:50 AM on Aug 12.', 'log', 'critical'],

            // CASE-010: Duplicate Payments
            ['CASE-010', 'Duplicate Transaction Report', '8 pairs of duplicate payments identified. Same amounts sent to the same accounts on the same dates.', 'log', 'critical'],
            ['CASE-010', 'Vendor Account Analysis', 'External vendor accounts receiving duplicate payments. Account names do not match approved vendor list.', 'record', 'high'],

            // CASE-011: The Insider Trading Ring
            ['CASE-011', 'File Access Timeline', 'Employees accessed confidential strategy documents, product roadmaps, and bid proposals within 48 hours of major announcements.', 'log', 'critical'],
            ['CASE-011', 'Confidential File Inventory', 'List of files with "confidential", "strategy", "roadmap", and "bid" in their names. All accessed by user 3.', 'record', 'high'],

            // CASE-012: The Shadow Network
            ['CASE-012', 'Suspicious Network Connections', 'IP 203.0.113.50 made 45 suspicious connections transferring 12.5 MB of data. Status flagged as suspicious by firewall.', 'log', 'critical'],
            ['CASE-012', 'Network Traffic Analysis', 'Traffic between internal devices and external IPs showing covert channel patterns using non-standard ports.', 'log', 'high'],

            // CASE-013: The Fund Diversion
            ['CASE-013', 'Money Trail Summary', 'Trace of ₹50+ lakhs from main operating account through 10+ intermediary accounts to external destinations.', 'log', 'critical'],
            ['CASE-013', 'External Account Network', 'Three external accounts (IDs 13-15) receiving funds from the main account. Accounts registered to shell companies.', 'record', 'high'],

            // CASE-014: The Data Hoarder
            ['CASE-014', 'File Access Volume Report', 'User 3 accessed 45+ files with confidential/strategy/roadmap/bid in their names over August 2026.', 'log', 'critical'],
            ['CASE-014', 'Email Exfiltration Evidence', 'Emails sent by user 3 to external addresses with file attachments matching the accessed confidential files.', 'log', 'high'],

            // CASE-015: The Permission Cascade
            ['CASE-015', 'Permission Escalation Chain', 'Complete chain of 12 permission changes showing employee 5 escalating from junior access to admin-level permissions.', 'log', 'critical'],
            ['CASE-015', 'Self-Approval Evidence', 'Employee 5 approved their own permission changes 6 times. No supervisor approval found.', 'log', 'high'],

            // CASE-016: The Speed Demon
            ['CASE-016', 'Automated Query Log', '15 database queries executed by user 6 in 42 seconds (02:30:00-02:30:42 on Aug 12). Queries targeted users, employees, salary_data, and transactions.', 'log', 'critical'],
            ['CASE-016', 'Query Pattern Analysis', 'Queries follow a reconnaissance pattern: enumerate users, access sensitive tables, export data. Consistent with automated data theft.', 'document', 'high'],

            // CASE-017: The Money Mule
            ['CASE-017', 'Recent Hire Transaction Report', 'Employees hired in 2025 or later with transactions exceeding ₹1,00,000. Multiple suspicious transactions found.', 'log', 'high'],
            ['CASE-017', 'Employee 14 Account Activity', 'Employee 14 (Sunita Reddy) has ₹20+ lakhs in transactions despite being a recent hire in a junior role.', 'log', 'critical'],

            // CASE-018: The Certificate Heist
            ['CASE-018', 'Certificate File Access Log', 'User 3 accessed 8 certificate-related files (cert, pem, key) from the secure PKI server.', 'log', 'critical'],
            ['CASE-018', 'Certificate Export Evidence', 'Emails and network transfers containing certificate files sent to external destinations.', 'log', 'high'],

            // CASE-019: The Access Anomaly
            ['CASE-019', 'Access Pattern Shift Report', 'Employee 6 shifted from accessing only finance resources to accessing engineering, HR, and admin resources in the second half of August.', 'log', 'critical'],
            ['CASE-019', 'UEBA Alert', 'User and Entity Behavior Analytics flagged employee 6 for accessing 5+ different resource types, up from 1 in the first half of the month.', 'log', 'high'],

            // CASE-020: The Vendor Fraud
            ['CASE-020', 'External Payment Report', 'Finance department employees initiated ₹65+ lakhs in payments to external accounts not in the approved vendor list.', 'log', 'critical'],
            ['CASE-020', 'Vendor Verification Report', 'Multiple vendor accounts receiving payments do not appear in the approved vendor database. Shell company indicators.', 'document', 'high'],

            // CASE-021: The APT Attack
            ['CASE-021', 'APT Timeline', '6-month attack lifecycle from initial phishing compromise (Feb 2026) through lateral movement, privilege escalation, to data exfiltration.', 'log', 'critical'],
            ['CASE-021', 'Compromise Indicators', 'IOCs including IP addresses (203.0.113.x), device fingerprints, and malware signatures matching APT-28 TTPs.', 'document', 'critical'],

            // CASE-022: The Money Puzzle
            ['CASE-022', 'Multi-Hop Transfer Map', '₹1 crore traced through 15+ transactions across 4 levels. Money splits and recombines through intermediary accounts.', 'log', 'critical'],
            ['CASE-022', 'Shell Company Network', 'Intermediary accounts registered to companies with shared directors and overlapping addresses.', 'document', 'high'],

            // CASE-023: The Zero Day
            ['CASE-023', 'Critical Security Events', '8 critical-severity log entries showing exploit activity across multiple systems on the same dates.', 'log', 'critical'],
            ['CASE-023', 'Exploit Fragment Analysis', 'Unusual process starts, network connections, and file modifications correlated with the exploit timeline.', 'document', 'high'],

            // CASE-024: The Chain of Command
            ['CASE-024', 'Manager Self-Approvals', 'Managers and directors approved their own permission changes, budget requests, and access grants.', 'log', 'critical'],
            ['CASE-024', 'Out-of-Department Access', 'Director (employee 3) accessed engineering, marketing, and HR resources — all outside their department scope.', 'log', 'high'],

            // CASE-025: The Silent Exfiltration
            ['CASE-025', 'Exfiltration Method Summary', 'Data exported via email (32 events), file access (28 events), and network transfers (15 events) over 3 months.', 'log', 'critical'],
            ['CASE-025', 'User 6 Export Activity', 'User 6 is the top data exfiltrator with 45+ events across all methods. Total estimated data: 500+ MB.', 'log', 'critical'],

            // CASE-026: The Phantom Transactions
            ['CASE-026', 'Phantom Transaction Report', '13 completed transactions from external IPs (not 192.168.x.x) between 2-4 AM. Bank has no record of these.', 'log', 'critical'],
            ['CASE-026', 'Bank Reconciliation Gap', '₹75+ lakhs in company ledger transactions that do not appear in bank statements. All from DEV-UNKNOWN devices.', 'document', 'critical'],

            // CASE-027: The Identity Thief
            ['CASE-027', 'Concurrent Session Report', '4 employees (IDs 4, 5, 7, 13) show concurrent logins from different IPs within 30-minute windows.', 'log', 'critical'],
            ['CASE-027', 'Suspicious IP Analysis', 'External IPs used in concurrent sessions include 203.0.113.50, 198.51.100.10, and 45.33.32.156 — known malicious ranges.', 'log', 'high'],

            // CASE-028: The Corporate Spy
            ['CASE-028', 'File-to-Email Correlation', '3 instances where confidential files were accessed and emailed externally within 24 hours.', 'log', 'critical'],
            ['CASE-028', 'Competitor Material Match', 'Marketing strategy and bid proposal documents found in competitor materials match company internal versions.', 'document', 'high'],

            // CASE-029: The Cover-Up
            ['CASE-029', 'Log Integrity Report', 'Duplicate log entries and timestamp inconsistencies detected. Some entries appear twice within 5 seconds.', 'log', 'critical'],
            ['CASE-029', 'Timestamp Anomaly Map', 'Log timestamps that are out of sequence or precede the previous entry. Gaps suggest deleted records.', 'log', 'high'],

            // CASE-030: The Mastermind
            ['CASE-030', 'Cross-Case Connection Map', 'Common IPs (203.0.113.x), devices (DEV-UNKNOWN-001), and employees appear across 8+ cases.', 'log', 'critical'],
            ['CASE-030', 'Employee 5 Activity Summary', 'Employee 5 (Sunita Reddy) appears in transaction logs, login records, access logs, and device associations across multiple cases.', 'record', 'critical'],
        ];

        $caseMap = [];
        $stmt = $this->db->query("SELECT id, case_code FROM cases");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $caseMap[$row['case_code']] = $row['id'];
        }

        foreach ($evidence as $e) {
            $caseId = $caseMap[$e[0]] ?? null;
            if (!$caseId) continue;

            $stmt = $this->db->prepare("SELECT COUNT(*) FROM evidence WHERE case_id = ? AND title = ?");
            $stmt->execute([$caseId, $e[1]]);
            if ($stmt->fetchColumn() === 0) {
                $this->db->prepare("
                    INSERT INTO evidence (case_id, title, description, evidence_type, importance)
                    VALUES (?, ?, ?, ?, ?)
                ")->execute([$caseId, $e[1], $e[2], $e[3], $e[4]]);
                echo "Evidence '{$e[1]}' created for {$e[0]}\n";
            }
        }
    }
}