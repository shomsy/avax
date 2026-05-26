# Validation Summary

Date: 2026-05-26

## Commands Run

```text
rg -n "\.agents/how-to/\*\*|\.agents/skills/\*\*|complete .*agents|all .*skills|all .*how-to" AGENTS.md .agents/GOVERNANCE_INDEX.md
```

Purpose: verify explicit complete `.agents` SDLC loading language exists.

```text
rg -n "EVIDENCE/" AGENTS.md .agents/how-to/00-how-to-reading-order.md .agents/how-to/documentation/how-to-document.md .agents/how-to/project/how-to-git.md .agents/GOVERNANCE_INDEX.md .agents/GOVERNANCE_ENFORCEMENT_MAP.md .agents/skills/avax-source-of-truth-resolver/SKILL.md
```

Purpose: verify active root `EVIDENCE/` references are marked legacy/transitional or timestamp-bound.

```text
git diff --check
php tooling/governance/check-governance-canonical-truth.php
php tooling/governance/check-governance-index-current.php
php tooling/governance/check-governance-leakage.php
php tooling/governance/check-self-explaining-architecture.php --mode=changed
php tooling/testing/check-shallow-tests.php --mode=changed
```

Results are recorded in the final assistant response for this pass.

## Results

Complete `.agents` SDLC loading language found in:

```text
.agents/GOVERNANCE_INDEX.md: all .agents/how-to/**/*.md
.agents/GOVERNANCE_INDEX.md: all .agents/skills/**/SKILL.md
AGENTS.md: all skills loaded before routing
AGENTS.md: complete .agents SDLC
AGENTS.md: every local skill contract before routing
```

Root `EVIDENCE/` references in active governance are now either:

```text
legacy/transitional
explicitly activated by current evidence or tooling
timestamp-bound for new unavoidable root files
```

Gate results:

```text
git diff --check: PASS
check-governance-canonical-truth.php: GREEN
check-governance-index-current.php: GREEN
check-governance-leakage.php: GREEN
check-self-explaining-architecture.php --mode=changed: GREEN, blocking findings 0
check-shallow-tests.php --mode=changed: GREEN, blocking findings 0
```
