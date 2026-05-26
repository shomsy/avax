# Architecture Fitness Functions

## Architecture Rule

Engineering canon governance must be traceable from support principles to binding how-to rules, skills, checkers, templates, SDLC runners, and evidence.

## Why This Rule Exists

Without traceability, the canon becomes non-executable documentation and can create architecture theater.

## Failure Mode Prevented

Fake governance that claims source-informed quality without an enforceable local rule or review path.

## Fitness Function Type

Executable checker and SDLC runner.

## Command / Review Procedure

```bash
php tooling/governance/check-engineering-canon-traceability.php
php tooling/sdlc/validate-agent-task.php
```

## Scope

`.agents/knowledge/**`, `.agents/how-to/**`, `.agents/skills/**`, `.agents/templates/evidence/**`, `tooling/governance/check-*`, and `tooling/sdlc/**`.

## Baseline Mode

Baseline mode is accepted by individual checkers where relevant.

## Changed-Scope Mode

Changed mode is the default for agent work and is run by `tooling/sdlc/validate-changed.php`.

## Full Mode

Full mode is reserved for complete repository governance sweeps.

## Expected Pass Signal

Checker exits 0 and prints GREEN.

## Expected Fail Signal

Checker exits non-zero and prints RED with missing files, markers, or headings.

## Evidence Path

`.agents/management/evidence/generated/engineering-canon-convergence/`

## Owner

AI agent / human reviewer pair.

## Review Date

2026-06-26

## Hardening Pass Update

Date: 2026-05-26

Changed governance/tooling files in this pass remain covered by this architecture fitness evidence.

Additional guardrails added:

- `check-architecture-fitness-functions.php --mode=changed --strict` is required by SDLC governance validation.
- governance-sensitive changes without `architecture-fitness-functions.md` or explicit `architecture-fitness-exception.md` now return RED.
- unsupported `baseline` and `full` modes return non-zero instead of fake GREEN.
- template headings are aligned with `.agents/templates/evidence/architecture-fitness-functions.md`.

Expected pass command:

```bash
/usr/bin/php8.4 tooling/governance/check-architecture-fitness-functions.php --mode=changed --strict
```

Expected pass signal:

```text
GREEN: Architecture fitness function check passed.
```
