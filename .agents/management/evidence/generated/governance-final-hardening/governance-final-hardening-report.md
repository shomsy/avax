# Governance Final Hardening Report

**Date:** 2026-05-26
**Branch:** `architecture/identity-runtime-convergence`
**Final classification:** RED_BLOCKED

## Executive Summary

The concrete governance repair items were addressed:

- root how-to shadow files are gone
- tracked generated `.agents/how-to/how-to.txt` was removed and ignored
- documentation model no longer uses the stale docs-only rule
- `ARCHITECTURE.md` exists at repository root
- review-pack generator includes `ARCHITECTURE.md`
- STATS.md generation now computes values before rendering
- canonical truth checker exists and is hardened
- leakage checker exists, scans recursively, and is GREEN
- reading order has unique numbering and planned docs are not required

FULL_GREEN_ENTERPRISE_READY is not allowed because mandatory validation still has unrelated but active project findings:

- PHPStan reports 100 errors after rerun with enough memory.
- self-explaining architecture checker reports 172 HIGH findings.
- shallow-test checker reports 345 HIGH findings and 47 MEDIUM findings.

## Governance Applicability Matrix

| File / Skill | Loaded | Relevant | Generic or Project-Specific | Used | Why |
|---|---:|---:|---|---:|---|
| `AGENTS.md` | yes | yes | project-specific root contract | yes | Defines blocker lifecycle, evidence, and GREEN rules. |
| `ARCHITECTURE.md` | yes | yes | project-specific | yes | Root architecture north star and review-pack requirement. |
| `.agents/GOVERNANCE_INDEX.md` | yes | yes | project-specific routing | yes | Canonical index location in this repo. |
| `.agents/how-to/README.md` | yes | yes | generic structure plus local map | yes | Defines root how-to folder rules. |
| `.agents/how-to/00-reading-order.md` | yes | yes | generic plus project overlay order | yes | Canonical reading order and planned-doc handling. |
| `.agents/how-to/documentation/how-to-document.md` | yes | yes | generic | yes | Documentation layering model. |
| `.agents/how-to/documentation/how-to-write-self-explaining-architecture.md` | yes | yes | generic | yes | Self-explaining architecture standard. |
| `.agents/how-to/verification/how-to-create-ai-code-review-packs.md` | yes | yes | generic | yes | Review-pack requirements. |
| `.agents/how-to/project/how-to-write-avax.md` | yes | yes | project-specific | yes | AvaX overlay rules remain here. |
| `.agents/how-to/project/how-to-git.md` | yes | yes | project-specific | yes | Local workflow/evidence rules. |
| `avax-source-of-truth-resolver` | yes | yes | project skill | yes | Required before execution. |
| `avax-enterprise-codecraft` | yes | yes | project skill | yes | Enterprise governance/tooling quality. |
| `avax-test-evidence-quality` | yes | yes | project skill | yes | Validation and shallow-test gate. |
| `avax-observability-failure-semantics` | yes | yes | project skill | yes | Failure behavior for tooling. |
| `avax-security-threat-model` | yes | yes | project skill | yes | Secret/path filtering in pack generator. |
| `self-explaining-architecture` | yes | yes | project skill | yes | Architecture docs and validation. |

## Exact BLOCKERs Fixed

| Finding | Closure |
|---|---|
| Root shadow how-to files | `find .agents/how-to -maxdepth 1 -type f -name 'how-to-*.md'` returns no files. |
| Duplicate `how-to-document.md` | Canonical truth checker reports no duplicate filenames. |
| Docs-only contradiction | Documentation governance now defines global, project overlay, local component, and generated evidence layers. |
| Missing root `ARCHITECTURE.md` | `ARCHITECTURE.md` exists. |
| ARCHITECTURE not packed | Generator include list contains `ARCHITECTURE.md` in governance architecture pack. |
| Broken STATS.md generator | `generate_stats_md()` computes timestamp, other file count, and PHP line approximation before rendering. |
| Missing canonical truth checker | `tooling/governance/check-governance-canonical-truth.php` exists and returns GREEN. |
| Missing leakage checker | `tooling/governance/check-governance-leakage.php` exists, scans 26 generic docs, and returns GREEN. |
| Generic/project leakage | Leakage checker reports 0 findings. |
| Reading order numbering/planned docs | Canonical truth checker reports reading order coherent. |
| Tracked generated `how-to.txt` | File removed and `.gitignore` now ignores `.agents/how-to/how-to.txt`. |

## Review Pack Decision

Fresh review packs were not regenerated.

Reason:

```text
The prompt says to generate fresh packs only after validation passes.
Mandatory validation did not pass.
```

This prevents fake GREEN and fake OK manifests.

## Final Decision

```text
RED_BLOCKED
```
