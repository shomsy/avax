# Governance Review — TODO-017 Filesystem Boundaries

## Applicable Governance Documents

| Document | Status |
|---|---|
| AGENTS.md | READ |
| .agents/how-to/how-to-system-security.md | READ — filesystem boundaries are security-sensitive |
| .agents/how-to/how-to-design-components.md | READ — canonical component shape |
| .agents/how-to/how-to-coding-standards.md | READ |
| .agents/how-to/how-to-clean-code.md | READ |
| .agents/how-to/how-to-use-ai-assisted-execution.md | READ |

## Findings Table

| Finding | Severity | File | Problem | Resolution |
|---|---|---|---|---|
| Raw filemtime | HIGH | FileSessionStore.php | `filemtime()` bypasses Filesystem boundary | Replaced with `$this->filesystem->modificationTime()` |
| Raw file ops in WriteCompiledFailurePolicies | HIGH | WriteCompiledFailurePolicies.php | `is_dir()`, `mkdir()`, `file_put_contents()` bypass Filesystem | Injected Filesystem, replaced all 3 ops |
| Raw file ops in ReadCompiledFailurePolicies | HIGH | ReadCompiledFailurePolicies.php | `file_exists()`, `file_get_contents()` bypass Filesystem | Injected Filesystem, replaced both ops |
| Raw is_file in route builder | MEDIUM | BuildDispatchConfiguredRoute.php | `is_file()` bypasses Filesystem | Added optional Filesystem constructor param, replaced with `$filesystem->isFile()` |
| Stream wrapper ops | ACCEPTED | Multiple | `php://input`, `php://temp` usage | ACCEPTED_EXCEPTION — not persistent filesystem I/O |
| PSR-7 upload idiom | ACCEPTED | Multiple | `move_uploaded_file()` usage | ACCEPTED_EXCEPTION — PSR-7 standard pattern, not raw filesystem exploration |
| Path parsing | ACCEPTED | Multiple | `dirname()`, `basename()` usage | ACCEPTED_EXCEPTION — path string manipulation, not filesystem I/O |

## Compliance Matrix

| Rule | Status |
|---|---|
| Folder says flow/capability | PASS — all changes within existing Capabilities/ |
| No forbidden folder names | PASS |
| Advanced OOP: class says responsibility | PASS — each class owns its capability |
| No cheap OOP wrappers | PASS — Filesystem is real component boundary |
| Constructor injection | PASS — Filesystem injected where applicable |
| Backward compatibility | PASS — BuildDispatchConfiguredRoute uses optional param with fallback |
| Security boundary respected | PASS — no new raw filesystem paths introduced |

## Decision

Governance review complete. No BLOCKER or HIGH findings remain. Accepted exceptions documented for non-persistent filesystem operations (stream wrappers, PSR-7 uploads, path parsing).
