# Generic vs Project Separation

**Date:** 2026-05-26
**Status:** CLOSED_FOR_GENERIC_GOVERNANCE

## Scan

Target:

```text
.agents/how-to/**/*.md
excluding .agents/how-to/project/**
excluding README.md and 00-reading-order.md
```

Result from hardened leakage checker:

```text
Files checked: 26
Files with leaks: 0
Total findings: 0
GREEN: No unapproved project leakage found in generic governance.
```

## Changes Made

- Generic docs now refer to configured checkers instead of hardcoded project tooling paths.
- Generic docs now refer to configured evidence locations instead of project-specific `EVIDENCE/...` paths.
- Root `.agents/how-to/README.md` no longer describes the folder as AvaX-only governance.
- AvaX-specific rules remain under `.agents/how-to/project/`.
- Skill names such as `avax-test-evidence-quality` remain allowed references because they identify actual project skills, not generic governance rules.

## Remaining Project-Specific Files

```text
.agents/how-to/project/how-to-write-avax.md
.agents/how-to/project/how-to-git.md
```

These are intentionally project overlay governance.
