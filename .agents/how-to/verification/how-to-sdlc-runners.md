# How To Use SDLC Runners

## PHP Runtime Problem

In this environment, the default `php` command resolves to `/home/shomsy/.local/bin/php`, a Docker wrapper (`php:8.5-cli` container) that **cannot access the host git binary** when invoked directly. This causes SDLC runners to fail with vague missing-git errors.

Two valid PHP runtimes exist:

| Runtime | Path | PHP | Git access | Use case |
|---|---|---|---|---|
| Docker wrapper | `/home/shomsy/.local/bin/php` | 8.5.5 | **NO** (direct) / YES (via Composer) | Composer scripts inherit Composer's environment |
| Native Ubuntu | `/usr/bin/php8.4` | 8.4.11 | **YES** | Direct invocation, fallback |

## Canonical SDLC Command

**Preferred command** (works via Composer's environment):

```bash
composer run sdlc:agent-task
```

**Fallback command** (native PHP with git):

```bash
/usr/bin/php8.4 tooling/sdlc/validate-agent-task.php
```

**Direct runner wrapper** (explicit PHP resolution):

```bash
tooling/sdlc/run-sdlc tooling/sdlc/<script>.php [args...]
```

The `run-sdlc` wrapper:
1. Locates a native PHP binary that can access git
2. Rejects Docker wrappers when running directly
3. Falls back to `PHP_BIN` environment variable override
4. Fails with explicit error if no valid PHP is found

**Plain `php` is NOT canonical.** Runners detect this and fail with:

```
SDLC Runtime Diagnostics
PHP_BINARY=/usr/local/bin/php
PHP_VERSION=8.5.5
git=missing
RED: This PHP runtime cannot access git. Use the canonical SDLC command...
```

## Detecting Invalid PHP Runtime

```bash
php -r 'echo "PHP_BINARY=", PHP_BINARY, PHP_EOL; echo "git=", shell_exec("command -v git 2>/dev/null") ?: "missing\n";'
```

Expected output for invalid runtime:

```
PHP_BINARY=/usr/local/bin/php
git=missing
```

Expected output for valid runtime:

```
PHP_BINARY=/usr/bin/php8.4
git=/usr/bin/git
```

## Final GREEN Requires

- canonical command passes (composer run sdlc:agent-task)
- runner diagnostics show PHP runtime that can access `git`
- `git diff --check` passes
- Composer JSON validates

## Preflight Runner

```bash
composer run sdlc:preflight
```

Checks project root, PHP version, branch, commit, dirty status, required files, root how-to shadows, executable scripts, and evidence templates.

Use `--strict` to fail on dirty worktree.

## Changed-Scope Runner

```bash
composer run sdlc:changed
```

Runs changed-scope validation for the active task.

## Governance Runner

```bash
composer run sdlc:governance
```

Runs governance validation and changed-scope governance checkers.

## Agent Task Runner

```bash
composer run sdlc:agent-task
```

Runs preflight, changed-scope validation, and governance validation.

## Composer Aliases

```bash
composer sdlc:preflight
composer sdlc:changed
composer sdlc:governance
composer sdlc:agent-task
```

## Status Rules

Required runner failure is RED_BLOCKED.
Optional governance failure is YELLOW unless it directly invalidates evidence.

## Final Report Requirement

Final reports must include exact command output summaries and exit codes.

## Runner Failure Classification

| Failure | Classification | Required Action |
|---|---|---|
| canonical command fails | RED_BLOCKED | fix runner or changed-scope finding |
| direct plain `php` lacks git | YELLOW environment risk if canonical command passes | use canonical command and record diagnosis |
| missing required checker | RED_BLOCKED | create or restore checker |
| unsupported mode returns non-zero | PASS for mode honesty test | record exact output |
| unsupported mode returns zero | RED_BLOCKED | fix checker |

## Evidence Table Requirement

Use this exact table shape in reports:

| Command | Exit Code | Result | Important Output |
|---|---:|---|---|

## Actual-Changes Pack Rule

Use actual-changes packs when reviewers need the real working-tree delta, including untracked files. The pack must exclude old `_pack` folders, top-level actual-changes archive files, secrets, cache, coverage, vendor, node_modules, and temporary directories.

## No Vague Report Language

Reports must not replace command output with phrases like "appears clean" or "the output indicates". Record the command, exit code, and a concrete output summary.
