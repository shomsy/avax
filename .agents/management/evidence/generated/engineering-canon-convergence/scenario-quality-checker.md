# Scenario Quality Checker Report

This document reports scenario input and quality checker status.

## What Changed
- Verified alignment of `scenario-input.md` template headings with the automated checker.
- Checker scans for: Task, Scope, System Boundary, Primary Actor, Actor Goal, Stakeholders and Interests, Preconditions, Success Guarantees, Minimal Failure Guarantees, Main Success Scenario, Extension / Failure Paths, Security Sensitivity, Data / State Mutation Sensitivity, Runtime / Concurrency Sensitivity, Observability Requirement, Acceptance Criteria, Planned Tests, Out of Scope.
- Added test validation checking that invalid mode options are rejected, and template checking runs properly.

## Why It Was Needed
- Prevented empty or copy-pasted scenario inputs from bypassing the quality checks.

## Files Affected
- `.agents/templates/evidence/scenario-input.md`
- `tooling/governance/check-scenario-input.php`

## Validation Command(s)
```bash
/usr/bin/php8.4 tooling/sdlc/validate-governance.php
/usr/bin/php8.4 vendor/bin/phpunit tests/Unit/Tooling/Governance/ScenarioInputCheckTest.php
```

## Result
- **PASS**: Mappings align, tests successfully verify validation.

## Remaining Risks
- Semantics of text content cannot be fully parsed via regex; review packs should be checked for quality.
