# Agents Governance Cleanup Report

Date: 2026-05-26

## Changes

- Renamed `.agents/how-to/00-reading-order.md` to `.agents/how-to/00-how-to-reading-order.md`.
- Updated `.agents/how-to/README.md` to point at the new reading-order filename.
- Updated the reading-order document's self references.
- Updated `tooling/governance/check-governance-canonical-truth.php`.
- Updated `tooling/governance/check-governance-leakage.php`.
- Updated `tooling/governance/generate-governance-truth-review-packs.php`.
- Updated stale active root how-to references in:
  - `.agents/skills/self-explaining-architecture/SKILL.md`
  - `.agents/how-to/verification/how-to-code-review.md`
  - `.agents/how-to/verification/how-to-create-ai-code-review-packs.md`
  - `.agents/how-to/architecture/how-to-runtime-composition.md`
  - `.agents/how-to/implementation/how-to-dependency-injection.md`
  - `.agents/how-to/implementation/how-to-clean-code.md`
  - `.agents/how-to/implementation/how-to-code-style.md`
  - `tooling/governance/generate-review-packs.sh`
- Clarified that `how-to.txt` is retired and non-canonical.

## Scope Not Changed

- Historical generated evidence was not rewritten.
- Identity implementation was not started.
- Legacy full-mode PHPStan/self-explaining/shallow-test debt was not modified.
