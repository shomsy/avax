# Semantic PHPDoc Ratchet Baseline

Date established: 2026-05-16
Command: `php tooling/governance/check-semantic-phpdoc.php`
Gate mode: `ratchet`

## Purpose

This file records the current untouched legacy Semantic PHPDoc debt floor.

The gate must keep this debt visible, but it must not force a mass PHPDoc rewrite.
New or touched production files still block when they contain missing or fake semantic PHPDoc.
The baseline may only move downward unless a regression is explicitly accepted as YELLOW/RED debt.

## Baseline

| Metric | Count | Decision |
|---|---:|---|
| Legacy violation baseline | 9823 | YELLOW_WITH_RATCHET |
| Touched/new production violation baseline | 0 | BLOCKING |

## Acceptance

| Field | Value |
|---|---|
| Owner | AvaX governance owner |
| Target | Reduce opportunistically whenever production files are touched; prioritize PublicSurface, runtime-critical, and security-sensitive files. |
| Risk | Architecture readability and review burden; not a V5.9 blocker unless touched/new scope regresses. |
| Expiry | Next touched-file pass or a dedicated Semantic PHPDoc hardening phase. |
| Evidence | `EVIDENCE/v5.9-codex/raw/semantic-phpdoc-ratchet-after.txt` |
| V5.9 blocking decision | Legacy untouched count is non-blocking YELLOW; touched/new violations and count regressions block. |
