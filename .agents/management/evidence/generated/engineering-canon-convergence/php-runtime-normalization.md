# PHP Runtime Normalization — Pass 2

Timestamp: 2026-05-26

## Root Cause

`/home/shomsy/.local/bin/php` is a Bash script that wraps `docker run php:8.5-cli`. When invoked directly, the Docker container cannot access `/usr/bin/git` on the host because `/usr` is not mounted. When invoked through Composer scripts, the Docker wrapper inherits Composer's environment where git is available.

## PHP Runtime Inventory

| Runtime | Path | Version | Git (direct) | Git (via Composer) |
|---|---|---|---|---|
| Docker wrapper | `/home/shomsy/.local/bin/php` | 8.5.5 | NO | YES |
| Native Ubuntu | `/usr/bin/php8.4` | 8.4.11 | YES | N/A |
| Composer internal | `/usr/local/bin/php` | 8.5.5 | YES (inherited env) | YES |

## Solution: run-sdlc Wrapper

Created `tooling/sdlc/run-sdlc` — a shell wrapper that:
1. Accepts `PHP_BIN` environment variable override
2. Probes candidate PHP binaries (php8.4, php8.5, php) for git access
3. Rejects Docker wrapper binaries when running directly
4. Fails with explicit error if no valid PHP is found
5. Resolves script paths relative to project root

Updated `composer.json` scripts to use `tooling/sdlc/run-sdlc` instead of plain `php`:

```diff
- "sdlc:preflight": "php tooling/sdlc/preflight.php"
+ "sdlc:preflight": "tooling/sdlc/run-sdlc tooling/sdlc/preflight.php"
```

## Validation After Normalization

| Command | Exit | Result | Notes |
|---|---:|---|---|
| `composer run sdlc:preflight` | 0 | PASS | PHP_BINARY=/usr/local/bin/php, git=/usr/bin/git |
| `composer run sdlc:changed` | 0 | PASS | GREEN_CHANGED_SCOPE_READY |
| `composer run sdlc:governance` | 0 | PASS | GREEN_CHANGED_SCOPE_READY |
| `composer run sdlc:agent-task` | 0 | PASS | GREEN_SDLC_AUTOMATION_READY |
| `tooling/sdlc/run-sdlc tooling/sdlc/preflight.php` | 0 | PASS | PHP_BINARY=/usr/bin/php8.4, git=/usr/bin/git |
| `/usr/bin/php8.4 tooling/sdlc/validate-agent-task.php` | 0 | PASS | GREEN_SDLC_AUTOMATION_READY |
| `php tooling/sdlc/preflight.php` | 1 | EXPECTED | RED: git=missing, explicit diagnostic |
| `git diff --check` | 0 | PASS | — |
| `composer validate --no-check-publish` | 0 | PASS | ./composer.json is valid |

## Files Changed

- `tooling/sdlc/run-sdlc` (new, executable)
- `composer.json` (scripts updated to use run-sdlc)
- `.agents/how-to/verification/how-to-sdlc-runners.md` (PHP runtime problem + wrapper docs)
- `.agents/how-to/verification/how-to-sdlc-automation.md` (wrapper reference)
- `.agents/skills/sdlc-automation/SKILL.md` (wrapper reference)

## Status

YELLOW_SDLC_RUNTIME_NORMALIZED_WITH_LOCAL_ENV_RISK

- Canonical composer command: GREEN
- Fallback php8.4 command: GREEN
- Run-sdlc wrapper: GREEN
- Direct plain php: EXPECTED FAILURE with explicit diagnostics
- Local environment dependency: YELLOW (requires native PHP 8.4+ or Composer)
