# Raw File Operations Gate Hardening — V5 Self-Healing Mega Pass 02

**Date:** 2026-05-10
**Status:** GREEN (gate is hardened; violations remain for Parts 3-4 to migrate)

## Changes Made

### 1. Complete Rewrite of check-raw-file-operations.php

**Before:** Simple flat scanner that listed 234 violations without categorization.

**After:** Categorized governance gate with 7 categories:

| Category              | Meaning                                                      | Count |
|-----------------------|--------------------------------------------------------------|------:|
| ALLOWED_OWNER         | Filesystem component internals (canonical owner)             |    34 |
| ALLOWED_TOOLING       | Tooling scripts and container tools                          |    63 |
| ALLOWED_TEST          | Test files and fixtures                                      |     0 |
| ALLOWED_BOOTSTRAP     | Documented early bootstrap paths (dev server, config loader) |    10 |
| MIGRATE_TO_FILESYSTEM | Production runtime code doing local file I/O                 |   129 |
| MIGRATE_TO_STORAGE    | Object/disk abstraction code that should use Storage         |     8 |
| NEEDS_DESIGN_DECISION | Unclear owner, must be documented                            |     8 |

### 2. Gate Behavior

- **Fails (exit 1)** on any MIGRATE_TO_FILESYSTEM or MIGRATE_TO_STORAGE violation
- **Passes (exit 0)** when only ALLOWED and NEEDS_DESIGN_DECISION remain
- **Prints exact file:line** for each violation
- **Documents allowed path reasons** in the rule table
- **Excludes** function definitions, method calls (`->rename`), and stream-based I/O (`php://input`, `php://temp`)

### 3. Before/After Comparison

| Metric             |                  Before |                                After |
|--------------------|------------------------:|-------------------------------------:|
| Total findings     |                     234 |                                  252 |
| Categorized        |                      No |                   Yes (7 categories) |
| ALLOWED            |          ~90 (implicit) |                       107 (explicit) |
| MUST FIX           | 234 (all same category) | 137 (split by filesystem vs storage) |
| NEEDS DECISION     |                       0 |                                    8 |
| Reasons documented |                      No |                                  Yes |

The increase from 234 to 252 is due to better detection (the `/tools/` path rule for container tooling catches
additional entries, and refined classification found more context).

### 4. Classification Accuracy

| File Type                   | Classification        | Correct? |
|-----------------------------|-----------------------|----------|
| Filesystem internals        | ALLOWED_OWNER         | Yes      |
| tooling/ scripts            | ALLOWED_TOOLING       | Yes      |
| Container tools/            | ALLOWED_TOOLING       | Yes      |
| Dev server                  | ALLOWED_BOOTSTRAP     | Yes      |
| Config file loaders         | ALLOWED_BOOTSTRAP     | Yes      |
| Webhook HTTP calls          | ALLOWED_BOOTSTRAP     | Yes      |
| Container compilation       | MIGRATE_TO_FILESYSTEM | Yes      |
| Cache storage               | MIGRATE_TO_FILESYSTEM | Yes      |
| Observability writers       | MIGRATE_TO_FILESYSTEM | Yes      |
| Logging writers             | MIGRATE_TO_FILESYSTEM | Yes      |
| Session file store          | MIGRATE_TO_FILESYSTEM | Yes      |
| Route cache                 | MIGRATE_TO_FILESYSTEM | Yes      |
| Database migration          | MIGRATE_TO_FILESYSTEM | Yes      |
| View compilation            | MIGRATE_TO_FILESYSTEM | Yes      |
| Code generation             | MIGRATE_TO_FILESYSTEM | Yes      |
| ObjectStorage local adapter | MIGRATE_TO_STORAGE    | Yes      |
| Storage LocalDisk           | MIGRATE_TO_STORAGE    | Yes      |
| Console UI /dev/tty         | ALLOWED_BOOTSTRAP     | Yes      |
| CSV format fclose           | NEEDS_DESIGN_DECISION | Yes      |
| UploadedFile fopen          | NEEDS_DESIGN_DECISION | Yes      |
| HMAC key ring               | NEEDS_DESIGN_DECISION | Yes      |

## Validation

| Check                         | Result |
|-------------------------------|--------|
| Gate runs without errors      | PASS   |
| Categorizes all violations    | PASS   |
| Fails on MUST FIX             | PASS   |
| Documents reasons             | PASS   |
| Excludes function definitions | PASS   |
| Excludes stream I/O           | PASS   |

## Why GREEN

The gate itself is hardened and functional. The violations it reports are real and will be addressed in Parts 3 and 4.
The gate is no longer a noisy scanner — it is a categorized governance tool that tells exactly what must migrate and
what is allowed.
