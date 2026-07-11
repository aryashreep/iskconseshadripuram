# Audit Logging

> **Last updated:** 2026-07-11
> **Canonical owner:** Development Team
> **Related:** `SECURITY.md` (A09 section), `docs/ADMIN.md`

---

## Current State

Audit logging exists in fragmented form:

| Event Type | Storage Location | Captures |
|-----------|-----------------|----------|
| Login attempts (success) | `login_attempts` table | admin_id, IP, timestamp — success=1 |
| Login attempts (failure) | `login_attempts` table | username, IP, timestamp — success=0 |
| PHP errors | `logs/php_errors.log` | Stack traces, error messages |
| Sudamaseva subscriptions | `logs/sudamaseva_subscriptions.log` | Creation/errors |
| Sudamaseva payments | `logs/sudamaseva_payments.log` | Verification details |
| Sudamaseva webhooks | `logs/sudamaseva_webhooks.log` | All webhook events |

## Events NOT Currently Logged

- Admin CRUD operations (create/edit/delete donors, causes, sevas)
- Permission denied events
- CSV export/download events
- Content publish/unpublish actions
- Booking status changes
- Role/permission modifications

## Recommended Schema for Future Centralized Audit Log

```sql
CREATE TABLE audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT DEFAULT NULL,
    event_type VARCHAR(50) NOT NULL,
    event_data JSON,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event_type (event_type),
    INDEX idx_admin (admin_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Required Audit Events

| Priority | Event | Data to Capture |
|----------|-------|----------------|
| High | Admin login success | admin_id, IP, timestamp, session_id |
| High | Admin login failure | username, IP, timestamp |
| High | Payment verification | order_id, payment_id, status, IP |
| High | Webhook receipt | event_type, payment_id, status |
| Medium | Permission denied | admin_id, page/endpoint, IP |
| Medium | Donor CRUD | admin_id, action, donor_id |
| Medium | Role/permission change | admin_id, role_id, changes |
| Medium | CSV export | admin_id, report_type, filters |
| Low | Content publish/unpublish | admin_id, content_id, action |

### Privacy Rules

- Never log passwords, tokens, or credit card numbers
- Mask PII in logs (e.g., phone numbers: `98*****10`)
- Logs must be accessible only to super_admin
- Retention period: minimum 1 year for financial events, 6 months for access events
