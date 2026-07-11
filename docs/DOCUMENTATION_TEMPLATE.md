# Documentation Template

> **Purpose:** Templates for creating consistent documentation across all modules.
> **Related:** `DOCUMENTATION_POLICY.md` (when to update)
> **Last updated:** 2026-07-11

---

## Module README Template

```markdown
# Module: [Module Name]

## Purpose
[One paragraph describing what this module does]

## Owned Database Tables
- `table_name` — Description and key columns

## Dependencies
- **[Module Name]** — What this module requires from other modules

## Entry Points
| Type | URL | File | Description |
|------|-----|------|-------------|
| Public | `/url` | `module/content/file.php` | Description |
| Admin | `/admin/url` | `module/Admin/file.php` | Description |
| API | `POST /api/endpoint` | `module/api/endpoint.php` | Description |

## Directory Structure
```
modules/Module/
├── Admin/           # Admin pages
├── api/             # API endpoints
├── content/         # Public pages
└── README.md
```

## Business Rules
- Rule 1: Description
- Rule 2: Description
```

## Module DATABASE Template

```markdown
# [Module Name] — Database Schema

## Owned Tables

### table_name
| Column | Type | Notes |
|--------|------|-------|
| id | INT (PK) | Auto-increment |
| column2 | VARCHAR(255) | Description |

## Key Relationships
```
table1.id ← table2.table1_id (FK)
```
```

## API Endpoint Template

```markdown
### METHOD /api/endpoint.php

**Request** (JSON):
```json
{
  "field1": "value1",
  "field2": 123
}
```

**Response** (200):
```json
{
  "success": true,
  "data": {}
}
```

**Error** (400/500):
```json
{
  "error": "Description"
}
```

**Validation:** Rules for each field.
**Security:** Auth requirement, signature verification, rate limiting.
```

## Architecture Decision Template

```markdown
## [YYYY-MM-DD] Decision Title

### Decision
One sentence stating the decision.

### Context
What problem or constraint led to this decision.

### Options Considered
- **Option A**: Pros/cons
- **Option B**: Pros/cons

### Rationale
Why the chosen option won.

### Trade-offs
- **Downside**: What was sacrificed
- **Risk**: What could go wrong

### Related Files
- `path/to/file.php`
```
