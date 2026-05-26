# Validation Summary

Date: 2026-05-26

## Size Check

```text
wc -l AGENTS.md
```

Result:

```text
651 AGENTS.md
```

Previous observed size:

```text
2429 AGENTS.md
```

## Stale Reference Check

```text
rg -n "00-reading-order\.md|\.agents/how-to/how-to-[A-Za-z0-9-]+\.md|AGENTS\.md §24A|AGENTS\.md §26|AGENTS\.md §28|AGENTS\.md §29|Documentation normally lives" AGENTS.md .agents/how-to .agents/skills tooling ARCHITECTURE.md
```

Result:

```text
no output
```

## Root How-To Shape

```text
find .agents/how-to -maxdepth 1 -type f
```

Result:

```text
.agents/how-to/README.md
.agents/how-to/00-how-to-reading-order.md
```

## Governance Gates

```text
git diff --check
```

Result:

```text
no output; command exited 0
```

```text
php tooling/governance/check-governance-canonical-truth.php
```

Result:

```text
GREEN: Governance canonical truth is coherent.
```

```text
php tooling/governance/check-governance-index-current.php
```

Result:

```text
GREEN: Governance index is current.
```

```text
php tooling/governance/check-governance-leakage.php
```

Result:

```text
Files checked: 26
Files with leaks: 0
Total findings: 0
GREEN: No unapproved project leakage found in generic governance.
```

```text
php tooling/governance/check-self-explaining-architecture.php --mode=changed
```

Result:

```text
Changed files: 194
Findings in changed scope: 14
Blocking findings: 0
GREEN - changed scope has no blocking findings for this gate.
```

```text
php tooling/testing/check-shallow-tests.php --mode=changed
```

Result:

```text
Changed files: 194
Findings in changed scope: 0
Blocking findings: 0
GREEN - changed scope has no blocking findings for this gate.
```

## Validation Scope

Focused governance/documentation validation only.

Full PHPUnit and PHPStan were not rerun in this pass because no production code was changed and full-mode legacy debt is already tracked by governance-gate-adoption baselines.
