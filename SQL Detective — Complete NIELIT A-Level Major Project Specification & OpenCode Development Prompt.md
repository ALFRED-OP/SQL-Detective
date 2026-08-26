# SQL Detective
## Database Investigation & SQL Learning Game

### NIELIT A-Level Major Project — Complete Development Specification

---

# 1. PROJECT OVERVIEW

Build a complete, production-quality web application called:

**SQL Detective**

Tagline:

**"Investigate. Query. Discover the Truth."**

SQL Detective is an interactive database investigation game designed around SQL, relational databases, logical reasoning, and investigative problem solving.

The player acts as a digital detective investigating fictional cases.

Each case contains:

- Crime/investigation briefing
- Victim information
- Suspect information
- Evidence
- Timeline
- Multiple fictional databases
- Database tables
- Relationships between tables
- SQL investigation tasks
- Progressive clues
- Query editor
- Query execution/result panel
- Case objectives
- Hints
- XP/rewards
- Case completion
- Detective rank
- Achievements
- Leaderboard

The application must be suitable as a **NIELIT A-Level Major Project** and should demonstrate:

- PHP
- MySQL
- HTML5
- CSS3
- JavaScript
- SQL
- Database normalization
- Relational database design
- CRUD
- Authentication
- Session management
- MVC architecture
- Form validation
- Security
- Debugging
- Testing
- Error handling
- Responsive web design
- REST-style API endpoints where appropriate
- Client/server architecture
- Database transactions
- Logging
- Access control

Do NOT make this a basic quiz website.

It must feel like a polished database investigation platform.

---

# 2. IMPORTANT DEVELOPMENT RULE

This project must be lightweight enough to run on a VPS with:

- 1 CPU core
- 1 GB RAM
- Linux
- Nginx/Apache
- PHP
- MySQL/MariaDB

The server currently hosts other websites.

Therefore:

DO NOT introduce:

- Node.js server
- Next.js
- React development server
- Redis
- MongoDB
- Elasticsearch
- WebSockets
- background workers
- continuously running game servers
- heavy frameworks
- unnecessary dependencies

The production application must run primarily through:

- PHP
- MySQL/MariaDB
- HTML
- CSS
- Vanilla JavaScript

Composer may be used only if genuinely necessary.

Keep the runtime extremely lightweight.

---

# 3. DESIGN DIRECTION

The provided reference image represents the general concept of a professional SQL/database IDE.

Use the reference only for inspiration regarding:

- database explorer
- SQL editor
- query history
- result table
- professional workspace layout

DO NOT copy the exact UI.

The final website must have a:

**Sweet + Simple + Classic + Modern + Professional**

visual style.

Avoid an overly cyberpunk/neon aesthetic.

Avoid excessive animations.

Avoid excessive gradients.

Avoid huge glowing text.

Avoid unnecessarily complicated dashboards.

The application should look like a polished educational software product.

---

# 4. VISUAL STYLE

Use a clean professional interface.

Primary design characteristics:

- soft dark/light neutral colors
- subtle borders
- rounded corners
- excellent spacing
- clean typography
- readable SQL editor
- subtle shadows
- restrained accent color
- professional data tables
- minimal animations
- clear visual hierarchy

Recommended visual direction:

### Light Mode

- white
- off-white
- soft gray
- slate
- blue accent
- green success
- red error
- amber warning

### Dark Mode

Provide optional dark mode.

Use:

- charcoal
- dark slate
- soft gray
- muted blue
- green success
- red error

Do not make everything pure black.

---

# 5. RESPONSIVE DESIGN

The application must work on:

- desktop
- laptop
- tablet
- mobile

Desktop should provide the best experience.

The SQL workspace should intelligently adapt on smaller screens.

Desktop layout:

```text
┌─────────────────────────────────────────────────────────────┐
│ SQL DETECTIVE                              Profile / XP       │
├───────────────┬─────────────────────────────┬───────────────┤
│               │                             │               │
│ DATABASE      │ SQL EDITOR                  │ QUERY         │
│ EXPLORER      │                             │ HISTORY       │
│               │                             │               │
│ Tables        │ SELECT ...                  │ Query 1       │
│ Columns       │ FROM ...                    │ Query 2       │
│ Relations     │ WHERE ...                   │ Query 3       │
│               │                             │               │
├───────────────┴─────────────────────────────┤               │
│ RESULT / MESSAGES                           │               │
│                                             │               │
└─────────────────────────────────────────────┴───────────────┘
```

---

# 6. PROJECT ARCHITECTURE

Use MVC architecture.

Recommended structure:

```text
sql-detective/
│
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Services/
│   ├── Repositories/
│   ├── Middleware/
│   ├── Validators/
│   └── Helpers/
│
├── config/
│   ├── database.php
│   ├── app.php
│   └── security.php
│
├── public/
│   ├── index.php
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
│   └── uploads/
│
├── routes/
│   └── web.php
│
├── views/
│   ├── layouts/
│   ├── auth/
│   ├── dashboard/
│   ├── cases/
│   ├── detective/
│   ├── leaderboard/
│   ├── profile/
│   ├── achievements/
│   └── errors/
│
├── database/
│   ├── migrations/
│   ├── seeds/
│   └── investigation_databases/
│
├── storage/
│   ├── logs/
│   └── cache/
│
├── tests/
│
├── docs/
│
├── .env.example
├── .gitignore
├── composer.json
└── README.md
```

Do not expose:

- config files
- database credentials
- logs
- environment files
- internal PHP files
- SQL seed files

through the public web root.

---

# 7. DATABASE ARCHITECTURE

Use MySQL/MariaDB.

The main application database must contain at least 10 related tables.

Use proper primary keys, foreign keys, indexes and constraints.

Required minimum:

## 1. users

Fields:

- id
- username
- email
- password_hash
- display_name
- xp
- level
- detective_rank
- created_at
- updated_at
- last_login_at
- status

---

## 2. cases

Fields:

- id
- case_code
- title
- description
- difficulty
- category
- briefing
- objective
- expected_result_description
- xp_reward
- estimated_minutes
- status
- created_at

---

## 3. suspects

Fields:

- id
- case_id
- name
- age
- occupation
- description
- alibi
- risk_level

Relationship:

cases 1 → many suspects

---

## 4. evidence

Fields:

- id
- case_id
- title
- description
- evidence_type
- evidence_data
- importance
- created_at

---

## 5. case_databases

Fields:

- id
- case_id
- database_name
- database_description
- schema_description
- created_at

This represents the fictional database used by an investigation.

---

## 6. database_tables

Fields:

- id
- case_database_id
- table_name
- description
- display_order

---

## 7. database_columns

Fields:

- id
- table_id
- column_name
- data_type
- is_primary_key
- is_nullable
- description
- display_order

---

## 8. investigation_records

Fields:

- id
- table_id
- record_data
- created_at

This stores the actual fictional records used for investigations.

---

## 9. challenges

Fields:

- id
- case_id
- title
- description
- challenge_type
- difficulty
- expected_query_type
- expected_result_hash
- xp_reward
- display_order

---

## 10. challenge_attempts

Fields:

- id
- user_id
- challenge_id
- submitted_query
- result_status
- execution_time
- created_at

---

Additional recommended tables:

## 11. user_case_progress

- id
- user_id
- case_id
- current_challenge
- progress_percentage
- completed
- completed_at
- xp_earned

## 12. hints

- id
- challenge_id
- hint_text
- hint_level
- xp_penalty

## 13. hint_usage

- id
- user_id
- hint_id
- used_at

## 14. achievements

- id
- name
- description
- icon
- requirement_type
- requirement_value

## 15. user_achievements

- id
- user_id
- achievement_id
- unlocked_at

## 16. query_history

- id
- user_id
- case_id
- query
- status
- execution_time
- created_at

## 17. audit_logs

- id
- user_id
- action
- ip_hash
- user_agent
- metadata
- created_at

Use appropriate indexes.

Use foreign keys.

Use InnoDB.

Use utf8mb4.

---

# 8. IMPORTANT DATABASE DESIGN RULE

Do NOT create 10 completely independent databases just to satisfy a number.

The project should demonstrate a proper relational database.

The main application database should contain interconnected tables.

Additionally, every investigation case can have its own fictional dataset/schema representation.

The project should demonstrate:

- 1NF
- 2NF
- 3NF
- primary keys
- foreign keys
- candidate keys
- indexes
- relationships
- joins
- aggregate queries
- subqueries
- views where useful

Document the database design.

---

# 9. INVESTIGATION DATABASE SYSTEM

This is the core feature.

Each case should provide the player with a fictional database.

Example:

```text
CASE #001
THE MISSING MILLION

DATABASE
└── corporatefinance
    ├── employees
    ├── departments
    ├── transactions
    ├── bank_accounts
    ├── login_logs
    ├── access_logs
    ├── locations
    └── devices
```

The player can inspect:

```text
employees
├── id
├── name
├── department_id
├── email
├── position
└── hire_date
```

The player must use SQL to investigate.

---

# 10. SQL EDITOR

Create a professional SQL editor.

Features:

- syntax highlighting
- line numbers
- indentation
- query execution
- clear button
- format button
- query history
- result panel
- error panel
- execution time
- row count
- copy query
- save query
- keyboard shortcuts

Do not build a huge IDE.

Keep it lightweight.

Prefer a lightweight editor implementation.

If CodeMirror is used, use only the required modules.

Avoid loading unnecessary libraries.

---

# 11. SQL QUERY SYSTEM

IMPORTANT:

Do NOT allow unrestricted SQL against the production application database.

Never execute player queries against:

```text
users
passwords
sessions
audit_logs
application configuration
```

The player must only interact with fictional investigation datasets.

Implement a controlled SQL execution system.

Recommended architecture:

```text
Player Query
     ↓
Authentication
     ↓
Case validation
     ↓
SQL parser/validator
     ↓
Allowed statement check
     ↓
Read-only transaction
     ↓
Investigation dataset
     ↓
Result
```

Only allow safe statements such as:

- SELECT
- WITH
- permitted read-only operations

Reject:

- INSERT
- UPDATE
- DELETE
- DROP
- ALTER
- CREATE
- TRUNCATE
- GRANT
- REVOKE
- SET
- LOAD DATA
- INTO OUTFILE
- INTO DUMPFILE
- CALL
- multiple statements

Disable dangerous functions where appropriate.

Reject multiple statements.

Limit query length.

Limit result rows.

Limit execution time.

Limit query frequency.

---

# 12. SQL SECURITY

Implement defense-in-depth.

Never rely on a single SQL keyword blacklist.

Security should include:

### Application layer

- authentication
- authorization
- CSRF protection
- input validation
- output escaping
- rate limiting
- session protection

### Database layer

- separate database user for application
- minimum required privileges
- separate read-only database user for investigation queries
- no DROP privileges
- no ALTER privileges
- no FILE privilege
- no GRANT privilege
- no administrative privileges

### Query layer

- statement validation
- single-statement enforcement
- read-only execution
- maximum execution time
- result limits
- memory-conscious result processing

---

# 13. CASE SYSTEM

Create at least:

**30 investigation cases**

Divide them into:

### Beginner

10 cases

Topics:

- SELECT
- WHERE
- ORDER BY
- LIMIT
- DISTINCT
- basic filtering

### Intermediate

10 cases

Topics:

- JOIN
- GROUP BY
- HAVING
- aggregate functions
- subqueries
- CASE
- date filtering

### Advanced

10 cases

Topics:

- complex joins
- nested subqueries
- CTE
- window functions where supported
- data correlation
- multi-step investigations
- analytical queries

Each case should contain a genuine investigation story.

Do not make cases simply:

"Write SELECT * FROM users."

The SQL must actually help solve the mystery.

---

# 14. CASE EXAMPLE

Example:

## CASE #001

### THE MISSING MILLION

A company reports an unauthorized transfer of ₹10,00,000.

The player receives:

```text
Crime Report
Suspect Profiles
Employee Records
Transaction Records
Login Records
Device Records
Access Logs
```

Objective:

> Determine which employee initiated the unauthorized transaction.

The player may need:

### Challenge 1

Find transactions above ₹500,000.

### Challenge 2

Identify the account owner.

### Challenge 3

Find who logged in immediately before the transaction.

### Challenge 4

Compare device IDs.

### Challenge 5

Correlate login and transaction timestamps.

### Final challenge

Identify the suspect.

The final answer should depend on the evidence collected.

---

# 15. EVIDENCE SYSTEM

Evidence should not simply be decorative.

Evidence can contain:

- documents
- timestamps
- employee records
- login records
- transaction information
- IP addresses
- device IDs
- locations
- messages
- audit logs

Players should use evidence to formulate SQL queries.

---

# 16. HINT SYSTEM

Every challenge can have 0–3 hints.

Example:

```text
HINT 1
You need to investigate the transactions table.

HINT 2
Look for transactions greater than the reported amount.

HINT 3
Try using WHERE.
```

Hints should reduce XP reward.

Example:

```text
No hint:       100 XP
Hint 1:         80 XP
Hint 2:         60 XP
Hint 3:         40 XP
```

Never reveal the complete query immediately.

---

# 17. XP SYSTEM

Implement:

- XP
- levels
- detective ranks
- streaks
- achievements

Example:

```text
Level 1
SQL Rookie

Level 5
Query Analyst

Level 10
Database Detective

Level 20
Senior Investigator

Level 30
SQL Master
```

XP must be calculated server-side.

Never trust XP values sent by JavaScript.

---

# 18. ACHIEVEMENTS

Create achievements such as:

### First Investigation

Complete your first case.

### Clean Query

Solve a challenge without hints.

### SQL Master

Complete 10 advanced challenges.

### No Errors

Complete a case without an incorrect query.

### Speed Detective

Solve a case under the target time.

### Database Explorer

Inspect every table in a case.

### Persistent Investigator

Complete 7 cases.

### Perfect Investigation

Complete a case with maximum XP.

---

# 19. LEADERBOARD

Create a global leaderboard.

Display:

- rank
- username
- XP
- level
- cases completed
- detective rank
- achievements

Prevent cheating.

Leaderboard values must come exclusively from trusted server-side data.

---

# 20. USER AUTHENTICATION

Implement:

### Registration

- username
- email
- password
- confirm password

### Login

- email/username
- password

### Logout

Destroy session correctly.

### Password security

Use:

```php
password_hash()
password_verify()
```

Never store plain passwords.

Never log passwords.

Never return password hashes to the frontend.

---

# 21. SESSION SECURITY

Use:

- HttpOnly cookies
- Secure cookies in HTTPS production
- SameSite=Lax or Strict
- session regeneration after login
- session timeout
- logout invalidation
- CSRF tokens

Protect authenticated routes.

---

# 22. CSRF PROTECTION

Every state-changing request must require a CSRF token.

Protect:

- login if applicable
- registration if applicable
- profile changes
- query saving
- challenge submission
- case completion
- hint usage
- account changes

---

# 23. XSS PROTECTION

Never directly print user-controlled content into HTML.

Escape output appropriately.

Be especially careful with:

- usernames
- query history
- query text
- evidence
- case names
- error messages

Use:

```php
htmlspecialchars()
```

where appropriate.

---

# 24. SQL INJECTION PROTECTION

Use PDO.

Use prepared statements for all application queries.

Never concatenate user input into SQL.

Example architecture:

```text
Controller
   ↓
Validator
   ↓
Service
   ↓
Repository
   ↓
PDO prepared statement
   ↓
MySQL
```

---

# 25. QUERY EXECUTION SECURITY

Player queries must never be able to access the application database.

Use a dedicated investigation database or isolated schema/dataset.

Prefer:

```text
application_db
investigation_db
```

The PHP application account should have only the permissions it needs.

The investigation query account should be read-only.

---

# 26. RATE LIMITING

Implement lightweight database/file-based rate limiting.

Do not introduce Redis.

Examples:

Login:

```text
5 failed attempts / 15 minutes
```

Query execution:

```text
Maximum 30 queries / minute / user
```

Challenge submissions:

```text
Maximum 20 submissions / minute / user
```

Use IP + authenticated user where appropriate.

---

# 27. ANTI-CHEAT

Do not trust:

- XP
- level
- completion status
- score
- time
- hint count

from the client.

All important game state must be calculated server-side.

Prevent duplicate rewards using database constraints and transactions.

Example:

```text
user_id + case_id
```

should have an appropriate unique constraint where required.

---

# 28. DEBUGGING SYSTEM

Create a proper application error system.

Production:

```text
display_errors = Off
log_errors = On
```

Development:

```text
display_errors = On
```

Never display:

- SQL credentials
- filesystem paths
- stack traces
- internal database errors
- session information

to normal users.

---

# 29. ERROR PAGES

Create:

```text
400
401
403
404
405
419
429
500
503
```

Pages should match the SQL Detective design.

Example:

```text
404

CASE FILE NOT FOUND

The requested investigation does not exist.

[ RETURN TO CASES ]
```

---

# 30. LOGGING

Create structured application logs.

Log:

- login failures
- authentication events
- suspicious requests
- query validation failures
- rate-limit events
- application exceptions
- administrative actions

Do NOT log:

- passwords
- session tokens
- CSRF tokens
- sensitive personal data

---

# 31. ADMIN PANEL

Create a lightweight administrator dashboard.

Admin can:

- view users
- disable users
- view cases
- create cases
- edit cases
- manage challenges
- manage evidence
- manage suspects
- manage hints
- manage achievements
- view submissions
- view security logs
- view application statistics

Admin panel must have strict authorization.

Never rely only on hiding buttons.

Check authorization server-side.

---

# 32. DASHBOARD

User dashboard should show:

```text
Welcome, Detective

LEVEL 12
DATABASE DETECTIVE

XP
██████████████░░░ 78%

Cases Solved: 14
Cases Remaining: 16
Current Streak: 4 days

Continue Investigation
────────────────────────

CASE #015
The Phantom Transaction

Difficulty: ★★★
Progress: 65%

[ CONTINUE ]
```

Also show:

- recent cases
- recent achievements
- leaderboard position
- recent queries
- XP history

---

# 33. CASE LIST

Create a polished case browser.

Filters:

- difficulty
- category
- completion
- SQL topic

Cards should show:

```text
CASE #012

THE PHANTOM TRANSACTION

Difficulty: Advanced
SQL Topics:
JOIN · GROUP BY · SUBQUERY

XP: 350

Progress: 40%

[ INVESTIGATE ]
```

---

# 34. DATABASE EXPLORER

The database explorer should allow users to expand:

```text
DATABASE
│
├── Tables
│   ├── employees
│   ├── transactions
│   ├── login_logs
│   └── devices
│
├── Views
│
└── Relationships
```

Clicking a table should show:

- columns
- data types
- primary key
- foreign key
- description
- sample records

Do not expose sensitive application tables.

---

# 35. RELATIONSHIP VIEW

Provide a simple visual relationship diagram.

Example:

```text
employees
    │
    │ employee_id
    ▼
transactions
    │
    │ transaction_id
    ▼
audit_logs
```

Keep it lightweight.

Do not introduce a large graph library unless necessary.

SVG/CSS can be used.

---

# 36. QUERY HISTORY

Store each user's investigation queries.

Show:

```text
QUERY HISTORY

SELECT * FROM transactions...
SUCCESS
2.4 ms

SELECT employee_id, COUNT(*)...
SUCCESS
1.8 ms

SELECT ...
ERROR
```

Allow:

- copy
- reuse
- delete history

History should be scoped to the authenticated user.

---

# 37. RESULT TABLE

Results should look professional.

Features:

- row count
- execution time
- horizontal scrolling
- copy cell
- copy result
- export CSV if appropriate

Limit result size.

Do not allow massive result sets.

---

# 38. ACCESSIBILITY

Implement:

- semantic HTML
- keyboard navigation
- visible focus states
- labels for form fields
- accessible buttons
- sufficient contrast
- reduced-motion support
- screen-reader-friendly messages

---

# 39. PERFORMANCE

The VPS only has 1 GB RAM.

Optimize aggressively.

Use:

- PHP OPcache
- database indexes
- pagination
- prepared statements
- small JavaScript bundles
- lazy loading
- minimal dependencies
- compressed assets
- efficient SQL queries

Do not perform expensive queries on every page load.

Do not load all cases at once.

Do not load all leaderboard users.

Use pagination.

---

# 40. SECURITY HEADERS

Configure appropriate headers:

```text
Content-Security-Policy
X-Content-Type-Options
X-Frame-Options
Referrer-Policy
Permissions-Policy
Strict-Transport-Security
```

Do not blindly use a CSP that breaks the application.

Test it properly.

---

# 41. FILE SECURITY

No arbitrary file upload is required.

If uploads are introduced later:

- validate MIME type
- validate extension
- rename files
- prevent executable uploads
- store outside public execution paths
- enforce size limits

For the initial project, avoid uploads entirely unless genuinely necessary.

---

# 42. API DESIGN

Use lightweight PHP endpoints.

Example:

```text
POST /api/auth/login
POST /api/auth/register
POST /api/auth/logout

GET /api/cases
GET /api/cases/{id}

GET /api/cases/{id}/schema
GET /api/cases/{id}/evidence

POST /api/query/execute
POST /api/challenges/{id}/submit

GET /api/leaderboard
GET /api/profile
GET /api/achievements
```

Every endpoint must validate:

- authentication
- authorization
- input
- CSRF where applicable
- rate limits

---

# 43. NO TRUST IN CLIENT

Never trust values such as:

```text
user_id
xp
level
score
case_completed
challenge_completed
hint_count
execution_time
```

The server determines all of them.

---

# 44. TESTING

Create a proper test plan.

Test:

### Authentication

- valid login
- invalid login
- brute force
- session fixation
- logout
- password validation

### Authorization

- user accessing another user's data
- admin-only pages
- direct URL manipulation

### SQL security

- SQL injection attempts
- multiple statements
- DROP
- DELETE
- UPDATE
- INSERT
- comments
- UNION
- subqueries
- dangerous functions
- excessive queries

### Web security

- XSS
- CSRF
- session attacks
- IDOR
- parameter tampering

### Functional

- case loading
- query execution
- challenge validation
- XP
- leaderboard
- achievements
- hints

---

# 45. SECURITY TEST CASES

Create an internal security test suite.

Examples:

```text
SELECT * FROM users
```

Must fail.

```text
DROP TABLE employees
```

Must fail.

```text
DELETE FROM transactions
```

Must fail.

```text
UPDATE employees SET ...
```

Must fail.

Multiple statements must fail.

Attempts to access another user's profile must fail.

Attempts to modify XP from the browser must fail.

Attempts to complete the same challenge repeatedly must not award duplicate XP.

---

# 46. DATABASE TRANSACTIONS

Use transactions when modifying multiple related records.

Example:

```text
Challenge Passed
      ↓
BEGIN TRANSACTION
      ↓
Record Attempt
      ↓
Update Progress
      ↓
Award XP
      ↓
Unlock Achievement
      ↓
COMMIT
```

If something fails:

```text
ROLLBACK
```

---

# 47. SEED DATA

Create realistic fictional data.

Do NOT use real people's personal data.

Create:

- fictional employees
- fictional companies
- fictional transactions
- fictional locations
- fictional devices
- fictional login logs
- fictional emails
- fictional departments

Create enough records to make SQL queries meaningful.

Each case should have enough data for joins and analysis.

---

# 48. SAMPLE DATA VOLUME

Example case:

```text
employees:       30
departments:      8
transactions:   500
login_logs:     800
devices:         50
locations:       20
access_logs:   1000
```

Do not create millions of records.

The objective is realistic SQL investigation without consuming VPS resources.

---

# 49. SQL TOPICS TO TEACH

The game should gradually introduce:

### Beginner

```text
SELECT
FROM
WHERE
AND
OR
NOT
ORDER BY
LIMIT
DISTINCT
LIKE
IN
BETWEEN
```

### Intermediate

```text
COUNT
SUM
AVG
MIN
MAX
GROUP BY
HAVING
INNER JOIN
LEFT JOIN
CASE
COALESCE
```

### Advanced

```text
subqueries
CTEs
UNION
EXISTS
window functions
date functions
conditional aggregation
complex joins
```

---

# 50. GAME CATEGORIES

Create categories:

```text
Financial Crime
Corporate Espionage
Missing Person
Digital Theft
Fraud Investigation
Cyber Incident
Data Leak
Identity Mystery
Time-Based Investigation
Database Intrusion
```

All scenarios must be fictional.

---

# 51. HOME PAGE

The landing page should immediately explain the concept.

Hero:

```text
SQL DETECTIVE

Investigate.
Query.
Discover the Truth.

Solve fictional cases using real SQL skills.

[ START INVESTIGATION ]
[ HOW IT WORKS ]
```

Below:

```text
How It Works

01
Read the Case

02
Inspect the Evidence

03
Explore the Database

04
Write SQL

05
Find the Truth
```

Then:

- featured cases
- SQL topics
- statistics
- leaderboard preview
- footer

---

# 52. PROFILE PAGE

Display:

```text
DETECTIVE PROFILE

Username
Rank
Level
XP

Cases Solved
Challenges Solved
Perfect Cases
Hints Used

Achievements

Recent Investigations
```

Allow changing:

- display name
- email
- password

Do not allow users to directly modify:

- XP
- level
- rank
- completion
- achievements

---

# 53. DARK MODE

Implement theme switching using CSS variables.

Store preference locally.

Optional authenticated user preference may be stored in database.

Do not require a large UI library.

---

# 54. ANIMATIONS

Use very subtle animations.

Allowed:

- hover
- fade
- slide
- progress animation
- modal transition
- success notification

Avoid:

- constant background animation
- particle effects
- excessive glowing
- expensive canvas animations

The application must remain fast.

---

# 55. MOBILE

On mobile:

```text
DATABASE
↓
SCHEMA
↓
EVIDENCE
↓
SQL EDITOR
↓
RESULT
```

Use tabs or collapsible panels.

The SQL editor must remain usable.

---

# 56. NIELIT PROJECT DOCUMENTATION

Create a `/docs` directory containing:

```text
01_Project_Proposal.md
02_Abstract.md
03_Objectives.md
04_Feasibility_Study.md
05_Requirement_Analysis.md
06_System_Analysis.md
07_System_Design.md
08_Database_Design.md
09_ER_Diagram.md
10_Data_Flow_Diagram.md
11_Use_Case_Diagram.md
12_Flowcharts.md
13_UI_Design.md
14_Security_Design.md
15_Testing.md
16_Test_Cases.md
17_User_Manual.md
18_Installation.md
19_Maintenance.md
20_Future_Scope.md
```

Also create:

```text
PROJECT_REPORT_OUTLINE.md
VIVA_QUESTIONS.md
```

---

# 57. NIELIT PROJECT REPORT CONTENT

Prepare the project so that the final report can explain:

1. Introduction
2. Problem Statement
3. Existing System
4. Proposed System
5. Objectives
6. Scope
7. Feasibility Study
8. Requirement Analysis
9. Hardware Requirements
10. Software Requirements
11. System Architecture
12. Database Design
13. ER Diagram
14. DFD
15. UML Use Case
16. UML Class Diagram
17. Module Design
18. UI Design
19. Implementation
20. Security
21. Testing
22. Results
23. Limitations
24. Future Scope
25. Conclusion
26. References

---

# 58. DOCUMENTATION MUST BE REAL

Do not create placeholder documentation such as:

```text
TODO
Add details here
Coming soon
```

Documentation must describe the actual implemented system.

Update documentation whenever architecture changes.

---

# 59. VIVA PREPARATION

Create a viva preparation document containing questions and answers covering:

### PHP

- What is PHP?
- Why PHP?
- Sessions
- Cookies
- PDO
- MVC
- Authentication

### MySQL

- Primary key
- Foreign key
- Normalization
- Indexing
- JOIN
- GROUP BY
- Transactions
- Views

### SQL Detective

- How queries are executed
- How query security works
- How cases are validated
- How XP is calculated
- How cheating is prevented

### Security

- SQL injection
- XSS
- CSRF
- session fixation
- authentication
- authorization
- prepared statements

### Software Engineering

- requirements
- feasibility
- architecture
- testing
- debugging
- maintenance

---

# 60. ADMIN SECURITY

There must be no default:

```text
admin / admin
```

During installation, require creation of an administrator account.

Never hardcode administrator credentials.

Protect admin routes with server-side authorization.

---

# 61. ENVIRONMENT CONFIGURATION

Use:

```text
.env
```

for secrets.

Example:

```text
APP_ENV=production
APP_DEBUG=false

DB_HOST=localhost
DB_NAME=sqldetective
DB_USER=
DB_PASSWORD=

SESSION_SECURE=true
```

Never commit `.env`.

Provide:

```text
.env.example
```

with empty secrets.

---

# 62. PRODUCTION DEPLOYMENT

Support:

```text
Ubuntu Linux
Nginx
PHP-FPM
MySQL/MariaDB
HTTPS
```

The public document root should point to:

```text
/public
```

Never expose the project root directly.

---

# 63. VPS RESOURCE TARGET

The application should aim for:

```text
Idle RAM:
as low as practical

No permanent worker:
YES

No Node runtime:
YES

No Redis:
YES

No WebSocket server:
YES

Database:
MySQL/MariaDB

Backend:
PHP-FPM

Frontend:
HTML/CSS/Vanilla JS
```

Optimize for a 1 GB VPS.

---

# 64. BACKUP SYSTEM

Provide documentation for:

```text
mysqldump
```

backup.

Document:

- database backup
- restore
- configuration backup
- log rotation

Do not create an automated backup worker unless necessary.

---

# 65. DEBUG MODE

Create development/debug mode.

Production:

```text
APP_DEBUG=false
```

Development:

```text
APP_DEBUG=true
```

Debug information must never leak when production mode is enabled.

---

# 66. CODE QUALITY

Follow these rules:

- PHP 8+
- strict typing where practical
- PSR-style structure
- PDO
- prepared statements
- reusable services
- clear naming
- small functions
- no unnecessary abstraction
- no duplicated security logic
- no hardcoded secrets
- no hardcoded absolute paths
- no debug `var_dump()`
- no production `print_r()`
- no commented-out dead code
- no unnecessary dependencies

---

# 67. JAVASCRIPT

Prefer vanilla JavaScript.

Use JavaScript for:

- AJAX/fetch
- editor interactions
- tabs
- modals
- theme switching
- notifications
- dynamic result rendering
- query history
- responsive controls

Do not turn the project into a SPA unless there is a strong reason.

Server-rendered PHP pages are preferred for simplicity and VPS efficiency.

---

# 68. CSS

Use modern CSS.

Prefer:

```text
CSS variables
Flexbox
Grid
media queries
transitions
```

Keep the stylesheet organized.

Do not use giant utility-class markup everywhere.

---

# 69. SQL EDITOR UX

The editor should have:

```text
[Run Query] [Clear] [Format] [Save]

SELECT *
FROM transactions
WHERE amount > 500000;
```

Keyboard shortcut:

```text
Ctrl + Enter
```

runs the query.

Display:

```text
Query executed successfully

Rows: 12
Execution time: 2.31 ms
```

Error:

```text
Query failed

Syntax error near...
```

Do not reveal internal database credentials or internal schema.

---

# 70. QUERY RESULT SECURITY

Limit:

```text
Maximum query length
Maximum returned rows
Maximum execution time
Maximum result size
Maximum queries per minute
```

If a query exceeds the limit:

```text
QUERY TERMINATED

The investigation query exceeded the allowed execution limits.
```

---

# 71. CASE COMPLETION

A case should only be marked complete when all required challenges are successfully completed.

Server flow:

```text
submit query
      ↓
validate
      ↓
execute
      ↓
compare result
      ↓
record attempt
      ↓
update progress
      ↓
if final challenge:
    complete case
    award XP
    unlock achievement
```

Use database transactions.

---

# 72. RESULT VALIDATION

Do not depend only on exact SQL text.

A player may solve the same problem using multiple valid SQL queries.

Validate the **result**, not merely the query string.

Normalize result ordering where appropriate.

For challenges where order matters, explicitly define expected ordering.

---

# 73. CASE DATA MODEL

Every challenge should define:

```text
database
allowed tables
allowed operations
expected result
comparison rules
```

Do not hardcode challenge logic directly inside controllers.

Use a challenge validation service.

---

# 74. SECURITY-FIRST DESIGN

Before implementing the UI, design:

1. Authentication
2. Authorization
3. Database separation
4. Query restrictions
5. CSRF
6. XSS prevention
7. SQL injection prevention
8. Rate limiting
9. Session security
10. Error handling
11. Logging
12. Anti-cheat

Security is part of the architecture, not an afterthought.

---

# 75. DEVELOPMENT PHASES

Build the project in phases.

## Phase 1 — Architecture

Create:

- project structure
- configuration
- routing
- database connection
- environment configuration
- error handling

Do not build the whole UI yet.

---

## Phase 2 — Database

Implement:

- migrations
- tables
- relationships
- indexes
- constraints
- seed system

Verify database integrity.

---

## Phase 3 — Authentication

Implement:

- registration
- login
- logout
- session security
- password hashing
- authorization

Test thoroughly.

---

## Phase 4 — Case Engine

Implement:

- cases
- suspects
- evidence
- case database
- tables
- columns
- records
- challenges
- hints

---

## Phase 5 — SQL Engine

Implement:

- query editor
- query validation
- read-only execution
- result processing
- query limits
- query history

This is the most security-sensitive module.

---

## Phase 6 — Game System

Implement:

- progress
- XP
- levels
- ranks
- achievements
- hints
- case completion
- leaderboard

---

## Phase 7 — Frontend

Build:

- landing page
- login
- register
- dashboard
- case browser
- investigation workspace
- profile
- achievements
- leaderboard
- admin

---

## Phase 8 — Security Hardening

Perform:

- SQL injection tests
- XSS tests
- CSRF tests
- authorization tests
- rate-limit tests
- query escape tests
- session tests
- parameter tampering tests

---

## Phase 9 — Testing

Create:

- unit tests
- integration tests
- functional tests
- security tests
- UI tests
- database tests

---

## Phase 10 — Documentation

Generate:

- architecture documentation
- database documentation
- diagrams
- testing documentation
- installation guide
- user manual
- viva questions

---

# 76. DEVELOPMENT RULE FOR OPENCODE

Do not attempt to generate the entire application in one giant response.

Work incrementally.

After each phase:

1. inspect the existing project
2. implement changes
3. run tests
4. fix errors
5. verify security
6. verify database integrity
7. continue to the next phase

Never overwrite working code unnecessarily.

Preserve existing architecture when modifying it.

---

# 77. ERROR HANDLING RULE

Never hide errors during development.

Never expose sensitive errors in production.

Use centralized error handling.

Log internal details server-side.

Return safe user-facing messages.

---

# 78. DATABASE MIGRATION RULE

Every database change must be represented by a migration.

Do not require manually editing the production database.

Provide:

```text
database/migrations/
database/seeds/
```

and an installation process.

---

# 79. INSTALLATION

Create:

```text
INSTALL.md
```

with complete instructions for:

- Ubuntu
- PHP
- PHP extensions
- MySQL/MariaDB
- Nginx
- PHP-FPM
- Composer if required
- database creation
- migrations
- seed data
- permissions
- HTTPS
- environment variables
- production configuration

Also document local Windows/XAMPP setup for demonstration.

---

# 80. DEMO ACCOUNT

Create seed functionality for a demo account.

Do not hardcode the demo password in production.

The installation process should allow setting the demo credentials.

---

# 81. PROJECT QUALITY REQUIREMENT

This must look like a serious final-year academic project.

Avoid:

- toy UI
- fake buttons
- non-functional features
- placeholder data
- lorem ipsum
- fake statistics
- broken links
- empty pages
- unnecessary animations

Every visible feature should work.

---

# 82. FINAL ACCEPTANCE CRITERIA

The project is complete only when:

- authentication works
- authorization works
- database works
- 30 cases exist
- at least 10 application database tables exist
- relationships are implemented
- SQL editor works
- safe query execution works
- dangerous queries are blocked
- query results are displayed
- challenges can be solved
- progress works
- XP works
- levels work
- achievements work
- leaderboard works
- hints work
- query history works
- admin works
- rate limiting works
- CSRF protection works
- XSS protection works
- SQL injection protection works
- error handling works
- logging works
- responsive UI works
- documentation exists
- testing exists
- installation works
- production configuration works
- application can run comfortably on a 1 GB RAM VPS

---

# 83. FINAL COMMAND TO OPENCODE

You are the lead software engineer responsible for implementing this project.

First inspect the current workspace.

Then create an implementation plan based on this specification.

Do NOT immediately generate the entire application in one operation.

Implement it phase-by-phase.

After every significant implementation:

- run syntax checks
- run tests
- inspect database integrity
- inspect security
- fix errors
- verify the feature manually where possible

Prioritize security and correctness over speed.

Never expose secrets.

Never use unsafe SQL concatenation.

Never trust client-side game state.

Never execute player SQL against the application database.

Never allow player queries to modify investigation data.

Never expose production debugging information.

Keep the application lightweight.

Do not introduce unnecessary dependencies.

Do not introduce a Node.js backend.

Do not introduce Redis.

Do not introduce WebSockets.

Do not introduce a heavy frontend framework.

Use PHP + MySQL/MariaDB + HTML/CSS + Vanilla JavaScript as the primary technology stack.

The final application must be suitable for deployment on a 1-core / 1-GB RAM VPS alongside other PHP websites.

Most importantly:

**Build SQL Detective as a real database-driven investigation system, not merely a quiz website.**

The final result should be polished enough for:

- NIELIT A-Level Major Project evaluation
- project demonstration
- viva
- portfolio
- real public deployment

Before considering the project finished, perform a complete security review, performance review, database review, UI review, and functional test pass.
