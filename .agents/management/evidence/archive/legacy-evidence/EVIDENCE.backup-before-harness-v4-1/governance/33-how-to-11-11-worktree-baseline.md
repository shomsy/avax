# How-To 11/11 Worktree Baseline

## Identity

| Field  | Value                                    |
|--------|------------------------------------------|
| Branch | main                                     |
| Commit | b103d17e3eb10cd7519346256723efcbc0a45c76 |
| Remote | origin/main — tracking, up to date       |

## Git Status

```
$ git status --short
 M components/Application/Container/System/Configuration/Builders/ContainerBuilder.php
 M components/Application/Validation/System/Configuration/Builders/ValidationBuilder.php
 M components/DataStack/Persistence/System/Configuration/Builders/PersistenceBuilder.php
 M components/HTTP/Request/System/Configuration/Builders/RequestBuilder.php
 M components/HTTP/Response/System/Capabilities/CreateHttpResponse/CreateHttpResponse.php
```

**5 dirty files found.** All are modified (unstaged). No staged changes. No untracked files.

## Dirty File Classification

| File                                                                                     | Change Type                                 | Classification                                | Belongs to This Pass? |
|------------------------------------------------------------------------------------------|---------------------------------------------|-----------------------------------------------|-----------------------|
| `components/Application/Container/System/Configuration/Builders/ContainerBuilder.php`    | Added `@var array` PHPDoc type annotation   | **PRE-EXISTING** — unrelated PHPDoc hardening | No                    |
| `components/Application/Validation/System/Configuration/Builders/ValidationBuilder.php`  | Added PHPDoc type annotations (2)           | **PRE-EXISTING** — unrelated PHPDoc hardening | No                    |
| `components/DataStack/Persistence/System/Configuration/Builders/PersistenceBuilder.php`  | Added PHPDoc type annotations (2)           | **PRE-EXISTING** — unrelated PHPDoc hardening | No                    |
| `components/HTTP/Request/System/Configuration/Builders/RequestBuilder.php`               | Added PHPDoc type annotation                | **PRE-EXISTING** — unrelated PHPDoc hardening | No                    |
| `components/HTTP/Response/System/Capabilities/CreateHttpResponse/CreateHttpResponse.php` | Added PHPDoc `@param` type annotations (12) | **PRE-EXISTING** — unrelated PHPDoc hardening | No                    |

**All 5 dirty files are pre-existing PHPDoc type-annotation additions.** They are not related to this governance
hardening pass. They should be committed separately or reverted.

**Decision:** These files are excluded from this pass. This pass touches ONLY governance/markdown/evidence files in
`.agents/how-to/`, `EVIDENCE/governance/`, and `docs/governance/`.

## Files This Pass Will Touch

| Area                   | Files                                                                                                                                                                                                                                                                                                                                                                                                                                                                 | Count |
|------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|-------|
| `.agents/how-to/`      | `how-to-architecture.md`, `how-to-architecture-extension-with-ddd.md`, `how-to-clean-code.md`, `how-to-code-review.md`, `how-to-coding-standards.md`, `how-to-dependency-injection.md`, `how-to-design-components.md`, `how-to-document.md`, `how-to-dogfooding.md`, `how-to-events-listeners-event-sourcing-cqrs-realtime.md`, `how-to-git.md`, `how-to-production-readiness.md`, `how-to-system-performance.md`, `how-to-system-security.md`, `how-to-unit-test.md` | 15    |
| `EVIDENCE/governance/` | New evidence files: 32 through 55+                                                                                                                                                                                                                                                                                                                                                                                                                                    | ~12   |
| `docs/governance/`     | `canonical-terms.md` (new)                                                                                                                                                                                                                                                                                                                                                                                                                                            | 1     |
| `EVIDENCE/`            | Exception register at canonical path                                                                                                                                                                                                                                                                                                                                                                                                                                  | 1     |

## Files This Pass Will NOT Touch

| Category            | Files                                                         |
|---------------------|---------------------------------------------------------------|
| Production PHP code | All `framework/`, `components/`, `config/`, `routes/`, `bin/` |
| Tests               | All `tests/`                                                  |
| Examples            | All `examples/`                                               |
| Tooling             | All `tooling/`                                                |
| Vendor              | `.gitignore`, `vendor/`                                       |
| Cache               | `.phpunit.cache/`, `.qoder/worktrees/`                        |
| Current dirty files | 5 files listed above                                          |

## Evidence/Generated Files

| File                                                                | Status                  |
|---------------------------------------------------------------------|-------------------------|
| `EVIDENCE/governance/32-how-to-11-11-preflight.md`                  | ✅ Created this session  |
| `EVIDENCE/governance/33-how-to-11-11-worktree-baseline.md`          | ✅ Created this session  |
| `EVIDENCE/governance/34-*.md` through `EVIDENCE/governance/55-*.md` | To be created this pass |

## Unrelated Files

| File                | Why unrelated                     |
|---------------------|-----------------------------------|
| `.qoder/worktrees/` | Agent worktrees, not in main tree |
| `.phpunit.cache/`   | Test cache                        |
| `var/`              | Runtime artifacts                 |
| `storage/`          | Runtime storage                   |
| `benchmarks/`       | Benchmark results                 |

## Untracked Files

None. All untracked files are either in `.gitignore` or absent.

## Cache Files

None in the main tree that would be committed. `.phpunit.cache/` is gitignored.

## .qoder/worktrees/

Present but gitignored. Not part of the main tree. No action needed.

## Next Step

Proceed to P0 fixes: ServiceProvider contradiction → Security Must Scream → Security Review Trigger → Security Commit
Block → Critical Quality Signal.
