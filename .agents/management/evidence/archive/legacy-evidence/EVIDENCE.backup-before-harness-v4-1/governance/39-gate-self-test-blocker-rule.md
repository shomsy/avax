# Gate Self-Test BLOCKER Rule

## Decision

The Gate Self-Test rule is upgraded to BLOCKER severity for mandatory gates in:

- `how-to-code-review.md` §14.2 (upgraded from HIGH to BLOCKER, expanded with required examples)

Added to:

- `how-to-production-readiness.md` — as new Section 16
- `how-to-git.md` — as new Section 9
- `how-to-unit-test.md` — as cross-reference section

## Changes

1. `how-to-code-review.md` §14.2: Severity upgraded from HIGH to BLOCKER. Added required examples for runtime
   composition gate, DI gate, Git gate, and Security gate failures. Added explicit rules: gate without self-test =
   YELLOW minimum; mandatory gate without self-test cannot close BLOCKER.

2. `how-to-production-readiness.md`: Added Gate Self-Test Rule section with the full rule text.

3. `how-to-git.md`: Added Gate Self-Test Rule section referencing the canonical source.

4. `how-to-unit-test.md`: Added cross-reference to gate self-test requirement.
