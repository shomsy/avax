# How To Detect AntiPatterns

## Purpose

Detect recurring bad solutions early, especially AI-generated structure that looks polished but hides poor ownership.

## When Required

Required for production code changes, refactors, architecture changes, and readiness claims.

## Evidence Path

`.agents/management/evidence/generated/<task-name>/antipattern-review.md`

## AntiPatterns

- Blob/God Object
- Spaghetti Code
- Golden Hammer
- Cut-and-Paste Programming
- Stovepipe System
- Analysis Paralysis
- Architecture Theater
- Fake Abstraction
- Service Locator
- Shallow Tests
- Generic Bucket

## Dictionary Requirement

Dictionary entries live under `.agents/dictionary/antipatterns/` and must define symptoms, danger, AI failure mode, fix, exceptions, and severity.

## Automation Rule

Run:

```bash
php tooling/governance/check-antipatterns.php --mode=changed
```

## Stop Conditions

Stop on service locator in production behavior, fake architecture guardrails, or shallow tests used to claim readiness.

## Severity

Service locator in business/runtime code is BLOCKER.
Shallow tests supporting GREEN are HIGH.
Architecture theater is HIGH.

## Final Report Requirement

Final reports must summarize anti-pattern findings and remaining risk.

## Complete Dictionary List

Required dictionary entries:

- `architecture-theater.md`
- `analysis-paralysis.md`
- `blob-god-object.md`
- `cut-and-paste-programming.md`
- `fake-abstraction.md`
- `generic-bucket.md`
- `golden-hammer.md`
- `service-locator.md`
- `shallow-tests.md`
- `spaghetti-code.md`
- `stovepipe-system.md`

## Symptoms, Consequences, Refactoring Path

| AntiPattern | Core Symptom | Consequence | Refactoring Path |
|---|---|---|---|
| Blob/God Object | one unit owns too much | unclear invariants | split by flow/capability |
| Spaghetti Code | tangled branches | unsafe changes | characterize and extract exact actions |
| Golden Hammer | one solution everywhere | poor fit | re-evaluate forces |
| Cut-and-Paste | copied knowledge | drift | extract or document variation |
| Stovepipe | local mini-system | platform inconsistency | dogfood approved capabilities |
| Analysis Paralysis | no decision | no progress | decide smallest safe slice |
| Architecture Theater | docs without guardrails | fake safety | add fitness function |
| Fake Abstraction | wrapper without boundary | noise | inline or name real boundary |
| Service Locator | hidden dependency lookup | runtime failure | inject at assembly |
| Shallow Tests | construction proof only | false confidence | test behavior and failures |
| Generic Bucket | vague grouping | bad placement | rename by owner/behavior |

## AI Failure Mode Per AntiPattern

Agents tend to overproduce polished structure. The review must ask whether each new unit has a real owner, behavior, boundary, test, and failure mode.

## Changed-Scope Enforcement

Run changed-scope detection on every production code, governance tooling, or readiness pass. Changed-scope findings are not legacy debt.

## Automated vs Manual Classification

Automated checks catch names, service locator signals, dictionary completeness, and shallow-test smells. Manual review remains required for cohesion, coupling intent, pattern fit, and whether a boundary is real.

## Accepted Exception Format

```text
antipattern:
reason:
scope:
severity:
owner:
mitigation:
expiry_or_review_date:
validator_or_manual_review:
```

## AntiPattern Review Checklist

- dictionary entries complete;
- changed files scanned;
- service locator signals checked;
- generic names reviewed;
- shallow tests checked;
- manual cohesion/coupling review completed;
- exceptions classified.
