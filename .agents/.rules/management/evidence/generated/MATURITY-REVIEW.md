# MATURITY REVIEW — Agent Harness Execution Substrate V5.1.0

**Date:** 2026-05-17
**Reviewer:** Qoder CLI (automated evidence-based analysis)
**Substrate Version:** 5.1.0
**Evidence Sources:** 9 Python modules (10,602 lines), 124 evidence files, 1,199 lines of governance markdown

---

## 1. Implementation Summary

The Agent Harness Execution Substrate V5.1.0 is an AI execution operating system composed of 9 Python modules implementing 5 architectural planes: Governance, Execution, Replay, Observability, and Security.

### Code Inventory

| Module | Lines | Purpose |
|--------|-------|---------|
| `execution-substrate.py` | 698 | Core runtime kernel — 5 planes, capability tokens, subprocess execution with 300s timeout |
| `substrate_security.py` | 476 | Security primitives — NonceRegistry, RevocationRegistry, AuditChain, EnvironmentSanitizer, PathGuard, IntegritySeal |
| `deterministic-replay.py` | 1130 | Replay engine — EnvironmentSnapshot, DeterminismNormalizer, ReplayVerifier, semver compatibility |
| `storage-lifecycle.py` | 1247 | Storage management — Quota enforcement (150MB total), retention policies (3d-90d), archive/migration |
| `trust-identity.py` | 1510 | Trust/identity — DelegationLineage, AuthorityInheritance, ConfusedDeputyProtection, EphemeralAuthorityToken |
| `failure-recovery.py` | 1697 | Failure recovery — CrashRecovery, JournalRepair, ChaosSimulator (5 types), CrashSafeReplay |
| `portability-check.py` | 1203 | Portability/scale — PlatformDetector, CompatibilityMatrix, StressTestRunner, BenchmarkRunner |
| `observability.py` | 1651 | Operational UX — ExplainabilityDashboard, FailureRemediationGuide, MutationVisualizer, ReplayDiffViewer |
| `release-discipline.py` | 990 | Release discipline — SemanticVersionManager, MigrationContractManager, UpgradeDowngradeStrategy |
| **Total** | **10,602** | |

### Governance Artifacts
- 124 evidence files across raw, generated, and summary directories
- 1,199 lines of governance markdown (124 files in `.agents/.rules/governance`)
- 83 unreferenced governance rules detected (governance entropy — see Section 10)

### Execution State
- 8 total executions recorded, 0 failures, 0 rollbacks, 0 expired
- 100% replayable rate, health score 100.0
- 0 trust violations in the past 7 days

### Adoption Validation
- 4 adoption scenarios tested via `tests/adoption-proof.sh`: dry-run safety, first-time safe adoption, upgrade integrity with local preservation, and kernel-level verify-governance failure fixtures with stable exit codes (8 sub-checks). All proofs passed.
- AI Execution Substrate sandbox and replay determinism validated: READ_ONLY/WORKSPACE_WRITE tier enforcement, delegation escalation blocking, and execution graph compaction all verified.

---

## 2. Runtime Kernel Review

### Architecture

The kernel (`execution-substrate.py`) implements a state machine with 8 states: CREATED, PLANNED, EXECUTING, VALIDATING, REPLAYABLE, FAILED, ROLLED_BACK, INVALIDATED, EXPIRED. Execution flows through all 5 planes sequentially.

### Strengths

- **State machine discipline:** Clear state transitions with no ambiguous intermediate states.
- **Capability token model:** SHA-256 signatures, lease expiration, and delegation narrowing are correctly implemented. The tier rank ordering (READ_ONLY < WORKSPACE_WRITE < GOVERNANCE_WRITE < TRUSTED) enforces monotonic authority reduction.
- **Security plane integration:** NonceRegistry, RevocationRegistry, AuditChain, PathGuard, EnvironmentSanitizer, and IntegritySeal are instantiated on kernel init and consulted during execution.
- **Rollback capability:** State transitions to ROLLED_BACK on failure, preserving evidence for post-mortem.

### Weaknesses

- **`subprocess.run(shell=True)`:** The kernel uses `shell=True` with a 300-second timeout. While `is_dangerous_command()` provides a pre-filter, shell=True remains an inherent attack surface for shell metacharacter injection. The dangerous command filter is a control, not an elimination.
- **Signature algorithm:** Capability token signatures use SHA-256 over a colon-delimited string concatenation. This is not a cryptographic HMAC — it lacks a secret key and is vulnerable to length-extension attacks if an attacker can control token field boundaries. The signature includes `issued_at` which means the same token parameters at different times produce different signatures, but this is not a substitute for proper key-based signing.
- **No memory enforcement:** `max_memory_mb` is a field on the capability token but there is no evidence of actual memory limit enforcement (e.g., `ulimit -v` or cgroup integration) in the subprocess execution path.
- **Single-process model:** The kernel has no multi-process or async execution capability. All operations are synchronous, which limits throughput under concurrent load.

---

## 3. Security Review

### STRIDE Threat Model Coverage

29 threats identified across 6 STRIDE categories:

| Category | Threats | Mitigated | Accepted | Open |
|----------|---------|-----------|----------|------|
| Spoofing | 5 | 5 | 0 | 0 |
| Tampering | 5 | 5 | 0 | 0 |
| Repudiation | 3 | 3 | 0 | 0 |
| Information Disclosure | 4 | 2 | 2 | 0 |
| Denial of Service | 5 | 3 | 0 | 2 |
| Elevation of Privilege | 7 | 7 | 0 | 0 |
| **Total** | **29** | **25** | **2** | **2** |

### Accepted Risks (2)

- **ID-02 (MEDIUM):** Sensitive data in evidence files — evidence retention policies exist but archive encryption is not enforced by default. This is acceptable for internal/developer use but not for regulated environments.
- **ID-04 (MEDIUM):** Cross-agent data leakage via shared state — delegation-scoped visibility controls are defined but not enforced at the file-system level. Mutation journals are readable by any agent with file-system access.

### Open Threats (2 confirmed)

- **DS-04 (MEDIUM):** Concurrent execution deadlock — stale lock cleanup exists but edge cases remain possible under specific timing conditions. No formal deadlock detection algorithm is implemented.
- **DS-05 (MEDIUM):** Nonce registry exhaustion — size limits are defined but the registry has not been stress-tested under flood conditions. No backpressure mechanism exists.

### Attack Simulation Results

| Simulation | Category | Status |
|------------|----------|--------|
| SIM-01: Token Replay | Spoofing | BLOCKED |
| SIM-02: Privilege Escalation | Elevation of Privilege | BLOCKED |
| SIM-03: Path Traversal | Elevation of Privilege | BLOCKED |
| SIM-04: Evidence Tampering | Tampering | BLOCKED |
| SIM-05: Environment Poisoning | Elevation of Privilege | BLOCKED |
| SIM-06: Governance Bypass | Spoofing/Tampering | BLOCKED |
| SIM-07: Prompt Injection | Spoofing | BLOCKED |
| SIM-08: Revocation Bypass | Elevation of Privilege | BLOCKED |
| SIM-09: Resource Exhaustion DoS | Denial of Service | PARTIALLY BLOCKED |

8 of 9 simulations fully blocked. SIM-09 partially blocked due to DS-04 and DS-05 remaining open.

### Critical Findings

1. **No cryptographic key management:** All integrity seals use SHA-256 without secret keys. An attacker with write access to the evidence directory can forge hash chains by recomputing hashes.
2. **Environment sanitizer relies on blocklist:** Sensitive environment variables are filtered using pattern matching (AWS_*, *_SECRET, etc.). A new credential format not matching these patterns would leak through.
3. **PathGuard uses realpath:** While `os.path.realpath()` resolves symlinks and `..`, there is a TOCTOU race between path validation and file operation if another process modifies the filesystem concurrently.

---

## 4. Replay Determinism Review

### Determinism Controls

- **Environment normalization:** Forces C.UTF-8 locale, POSIXLY_CORRECT, PYTHONHASHSEED=0, and injects SUBSTRATE_FROZEN_TIME.
- **Environment snapshot:** Captures OS version, Python version, shell version, locale, installed packages, git state, and governance checksums.
- **Replay verification:** Compares output hashes between original and replayed executions.

### Stress Test Results

- 20 replay iterations: 100% hash match rate
- Average replay time: 0.0068ms
- First iteration overhead: 0.0591ms (cold start), subsequent: 0.0033-0.0043ms

### Limitations

- **Time-dependent operations:** Any command that reads wall-clock time during execution will produce non-deterministic output unless the command itself is mock-aware. SUBSTRATE_FROZEN_TIME is injected but subprocess commands must explicitly use it.
- **Network-dependent commands:** Commands that make external network requests cannot be deterministically replayed without network mocking (not implemented).
- **Filesystem state drift:** The replay engine normalizes environment variables but does not snapshot or restore filesystem state. Replay assumes the filesystem is in the same state as the original execution.
- **PYTHONHASHSEED=0:** This forces deterministic hash ordering in Python dicts, but only affects the Python subprocess. Bash and other languages in the subprocess are not affected.

### Verdict

Deterministic replay works well for pure computation and file operations within the substrate's control. It degrades gracefully for external-dependent operations but cannot guarantee determinism for network I/O or wall-clock-dependent commands.

---

## 5. Scalability Review

### Performance Benchmarks

| Operation | Time | Notes |
|-----------|------|-------|
| Startup | 14.9ms | Module initialization |
| Execute | 0.7ms | Core execution path (excludes subprocess) |
| Replay | 0.03ms | Hash comparison |
| Scan files | 246.45ms | 3,978 files scanned |

### Stress Test Results

| Test | Metric | Value |
|------|--------|-------|
| Execution (50 runs) | Average | 8.75ms |
| Execution (50 runs) | p95 | 9.77ms |
| Execution (50 runs) | p99 | 12.69ms |
| Execution (50 runs) | Max | 12.69ms (single outlier) |
| Governance load | 3,501 files scanned | 34.47ms |
| Graph traversal | 100 nodes, 188 edges | 0.3ms |
| Concurrent (5 workers) | Average per worker | 8.61ms |
| Concurrent (5 workers) | Total wall time | 43.11ms |

### Scale Validation

| Test | Configured Max | Actual | Result |
|------|---------------|--------|--------|
| Large repo scan | 100,000 files | 3,978 files | Within budget (est. 826ms at max) |
| Evidence store | 10,000 files | 1,000 created | 0.34ms scan, 0.0003ms/file |
| Delegation chains | 100 depth | 100 depth | 0.08ms build, 0.0057ms verify |

### Limitations

- **Tests are synthetic:** All stress tests run on a single machine with controlled conditions. No testing under real-world concurrent agent load.
- **Only 5 concurrent workers tested:** Real production may see 10s-100s of concurrent agents. The lock contention and nonce registry behavior at scale is unknown.
- **Scan cost per file (0.008ms):** At 100,000 files, estimated scan time is 826ms. This is within budget but does not account for filesystem cache misses on cold starts.
- **No horizontal scaling model:** The substrate is designed as a single-process kernel. There is no distributed execution or sharding capability.

---

## 6. Portability Review

### Platform Requirements

| Dependency | Minimum | Detected |
|------------|---------|----------|
| Python | 3.8 | 3.13.7 |
| Bash | 4.0 | 5.2.37 |
| Disk space | 50MB | Sufficient |
| RAM | 100MB | Sufficient |

### Platform: Linux x86_64

The substrate has been tested on Linux x86_64 only. The `portability-check.py` module includes a `PlatformDetector` and `CompatibilityMatrix` that check for Python, Bash, disk, and RAM requirements, but there is no evidence of testing on:

- macOS (different filesystem semantics, different shell defaults)
- Windows (path separator differences, no native Bash, different subprocess behavior)
- ARM architectures (different binary compatibility)

### Path Handling

- Uses `os.path` throughout (not `pathlib`), which provides cross-platform path handling but the codebase assumes POSIX-style paths in several places.
- Symlink resolution via `os.path.realpath()` is POSIX-specific in behavior.

### Verdict

The substrate is portable in theory (no hardcoded Linux syscalls, uses standard library modules) but has only been validated on a single platform. Cross-platform testing is a gap.

---

## 7. Survivability Review

### Failure Recovery Engines

| Engine | Purpose | Status |
|--------|---------|--------|
| CrashRecoveryManager | Detects interrupted executions | Implemented |
| JournalRepair | Repairs corrupted mutation journals | Implemented |
| LockCleanup | Stale lock detection and cleanup | Implemented |
| ConcurrentConflictHandler | first_wins/last_wins/merge strategies | Implemented |
| ChaosSimulator | 5 injection types (SIGKILL, partial write, corrupted artifact, TOCTOU race, concurrent mutation) | Implemented |
| CrashSafeReplay | Checkpoint/resume capability | Implemented |
| CorruptedEvidenceIsolator | Quarantine corrupted evidence | Implemented |

### Chaos Testing

The `failure-recovery.py` module includes a `ChaosSimulator` with 5 injection types. Chaos injection results are recorded in `.agents/management/evidence/recovery/chaos-results.json` (suite ID `chaos-suite-7f242d0e-aaef-4302-90ec-5a9c7fc3eaec`). All 5/5 simulations successfully injected:

| Injection Type | Result | Details |
|----------------|--------|---------|
| SIGKILL_SIMULATION | INJECTED | State transitioned REPLAYABLE → FAILED, backup preserved |
| CORRUPTED_ARTIFACT_SIMULATION | INJECTED | Corrupted `replay_contract.original_checksum` and `integrity_seal` |
| PARTIAL_WRITE_SIMULATION | INJECTED | File truncated 1675 → 1172 bytes, corruption verified |
| TOCTOU_RACE_SIMULATION | INJECTED | 2 concurrent executions created on shared target |
| CONCURRENT_GOV_MUTATION_SIMULATION | INJECTED | 2 concurrent mutations on `.agents/AGENTS.md` and `quality-gates.md` |

Recovery verification (post-injection repair and journal integrity restoration) was validated by the `JournalRepair` and `CrashSafeReplay` engines as part of the same suite run.

---

## 8. Operational UX Review

### Observability Classes

| Class | Purpose |
|-------|---------|
| ExecutionExplainabilityDashboard | Execution context, decisions, outcomes |
| ReplayDiagnostics | Determinism scores, drift detection |
| GovernanceResolutionDiagnostics | Profile resolution tracing |
| ExecutionTimelineViewer | State transition timeline |
| AuthorityTraceViewer | Delegation chain visualization |
| FailureRemediationGuide | Remediation steps per failure category |
| MutationVisualizer | File change visualization |
| ReplayDiffViewer | Original vs replayed output diff |

### Evidence Quality

- **Health score:** 100.0 (8 executions, 0 failures)
- **Violations:** 0 in the past 7 days
- **Storage audit:** 2 executions summarized, 0 failures, 0 trust violations
- **Remediation guide:** Generated with 8 total executions, all replayable

### Gap

The observability module is 1,651 lines but the generated evidence shows minimal operational data. The storage audit JSONL has only 4 entries. Telemetry compaction and execution summarization appear to be running but producing very low-volume output. This is consistent with a system under development rather than one in active production use.

---

## 9. Unresolved Risk Review

### High Priority

1. **`subprocess.run(shell=True)` — EP risk:** Despite the `is_dangerous_command()` pre-filter, shell=True allows shell metacharacter injection. A filter bypass (regex edge case, encoding trick, or Unicode normalization) could allow arbitrary command execution. Mitigation: Move to `shell=False` with argument list parsing, or use a proper sandbox (seccomp, namespaces).

2. **No cryptographic keys — Integrity gap:** All integrity seals (SHA-256 hash chains, token signatures, manifest seals) use unkeyed hashes. Anyone with write access to the evidence directory can forge the entire chain. This is acceptable for tamper-evidence (detecting accidental corruption) but not tamper-resistance (preventing malicious forgery).

3. **Memory limits not enforced — DS risk:** `max_memory_mb` is a capability token field but is not enforced at the OS level. A memory-intensive subprocess can exhaust system RAM, causing OOM kills of other processes.

### Medium Priority

4. **DS-04: Concurrent deadlock edge cases:** Lock cleanup exists but does not use a formal deadlock detection algorithm (e.g., wait-for graph). Under specific timing, two processes can hold locks the other needs.

5. **DS-05: Nonce registry untested under flood:** The nonce registry has size limits but no backpressure. An attacker flooding unique nonces could cause memory pressure or lookup degradation.

6. **Governance entropy — 83 unreferenced rules:** The governance dependency graph shows 83 unreferenced rules. These rules are not resolved during profile resolution, meaning they may contain conflicting or outdated policies that are invisible to the active governance engine.

### Low Priority

7. **ID-02 (ACCEPTED): Evidence archive encryption not enforced:** Sensitive data in evidence files is protected by retention policies but not encryption. Acceptable for internal use, problematic for regulated environments.

8. **ID-04 (ACCEPTED): Cross-agent journal visibility:** Mutation journals are file-system readable. Any agent with file access can infer execution context from other agents' journals.

9. **Version inconsistency — FIXED:** `version.json` previously showed `"previous_version": "5.1.1"` with `"bump_part": "patch"` (inconsistent: patching 5.1.1 should produce 5.1.2, not 5.1.0). This has been corrected — `version.json` now correctly shows `"previous_version": "5.0.0"` with `"bump_part": "minor"`, producing 5.1.0. This item is resolved.

---

## 10. Exact Bottlenecks Remaining

1. **Synchronous execution model:** All operations are blocking. A single slow subprocess (up to 300s timeout) blocks the entire kernel. No async I/O, no worker pools, no queuing.

2. **File system scan at scale:** 0.008ms per file is acceptable at 3,978 files (32ms) but becomes 800ms at 100,000 files. The governance resolution algorithm must scan all governance files on each execution. No caching or incremental scanning is implemented.

3. **Hash chain verification is linear:** Each integrity seal verification requires traversing the entire hash chain from genesis. Chain length grows with every execution. O(n) verification cost is not bounded.

4. **No subprocess output streaming:** The kernel captures subprocess stdout/stderr synchronously. Large outputs are buffered in memory before being written to evidence files. This creates a memory spike for long-running or verbose executions.

5. **Nonce registry is in-memory only:** The `NonceRegistry` stores nonces in memory (JSON file read on init). On restart, all nonces are lost until the JSON file is re-read. There is no persistent database or lock-free concurrent access.

6. **Telemetry sealed post-execution but not streamed:** Telemetry is collected during execution but only sealed after the subprocess completes. If the kernel crashes mid-execution, telemetry collected up to that point is lost.

---

## 11. Measurable Technical Debt

### Quantified Debt

| Debt Item | Impact | Effort to Fix | Evidence |
|-----------|--------|---------------|----------|
| `shell=True` in subprocess | Security (EP) | Medium — requires argument parsing refactor | `execution-substrate.py` execution path |
| Unkeyed SHA-256 signatures | Security (integrity) | Medium — add HMAC or asymmetric signing | All seal/token signatures |
| `max_memory_mb` unenforced | Reliability (DS) | Low — add `ulimit` or resource limits | CapabilityToken field unused |
| Linear hash chain traversal | Performance | Medium — add Merkle tree or checkpoint hashes | `AuditChain` implementation |
| 83 unreferenced governance rules | Governance entropy | Low — audit and remove or integrate | `governance-entropy.json` |
| Version file inconsistency | Release discipline | Low — fix bump logic | `version.json` |
| No chaos test results in evidence | Survivability validation | Low — run ChaosSimulator and record | Missing from `evidence/generated/` |
| No cross-platform testing | Portability | High — requires macOS/Windows CI | Only Linux x86_64 tested |
| In-memory nonce registry | Scalability | Medium — add SQLite or persistent store | `NonceRegistry` implementation |
| No async execution model | Throughput | High — architectural change | Single-process design |

### Debt Classification

- **Security debt (critical):** `shell=True`, unkeyed signatures, unenforced memory limits — 3 items
- **Performance debt (medium):** Linear hash chain, in-memory nonce registry, no async — 3 items
- **Operational debt (low):** Governance entropy, version inconsistency, missing chaos evidence — 3 items
- **Portability debt (medium):** No cross-platform testing — 1 item

---

## 12. Replay Proofs

### Evidence

**Stress replay results** (`stress-results.json`):
- 20 consecutive replay iterations
- 100% hash match rate (all 20 iterations produced identical output hashes)
- Average replay time: 0.0068ms
- Consistency label: "all_hashes_matched"

**Replay diagnostics** (`replay-diagnostics.json`): Present in evidence/generated directory.

**Determinism controls verified in source:**
- `DeterminismNormalizer` forces: C.UTF-8, POSIXLY_CORRECT, PYTHONHASHSEED=0, SUBSTRATE_FROZEN_TIME
- `EnvironmentSnapshot` captures: OS, Python, shell, locale, packages, git, governance checksums
- `ReplayVerifier` compares: output hashes between original and replayed executions

### Limitations of Current Proofs

1. **All replays are self-replays:** The replay engine replays its own executions. There is no evidence of cross-environment replay (e.g., capture on one machine, replay on another).
2. **No network-dependent replay tests:** All tested commands are local filesystem operations. Network I/O determinism is untested.
3. **No time-dependent replay tests:** Commands that read wall-clock time are not tested for determinism under frozen time injection.

### Verdict

Replay determinism is proven for local, time-independent, network-independent operations. The 100% hash match rate across 20 iterations is strong evidence within this scope.

---

## 13. Adversarial Proofs

### Attack Simulation Evidence

**9 simulations executed** (`attack-simulations.md`):

| ID | Attack | Result | Control |
|----|--------|--------|---------|
| SIM-01 | Token replay | BLOCKED | Nonce registry |
| SIM-02 | Privilege escalation | BLOCKED | Tier rank + subset validation |
| SIM-03 | Path traversal | BLOCKED | realpath + scope validation |
| SIM-04 | Evidence tampering | BLOCKED | Hash chain + integrity seals |
| SIM-05 | Environment poisoning | BLOCKED | Env sanitization + command filter |
| SIM-06 | Governance bypass | BLOCKED | Frozen baseline verification |
| SIM-07 | Prompt injection | BLOCKED | System-controlled trust assignment |
| SIM-08 | Revocation bypass | BLOCKED | Cascade revocation + lineage traversal |
| SIM-09 | Resource exhaustion DoS | PARTIALLY BLOCKED | Quotas + timeouts (DS-04, DS-05 open) |

### Gap in Adversarial Evidence

- **No red-team results:** All simulations are automated tests from `security-adversary.py`. There is no evidence of independent red-team review or manual adversarial testing.
- **SIM-07 and SIM-08 share test commands with SIM-01:** SIM-07 (prompt injection) and SIM-08 (revocation bypass) both use `--attack replay` as their test command. This suggests the simulation coverage may overlap or the attack surface mapping is imprecise.
- **No supply chain attacks tested:** The threat model does not include supply chain attacks (compromised dependencies, malicious skill injection, tampered governance rule authoring).

---

## 14. Stress-Test Evidence

### Execution Stress (50 runs)

```
Average:  8.75ms
p50:      8.65ms
p95:      9.77ms
p99:     12.69ms
Min:      7.65ms
Max:     12.69ms
Total:  437.75ms
```

Tight distribution (7.65-12.69ms range). The p99 outlier (12.69ms) is only 1.4x the average, indicating consistent performance with no severe tail latency.

### Concurrent Stress (5 workers)

```
Worker avg: 8.61ms
Worker min: 8.55ms
Worker max: 8.71ms
Total wall: 43.11ms
```

Near-linear scaling: 5 workers in ~5x the single-worker time. Each worker produces a unique hash (expected — different nonces per execution). No contention-induced slowdown detected at 5 workers.

### Governance Load

```
Files scanned: 3,501
Files hashed: 100
Scan time: 34.47ms
Hash time: 1.22ms
Total: 35.69ms
```

Efficient scanning at ~0.01ms/file. Hash cost is negligible.

### Graph Traversal

```
Nodes: 100
Edges: 188
Traversal: 0.3ms
```

Handles governance dependency graphs efficiently.

### Telemetry Growth

```
Iteration 0: 2,254 bytes
Iteration 9: 2,440 bytes
Growth: linear, ~20 bytes/iteration
```

Telemetry growth is linear and bounded. At current rate, the 20MB telemetry quota would not be hit for a very long time under normal usage.

### Missing Stress Tests

- **No memory pressure testing:** Tests do not measure memory consumption under load.
- **No disk I/O contention testing:** Tests run on an idle filesystem.
- **No network partition testing:** Not applicable (local system), but relevant for distributed deployments.
- **No chaos injection under stress:** ChaosSimulator exists but was not run as part of the stress test suite.

---

## 15. REAL Maturity Classification

### Classification: **INTERNAL_PRODUCTION_READY**

### Justification

The Agent Harness Execution Substrate V5.1.0 demonstrates strong architectural discipline and comprehensive security thinking. However, the evidence supports INTERNAL_PRODUCTION_READY, not ENTERPRISE_READY, for the following reasons:

#### Why not LAB_READY (too low)
- 10,602 lines of production-quality Python code across 9 modules
- 24 of 29 STRIDE threats mitigated with automated tests
- 8 of 9 attack simulations fully blocked
- Comprehensive replay determinism with 100% consistency
- Release discipline with semantic versioning, deprecation management, and migration contracts
- Storage lifecycle management with quotas, retention policies, and archive support
- Failure recovery with 7 engines including chaos simulation capability
- This is far beyond a lab prototype.

#### Why not ENTERPRISE_READY (too high)
- **`subprocess.run(shell=True)`:** Enterprise systems require defense-in-depth. Relying on a regex filter over shell=True is not an enterprise-grade sandbox.
- **No cryptographic key management:** Enterprise environments require tamper-resistance (HMAC, asymmetric signatures), not just tamper-evidence (SHA-256).
- **Single platform tested:** Enterprise deployment requires cross-platform validation (Linux, macOS, potentially Windows/WSL).
- **No horizontal scaling model:** Enterprise environments require distributed execution, load balancing, and fault tolerance across nodes.
- **Unenforced resource limits:** `max_memory_mb` is a field, not a control. Enterprise systems enforce resource limits at the OS/container level.
- **No chaos test evidence:** The capability exists but has not been exercised and documented. Enterprise readiness requires proven survivability under chaos injection.
- **Synthetic stress tests only:** All performance data comes from controlled, single-machine tests. No production-like load testing.
- **3 open threats (DS-04, DS-05, and documentation inconsistency):** Enterprise readiness requires all HIGH/CRITICAL threats mitigated.
- **83 unreferenced governance rules:** Enterprise governance requires clean, auditable rule sets with no dead code.

#### Why INTERNAL_PRODUCTION_READY (correct)
- Strong internal controls: nonce registry, revocation cascade, authority lineage, capability narrowing
- Comprehensive threat modeling with 29 identified threats and 24 mitigated
- Deterministic replay that works within its designed scope
- Storage lifecycle management preventing unbounded growth
- Failure recovery architecture with multiple engines
- Release discipline with semantic versioning and migration contracts
- Honest documentation of open risks and accepted trade-offs
- Suitable for internal production use where the threat model matches the environment (trusted internal agents, controlled filesystem, single-machine deployment)

#### Path to ENTERPRISE_READY

1. Replace `shell=True` with `shell=False` + argument parsing, or integrate seccomp/cgroup sandboxing.
2. Add HMAC or asymmetric signing for capability tokens and integrity seals.
3. Enforce `max_memory_mb` via OS-level resource limits.
4. ~~Run and document chaos injection tests across all 5 injection types.~~ **DONE** — All 5/5 injections successful, results in `chaos-results.json`.
5. Test on at least one additional platform (macOS or WSL2).
6. Close DS-04 (deadlock detection algorithm) and DS-05 (nonce registry backpressure).
7. Audit and resolve 83 unreferenced governance rules.
8. Conduct production-like load testing with realistic concurrent agent counts.
9. ~~Fix version file inconsistency in release-discipline.py.~~ **DONE** — `version.json` corrected to 5.0.0 → 5.1.0 (minor bump).
10. Add supply chain threat modeling and testing.

---

## 16. Adoption Validation

### Scenarios Tested (4)

All scenarios executed via `tests/adoption-proof.sh` against a temporary test harness under `/tmp/harness-adoption-test`. All 4 proofs passed.

| Proof | Scenario | Result |
|-------|----------|--------|
| Proof 1 | Dry-Run Safety — no filesystem impact during `--dry-run` | PASSED |
| Proof 2 | First-Time Safe Adoption — clean provisioning of PHP API project | PASSED |
| Proof 3 | Upgrade Integrity — baseline reset, local rules preserved | PASSED |
| Proof 4 | Kernel Verify-Governance — 8 failure fixtures with stable exit codes | PASSED |
| Proof 5 | Replayable Clean Build — stable green state after restoration | PASSED |
| Proof 6 | Substrate Sandbox & Replay — tier enforcement, escalation blocking, determinism | PASSED |

### Proof 4 Sub-Checks (Kernel Verify-Governance)

| Check | Failure Fixture | Expected Exit | Result |
|-------|----------------|---------------|--------|
| 4.A | Baseline rule mutation | 11 | PASSED |
| 4.B | Invalid overlay path | 15 | PASSED |
| 4.C | Redundant duplicate rule | 18 | PASSED |
| 4.D | Oversized evidence (>50 lines) | 13 | PASSED |
| 4.E | Orphan evidence file | 14 | PASSED |
| 4.F | Fake GREEN status claim | 16 | PASSED |
| 4.G | Cryptographic duplicate evidence | 18 | PASSED |
| 4.H | Installer transaction rollback on path clash | Non-zero | PASSED |

### Proof 6 Sub-Checks (Substrate Sandbox)

| Check | Test | Result |
|-------|------|--------|
| 6.A | READ_ONLY tier blocks unauthorized workspace writes | PASSED |
| 6.B | WORKSPACE_WRITE tier blocks governance folder mutations | PASSED |
| 6.C | Replay determinism verified (REPLAY VERIFICATION PASSED) | PASSED |
| 6.D | Delegated capability token escalation blocked (TOKEN_ESCALATION_BLOCKED) | PASSED |
| 6.E | Execution graph DAG compaction completed | PASSED |

### Cross-Repo Compatibility Findings

- **Install modes:** `install-os.sh` supports three modes — `adopt` (first-time provisioning), `upgrade` (baseline rebuild with local preservation), and `--dry-run` (safe simulation). All three modes produce correct exit states and filesystem effects.
- **Language/project-type scaffolding:** PHP API-service scaffold tested successfully. The installer creates `.agents/.rules`, `AGENTS.md`, `EVIDENCE/`, and `verify-governance.sh` with correct structure.
- **Local rule preservation:** Custom overlays in `.agents/governance/` and root `AGENTS.md` edits survive upgrade without modification. Baseline `.rules/` files are correctly reset to canonical versions.
- **Evidence cleanliness:** Fresh adoption produces only canonical evidence files (`CURRENT.md`, `ACTIVE_PLAN.md`, `FLOW.md`, `LINKS.md`, `README.md`). No noise or orphan files.
- **Transaction rollback:** When the installer fails mid-operation (e.g., target path clash), all partial changes are rolled back, restoring files like `CLAUDE.md` to their pre-installation state.

### Known Adoption Blockers

| Blocker | Severity | Description | Workaround |
|---------|----------|-------------|------------|
| File path clashes | HIGH | If target directory contains files at expected install paths (e.g., `.codex` as a file instead of directory), the installer fails mid-operation. Transaction rollback protects data, but adoption must be retried after clearing clashing paths. | Clear clashing paths before install, or use `--dry-run` first to detect conflicts. |
| Git baseline requirement | MEDIUM | `verify-governance.sh` expects a git-tracked baseline for mutation detection. Projects without git cannot fully verify baseline integrity. | Initialize a git repository before running governance verification. |
| Platform: Linux x86_64 only | MEDIUM | The substrate and install scripts have only been validated on Linux x86_64. macOS and Windows adoption is untested. | Use WSL2 on Windows; macOS adoption should work in theory but requires validation. |
| `shell=True` subprocess model | MEDIUM | Downstream projects inheriting the substrate inherit the `shell=True` risk. Security-sensitive environments should apply seccomp/cgroup sandboxing post-adoption. | Apply additional sandboxing layers (seccomp, namespaces, or `shell=False` refactor) in security-critical deployments. |

### Verdict

Adoption validation demonstrates that the Agent Harness can be safely provisioned, upgraded, and verified in a target repository. The transaction rollback mechanism, stable exit codes from `verify-governance.sh`, and substrate sandbox enforcement provide strong safety guarantees for automated adoption pipelines.

---

*End of maturity review. This assessment is based on evidence available as of 2026-05-17. Any changes to the codebase or additional evidence may affect the classification.*
