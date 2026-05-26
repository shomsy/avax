# Runtime Helper Report

Task: Engineering Canon Convergence runtime helper hardening.

## Created Helpers

| File | Purpose |
|---|---|
| `tooling/sdlc/SdlcRuntime.php` | Central runtime diagnostics, PHP binary/version reporting, git discovery, command execution. |
| `tooling/sdlc/GitChangedFiles.php` | Central changed-file collection for unstaged, staged, and untracked files. |

## Runtime Rules

- no hardcoded git path in new checkers;
- git is discovered using `command -v git`;
- missing git fails explicitly;
- runtime diagnostics include `PHP_BINARY`, `PHP_VERSION`, and `git`;
- changed-file collection cannot silently return empty when git is missing.

## Consumers Updated

- `tooling/governance/check-scenario-input.php`
- `tooling/governance/check-coupling-decisions.php`
- `tooling/governance/check-architecture-fitness-functions.php`
- `tooling/governance/check-antipatterns.php`
- `tooling/sdlc/preflight.php`
- `tooling/sdlc/validate-changed.php`
- `tooling/sdlc/validate-governance.php`
- `tooling/sdlc/validate-agent-task.php`
