---
name: sdlc-automation
description: Runs AvaX AI-native SDLC preflight, changed-scope validation, governance validation, correction loop, and exact evidence reporting.
---

# SDLC Automation Skill

## Activate Conditions

Use for validation, runner work, governance automation, evidence closure, review pack generation, and readiness claims.

## Required Preflight

- verify project root
- verify branch
- verify dirty status
- load required governance files
- classify missing optional tooling

## Required Changed Validation

Run:

```bash
composer run sdlc:changed
```

## Governance Change Validation

Run:

```bash
composer run sdlc:governance
```

## Canonical SDLC Command

Use:

```bash
composer run sdlc:agent-task
```

Fallback:

```bash
/usr/bin/php8.4 tooling/sdlc/validate-agent-task.php
```

Runner wrapper:

```bash
tooling/sdlc/run-sdlc tooling/sdlc/<script>.php [args...]
```

Plain `php` is not canonical. Direct invocation enters a Docker wrapper without git access. Composer's environment inherits git access, so `composer run` works. Runners detect missing git and fail with explicit diagnostics.

## Evidence Path

`.agents/management/evidence/generated/<task-name>/`

## Correction Loop

Validate, audit, fix caused failures, rerun, update evidence.

## Final Output

Include exact runner results, exit codes, review pack status, and final classification.

## Stop Conditions

Stop on required runner failure, unclassified dirty state, wrong branch, missing mandatory file, or fake evidence risk.
