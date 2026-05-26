# Validation Summary

Date: 2026-05-26

## Commands

```text
find .agents/how-to -maxdepth 1 -type f
```

Result:

```text
.agents/how-to/README.md
.agents/how-to/00-how-to-reading-order.md
```

```text
rg -n "\.agents/how-to/how-to-[A-Za-z0-9-]+\.md|00-reading-order\.md" .agents/how-to .agents/skills .agents/GOVERNANCE_INDEX.md .agents/GOVERNANCE_ENFORCEMENT_MAP.md AGENTS.md ARCHITECTURE.md tooling
```

Result:

```text
no output
```

```text
php tooling/governance/check-governance-canonical-truth.php
```

Result:

```text
GREEN: Governance canonical truth is coherent.
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
php tooling/governance/check-governance-index-current.php
```

Result:

```text
GREEN: Governance index is current.
```

```text
git diff --check
```

Result:

```text
no output; command exited 0
```
