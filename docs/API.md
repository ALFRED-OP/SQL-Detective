# SQL Detective — API Reference

All API endpoints are routed through `public/index.php` and handled by `controllers/api.php`. All endpoints require authentication and CSRF tokens unless noted.

## Query Execution

### POST /api/query

Execute a SQL query against the investigation database.

**Request:**
```json
{
    "query": "SELECT * FROM employees WHERE department = 'Finance'",
    "case_id": 1,
    "challenge_id": 5
}
```

**Response (success):**
```json
{
    "success": true,
    "columns": ["id", "first_name", "last_name", "department"],
    "rows": [
        {"id": 3, "first_name": "Anita", "last_name": "Desai", "department": "Finance"},
        {"id": 4, "first_name": "Vikram", "last_name": "Patel", "department": "Finance"}
    ],
    "row_count": 2,
    "execution_time": "2ms",
    "xp_earned": null
}
```

**Response (error):**
```json
{
    "error": "Query contains forbidden keyword: DROP"
}
```

**Validation Rules:**
- Query length: max 10,000 characters
- Execution timeout: 5 seconds
- Only SELECT statements allowed
- Blocked keywords: DROP, DELETE, INSERT, UPDATE, ALTER, TRUNCATE, GRANT, etc.
- Maximum result rows returned: 1,000

---

### POST /api/challenge/submit

Submit an answer for a challenge.

**Request:**
```json
{
    "challenge_id": 5,
    "query": "SELECT COUNT(*) as total FROM transactions WHERE amount > 1000000"
}
```

**Response (correct):**
```json
{
    "correct": true,
    "xp_earned": 50,
    "feedback": "Excellent detective work!",
    "level_up": false,
    "new_level": null,
    "achievement": null
}
```

**Response (incorrect):**
```json
{
    "correct": false,
    "feedback": "Not quite right. Check your filtering conditions.",
    "hint": "Try looking at the transactions table with amount > 1000000"
}
```

---

### GET /api/schema

Get the schema for a table in the investigation database.

**Parameters:**
- `case_id` (query) — The case ID
- `table` (query) — The table name

**Response:**
```json
{
    "columns": [
        {"name": "id", "type": "int(11)", "nullable": "NO", "key": "PRI"},
        {"name": "first_name", "type": "varchar(50)", "nullable": "NO", "key": ""},
        {"name": "salary", "type": "decimal(12,2)", "nullable": "NO", "key": ""}
    ],
    "description": "Employee records",
    "sample_data": [
        {"id": 1, "first_name": "Priya", "salary": 2500000.00}
    ]
}
```

---

### POST /api/hint

Request a hint for a challenge.

**Request:**
```json
{
    "hint_id": 12,
    "case_id": 1
}
```

**Response:**
```json
{
    "hint_text": "Try using a JOIN between the employees and transactions tables.",
    "xp_cost": 10
}
```

---

## User Profile

### GET /api/profile/stats

Get user statistics (XP, level, cases solved, etc.).

**Response:**
```json
{
    "xp": 2500,
    "level": 5,
    "rank": "SQL Investigator",
    "cases_solved": 8,
    "total_queries": 145,
    "achievements_count": 12
}
```

---

## Admin Endpoints

### POST /admin/api/update

Update a field via inline editing (admin only).

**Request:**
```json
{
    "id": 1,
    "field": "status",
    "value": "active",
    "type": "user"
}
```

**Response:**
```json
{
    "success": true
}
```

---

## Error Responses

All endpoints return errors in this format:

```json
{
    "error": "Error message describing the problem"
}
```

Common HTTP status codes:
- `400` — Bad request / validation error
- `401` — Authentication required
- `403` — Forbidden (insufficient permissions)
- `404` — Resource not found
- `419` — CSRF token mismatch
- `429` — Rate limit exceeded
- `500` — Internal server error
