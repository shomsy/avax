# Agents Backup Folder Audit

Generated at: 2026-05-26 14:23:57 CEST

## Scope

Audited backup folders:

- `.agents.backup-before-harness-v4-1`
- `.agents.backup-before-harness-v6-20260517-221607`

Current target:

- `.agents/`

## Commands

```bash
find .agents.backup-before-harness-v4-1 -type f | sort
find .agents.backup-before-harness-v6-20260517-221607 -type f | sort
find .agents -type f | sort
find .agents.backup-before-harness-v4-1 -type f | sed 's#^\.agents\.backup-before-harness-v4-1/##' | sort
find .agents.backup-before-harness-v6-20260517-221607 -type f | sed 's#^\.agents\.backup-before-harness-v6-20260517-221607/##' | sort
find .agents -type f | sed 's#^\.agents/##' | sort
comm -23 /tmp/avax-agents-backup-v4-files.txt /tmp/avax-agents-current-files.txt | sort
comm -23 /tmp/avax-agents-backup-v6-files.txt /tmp/avax-agents-current-files.txt | sort
diff -qr .agents.backup-before-harness-v4-1 .agents.backup-before-harness-v6-20260517-221607 | head -80
```

## Inventory Counts

```text
.agents.backup-before-harness-v4-1: 273 files
.agents.backup-before-harness-v6-20260517-221607: 273 files
.agents current: 5023 files
```

## Backup-Only Relative Paths

Both backup folders had the same backup-only relative paths compared to current `.agents/`:

```text
.rules/governance/architecture/how-to-architecture.md
how-to/how-to-architecture-extension-with-ddd.md
how-to/how-to-architecture.md
how-to/how-to-clean-code.md
how-to/how-to-code-review.md
how-to/how-to-code-style.md
how-to/how-to-coding-standards.md
how-to/how-to-dependency-injection.md
how-to/how-to-design-components.md
how-to/how-to-document.md
how-to/how-to-dogfooding.md
how-to/how-to-events-listeners-event-sourcing-cqrs-realtime.md
how-to/how-to-git.md
how-to/how-to-modern-php-attributes-di.md
how-to/how-to-production-readiness.md
how-to/how-to-runtime-composition.md
how-to/how-to-system-performance.md
how-to/how-to-system-security.md
how-to/how-to-unit-test.md
how-to/how-to-use-advanced-architecture-patterns.md
how-to/how-to.txt
```

## Resolution

No canonical content needed to be moved from the backup folders.

Reason:

- The old root `how-to/*.md` files are already present in current canonical categorized locations under `.agents/how-to/architecture/`, `.agents/how-to/components/`, `.agents/how-to/documentation/`, `.agents/how-to/implementation/`, `.agents/how-to/project/`, and `.agents/how-to/verification/`.
- The old root `how-to/how-to.txt` file is intentionally retired.
- `.rules/governance/architecture/how-to-architecture.md` is superseded by `.agents/how-to/architecture/how-to-architecture.md` and the current mounted `.agents/.rules/` architecture standards.
- The two backup folders differ only by whitespace formatting in `.rules/management/evidence/CHANGELOG.md`; the active `.agents/.rules/management/evidence/CHANGELOG.md` already contains the normalized content.

## Decision

Delete both `.agents.backup*` folders after this audit.

No recovered file was promoted into canonical `.agents` because no missing canonical content was found.

## Deletion Verification

Command:

```bash
find . -maxdepth 1 -type d -name '.agents.backup*' | sort
```

Output:

```text

```

Result: no `.agents.backup*` directories remain in the worktree.
