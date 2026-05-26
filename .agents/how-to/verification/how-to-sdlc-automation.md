# How To Use SDLC Automation

## AI-Native Lifecycle

```text
intake -> context load -> task classification -> branch/worktree verification -> scenario/domain/coupling decision where applicable -> implementation/governance change -> changed-scope validation -> anti-pattern review -> evidence -> correction loop -> final validation -> review pack -> human review -> commit/push only when allowed
```

## Task Classification

| Task | Required Extra Step |
|---|---|
| production behavior | scenario input |
| domain modeling | domain discovery |
| boundary/coupling | coupling decision |
| governance/architecture rule | architecture fitness function |
| refactor | refactoring safety |
| pattern use | pattern decision |
| production code | anti-pattern review |

## Context Loading

Load AGENTS.md, governance index, reading order, all skills, applicable how-to docs, source principles, latest evidence, and touched files.

## Evidence Rule

Evidence goes under `.agents/management/evidence/generated/<task-name>/`.

## Changed-Scope Validation

```bash
composer run sdlc:changed
```

## Canonical SDLC Command

Preferred full agent task validation:

```bash
composer run sdlc:agent-task
```

Fallback command (native PHP with git):

```bash
/usr/bin/php8.4 tooling/sdlc/validate-agent-task.php
```

Runner wrapper for explicit PHP resolution:

```bash
tooling/sdlc/run-sdlc tooling/sdlc/<script>.php [args...]
```

Do not assume plain `php` is valid. In this repository's local setup, plain `php` invokes a Docker wrapper that cannot access `git` when run directly (but works when inherited through Composer's environment). Runners fail with explicit runtime diagnostics when `git` is missing.

## Mandatory Validation Minimum

- `git diff --check`
- engineering canon traceability
- scenario input changed-scope checker
- coupling decision changed-scope checker
- architecture fitness changed-scope checker
- anti-pattern changed-scope checker

## Review Pack Rule

Generate review packs only after required validation has been run and evidence says whether the result is GREEN, YELLOW, or RED.

## Status Rules

- GREEN_CHANGED_SCOPE_READY: changed-scope runners pass.
- YELLOW_BASELINED_READY: legacy findings are tracked and changed scope is clean.
- YELLOW_REQUIRES_REVIEW: warnings or optional gates need human review.
- RED_BLOCKED: required runner failed.

## Final Report Format

Include command, exit code, PASS/FAIL, important output, evidence path, remaining risks, and next allowed action.

## Stop Conditions

Stop on wrong branch, dirty unclassified worktree, missing mandatory runner, failed required check, or evidence mismatch.

## Human Reviewer Role

Humans decide accepted risk. Agents supply exact evidence.

## Runtime Detection

Every SDLC runner prints:

```text
PHP_BINARY=<path>
PHP_VERSION=<version>
git=<path|missing>
```

If `git=missing`, the runner must fail with an explicit runtime error and point to `.agents/how-to/verification/how-to-sdlc-runners.md`.

## Composer vs PHP Binary Guidance

Use Composer scripts for normal agent validation because they are the canonical command surface. Use `/usr/bin/php8.4` only when direct native PHP invocation is required for evidence or when Composer is unavailable.

Plain `php` is not a reliable command in this local setup and must not be used as proof of SDLC failure unless the report classifies it as environment runtime failure.

## Exact Status Semantics

| Status | Meaning |
|---|---|
| GREEN_CHANGED_SCOPE_READY | Required changed-scope gates passed. |
| YELLOW_BASELINED_READY | Changed scope is clean, legacy debt is tracked. |
| YELLOW_REQUIRES_REVIEW | Manual review or optional gate remains. |
| RED_BLOCKED | Required runner, evidence, branch, or validation failed. |

## Runner Failure Classification

- missing git in PHP runtime: RED for that invocation, YELLOW environment risk if canonical command passes;
- failed required checker: RED_BLOCKED;
- optional checker missing: YELLOW with explicit reason;
- unsupported checker mode: RED for that command, not a silent pass.

## Evidence Table Requirement

Every runner report must include:

```text
command
exit_code
PASS_or_FAIL
important_output
classification
```

## No Vague Report Language

Do not write "appears", "likely", "probably", "the output indicates", or "should be fine" as evidence. Use command output, exit code, and exact file paths.

## Actual-Changes Pack Rule

When normal review packs do not include current untracked changes, create an actual-changes review pack. It must include changed tracked files, untracked files, diffs, copied-file list, expected-file presence report, archive validation, and explicit exclusions for old pack artifacts.
