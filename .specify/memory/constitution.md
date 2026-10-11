<!--
Sync Impact Report:
- Version change: Uninitialized Template -> 1.0.0
- List of modified principles:
  * [PRINCIPLE_1_NAME] -> I. Modular Architecture & Backward-Compatible Wrappers
  * [PRINCIPLE_2_NAME] -> II. Security & Data Integrity (NON-NEGOTIABLE)
  * [PRINCIPLE_3_NAME] -> III. Lightweight Vanilla PHP & Minimal Dependencies
  * [PRINCIPLE_4_NAME] -> IV. Spec-Driven & Documentation-First Development
  * [PRINCIPLE_5_NAME] -> V. Rigorous Testing & Quality Gates
- Added sections:
  * Technical Constraints & Environment Standards
  * Quality Gates & Deployment Process
- Removed sections: None
- Follow-up TODOs: None
-->

# ISKCON Sri Jagannath Mandir (ISJM) Constitution

## Core Principles

### I. Modular Architecture & Backward-Compatible Wrappers
All application logic, views, services, and assets MUST reside within their designated module directory under `modules/<ModuleName>/`. Root-level and legacy administrative/API entry points MUST remain as thin, single-line backward-compatibility wrappers utilizing `require_once __DIR__ . '/.../' . basename(__FILE__)`. Direct modifications to wrapper files that decouple them from modular targets are prohibited.

### II. Security & Data Integrity (NON-NEGOTIABLE)
Security is paramount across financial transactions and devotee data.
- **Prepared Statements**: All database operations MUST use PDO prepared statements via `getDB()`. Direct concatenation or interpolation of user input into SQL queries is strictly prohibited.
- **Access Control & CSRF**: All admin endpoints MUST verify granular RBAC permissions via `requirePermission('module.action')`. All form submissions and state-altering GET actions MUST validate anti-CSRF tokens.
- **Payment Verification**: Razorpay transactions MUST be verified server-side with HMAC signature checks; amounts MUST be validated against the server catalog.
- **Transaction Table Immutability**: Financial and audit tables (`donation_transactions`, `panihati_yatra_registrations`, `sudamaseva_payments`) MUST NEVER be truncated, dropped, or modified destructively.

### III. Lightweight Vanilla PHP & Minimal Dependencies
The application deliberately avoids heavy full-stack PHP frameworks in favor of clean, performant vanilla PHP with PDO and Apache `mod_rewrite`.
- Follow PSR-4 autoloading conventions under the `Isjm\` namespace where OOP classes and services are utilized.
- External dependencies must be justified, audited, and added via Composer only when necessary.
- Local execution targets Laragon on Windows; production runs on Apache/Linux.

### IV. Spec-Driven & Documentation-First Development
Every new feature, architectural migration, or significant refactor MUST begin with a specification and technical plan before implementation.
- All code changes impacting APIs, schemas, or module boundaries MUST update corresponding documentation (`README.md`, `API.md`, `DATABASE.md`, `DECISIONS.md`) adhering to `DOCUMENTATION_POLICY.md`.
- Historical design documents and specs must be preserved for auditability.

### V. Rigorous Testing & Quality Gates
Changes must prove correctness before integration.
- PHPUnit unit and regression tests (`vendor/bin/phpunit`) MUST pass. Unit tests should utilize SQLite in-memory databases for isolated execution without depending on external MySQL state.
- Playwright E2E suites (`npx playwright test`) must validate booking and payment flows.
- PHP syntax checks (`php -l <file>`) must be executed on all touched files before committing.

## Technical Constraints & Environment Standards

- **PHP Version**: PHP 8.x compatibility.
- **Database**: MySQL/MariaDB for local (Laragon `isjm_donations`) and production (`iskcop35_iskconseshadripuram`). Migrations MUST be idempotent and tested.
- **Client Assets**: Plain CSS and vanilla JavaScript; build scripts run via `npm run build` where bundlers apply.
- **Secrets Management**: Database credentials and payment secret keys MUST be loaded from environment variables, never hardcoded.

## Development Workflow & Quality Gates

1. **Specify & Plan**: Features, enhancements, and complex fixes must go through the Spec Kit workflow (`/speckit-specify` -> `/speckit-plan` -> `/speckit-tasks`).
2. **Local Syntax & Unit Validation**: Run `php -l` on modified files and `vendor/bin/phpunit` before submitting changes.
3. **Review & Verification**: Ensure no CSRF vulnerabilities, no unescaped output (XSS defense via `htmlspecialchars()`), and proper permission checks.
4. **Documentation Sync**: Update corresponding module docs and root references alongside functional changes.

## Governance

This Constitution represents the supreme architectural and development standard for the ISJM codebase. Any changes to this document must be versioned and justified through an amendment proposal.

- **MAJOR**: Structural shifts, architectural redesigns, or changes that alter foundational security invariants.
- **MINOR**: Addition of new modules, principles, or expanded compliance mandates.
- **PATCH**: Refinements, typo fixes, or documentation clarifications.

**Version**: 1.0.0 | **Ratified**: 2026-10-11 | **Last Amended**: 2026-10-11
