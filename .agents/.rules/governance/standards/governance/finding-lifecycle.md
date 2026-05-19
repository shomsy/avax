# Finding Lifecycle Contract

Version: 1.0.0
Status: Normative

## Purpose

Define the canonical lifecycle for scanner-detected findings. This contract
ensures that every finding is resolved through machine-verifiable decisions,
not Markdown summaries.

## No Markdown-Only Closure

A finding detected by an automated tool is NOT closed when:
- An agent writes a Markdown note saying "accepted" or "deferred"
- A review summary mentions the finding without a registry entry
- A dashboard claims GREEN while the detecting tool still reports RED

A finding IS closed only when:
1. The detecting tool reports it fixed (exit code 0 for that check), OR
2. A machine-verifiable decision exists in the Finding Decision Registry

## Finding Decision Registry

Location: `.agents/management/evidence/indexes/finding-decisions.json`

Schema: `.agents/config/schemas/finding-decision.schema.json`

Tool: `.agents/skills/bin/finding_decisions.py`

## Scanner Output Categories

Every scanner MUST classify its findings into one of these categories:

| Category | Meaning | When |
|---|---|---|
| `RED_ACTIVE` | Finding detected, no valid decision exists | New finding, expired decision, revoked decision |
| `YELLOW_ACCEPTED` | Finding has an active ACCEPTED_EXCEPTION decision | Formally waived with owner, mitigation, expiry |
| `YELLOW_DEFERRED` | Finding has an active DEFERRED or SCAFFOLD_DEFERRED decision | Tracked in backlog with expiry |
| `INFO_FALSE_POSITIVE` | Finding has an active FALSE_POSITIVE or TOOLING_BUG decision | Tool error or known bug |
| `GREEN_FIXED` | Finding has an active FIXED decision with evidence | Root cause resolved |

## Exit Code Policy

Scanners MUST follow this exit code policy:

- **Any RED_ACTIVE finding** => non-zero exit (blocking)
- **Only YELLOW_ACCEPTED / YELLOW_DEFERRED / INFO_FALSE_POSITIVE** => exit 0 (or exit 2 with `--strict` flag for warnings)
- **Any expired decision** => treat as RED_ACTIVE => non-zero exit
- **No findings at all** => exit 0

## Fingerprint Specification

Every finding MUST have a deterministic fingerprint for stable matching:

```
fingerprint = SHA-256(tool_name + ":" + file_path + ":" + line_number + ":" + pattern)
```

The `finding_decisions.py match --fingerprint <fp>` command is the authoritative
lookup. Scanners should compute the same fingerprint and query the registry
before emitting a finding.

## Decision Lifecycle

```
detected -> fingerprint computed -> registry lookup
  -> match found + active decision -> apply category
  -> match found + expired decision -> emit RED_ACTIVE, flag for re-evaluation
  -> no match -> emit RED_ACTIVE
```

Decision states:
- `active` — currently valid
- `expired` — past expiry date, finding reverts to RED_ACTIVE
- `revoked` — manually withdrawn, finding reverts to RED_ACTIVE
- `superseded` — replaced by a newer decision

## Decision Types

| Type | Use When | Requirements |
|---|---|---|
| `FIXED` | Root cause is resolved | Evidence of fix in evidenceRefs |
| `ACCEPTED_EXCEPTION` | Intentionally waived | owner, reason, mitigation, expiry, evidenceRefs |
| `DEFERRED` | Tracked in backlog | expiry, backlog link |
| `FALSE_POSITIVE` | Tool error | evidence showing why |
| `COMPOSITION_ROOT_ALLOWED` | Acceptable at composition boundary | boundary justification |
| `SCAFFOLD_DEFERRED` | Scaffold/template intentionally incomplete | adoption condition |
| `TOOLING_BUG` | Known bug in detecting tool | bug reference |

## Scanner Contract

Every scanner that emits findings MUST:

1. Emit machine-readable output (JSON or structured text with stable fingerprints)
2. Compute a deterministic fingerprint for each finding
3. Read `finding-decisions.json` before finalizing output (if registry exists)
4. Classify each finding into one of the five output categories
5. Follow the exit code policy above
6. Print all findings honestly — do not suppress output
7. Do not blanket-exempt patterns without explicit decision records

## Enforcement

- `verify-governance.sh` Check 12 validates the finding lifecycle
- `release-readiness.sh` Section 13 runs registry validation
- Gates fail on RED_ACTIVE findings, invalid registry, or expired blocking decisions
