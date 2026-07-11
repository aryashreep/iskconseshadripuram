# RBAC (Role-Based Access Control) Specification

> **📜 HISTORICAL DOCUMENT** — This specification guided the initial RBAC implementation (Phase 8) which has been completed.
> **Status:** Implemented ✅
> **Current canonical sources:** [`modules/RBAC/README.md`](modules/RBAC/README.md) (module docs), [`SECURITY.md`](SECURITY.md) (authorization section), [`docs/ADMIN.md`](docs/ADMIN.md) (admin RBAC reference), [`docs/AUTHORIZATION_MATRIX.md`](docs/AUTHORIZATION_MATRIX.md) (permission matrix)
> **Parts still authoritative:** Sections 4 (Database Schema), 5 (Permission Modules & Actions), 6 (Role Definitions), 7 (Service Layer)
> **Parts superseded:** Section 8 (UI/UX) — actual implementation may differ; Section 10 (Implementation Phasing) — all phases complete

---

> **Project:** ISKCON Sri Jagannath Mandir — Admin Portal
> **Author:** Buffy (Codebuff AI)
> **Date:** July 7, 2026
> **Status:** Implemented ✅

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Current System Analysis](#2-current-system-analysis)
3. [Architecture Overview](#3-architecture-overview)
4. [Database Schema Design](#4-database-schema-design)
5. [Permission Modules & Actions](#5-permission-modules--actions)
6. [Role Definitions & Seed Permissions](#6-role-definitions--seed-permissions)
7. [API & Service Layer Design](#7-api--service-layer-design)
8. [UI/UX Design](#8-uiux-design)
9. [Migration Strategy](#9-migration-strategy)
10. [Implementation Phasing](#10-implementation-phasing)
11. [Design Decisions & Rationale](#11-design-decisions--rationale)
12. [Open Questions](#12-open-questions)

---

## 1. Executive Summary

This specification defines a Role-Based Access Control (RBAC) system for the ISKCON Sri Jagannath Mandir admin portal. The system moves from the current flat comma-separated role string to a full multi-table RBAC architecture with granular permissions, role management UI, and seamless migration for existing users.

### Key Design Principles

- **Principle of Least Privilege** — Users receive only the permissions required for their responsibilities
- **Union-based Resolution** — Multiple roles merge permissions via UNION (OR) logic
- **Super Admin Bypass** — Super Administrators implicitly have all permissions
- **Defense in Depth** — Permissions checked at both server (PHP) and client (UI visibility) levels
- **No Audit Logging** — Excluded to avoid database bloat
- **No Role Inheritance** — Each role has explicit flat permissions for predictability

---

## 2. Current System Analysis

### Current `admins` Table Structure

```sql
CREATE TABLE `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `full_name` VARCHAR(100) DEFAULT NULL,
  `role` VARCHAR(255) NOT NULL DEFAULT 'editor',  -- Comma-separated role string
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Current Role System

The current system stores roles as a comma-separated string in the `role` column. Existing roles:
- `super_admin` — Full access
- `editor` — Manage blogs, festivals, seva catalog
- `pujari` — Manage puja/yagya bookings
- `treasurer` — View donations, export reports
- `travel_agent` — Manage Panihati Yatra
- `sudamaseva` — Manage subscription donations

### Current Guard Implementation

The `SessionGuard` class (`modules/Kernel/src/Helpers/SessionGuard.php`) provides:
- `requireLogin()` — Redirects unauthenticated users
- `requireRole(['super_admin', 'editor'])` — Blocks with 403 if role not present
- `hasRole(['super_admin'])` — Boolean check for UI visibility

### Limitations

1. **No granular permissions** — Roles are hardcoded, not data-driven
2. **No permission matrix** — Cannot assign fine-grained access per module
3. **No UI for role management** — Roles are strings, not managed entities
4. **Mixed single/multi-role** — Roles are comma-separated but treated inconsistently
5. **No scalability** — Adding new roles requires code changes everywhere
6. **Hardcoded checks** — `requireRole(['super_admin', 'treasurer'])` scattered across 34+ files

---

## 3. Architecture Overview

### Module Location

All RBAC code lives in a new module at `modules/RBAC/`, following the existing project's modular architecture.

```
modules/RBAC/
├── Admin/
│   ├── roles.php              # Role listing & management
│   ├── role-edit.php          # Create/edit role + permission assignment
│   ├── permissions.php        # Permission listing (read-only reference)
│   └── partials/
│       └── permission-matrix.php  # Reusable matrix component
├── src/
│   ├── RbacService.php        # Core RBAC logic
│   ├── PermissionRegistry.php # Permission definitions registry
│   └── RbacMiddleware.php     # Middleware for permission checks
├── database/
│   └── migrations/
│       └── 001_create_rbac_tables.php  # Schema migration
│       └── 002_seed_roles_and_permissions.php  # Seed data
│       └── 003_migrate_existing_admins.php    # Auto-mapping migration
├── docs/
│   └── README.md
└── routes.php                 # Route registration
```

### Key Classes

| Class | Location | Purpose |
|-------|----------|---------|
| `RbacService` | `src/RbacService.php` | Core permission checking, role/permission CRUD |
| `PermissionRegistry` | `src/PermissionRegistry.php` | Central registry of all permission definitions |
| `RbacMiddleware` | `src/RbacMiddleware.php` | Middleware-style permission check (extends SessionGuard) |

### Integration Points

The RBAC system integrates with the existing auth system through these touch points:

1. **`SessionGuard`** — Extended with `requirePermission()` method
2. **`auth-check.php`** — Updated to load user permissions into session on page load
3. **`admin/partials/header.php`** — Sidebar menu items use permission checks
4. **`admin/admin-edit.php`** — Updated to assign roles via UI
5. **`admin/admins.php`** — Updated to show assigned roles & permission summary

---

## 4. Database Schema Design

### 4.1 New Tables

```sql
-- ============================================
-- RBAC Module — Schema
-- ============================================

-- 1. ROLES TABLE
-- Stores all role definitions. Data-driven — new roles can be added via UI.
CREATE TABLE `rbac_roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(100) NOT NULL UNIQUE COMMENT 'Machine-readable identifier, e.g. content_manager',
    `name` VARCHAR(255) NOT NULL COMMENT 'Human-readable name, e.g. Content Manager',
    `description` TEXT DEFAULT NULL COMMENT 'What this role is for',
    `is_system` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'System roles cannot be deleted via UI',
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. PERMISSIONS TABLE
-- Each row = a single permission in the format "module.action"
-- e.g. "donations.view", "festivals.create"
CREATE TABLE `rbac_permissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(150) NOT NULL UNIQUE COMMENT 'e.g. donations.view',
    `module` VARCHAR(100) NOT NULL COMMENT 'Module name, e.g. donations, festivals',
    `action` VARCHAR(50) NOT NULL COMMENT 'Action name, e.g. view, create, edit, delete, export',
    `label` VARCHAR(255) NOT NULL COMMENT 'Human-readable label, e.g. View Donations',
    `description` TEXT DEFAULT NULL,
    `is_system` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'System permissions cannot be deleted via UI',
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_perm_module` (`module`),
    INDEX `idx_perm_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. ROLE-PERMISSION LINK TABLE (Many-to-Many)
CREATE TABLE `rbac_role_permissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `role_id` INT NOT NULL,
    `permission_id` INT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_role_perm` (`role_id`, `permission_id`),
    CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `rbac_roles`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_rp_perm` FOREIGN KEY (`permission_id`) REFERENCES `rbac_permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. USER-ROLE LINK TABLE (Many-to-Many)
-- Replaces the `admins.role` column
CREATE TABLE `rbac_user_roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT NOT NULL,
    `role_id` INT NOT NULL,
    `assigned_by` INT DEFAULT NULL COMMENT 'Admin ID who assigned this role',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_user_role` (`admin_id`, `role_id`),
    CONSTRAINT `fk_ur_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ur_role` FOREIGN KEY (`role_id`) REFERENCES `rbac_roles`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ur_assigner` FOREIGN KEY (`assigned_by`) REFERENCES `admins`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 4.2 Migration from `admins.role` Column

After creating new tables and migrating data, the `admins.role` column becomes **deprecated** but should be kept for backward compatibility during transition. A future cleanup migration can remove it.

### 4.3 Entity Relationship Diagram

```
admins ──1:N──> rbac_user_roles ──N:1──> rbac_roles
                                              │
                                              │ 1:N
                                              │
                                     rbac_role_permissions
                                              │
                                              │ N:1
                                              │
                                     rbac_permissions
```

### 4.4 Permission Slug Convention

```
{module}.{action}

Examples:
  donations.view
  donations.create
  donations.edit
  donations.delete
  donations.export
  festivals.view
  festivals.create
  festivals.edit
  festivals.delete
  festivals.export
```

---

## 5. Permission Modules & Actions

### 5.1 Modules

| Module | Slug | Description |
|--------|------|-------------|
| Dashboard | `dashboard` | Admin dashboard overview |
| Donations | `donations` | Transaction logs, cause management |
| Festivals | `festivals` | Festival/cause listing & detail |
| Seva Catalog | `seva_catalog` | Master seva catalog management |
| Blogs & Content | `blogs` | Blog posts & content management |
| Bookings | `bookings` | Puja & Yagya bookings |
| Panihati Yatra | `panihati` | Yatra registration & management |
| Sudamaseva | `sudamaseva` | Subscription donation management |
| Reports | `reports` | Donation reports & dashboards |
| Devotees | `devotees` | Devotee management |
| Volunteers | `volunteers` | Volunteer management |
| Events | `events` | Special events management |
| Audit Logs | `audit_logs` | System audit log viewing |

### 5.2 Standard Actions

Each module supports the **standard 5 actions**:

| Action | Slug | Description |
|--------|------|-------------|
| View | `view` | View/list records in this module |
| Create | `create` | Create new records |
| Edit | `update` | Edit existing records |
| Delete | `delete` | Delete records |
| Export | `export` | Export data (CSV, reports) |

### 5.3 Total Permissions

13 modules × 5 actions = **65 permissions** (max)

---

## 6. Role Definitions & Seed Permissions

### 6.1 Role Table

| # | Role Slug | Name | Description |
|---|-----------|------|-------------|
| 1 | `super_admin` | Super Administrator | Unrestricted access across all system modules. Bypasses all permission checks. |
| 2 | `temple_admin` | Temple Administrator | Full access to all operational modules. Cannot manage admin users, roles, or system settings. |
| 3 | `donation_manager` | Donation Manager | Manage donations, causes, and related reporting. |
| 4 | `festival_manager` | Festival Manager | Manage festivals, events, and seva catalog. |
| 5 | `accounts` | Accounts / Finance | View financial data, reports, exports, and process refunds. |
| 6 | `content_manager` | Content Manager | Manage blogs and website content. |
| 7 | `report_viewer` | Report Viewer | Read-only access to reports and dashboards. |
| 8 | `devotee_care` | Devotee Care | Manage devotee records and relationships. |
| 9 | `volunteer_coordinator` | Volunteer Coordinator | Manage volunteers and assignments. |
| 10 | `event_coordinator` | Event Coordinator | Manage special events and programs. |
| 11 | `read_only` | Read Only User | View-only access across permitted modules. No create/edit/delete rights. |

> **Note:** The original 12th option "Read Only User" is merged with role #11. All suggested roles are covered.

### 6.2 Super Administrator (Special)

Super Administrator has an **implicit bypass** — no explicit permissions need to be assigned. The system treats `super_admin` as always having access. This is handled in code:

```php
// Permission check logic:
if (in_array('super_admin', $userRoles)) {
    return true; // Always allowed
}
// Otherwise, check explicit permission assignment
```

### 6.3 Seed Permission Matrix

The following matrix defines which permissions each role receives by default. Roles are seeded via migration script and can be modified through the admin UI.

#### Legend
- ✅ = Has this permission
- ✗ = Does not have this permission

#### Dashboard

| Permission | temple_admin | donation_manager | festival_manager | accounts | content_manager | report_viewer | devotee_care | volunteer_coordinator | event_coordinator | read_only |
|---|---|---|---|---|---|---|---|---|---|---|
| dashboard.view | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |

#### Donations

| Permission | temple_admin | donation_manager | festival_manager | accounts | content_manager | report_viewer | devotee_care | volunteer_coordinator | event_coordinator | read_only |
|---|---|---|---|---|---|---|---|---|---|---|
| donations.view | ✅ | ✅ | ✗ | ✅ | ✗ | ✅ | ✗ | ✗ | ✗ | ✅ |
| donations.create | ✅ | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| donations.edit | ✅ | ✅ | ✗ | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| donations.delete | ✅ | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| donations.export | ✅ | ✅ | ✗ | ✅ | ✗ | ✅ | ✗ | ✗ | ✗ | ✗ |

#### Festivals

| Permission | temple_admin | donation_manager | festival_manager | accounts | content_manager | report_viewer | devotee_care | volunteer_coordinator | event_coordinator | read_only |
|---|---|---|---|---|---|---|---|---|---|---|
| festivals.view | ✅ | ✗ | ✅ | ✅ | ✅ | ✅ | ✗ | ✗ | ✅ | ✅ |
| festivals.create | ✅ | ✗ | ✅ | ✗ | ✅ | ✗ | ✗ | ✗ | ✅ | ✗ |
| festivals.edit | ✅ | ✗ | ✅ | ✗ | ✅ | ✗ | ✗ | ✗ | ✅ | ✗ |
| festivals.delete | ✅ | ✗ | ✅ | ✗ | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ |
| festivals.export | ✅ | ✗ | ✅ | ✗ | ✗ | ✅ | ✗ | ✗ | ✗ | ✗ |

#### Seva Catalog

| Permission | temple_admin | donation_manager | festival_manager | accounts | content_manager | report_viewer | devotee_care | volunteer_coordinator | event_coordinator | read_only |
|---|---|---|---|---|---|---|---|---|---|---|
| seva_catalog.view | ✅ | ✅ | ✅ | ✗ | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ |
| seva_catalog.create | ✅ | ✗ | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| seva_catalog.edit | ✅ | ✗ | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| seva_catalog.delete | ✅ | ✗ | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| seva_catalog.export | ✅ | ✗ | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |

#### Blogs / Content

| Permission | temple_admin | donation_manager | festival_manager | accounts | content_manager | report_viewer | devotee_care | volunteer_coordinator | event_coordinator | read_only |
|---|---|---|---|---|---|---|---|---|---|---|
| blogs.view | ✅ | ✗ | ✗ | ✗ | ✅ | ✅ | ✗ | ✗ | ✗ | ✅ |
| blogs.create | ✅ | ✗ | ✗ | ✗ | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ |
| blogs.edit | ✅ | ✗ | ✗ | ✗ | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ |
| blogs.delete | ✅ | ✗ | ✗ | ✗ | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ |
| blogs.export | ✅ | ✗ | ✗ | ✗ | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ |

#### Bookings (Puja/Yagya)

| Permission | temple_admin | donation_manager | festival_manager | accounts | content_manager | report_viewer | devotee_care | volunteer_coordinator | event_coordinator | read_only |
|---|---|---|---|---|---|---|---|---|---|---|
| bookings.view | ✅ | ✗ | ✗ | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| bookings.create | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| bookings.edit | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| bookings.delete | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| bookings.export | ✅ | ✗ | ✗ | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |

#### Panihati Yatra

| Permission | temple_admin | donation_manager | festival_manager | accounts | content_manager | report_viewer | devotee_care | volunteer_coordinator | event_coordinator | read_only |
|---|---|---|---|---|---|---|---|---|---|---|
| panihati.view | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✅ | ✗ |
| panihati.create | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✅ | ✗ |
| panihati.edit | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✅ | ✗ |
| panihati.delete | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| panihati.export | ✅ | ✗ | ✗ | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |

#### Sudamaseva (Subscriptions)

| Permission | temple_admin | donation_manager | festival_manager | accounts | content_manager | report_viewer | devotee_care | volunteer_coordinator | event_coordinator | read_only |
|---|---|---|---|---|---|---|---|---|---|---|
| sudamaseva.view | ✅ | ✅ | ✗ | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| sudamaseva.create | ✅ | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| sudamaseva.edit | ✅ | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| sudamaseva.delete | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| sudamaseva.export | ✅ | ✅ | ✗ | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |

#### Reports

| Permission | temple_admin | donation_manager | festival_manager | accounts | content_manager | report_viewer | devotee_care | volunteer_coordinator | event_coordinator | read_only |
|---|---|---|---|---|---|---|---|---|---|---|
| reports.view | ✅ | ✅ | ✗ | ✅ | ✗ | ✅ | ✗ | ✗ | ✗ | ✅ |
| reports.export | ✅ | ✅ | ✗ | ✅ | ✗ | ✅ | ✗ | ✗ | ✗ | ✗ |

#### Devotees

| Permission | temple_admin | donation_manager | festival_manager | accounts | content_manager | report_viewer | devotee_care | volunteer_coordinator | event_coordinator | read_only |
|---|---|---|---|---|---|---|---|---|---|---|
| devotees.view | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✅ | ✅ | ✗ | ✗ |
| devotees.create | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✅ | ✅ | ✗ | ✗ |
| devotees.edit | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✅ | ✅ | ✗ | ✗ |
| devotees.delete | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✅ | ✗ | ✗ | ✗ |
| devotees.export | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✅ | ✗ | ✗ | ✗ |

#### Volunteers

| Permission | temple_admin | donation_manager | festival_manager | accounts | content_manager | report_viewer | devotee_care | volunteer_coordinator | event_coordinator | read_only |
|---|---|---|---|---|---|---|---|---|---|---|
| volunteers.view | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✅ | ✅ | ✗ | ✗ |
| volunteers.create | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✅ | ✗ | ✗ |
| volunteers.edit | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✅ | ✗ | ✗ |
| volunteers.delete | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✅ | ✗ | ✗ |
| volunteers.export | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✅ | ✗ | ✗ |

#### Events

| Permission | temple_admin | donation_manager | festival_manager | accounts | content_manager | report_viewer | devotee_care | volunteer_coordinator | event_coordinator | read_only |
|---|---|---|---|---|---|---|---|---|---|---|
| events.view | ✅ | ✗ | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✅ | ✗ |
| events.create | ✅ | ✗ | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✅ | ✗ |
| events.edit | ✅ | ✗ | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✅ | ✗ |
| events.delete | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✅ | ✗ |
| events.export | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |

#### Audit Logs

| Permission | temple_admin | donation_manager | festival_manager | accounts | content_manager | report_viewer | devotee_care | volunteer_coordinator | event_coordinator | read_only |
|---|---|---|---|---|---|---|---|---|---|---|
| audit_logs.view | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| audit_logs.export | ✅ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |

> **Note:** Audit Logs module is a placeholder. The actual audit log feature is **not implemented** (per user decision to avoid DB bloat). The permission exists for future use.

### 6.4 Old-to-New Role Mapping (Migration)

When migrating existing admin users, the following mapping is applied:

| Old Role String | New Role(s) |
|----------------|-------------|
| `editor` | `content_manager` |
| `pujari` | `temple_admin` (restricted to bookings) |
| `treasurer` | `accounts` |
| `travel_agent` | `event_coordinator` + `reports.view` |
| `sudamaseva` | `donation_manager` (restricted to sudamaseva) |
| `super_admin` | `super_admin` (no change) |
| Comma-separated (e.g., `super_admin,editor`) | All matching new roles assigned |

---

## 7. API & Service Layer Design

### 7.1 `RbacService` — Core Class

```php
namespace Isjm\Modules\RBAC;

class RbacService
{
    /**
     * Check if an admin has a specific permission.
     * Super Admin always returns true.
     */
    public function hasPermission(int $adminId, string $permissionSlug): bool;

    /**
     * Check if admin has ANY of the given permissions.
     */
    public function hasAnyPermission(int $adminId, array $permissionSlugs): bool;

    /**
     * Check if admin has ALL of the given permissions.
     */
    public function hasAllPermissions(int $adminId, array $permissionSlugs): bool;

    /**
     * Get all permission slugs for an admin (union of all roles).
     */
    public function getAdminPermissions(int $adminId): array;

    /**
     * Get all roles assigned to an admin.
     */
    public function getAdminRoles(int $adminId): array;

    /**
     * Assign roles to an admin (replaces all existing assignments).
     */
    public function assignRoles(int $adminId, array $roleIds, ?int $assignedBy): void;

    /**
     * Get all permissions for a role.
     */
    public function getRolePermissions(int $roleId): array;

    /**
     * Assign permissions to a role.
     */
    public function setRolePermissions(int $roleId, array $permissionIds): void;
}
```

### 7.2 Integration with `SessionGuard`

The existing `SessionGuard` class is extended with a permission check method:

```php
class SessionGuard
{
    // Existing methods...

    /**
     * Check if current admin has a specific permission.
     * Loads permissions into session on first call for performance.
     */
    public function hasPermission(string $permissionSlug): bool;

    /**
     * Block access if admin doesn't have the specified permission.
     */
    public function requirePermission(string $permissionSlug): void;
}
```

### 7.3 Global Helper Functions

```php
// Check permission (usable in any admin page)
function hasPermission(string $permissionSlug): bool;

// Block if no permission
function requirePermission(string $permissionSlug): void;

// Get all current admin's permissions
function getMyPermissions(): array;
```

### 7.4 Permission Loading Strategy

On each page load:
1. `auth-check.php` loads the admin's assigned roles
2. `RbacService` fetches the union of all permissions for those roles
3. Permissions are cached in `$_SESSION['admin_permissions']` as an array of slugs
4. `hasPermission()` checks this array in-memory (no DB query on every check)
5. Session cache is refreshed when roles change

---

## 8. UI/UX Design

### 8.1 New Navigation Item

The admin sidebar gets a new section under "Manage Admins":

```
<?php if (hasRole(['super_admin'])): ?>
  <li class="admin-nav-item <?php echo in_array($activePage, ['roles', 'role-edit', 'permissions']) ? 'active' : ''; ?>">
    <a href="admin/roles">
      <i class="fas fa-shield-alt"></i> Roles & Permissions
    </a>
  </li>
<?php endif; ?>
```

### 8.2 Roles Management Page (`admin/roles`)

**Purpose:** List all roles with their assigned user count, provide edit/delete actions.

**Layout:**
- Header: "Roles & Permissions" with "Add New Role" button
- Table columns: Role Name, Slug, Description, Users Count, Actions (Edit, Delete)
- Color-coded badges per role
- System roles (non-deletable) marked with a lock icon
- Users count links to filtered admin list

### 8.3 Role Edit Page (`admin/role-edit`)

**Purpose:** Create or edit a role and assign its permissions via the matrix.

**Layout (top section):**
- Role Name (text input)
- Slug (auto-generated from name, editable)
- Description (textarea)

**Layout (permission matrix):**
A table with:
- **Rows:** Permission modules (grouped)
- **Columns:** Actions (View, Create, Edit, Delete, Export)
- **Cells:** Checkboxes at each intersection
- **Select All / Deselect All** per row (module) and per column (action)
- Summary showing total permissions selected

**Visual Design:**
```
Role: Temple Administrator

                                View  Create  Edit  Delete  Export
  ────────────────────────────────────────────────────────────────
  Dashboard                     [✅]   [  ]   [  ]   [  ]    [  ]
  Donations                     [✅]   [✅]   [✅]   [✅]    [✅]
  Festivals                     [✅]   [✅]   [✅]   [✅]    [✅]
  Seva Catalog                  [✅]   [✅]   [✅]   [✅]    [✅]
  Blogs & Content              [✅]   [✅]   [✅]   [✅]    [✅]
  Bookings                      [✅]   [✅]   [✅]   [✅]    [✅]
  ...
  
  [Select All Modules]  [Save Role]
```

### 8.4 User Edit Page Updates (`admin/admin-edit`)

The existing admin edit page is updated to support multi-role assignment:

**Multi-role selection:**
- Currently: Checkboxes for hardcoded roles
- Updated: Dynamic checkboxes loaded from `rbac_roles` table
- Each role shows its description on hover
- "Effective Permissions" summary section shows union of all selected roles' permissions

**Effective Permissions Display:**
- After selecting roles, display a read-only summary of the combined permissions
- Grouped by module
- View/Create/Edit/Delete/Export badges

### 8.5 Admins Listing Page Updates (`admin/admins`)

**Current:** Shows role column as badges
**Updated:** Shows:
- Role badges (same color coding)
- Clicking a role badge filters the table by that role
- "Permissions" link shows a tooltip/popover with the admin's effective permissions
- "Last Login" column (if available from login tracking)

### 8.6 Permission Matrix Page (`admin/permissions`)

**Purpose:** Read-only reference view showing all permissions and their descriptions.

- Table of all permissions with module, action, label, description
- Search/filter by module
- Useful for role editors to understand what permissions exist

---

## 9. Migration Strategy

### 9.1 Migration Script 1: Create RBAC Tables

```php
// database/migrations/001_create_rbac_tables.php
// Creates: rbac_roles, rbac_permissions, rbac_role_permissions, rbac_user_roles
```

### 9.2 Migration Script 2: Seed Roles & Permissions

```php
// database/migrations/002_seed_roles_and_permissions.php
// Seeds: All 11 roles + 65 permissions + role-permission assignments
```

### 9.3 Migration Script 3: Migrate Existing Admins

```php
// database/migrations/003_migrate_existing_admins.php
// Reads each admin's current role column
// Maps old roles to new roles (see Section 6.4)
// Inserts into rbac_user_roles
// Reports summary of mapped users
```

### 9.4 Rollback Strategy

1. Keep `admins.role` column intact (do NOT drop it)
2. If rollback is needed: delete RBAC tables, existing auth continues working
3. All existing `requireRole()` calls continue to work via the old column as fallback

### 9.5 Existing Code Compatibility

After migration:
1. Old `requireRole(['super_admin'])` calls continue to work via backward-compatible wrapper in `SessionGuard`
2. NEW code should use `requirePermission('donations.view')` instead
3. A migration task identifies all 34+ `requireRole()` calls and adds equivalent `requirePermission()` checks
4. Eventually, old `requireRole()` calls can be phased out

---

## 10. Implementation Phasing

### Phase 1 — Core RBAC (Full Delivery)

**Duration Estimate:** ~2-3 days

**Deliverables:**

| # | Task | Files Affected |
|---|------|----------------|
| 1 | Create `modules/RBAC/` directory structure | New |
| 2 | Migration 001: Create RBAC tables | New |
| 3 | Migration 002: Seed roles, permissions, matrix | New |
| 4 | Implement `RbacService` with all CRUD methods | New |
| 5 | Implement `PermissionRegistry` | New |
| 6 | Extend `SessionGuard` with `hasPermission()` / `requirePermission()` | `SessionGuard.php` |
| 7 | Update `auth-check.php` to load permissions into session | `auth-check.php` |
| 8 | Create `admin/roles.php` — Role listing page | New |
| 9 | Create `admin/role-edit.php` — Role create/edit with permission matrix | New |
| 10 | Update `admin/admin-edit.php` — Multi-role select + effective permissions | `admin/admin-edit.php` |
| 11 | Update `admin/admins.php` — Updated role display | `admin/admins.php` |
| 12 | Update `admin/partials/header.php` — New nav item + permission-based menu visibility | `admin/partials/header.php` |
| 13 | Migration 003: Auto-migrate existing admins | New |
| 14 | Update `admin/login.php` — Load permissions on login | `admin/login.php` |
| 15 | Add `hasPermission()` / `requirePermission()` helper functions in `auth-check.php` | `auth-check.php` |

### Phase 2 — Permission Enforcement Across All Admin Pages

**Duration Estimate:** ~1-2 days

**Deliverables:**

| # | Task | Description |
|---|------|-------------|
| 1 | Audit all 34+ `requireRole()` calls in admin files | Replace/add `requirePermission()` equivalent |
| 2 | Update sidebar menu visibility for all nav items | Uses `hasPermission()` instead of `hasRole()` |
| 3 | Add permission checks on action buttons (Edit, Delete, Export, etc.) | Hide/disable buttons user can't use |
| 4 | Test all admin pages with different roles | Verify enforcement |

### Phase 3 — Polish & Hardening (Optional)

- Filter menu items that user doesn't have access to
- Add bulk operations permission checks
- Performance optimization (caching)
- Documentation

---

## 11. Design Decisions & Rationale

| Decision | Choice | Rationale |
|----------|--------|-----------|
| Permission storage | Dedicated `rbac_permissions` table | Maximum flexibility, data-driven permissions, can add/modify via UI without code changes |
| Role inheritance | Not implemented | Adds complexity without clear benefit; flat permissions are more predictable and easier to debug |
| Multi-role resolution | Union (OR) | Standard RBAC practice; if any role grants a permission, user has it |
| Super admin treatment | Implicit bypass | Simplifies configuration; super_admin always has access regardless of permission assignments |
| Permission granularity | 5 standard actions (View, Create, Edit, Delete, Export) per module | Consistent across all modules; covers all needs without over-engineering |
| Module location | `modules/RBAC/` | Follows existing modular architecture pattern |
| Audit logging | Not implemented | User decision — would increase DB data without clear current need |
| Migration strategy | Auto-map existing roles + keep old column | Zero-downtime migration; backward compatibility maintained |
| UI location for roles | Separate sidebar nav item ("Roles & Permissions") | Clean separation of concerns; roles page has its own focused interface |
| Permission checking | Both server (PHP) and client (UI) | Defense in depth; UI hiding prevents confusion, server blocking prevents bypass |
| Permission cache | Session-based (`$_SESSION['admin_permissions']`) | Fast in-memory checking; auto-refreshes on page load; no DB query per check |

### 11.1 Why Not Bitfields?

Bitfield permissions (storing permissions as bits in a BIGINT) were considered but rejected:
- Limited to 64 permissions maximum
- Not human-readable in the database
- Cannot add permissions without schema changes
- No easy way to see what permissions a role has via SQL queries

### 11.2 Why No Audit Logging?

The user explicitly decided against audit logging to avoid database bloat. The `audit_logs` permission module exists as a placeholder for future implementation, but no logging infrastructure is built in Phase 1.

### 11.3 Why `modules/RBAC/` Instead of Extending `Kernel`?

The existing `Kernel` module handles shared infrastructure. RBAC is a distinct feature with its own admin UI, database tables, and service layer. Following the modular pattern makes it self-contained and easier to maintain.

---

## 12. Open Questions

1. **Audit Log module:** A permission for `audit_logs` exists in the schema but the feature is not implemented. Should this permission be removed entirely from the seed data until the feature is needed?

2. **Refund action:** The `donations` module currently doesn't have a `refund` action in the standard 5. Should a 6th action `refund` be added specifically for the Donations module?

3. **Publish/Unpublish actions:** Blogs and festivals have publish/unpublish state. Should these be covered by the existing `edit` action, or is a separate `publish` action needed?

4. **Temple Admin role definition:** This role has "full minus user management" — should it include ALL permissions from all modules except User Management and Settings?

5. **Read Only User scope:** Which modules should a Read Only User have view access to? The seed matrix above is a starting point — should it be all modules, or a subset?

6. **Panihati & Sudamaseva integration:** These are existing modules. Should their admin pages get permission-based checks in Phase 2, or are they less critical?
