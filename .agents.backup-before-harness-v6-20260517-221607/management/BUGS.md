# BUGS

Canonical active defect and regression queue.

## Rules

- keep newest items first
- describe user-visible failure first
- include expected fixed behavior
- capture severity and risk
- use timestamp and estimate fields from `TIMELINE.md`
- keep `ACTIVE.md` in sync for non-closed items

## Entry Format

- `id`:
- `detected_at`:
- `updated_at`:
- `status`: open | in_progress | blocked | fixed | closed
- `severity`: low | medium | high | critical
- `estimate`:
- `actual`:
- `symptom`:
- `expected_behavior`:
- `risk`:
- `links`:

## Current Items

- `id`: BUG-V5.9-GOVERNANCE-BASELINE-RED
- `detected_at`: 2026-05-16
- `updated_at`: 2026-05-16
- `status`: fixed
- `severity`: high
- `estimate`: large
- `actual`: gate baseline classified
- `symptom`: Harness-Full baseline governance gates failed before V5.9 Phase 1 could safely continue.
- `expected_behavior`: Semantic PHPDoc legacy debt is ratcheted, how-to document structure passes, and large-unit
  threshold debt names the exact next blocker.
- `risk`: Closed as fake-RED baseline drift. Remaining risk is tracked by `V5.9-AUTHBUILDER-SPLIT-FIRST-SLICE`.
- `links`: `EVIDENCE/v5.9-codex/04-semantic-phpdoc-ratchet-correction.md`,
  `EVIDENCE/v5.9-codex/05-how-to-document-structure-correction.md`,
  `EVIDENCE/v5.9-codex/06-large-unit-gate-classification.md`
