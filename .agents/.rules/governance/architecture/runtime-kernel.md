# Runtime Kernel Architecture

Version: 1.0.0
Status: Authoritative
Scope: `.agents/skills/bin/execution-substrate.py` and all runtime modules

This document defines the runtime architecture of the Agent Harness Execution
Substrate. It is the single source of truth for operators, developers, and
auditors who need to understand how the substrate executes, secures, replays,
observes, and evolves agent work.

---

## 1. Runtime Architecture Overview

### 1.1 The Five Architectural Planes

The substrate is organized into five orthogonal planes. Each plane owns a
distinct concern and communicates with the others through well-defined
contracts.

| Plane | Responsibility | Primary Module |
|---|---|---|
| **Governance** | Policy resolution, rule partitioning, value audits, governance compaction | `execution-substrate.py` (Plane 1 logic), `compile-governance.py` |
| **Execution** | Sandbox isolation, capability token validation, state transitions, mutation journaling | `execution-substrate.py` (core orchestrator) |
| **Replay** | Deterministic replay, drift detection, environment normalization, ordering guarantees | `deterministic-replay.py` |
| **Observability** | Telemetry, DAG lineage, explainability dashboards, timeline views, health checks | `observability.py` |
| **Security** | Nonce registry, revocation, audit chain, path guards, integrity seals, trust identity | `substrate_security.py`, `trust-identity.py` |

### 1.2 Component Diagram

```
┌──────────────────────────────────────────────────────────────────────────┐
│                         OPERATOR / CLIENT CLI                            │
│   execution-substrate.py run | replay | graph | compress                 │
└───────────────────────────────┬──────────────────────────────────────────┘
                                │
                     ┌──────────▼──────────┐
                     │  Execution Substrate │  (execution-substrate.py)
                     │  ──────────────────  │
                     │  • CapabilityToken   │
                     │  • scan_files_state  │
                     │  • enforce_trust     │
                     │  • execute()         │
                     │  • replay_execution()│
                     │  • build_graph()     │
                     └──┬───┬───┬───┬───┬──┘
                        │   │   │   │   │
           ┌────────────┘   │   │   │   └────────────┐
           │     ┌──────────┘   │   └──────────┐     │
           ▼     ▼              ▼              ▼     ▼
  ┌─────────────┐  ┌──────────────────┐  ┌──────────────┐  ┌─────────────────┐  ┌──────────────────┐
  │   Security   │  │    Deterministic  │  │   Storage    │  │    Trust &      │  │   Failure &      │
  │   Primitives  │  │    Replay        │  │  Lifecycle   │  │   Identity     │  │   Recovery       │
  │             │  │                  │  │              │  │                │  │                  │
  │ NonceReg    │  │ Environment      │  │ QuotaMgr     │  │ IdentityChain  │  │ CrashRecovery    │
  │ Revocation  │  │ Snapshot         │  │ Retention    │  │ DelegationLin. │  │ JournalRepair    │
  │ AuditChain  │  │ Normalizer       │  │ Compactor    │  │ AuthorityInh.  │  │ LockCleanup      │
  │ EnvSanitizer│  │ Verifier         │  │ Summarizer   │  │ NarrowingProof │  │ ConflictHandler  │
  │ PathGuard   │  │ HashSignature    │  │ TracePruner  │  │ DeputyProtect  │  │ ChaosSimulator   │
  │ IntegritySeal│ │ OrderingGuarante │  │ ArchiveMigr  │  │ TransitiveVal  │  │ CrashSafeReplay  │
  └──────┬──────┘  └────────┬─────────┘  └──────┬───────┘  └───────┬────────┘  │ EvidenceIsolator │
         │                  │                   │                  │            └────────┬─────────┘
         │                  │                   │                  │                     │
         └──────────────────┴───────────────────┴──────────────────┴─────────────────────┘
                                │                   │
                     ┌──────────▼──────────┐  ┌─────▼────────────────┐
                     │   Observability     │  │   Portability        │
                     │                     │  │   & Scale            │
                     │ ExplainabilityDash  │  │   PlatformDetector   │
                     │ ReplayDiagnostics   │  │   CompatMatrix       │
                     │ GovResolutionDiag   │  │   PortabilityMatrix  │
                     │ TimelineViewer      │  │   StressTestRunner   │
                     │ AuthorityTrace      │  │   BenchmarkRunner    │
                     │ FailureRemediation  │  │   ScaleValidator     │
                     │ MutationVisualizer  │  └──────────────────────┘
                     │ ReplayDiffViewer    │
                     └─────────────────────┘
                                │
                     ┌──────────▼──────────┐
                     │  Release Discipline  │
                     │                      │
                     │ SemanticVersion      │
                     │ RuntimeCompat        │
                     │ MigrationContract    │
                     │ Upgrade/Downgrade    │
                     │ Changelog            │
                     │ Deprecation          │
                     │ SchemaVersioner      │
                     │ ReleasePackager      │
                     └──────────────────────┘
```

### 1.3 Data Flow

1. **Initiation** — Operator invokes `execution-substrate.py run` with task,
   trust tier, domain scope, and command.
2. **Token Creation** — A `CapabilityToken` is generated (or a parent token is
   validated for delegation). The token carries allowed tools, scopes, trust
   tier, and a lease duration.
3. **Security Gate** — Nonce is registered, revocation checked, and delegation
   narrowing enforced. Any violation aborts immediately.
4. **Governance Resolution** — The governance index is scanned for rules
   matching the domain scope. Only scoped rules are loaded (lazy partitioning).
5. **Baseline Capture** — `scan_files_state()` computes SHA-256 hashes of all
   tracked files to establish a pre-execution snapshot.
6. **Execution** — The command runs in a sanitized subprocess with frozen time,
   deterministic locale, and stripped sensitive environment variables.
7. **Validation** — Post-execution file state is compared against baseline.
   Trust boundaries are enforced per tier. Violations trigger automatic rollback.
8. **Sealing** — The execution manifest is sealed with an integrity hash and
   appended to the audit chain. Evidence files are written to disk.
9. **Observability** — Telemetry metrics (duration, overhead, context budget,
   memory) are recorded and available via the observability module.

---

## 2. Module Boundaries

### 2.1 execution-substrate.py — Main Orchestrator

| Aspect | Detail |
|---|---|
| **Path** | `.agents/skills/bin/execution-substrate.py` |
| **Version** | V5.1.0 |
| **Role** | Central execution engine. Coordinates all five planes during a single execution lifecycle. |
| **Key Classes** | `State` (enum), `CapabilityToken`, `ExecutionSubstrate` |
| **CLI** | `run`, `replay`, `graph`, `compress` |
| **Imports** | `substrate_security` (NonceRegistry, RevocationRegistry, AuditChain, EnvironmentSanitizer, PathGuard, IntegritySeal) |
| **Output** | `execution-manifest-{exec_id}.json`, `delegation-manifest-{deleg_id}.json` |

**Boundary rules:** The orchestrator never directly manipulates evidence files
outside `.agents/management/evidence/execution/`. All security primitives are
delegated to `substrate_security.py`. Replay logic is delegated to
`deterministic-replay.py`.

### 2.2 substrate_security.py — Security Primitives

| Aspect | Detail |
|---|---|
| **Path** | `.agents/skills/bin/substrate_security.py` |
| **Role** | Reusable cryptographic and access-control primitives. |

| Class | Purpose | Storage |
|---|---|---|
| `NonceRegistry` | Prevents token replay via UUID nonce tracking | `.agents/management/evidence/security/nonce-registry.jsonl` |
| `RevocationRegistry` | Tracks revoked tokens and cascades chain revocation | `.agents/management/evidence/security/revocation-registry.jsonl` |
| `AuditChain` | Immutable append-only hash chain of execution manifests | `.agents/management/evidence/security/audit-chain.jsonl` |
| `EnvironmentSanitizer` | Cleans environment variables (blocklist + whitelist + deterministic locale) | In-memory only |
| `PathGuard` | Symlink detection and path traversal protection | In-memory only (scans filesystem) |
| `IntegritySeal` | SHA-256 seal creation and verification on manifest dicts | In-memory (seal written into manifest JSON) |

**Boundary rules:** Security modules have no dependency on the orchestrator.
They are pure libraries that can be imported by any runtime module.

### 2.3 deterministic-replay.py — Replay Engine

| Aspect | Detail |
|---|---|
| **Path** | `.agents/skills/bin/deterministic-replay.py` |
| **Version** | V1.0.0 (Replay Format) |
| **Role** | Captures full execution environment, normalizes for deterministic replay, verifies determinism, detects drift/poisoning/corruption. |

| Class | Purpose |
|---|---|
| `EnvironmentSnapshot` | Captures OS, Python, shell, locale, packages, git HEAD, governance checksums |
| `DeterminismNormalizer` | Forces LANG=C.UTF-8, POSIXLY_CORRECT, frozen time, sorted PATH, PYTHONHASHSEED=0 |
| `ReplayVerifier` | Verifies determinism: exit code, stdout/stderr hashes, file mutations, dependency checksums |
| `ReplayHashSignature` | Creates and verifies combined manifest+snapshot replay signatures |
| `ExecutionOrderingGuarantees` | Records and verifies execution ordering dependencies |
| `ReplayCompatibilityVersion` | Semantic version compatibility checks for replay format |

**CLI:** `snapshot`, `verify`, `drift`, `signature`, `normalize`

**Boundary rules:** Reads execution manifests from
`.agents/management/evidence/execution/`. Writes snapshots to
`.agents/management/evidence/replay/`. Imports `IntegritySeal`, `AuditChain`,
`NonceRegistry` from `substrate_security.py` for verification.

### 2.4 storage-lifecycle.py — Storage Management

| Aspect | Detail |
|---|---|
| **Path** | `.agents/skills/bin/storage-lifecycle.py` |
| **Version** | V1.0.0 |
| **Role** | Prevents telemetry self-destruction. Manages quotas, retention, compaction, summarization, trace pruning, and archive migration. |

| Class | Purpose |
|---|---|
| `StorageQuotaManager` | Per-type quotas (execution: 50MB, telemetry: 20MB, traces: 30MB, replay: 10MB, total: 150MB) |
| `EvidenceRetentionEngine` | Time-based retention (execution: 30d, telemetry: 7d, traces: 3d, replay: 90d) with DELETE/ARCHIVE/COMPACT actions |
| `TelemetryCompactor` | Aggregates telemetry and trace files into summary statistics |
| `ExecutionSummarizer` | Creates execution, failure, performance, and trust violation summaries |
| `TracePruner` | Prunes traces by count, age, or total size |
| `ArchiveMigration` | Validates and migrates archive formats with integrity checks |

**CLI:** `quota`, `retention`, `compact`, `summarize`, `prune`, `report`

**Boundary rules:** Operates on `.agents/management/evidence/` root. Writes
audit logs to `.agents/management/evidence/generated/storage-audit.jsonl`.
Never modifies active (non-expired) evidence.

### 2.5 trust-identity.py — Identity & Delegation

| Aspect | Detail |
|---|---|
| **Path** | `.agents/skills/bin/trust-identity.py` |
| **Version** | V5.2.0 |
| **Role** | Multi-agent trust and identity engine. Execution identity chains, delegation lineage, authority inheritance, capability narrowing proofs, confused deputy protection, transitive delegation validation, ephemeral tokens. |

| Class | Purpose |
|---|---|
| `ExecutionIdentityChain` | Creates and verifies execution identities (token_id + nonce + timestamp + executor_hash) |
| `DelegationLineage` | Records delegation events, traces chains back to root, detects broken chains and loops |
| `AuthorityInheritance` | Computes inherited authority via intersection, verifies narrowing, maintains authority graph |
| `CapabilityNarrowingProof` | Cryptographic proofs that child authority is strictly narrower than parent |
| `ConfusedDeputyProtection` | Validates scope-purpose alignment, detects scope drift |
| `TransitiveDelegationValidator` | Validates full transitive chains, computes trust paths, enforces depth bounds (max 5) |
| `EphemeralAuthorityToken` | Short-lived authority tokens with TTL and revocation |
| `DelegationAuditGraph` | Builds complete delegation audit graph from all evidence sources |

**CLI:** `identity`, `lineage`, `verify`, `deputy`, `graph`, `ephemeral`

**Boundary rules:** Stores identity data in `.agents/management/evidence/identity/`.
Reads execution manifests from `execution/`. The trust tier ranking
(`READ_ONLY:1`, `WORKSPACE_WRITE:2`, `GOVERNANCE_WRITE:3`, `TRUSTED:4`) is
shared with `execution-substrate.py` and must remain consistent.

### 2.6 failure-recovery.py — Crash Recovery

| Aspect | Detail |
|---|---|
| **Path** | `.agents/skills/bin/failure-recovery.py` |
| **Version** | V3.0.0 |
| **Role** | Enterprise failure safety layer. Detects and recovers interrupted executions, repairs journals, cleans stale locks, resolves concurrent conflicts, simulates chaos, provides crash-safe checkpoints, isolates corrupted evidence. |

| Class | Purpose |
|---|---|
| `CrashRecoveryManager` | Detects executions stuck in non-terminal states (CREATED, PLANNED, EXECUTING, VALIDATING), transitions them to FAILED |
| `JournalRepair` | Repairs corrupted mutation journals, replays journals, verifies journal integrity |
| `LockCleanup` | Detects and removes stale `.lock` files older than threshold (default 15min) |
| `ConcurrentConflictHandler` | Detects shared-file mutations across executions, resolves with first_wins/last_wins/merge strategies |
| `ChaosSimulator` | Injects SIGKILL, partial writes, corrupted artifacts, filesystem races, concurrent governance mutations |
| `CrashSafeReplay` | Saves/loads/resumes execution checkpoints with integrity verification |
| `CorruptedEvidenceIsolator` | Scans evidence for corruption (JSON parse errors, seal mismatches), moves corrupted files to quarantine |

**CLI:** `recover`, `repair`, `locks`, `conflicts`, `chaos`, `checkpoint`, `quarantine`, `report`

**Boundary rules:** Writes recovery artifacts to
`.agents/management/evidence/recovery/`. Quarantines corrupted evidence to
`.agents/management/evidence/quarantine/`. Creates backup files with
`.pre-recovery` and `.pre-repair` suffixes before modifying manifests.

### 2.7 portability-check.py — Platform Validation

| Aspect | Detail |
|---|---|
| **Path** | `.agents/skills/bin/portability-check.py` |
| **Role** | Validates substrate works across platforms and at scale. Platform detection, compatibility matrix, portability matrix, stress tests, benchmarks, scale validation. |

| Class | Purpose |
|---|---|
| `PlatformDetector` | Detects OS, shell, architecture, BusyBox, WSL, Alpine |
| `CompatibilityMatrix` | Checks Python >= 3.8, Bash >= 4.0, disk >= 50MB, RAM >= 100MB |
| `PortabilityMatrix` | Path portability, encoding portability, symlink support, subprocess features |
| `StressTestRunner` | Sequential execution, replay consistency, governance load, graph traversal, telemetry growth |
| `BenchmarkRunner` | Startup time, execution time, replay time, file scan time |
| `ScaleValidator` | Large repo performance, evidence store scale, delegation volume, concurrent stress |

**CLI:** `platform`, `compat`, `matrix`, `stress`, `benchmark`, `scale`, `all`

**Boundary rules:** Writes reports to `.agents/management/evidence/generated/`.
Does not modify any execution evidence or security registries.

### 2.8 observability.py — Diagnostics

| Aspect | Detail |
|---|---|
| **Path** | `.agents/skills/bin/observability.py` |
| **Role** | Operational UX and observability. Execution explainability, replay health, governance resolution diagnostics, timeline views, authority tracing, failure remediation, mutation visualization, replay diff, drift reporting. |

| Class | Purpose |
|---|---|
| `ExecutionExplainabilityDashboard` | Explains what happened during an execution: timeline, authority chain, failure details |
| `ReplayDiagnostics` | Replay success rate, drift rate, corruption count, health summary |
| `GovernanceResolutionDiagnostics` | Profile coverage, rule consistency, frontmatter coverage |
| `ExecutionTimelineViewer` | Timeline of executions filtered by state or time range |
| `AuthorityTraceViewer` | Traces authority chains, delegation trees, authorized actions |
| `FailureRemediationGuide` | Classifies failures, provides remediation steps and prevention advice, system health score |
| `MutationVisualizer` | Visualizes file mutations and trust violation patterns |
| `ReplayDiffViewer` | Compares replay results, shows drift details |

**CLI:** `explain`, `explain-last`, `replay`, `replay-health`, `governance`,
`timeline`, `timeline-by-state`, `authority`, `delegation-tree`,
`find-authorized`, `remediate`, `health`, `mutations`, `trust-violations`,
`replay-diff`, `drift`

**Boundary rules:** Read-only on execution evidence. Writes diagnostic reports
to `.agents/management/evidence/generated/` and
`.agents/management/evidence/evidence/generated/`.

### 2.9 release-discipline.py — Versioning

| Aspect | Detail |
|---|---|
| **Path** | `.agents/skills/bin/release-discipline.py` |
| **Version** | V5.1.0 |
| **Role** | Release and evolution discipline. Semantic versioning, runtime compatibility guarantees, migration contracts, upgrade/downgrade strategies, changelog, deprecation management, governance schema versioning, release packaging. |

| Class | Purpose |
|---|---|
| `SemanticVersionManager` | Semver parsing, bumping, validation, comparison. Default version: `5.1.0` |
| `RuntimeCompatibilityGuarantees` | Compatibility matrix per version (Python min/max, governance schemas, breaking changes) |
| `MigrationContractManager` | Creates, applies, and tracks migrations between versions |
| `UpgradeDowngradeStrategy` | Plans upgrade/downgrade paths with safety checks and intermediate steps |
| `ChangelogManager` | Manages changelog entries (added, changed, deprecated, removed, fixed, security) |
| `DeprecationManager` | Tracks deprecated features, replacements, and removal schedules |
| `GovernanceSchemaVersioner` | Governance schema versioning (default: `5.1`). Validates compatibility with runtime version |
| `ReleasePackager` | Packages releases, validates artifacts, generates release notes, checks readiness |

**CLI:** `version`, `bump`, `compat`, `migrate`, `upgrade`, `changelog`,
`deprecations`, `schema`, `release`

**Boundary rules:** Writes version and release artifacts to
`.agents/management/evidence/generated/`. Migration contracts stored in
`.agents/management/evidence/migrations/`.

---

## 3. Execution Contracts

### 3.1 CapabilityToken Contract

A `CapabilityToken` represents bounded authority for a single execution.

```json
{
  "token_id": "token-{exec_id}",
  "lease_duration_sec": 600,
  "max_memory_mb": 512,
  "allowed_tools": ["view_file", "search_web", "write_to_file", "replace_file_content"],
  "allowed_scopes": ["security"],
  "trust_tier": "READ_ONLY",
  "issued_at": 1779112800.0,
  "signature": "sha256(token_id:lease_duration:max_memory_mb:tools:scopes:tier:issued_at)"
}
```

**Invariants:**
- `signature` is SHA-256 of the colon-joined fields listed above.
- `allowed_tools` and `allowed_scopes` must be strict subsets of the parent token's (delegation narrowing).
- `trust_tier` must be <= parent's tier (no escalation).
- Token is expired when `(now - issued_at) > lease_duration_sec`.

### 3.2 Execution Manifest Contract

Every execution produces a sealed manifest:

```json
{
  "execution_id": "exec-{uuid}",
  "delegation_id": "deleg-{uuid}",
  "nonce": "{uuid}",
  "task": "string",
  "trust_tier": "READ_ONLY|WORKSPACE_WRITE|GOVERNANCE_WRITE|TRUSTED",
  "domain_scope": "string",
  "lifecycle_state": "REPLAYABLE|FAILED|ROLLED_BACK",
  "lifecycle_history": [{"state": "...", "timestamp": epoch}],
  "capability_token": {...},
  "authority_lineage": {
    "initiator": "Operator",
    "parent_token_id": "...",
    "capability_signature": "..."
  },
  "environment_snapshot": {
    "os": "linux|darwin|win32",
    "python_version": "x.y.z",
    "frozen_timestamp": 1779112800.0,
    "env_vars_sanitized": true
  },
  "context_package": {
    "domain_rules_loaded": ["..."],
    "dependency_checksums": {"rel/path": "sha256"}
  },
  "mutation_journal": {
    "journal_id": "journal-{exec_id}",
    "mutations": {"created": [], "modified": [], "deleted": []},
    "symlinks_created": [],
    "violations_detected": [],
    "rollback_executed": false
  },
  "replay_contract": {
    "contract_id": "replay-{exec_id}",
    "nonce": "{uuid}",
    "payload_command": "...",
    "expected_exit_code": 0,
    "expected_mutation_count": 0,
    "original_checksum": "sha256"
  },
  "telemetry": {
    "total_duration_ms": 0.0,
    "command_execution_duration_ms": 0.0,
    "governance_resolution_overhead_ms": 0.0,
    "context_expansion_budget_bytes": 0,
    "memory_usage_mb": 0.0,
    "lease_expired_during_execution": false,
    "sanitized_env_vars_removed": 0
  },
  "integrity_seal": "sha256(canonical JSON without seal field)",
  "sealed_at": epoch
}
```

### 3.3 Delegation Manifest Contract

```json
{
  "delegation_id": "deleg-{uuid}",
  "execution_id": "exec-{uuid}",
  "nonce": "{uuid}",
  "token": {...},
  "status": "SUCCESS|ROLLED_BACK"
}
```

### 3.4 Replay Contract

Embedded in the execution manifest. Defines what determinism means for this execution:

- `payload_command` — the exact command string to re-run.
- `expected_exit_code` — the exit code the replay must match.
- `expected_mutation_count` — total created + modified + deleted files.
- `original_checksum` — SHA-256 of the command string.

### 3.5 Integrity Seal Contract

Every execution manifest carries an `integrity_seal` field computed as:

```
sealable = manifest without "integrity_seal" and "sealed_at"
canonical = JSON.dumps(sealable, sort_keys=True)
seal = SHA-256(canonical.encode("utf-8"))
```

Verification recomputes the seal over the stored manifest (excluding
`integrity_seal` and `sealed_at`) and compares it to the stored value.

### 3.6 Audit Chain Contract

The audit chain is a JSONL file where each entry links to the previous via a
chain hash:

```
prev_hash = previous entry's "chain_hash" (or "" for first entry)
manifest_json = JSON.dumps(manifest_data, sort_keys=True)
chain_hash = SHA-256(prev_hash + manifest_json)
entry_hash = SHA-256(manifest_json)
```

The chain is verified by recomputing each entry's hash and chain linkage
sequentially. Any mismatch indicates tampering.

---

## 4. Runtime Invariants

### 4.1 Must Always Be True

1. **Every execution has a unique nonce.** Nonces are registered in
   `NonceRegistry` before execution begins. Reused nonces are rejected as
   replay attacks.
2. **Every execution manifest has a valid integrity seal.** The seal is
   computed after all fields are populated and before the manifest is written
   to disk.
3. **Every manifest is appended to the audit chain.** The audit chain is
   append-only and hash-linked.
4. **Capability tokens never escalate.** Child tokens have tool sets, scope
   sets, and trust tiers that are subsets of (or equal to) their parent's.
5. **Trust boundaries are enforced per tier:**
   - `READ_ONLY` — zero file mutations allowed.
   - `WORKSPACE_WRITE` — no modifications to `.agents/` paths.
   - `GOVERNANCE_WRITE` — no modifications to `.agents/.rules/` or
     `.agents/config/`.
6. **Rollback is automatic on violation.** Created files are deleted; modified
   and deleted files are restored via `git checkout`.
7. **The audit chain is append-only.** Entries are never removed or reordered.
8. **Frozen time is deterministic.** `SUBSTRATE_FROZEN_TIME` is set to
   `1779112800.0` for all executions.

### 4.2 Can Never Happen

1. **A revoked token cannot execute.** Revocation is checked before nonce
   registration.
2. **A reused nonce cannot execute.** The nonce registry rejects duplicates.
3. **An execution can silently mutate `.agents/.rules/` or `.agents/config/`.**
   Even `GOVERNANCE_WRITE` tier blocks these frozen baseline paths.
4. **A manifest can be written without an integrity seal.** The seal is
   computed as part of the manifest construction pipeline.
5. **An execution can escape its sandbox via symlinks** in `READ_ONLY` or
   `WORKSPACE_WRITE` tiers — new symlinks trigger violation and rollback.
6. **A child token can have higher trust tier than its parent.** Escalation
   is blocked and the child token is immediately revoked.

### 4.3 Must Eventually Happen

1. **Expired nonces are cleaned up.** `NonceRegistry.cleanup_expired()` removes
   entries past their expiration timestamp.
2. **Evidence is subject to retention policies.** After the configured retention
   period (e.g., 30 days for execution manifests), evidence is archived,
   compacted, or deleted.
3. **Storage quotas are enforced.** When a type exceeds its quota, the oldest
   files are pruned until under the limit.
4. **Stale locks are cleaned up.** Locks older than the threshold (default
   15 minutes) are removed.
5. **Interrupted executions are detected and recovered.** Executions stuck in
   non-terminal states beyond the age threshold (default 30 minutes) are
   transitioned to `FAILED`.

---

## 5. Stable Interfaces

### 5.1 CLI Interface

#### execution-substrate.py

```
execution-substrate.py run --task <task> --tier <tier> --scope <scope> --cmd <command> [--dir <dir>] [--allowed-tools <csv>] [--parent-token <json>]
execution-substrate.py replay <exec_id> [--dir <dir>]
execution-substrate.py graph [--dir <dir>]
execution-substrate.py compress [--dir <dir>]
```

#### substrate_security.py (library, no CLI)

#### deterministic-replay.py

```
deterministic-replay.py snapshot [--dir <dir>]
deterministic-replay.py verify <exec_id> [--dir <dir>]
deterministic-replay.py drift <exec_id> [--dir <dir>]
deterministic-replay.py signature <exec_id> [--dir <dir>]
deterministic-replay.py normalize [--dir <dir>]
```

#### storage-lifecycle.py

```
storage-lifecycle.py quota [--dir <dir>]
storage-lifecycle.py retention [--preview] [--dir <dir>]
storage-lifecycle.py compact [--dir <dir>]
storage-lifecycle.py summarize [--days N] [--dir <dir>]
storage-lifecycle.py prune --strategy count|age|size [--value N] [--dir <dir>]
storage-lifecycle.py report [--dir <dir>]
```

#### trust-identity.py

```
trust-identity.py identity <exec_id> [--dir <dir>]
trust-identity.py lineage <token_id> [--dir <dir>]
trust-identity.py verify <token_id> [--dir <dir>]
trust-identity.py deputy <exec_id> [--dir <dir>]
trust-identity.py graph [--dir <dir>]
trust-identity.py ephemeral create|revoke|check <token_id> [--dir <dir>]
```

#### failure-recovery.py

```
failure-recovery.py recover [--dir <dir>]
failure-recovery.py repair <journal_id> [--dir <dir>]
failure-recovery.py locks [--dir <dir>]
failure-recovery.py conflicts [--dir <dir>]
failure-recovery.py chaos [--dir <dir>]
failure-recovery.py checkpoint <exec_id> [--dir <dir>]
failure-recovery.py quarantine [--dir <dir>]
failure-recovery.py report [--dir <dir>]
```

#### portability-check.py

```
portability-check.py platform [--dir <dir>]
portability-check.py compat [--dir <dir>]
portability-check.py matrix [--dir <dir>]
portability-check.py stress [--count N] [--dir <dir>]
portability-check.py benchmark [--dir <dir>]
portability-check.py scale [--dir <dir>]
portability-check.py all [--dir <dir>]
```

#### observability.py

```
observability.py explain <exec_id> [--dir <dir>]
observability.py explain-last [<n>] [--dir <dir>]
observability.py replay <exec_id> [--dir <dir>]
observability.py replay-health [--dir <dir>]
observability.py governance [--dir <dir>]
observability.py timeline [--days N] [--dir <dir>]
observability.py timeline-by-state <state> [--dir <dir>]
observability.py authority <exec_id> [--dir <dir>]
observability.py delegation-tree <token_id> [--dir <dir>]
observability.py find-authorized <token_id> [--dir <dir>]
observability.py remediate <exec_id> [--dir <dir>]
observability.py health [--dir <dir>]
observability.py mutations <exec_id> [--dir <dir>]
observability.py trust-violations [--days N] [--dir <dir>]
observability.py replay-diff <exec_id> [<replay_run>] [--dir <dir>]
observability.py drift <exec_id> [--dir <dir>]
```

#### release-discipline.py

```
release-discipline.py version [--dir <dir>]
release-discipline.py bump major|minor|patch [--dir <dir>]
release-discipline.py compat [--dir <dir>]
release-discipline.py migrate [--dir <dir>]
release-discipline.py upgrade <version> [--dir <dir>]
release-discipline.py changelog [--dir <dir>]
release-discipline.py deprecations [--dir <dir>]
release-discipline.py schema [--dir <dir>]
release-discipline.py release <version> [--dir <dir>]
```

### 5.2 Internal API Boundaries

| Consumer | Provider | Contract |
|---|---|---|
| `execution-substrate.py` | `substrate_security.*` | Import classes directly; no subprocess calls |
| `execution-substrate.py` | `deterministic-replay.EnvironmentSnapshot` | Optional: snapshot captured before execution |
| `deterministic-replay.ReplayVerifier` | `substrate_security.IntegritySeal` | `verify_seal(manifest) -> bool` |
| `deterministic-replay.ReplayVerifier` | `substrate_security.AuditChain` | `verify_chain() -> (bool, broken_index)` |
| `trust-identity.*` | `substrate_security.*` | Import for nonce, revocation, audit checks |
| `observability.*` | Execution manifests | Read-only access to `execution-manifest-*.json` |
| `failure-recovery.*` | Execution manifests | Read + write with backup before modification |
| `storage-lifecycle.*` | Evidence directories | Read + write audit logs; move/delete per policy |
| `release-discipline.*` | `.agents/management/evidence/generated/` | Read + write version, matrix, changelog files |

### 5.3 Evidence File Formats

All evidence files are JSON or JSONL:

| Pattern | Format | Location |
|---|---|---|
| `execution-manifest-{exec_id}.json` | JSON (sealed) | `.agents/management/evidence/execution/` |
| `delegation-manifest-{deleg_id}.json` | JSON | `.agents/management/evidence/execution/` |
| `nonce-registry.jsonl` | JSONL (one entry per line) | `.agents/management/evidence/security/` |
| `revocation-registry.jsonl` | JSONL | `.agents/management/evidence/security/` |
| `audit-chain.jsonl` | JSONL | `.agents/management/evidence/security/` |
| `snapshot-{exec_id}.json` | JSON | `.agents/management/evidence/replay/` |
| `execution-ordering.json` | JSON | `.agents/management/evidence/replay/` |
| `identities.jsonl` | JSONL | `.agents/management/evidence/identity/` |
| `delegation-lineage.jsonl` | JSONL | `.agents/management/evidence/identity/` |
| `authority-graph.json` | JSON | `.agents/management/evidence/identity/` |
| `narrowing-proofs.jsonl` | JSONL | `.agents/management/evidence/identity/` |
| `ephemeral-tokens.jsonl` | JSONL | `.agents/management/evidence/identity/` |
| `delegation-audit-graph.json` | JSON | `.agents/management/evidence/identity/` |
| `{type}-summary.json` | JSON | `.agents/management/evidence/compacted/` |
| `storage-audit.jsonl` | JSONL | `.agents/management/evidence/generated/` |
| `version.json` | JSON | `.agents/management/evidence/generated/` |
| `compatibility-matrix.json` | JSON | `.agents/management/evidence/generated/` |
| `schema-version.json` | JSON | `.agents/management/evidence/generated/` |

---

## 6. Lifecycle Boundaries

### 6.1 Execution Lifecycle

```
  CREATED ──▶ PLANNED ──▶ EXECUTING ──▶ VALIDATING ──┬─▶ REPLAYABLE
                                                     │
                                                     ├─▶ FAILED ──▶ ROLLED_BACK
                                                     │
                                                     └─▶ INVALIDATED / EXPIRED
```

| State | Meaning | Transition Trigger |
|---|---|---|
| `CREATED` | Execution context initialized, nonce generated | Entry point |
| `PLANNED` | Capability token validated, governance resolved, baseline captured | After security gate passes |
| `EXECUTING` | Subprocess running | After baseline snapshot |
| `VALIDATING` | Subprocess finished, post-state being compared | Command exit |
| `REPLAYABLE` | No violations; manifest sealed | Validation passes |
| `FAILED` | Violation detected or execution error | Validation fails |
| `ROLLED_BACK` | Mutations reverted after failure | After rollback completes |
| `INVALIDATED` | Evidence integrity compromised | External detection |
| `EXPIRED` | Lease expired before completion | Token expiry check |

**Terminal states:** `REPLAYABLE`, `FAILED`, `ROLLED_BACK`, `INVALIDATED`, `EXPIRED`
**Non-terminal states:** `CREATED`, `PLANNED`, `EXECUTING`, `VALIDATING`

### 6.2 Evidence Lifecycle

```
  GENERATED ──▶ ACTIVE ──▶ STALE ──┬─▶ ARCHIVED
                                   ├─▶ INVALIDATED
                                   └─▶ REPLAYABLE
```

| State | Meaning | Trigger |
|---|---|---|
| `GENERATED` | Evidence file written to disk | Execution completes |
| `ACTIVE` | Within retention period | Time-based |
| `STALE` | Past retention period | Retention engine scan |
| `ARCHIVED` | Moved to archive directory | Retention action: ARCHIVE |
| `INVALIDATED` | Integrity seal broken or quarantine | Corruption scan |
| `REPLAYABLE` | Snapshot captured, replay contract valid | Replay engine |

### 6.3 Token Lifecycle

```
  ISSUED ──▶ ACTIVE ──┬─▶ EXPIRED
                      └─▶ REVOKED
```

| State | Meaning | Trigger |
|---|---|---|
| `ISSUED` | Token created with signature | `CapabilityToken.__init__` |
| `ACTIVE` | Nonce registered, not expired, not revoked | After nonce registration |
| `EXPIRED` | Lease duration elapsed | `is_expired()` returns true |
| `REVOKED` | Token explicitly revoked (escalation, chain revocation) | `RevocationRegistry.revoke_token` |

---

## 7. Versioned Execution Contracts

### 7.1 Current Versions

| Component | Version | Notes |
|---|---|---|
| **Execution Substrate** | `5.1.0` | Main orchestrator version |
| **Replay Format** | `1.0.0` | `REPLAY_FORMAT_VERSION` in `deterministic-replay.py` |
| **Trust & Identity** | `5.2.0` | Phase 5 multi-agent engine |
| **Failure Recovery** | `3.0.0` | Phase 6 enterprise safety layer |
| **Storage Lifecycle** | `1.0.0` | Phase 4 survivability engine |
| **Governance Schema** | `5.1` | `DEFAULT_SCHEMA_VERSION` in `release-discipline.py` |
| **Semantic Version** | `5.1.0` | `DEFAULT_VERSION` in `release-discipline.py` |

### 7.2 Compatibility Guarantees

| Guarantee | Detail |
|---|---|
| **Substrate 5.x backward compatible with 5.0** | Minor version bumps add fields; old readers ignore unknown fields |
| **Replay format 1.x** | Major version 1 — no breaking changes within 1.x. Minor bumps add optional fields |
| **Governance schema 5.0 and 5.1** | Both schemas supported by substrate 5.1.0 |
| **Audit chain format** | Stable — JSONL with `chain_hash` linkage. No version field needed |
| **Nonce registry format** | Stable — JSONL with `nonce`, `token_id`, `registered_at`, `expires_at` |
| **Manifest JSON schema** | Fields may be added; required fields are: `execution_id`, `trust_tier`, `lifecycle_state`, `lifecycle_history`, `mutation_journal`, `replay_contract`, `integrity_seal` |

### 7.3 Replay Compatibility

The `ReplayCompatibilityVersion` class implements semantic version comparison:

- **Major mismatch** = breaking change, migration required
- **Minor upgrade** = new fields may be present; old replays still readable
- **Minor downgrade** = some fields may not be understood by older readers
- **Patch difference** = backward-compatible changes only

---

## 8. Internal Compatibility Guarantees

### 8.1 Backward Compatible (No Action Required)

| Change | Impact |
|---|---|
| Adding new optional fields to execution manifest | Old readers ignore unknown fields |
| Adding new trust tiers (if ranked > existing) | Existing tokens unaffected |
| Adding new CLI subcommands | Existing commands unchanged |
| Adding new evidence types | Existing evidence types unaffected |
| Increasing storage quotas | Existing evidence valid |
| Adding new normalization steps in `DeterminismNormalizer` | Existing snapshots still valid |
| Adding new diagnostic commands to `observability.py` | Existing commands unchanged |

### 8.2 Requires Migration

| Change | Migration Required |
|---|---|
| Adding required fields to execution manifest | Old manifests lack the field; migration must backfill defaults |
| Changing nonce registry format | Existing nonces must be converted or registry rebuilt |
| Changing audit chain hash algorithm | Entire chain must be rehashed or a new chain started |
| Changing integrity seal computation | Existing manifests have invalid seals; must be re-sealed |
| Changing trust tier ranking order | All existing tokens must be re-validated |
| Changing replay format major version | All existing snapshots must be migrated |
| Changing governance schema major version | All governance files must be updated |

### 8.3 Breaking Changes

| Change | Consequence |
|---|---|
| Removing required fields from execution manifest | Old replays cannot reconstruct execution state |
| Changing SHA-256 to a different hash algorithm | All existing seals, chains, and snapshots become unverifiable |
| Changing the frozen time constant | Deterministic replays will produce different results |
| Removing `READ_ONLY` tier | All existing `READ_ONLY` tokens become invalid |
| Changing the delegation narrowing rule (allowing escalation) | Existing delegation proofs become invalid |
| Changing the JSON serialization format (e.g., removing `sort_keys=True`) | All existing integrity seals become invalid |
| Removing the audit chain | Tamper-evident history is lost |

### 8.4 Module Dependency Graph

```
execution-substrate.py
  ├── substrate_security.py (hard dependency)
  ├── deterministic-replay.py (optional, for replay operations)
  └── compile-governance.py (optional, for governance index)

deterministic-replay.py
  ├── substrate_security.py (IntegritySeal, AuditChain, NonceRegistry)
  └── execution-substrate.py (manifest reading only)

trust-identity.py
  └── substrate_security.py (optional, for audit chain verification)

observability.py
  ├── execution-substrate.py (manifest reading only)
  ├── deterministic-replay.py (snapshot reading only)
  └── trust-identity.py (identity lineage reading only)

failure-recovery.py
  └── execution-substrate.py (manifest reading + repair writing)

storage-lifecycle.py
  └── (no module dependencies; operates on evidence directories directly)

portability-check.py
  └── (no module dependencies; platform-only checks)

release-discipline.py
  └── (no module dependencies; version management only)
```

**Key principle:** Only `execution-substrate.py` has write dependencies on
other modules' output. All other modules are read-only consumers of evidence
files, except `failure-recovery.py` which repairs manifests with explicit
backup-and-replace semantics.

---

*This document is versioned. When the substrate architecture changes, update
this file and bump its version. Cross-reference with `release-discipline.py`
for the authoritative substrate version number.*
