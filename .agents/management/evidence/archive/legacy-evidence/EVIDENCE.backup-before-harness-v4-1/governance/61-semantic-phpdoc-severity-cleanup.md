# Semantic PHPDoc Severity Cleanup

## Changes

1. `how-to-document.md` — Added "PHPDoc GREEN Status Rule" sub-section with BLOCKER/HIGH/MEDIUM/LOW severity and
   anti-spam rule
2. `how-to-code-review.md` — Updated cross-reference to include severity and GREEN status
3. `how-to-clean-code.md` — Updated cross-reference to include severity
4. `how-to-production-readiness.md` — Added §26 Semantic PHPDoc GREEN Status Rule with full severity table

## Severity Summary

- BLOCKER: missing PHPDoc on PublicSurface/runtime-critical/security-sensitive classes, lying PHPDoc
- HIGH: missing PHPDoc on ordinary touched classes/methods, missing type boundary docs
- MEDIUM: missing PHPDoc on private non-trivial methods, unclear intent
- LOW: wording polish only

## Anti-Spam Rule

Mandatory PHPDoc does not allow decorative PHPDoc. A required docblock that merely repeats code is still a violation.
