# Canonical Truth Checker Hardening

**Date:** 2026-05-26
**Status:** GREEN

## Tool

```text
tooling/governance/check-governance-canonical-truth.php
```

## Behavior

The checker now detects:

- root-level how-to shadow files
- duplicate canonical governance filenames
- stale reading-order references
- missing canonical governance files
- canonical files not listed in reading order
- evidence paths used as mandatory canonical governance
- planned docs treated as required docs
- duplicate reading-order numbering
- duplicate listed canonical files
- stale `.agents/GOVERNANCE_INDEX.md` how-to references

## Validation

```text
php -l tooling/governance/check-governance-canonical-truth.php
No syntax errors detected in tooling/governance/check-governance-canonical-truth.php

php tooling/governance/check-governance-canonical-truth.php
GREEN: Governance canonical truth is coherent.
No root shadow how-to files remain.
Every canonical governance file is listed exactly once.
Planned docs are not treated as required governance.
Evidence is not mandatory canonical governance.
```
