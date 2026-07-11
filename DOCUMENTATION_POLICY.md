# DOCUMENTATION_POLICY.md — Documentation Maintenance Policy

> **Last updated:** 2026-07-11
> **Canonical owner:** Development Team
> **Purpose:** Define how documentation is maintained, updated, and kept in sync with code.

---

## 1. Core Principle: Documentation Is Part of the Definition of Done

A feature is not complete until its documentation is updated. This applies to:
- New features and enhancements
- API endpoint changes
- Database schema changes
- Security controls
- Workflow changes
- Module structure changes

---

## 2. Documentation Ownership

| Doc | Owner | Review Cadence |
|-----|-------|---------------|
| `README.md` | Development Team | Every major release |
| `ARCHITECTURE.md` | Lead Developer | Architecture changes |
| `SECURITY.md` | Development Team | Security policy changes |
| `CODING_STANDARDS.md` | Development Team | Convention changes |
| `WORKFLOWS.md` | Development Team | Workflow changes |
| `MODULE_INDEX.md` | Development Team | Module structure changes |
| `DOCUMENTATION_POLICY.md` | Development Team | Policy changes |
| `docs/API.md` | Development Team | API changes |
| `docs/DATABASE.md` | Development Team | Schema changes |
| `docs/ADMIN.md` | Development Team | Admin UI changes |
| `docs/DEVELOPER.md` | Development Team | Setup/tooling changes |
| `docs/DEPLOYMENT.md` | Development Team | Deployment process changes |
| `docs/TESTING.md` | Development Team | Testing framework changes |
| Module READMEs | Per-module owner | Module changes |
| Module DECISIONS.md | Per-module owner | Architecture decision changes |

---

## 3. When to Update Each Document

### Required updates (must update, or explain why not)

| Change Type | Docs That Must Be Updated |
|-------------|--------------------------|
| New API endpoint | `docs/API.md`, module `API.md` |
| Changed endpoint (request/response) | `docs/API.md`, module `API.md` |
| New DB table/column | `docs/DATABASE.md`, module `DATABASE.md` |
| Changed table relationship | `docs/DATABASE.md`, module `DATABASE.md` |
| New module | `MODULE_INDEX.md`, new module `README.md`, `ARCHITECTURE.md` |
| New permission/role | `docs/ADMIN.md`, `docs/AUTHORIZATION_MATRIX.md` |
| New security control | `SECURITY.md` |
| Changed security control | `SECURITY.md` |
| Changed workflow | `WORKFLOWS.md` |
| Changed coding convention | `CODING_STANDARDS.md` |
| Changed build/deploy process | `docs/DEPLOYMENT.md` |
| New test pattern | `docs/TESTING.md` |
| New admin page | `docs/ADMIN.md`, `MODULE_INDEX.md` |

### Strongly recommended updates

| Change Type | Docs to Consider |
|-------------|-----------------|
| Bug fix | `CHANGELOG.md` |
| Dependency update | `ROADMAP.md` (if planned items affected) |
| Architecture decision | Module `DECISIONS.md` |
| New business rule | `WORKFLOWS.md` |
| New file upload feature | `docs/FILE_UPLOADS.md` |

---

## 4. Documentation Quality Standards

### All Docs Should Be:

- **Accurate**: Reflect current code state, not aspirational state
- **Specific**: Include examples, code snippets, URLs
- **Structured**: Use consistent headings, tables, and formatting
- **Canonical**: Avoid duplicating content from other docs — link instead
- **Current-dated**: Include `Last updated:` line at the top
- **Owned**: Identify who maintains this document

### Module Docs Must Include:

| Document | Required? | Contents |
|----------|-----------|----------|
| `README.md` | ✅ Required | Purpose, owned tables, dependencies, entry points, directory structure |
| `DECISIONS.md` | ✅ Required | Architecture decisions and trade-offs |
| `DATABASE.md` | ⚠️ If owns tables | Schema, columns, relationships, indexes |
| `API.md` | ⚠️ If has endpoints | Request/response schemas, validation, security |
| `TASKS.md` | ⚠️ If active backlog | Known bugs, upcoming work, technical debt |
| `TESTING.md` | ⚠️ If has tests | Test patterns, coverage, gotchas |

---

## 5. AI-Friendly Documentation Rules

These rules help AI coding assistants understand the project correctly:

1. **Stable headings**: Use the same heading structure across similar docs (e.g., all module READMEs use `## Purpose`, `## Owned Database Tables`, `## Dependencies`)
2. **Canonical owners**: Every doc identifies what it owns and what other docs cover
3. **Current vs legacy**: Clearly mark deprecated/legacy features with **DEPRECATED** or **LEGACY** tags
4. **Examples**: Include code examples for non-obvious patterns
5. **Avoid vague language**: Use "must" for requirements, "should" for recommendations, "may" for options
6. **Link don't duplicate**: When a topic is covered in another doc, link to it rather than restating
7. **TODO vs implemented**: Clearly mark future/hypothetical features with `> **TODO:**` or `> **Future:**`

---

## 6. Staleness Detection

Check for stale documentation when:

- Tests fail unexpectedly (documentation might be outdated)
- An AI assistant makes incorrect assumptions (docs are misleading)
- Code review reveals implementation differs from documented behavior
- Onboarding a new developer reveals confusing or missing docs
- Quarterly review of all docs (recommended)

---

## 7. Documentation Template

When creating a new doc, use the structure defined in `docs/DOCUMENTATION_TEMPLATE.md`.

---

## 8. Historical Spec Documents

The following documents are **historical specifications** — they guided implementation but may not reflect current state:

| Document | Status | Current Canonical Source |
|----------|--------|------------------------|
| `modularization-spec.md` | 📜 Historical | `MODULE_INDEX.md`, `ARCHITECTURE.md` |
| `rbac-spec.md` | 📜 Historical | `modules/RBAC/README.md`, `docs/AUTHORIZATION_MATRIX.md` |
| `sudamaseva-spec.md` | 📜 Historical | `modules/Sudamaseva/README.md` |
| `sudamaseva-spec-review.md` | 📜 Historical | `modules/Sudamaseva/README.md` |

These are preserved for reference and audit trail. When implementing new features, refer to the current canonical sources listed above.
