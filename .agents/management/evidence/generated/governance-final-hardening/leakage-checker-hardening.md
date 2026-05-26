# Leakage Checker Hardening

**Date:** 2026-05-26
**Status:** GREEN

## Tool

```text
tooling/governance/check-governance-leakage.php
```

## Behavior

The checker now:

- recursively scans all generic `.agents/how-to/**/*.md`
- excludes `.agents/how-to/project/**`
- excludes `README.md` and `00-reading-order.md`
- skips fenced code blocks
- allows explicit example/project overlay markers
- reports file, line, matched phrase, reason, suggested fix, and source line
- detects project name variants, project component paths, hardcoded project paths, runtime project terms, tooling paths, evidence paths, and project-bound PublicSurface taxonomy

## Validation

```text
php -l tooling/governance/check-governance-leakage.php
No syntax errors detected in tooling/governance/check-governance-leakage.php

php tooling/governance/check-governance-leakage.php
Files checked: 26
Files with leaks: 0
Total findings: 0
GREEN: No unapproved project leakage found in generic governance.
```
