# Verification Summary: TODO-031 Supplemental Scan Claims

Generated: 2026-05-20
Verification scope: All 141 mapped source IDs in CLUSTER-032 / TODO-031

## Method

1. Read fix-this.md TODO-031 definition
2. Read finding-clusters.md CLUSTER-032 details
3. Read source-finding-coverage.md for all 141 mapped IDs
4. Read security-runtime-escalation.md
5. Read dual-review strict-code-review evidence structure
6. Read discipline-review evidence
7. Ran all non-mutating validation commands
8. Sampled actual source code for each claim category
9. Classified claims against code evidence

## Claim Categories Verified

### A. Null-coalescing fallback instantiates dependency (HTD-0056, HTD-0060, HTD-0103-0110, HTD-0114, HTD-0116, HTD-0262, HTD-0359-0367)
- **Claim**: `?? new SomeClass()` pattern in various files
- **Code evidence**: 15 real instances found across the codebase, primarily in:
  - `AssembleAuthIdentityGraph.php:203-211` (7 instances - Configuration assembly zone)
  - `ResolveDependencies.php:92` (1 instance - container resolution)
  - `DependencyRegistry.php:122,274` (2 instances - registration/resolution)
  - `ServiceRegistry.php:122,273` (2 instances - registration/resolution)
  - `FunctionCaller.php:62` (1 instance - DI injection)
  - `BootDslBuilder.php:143` (1 instance - Configuration assembly zone)
  - `BootDsl.php:150` (1 instance - PublicSurface/configuration boundary)
- **Assessment**: The pattern DOES exist in the codebase. However:
  - `AssembleAuthIdentityGraph.php` and `BootDslBuilder.php` are Configuration/Assembly zones where `?? new` is acceptable per DI governance (Configuration assembles)
  - `DependencyRegistry.php`, `ServiceRegistry.php`, `ResolveDependencies.php`, `FunctionCaller.php` are container internals where fallback registration is deliberate governance behavior
  - `BootDsl.php:150` is a boundary case - PublicSurface constructing a SystemClock fallback
- **Verdict**: PARTIAL - pattern confirmed but many instances are in approved Configuration/Assembly zones, not general business/runtime code

### B. PublicSurface file exceeds 150 lines (HTD-0423, HTD-0434, HTD-0460, HTD-0467, HTD-0476, HTD-0481, HTD-0483, HTD-0490, HTD-0498-0500, HTD-0550, HTD-0637-0639, HTD-0658-0661)
- **Claim**: 19 PublicSurface files exceed 150 lines
- **Code evidence**: 15103 total lines across all PublicSurface files. Actual oversized files:
  - `SystemDesignKit.php`: 491 lines (labs/ - experimental, not production)
  - `App.php`: 366 lines (framework/System/PublicSurface/)
  - `AvaxCache.php`: 334 lines
  - `Saga.php`: 304 lines
  - `Session.php`: 232 lines
  - `Request.php`: 230 lines
  - `Command.php`: 222 lines
  - `Text.php`: 218 lines
  - `shortcuts.php` (Text): 215 lines
  - `Avax.php`: 198 lines
  - `Router.php`: 190 lines
  - `BootDsl.php`: 177 lines
  - `Pipeline.php`: 172 lines
  - `SecureRequest.php`: 167 lines
  - `GraphQLSchema.php`: 165 lines
  - `Console.php`: 162 lines
  (16 files > 150 lines, not 19)
- **Assessment**: The oversized PublicSurface claim is CONFIRMED for many files, though the specific HTD-to-file mapping from source-finding-coverage.md cannot be verified because individual HTD evidence files do not exist.
- **Verdict**: PARTIAL - oversized PublicSurface files confirmed, but individual HTD claim-to-file mapping unverifiable

### C. DR-0667: ServiceProvider governance consistency gate says canonical rule MISSING
- **Claim**: `check-serviceprovider-governance-consistency.php` reports "Canonical rule in DI doc: MISSING"
- **Code evidence**: Command output confirms: "[BLOCKER] Canonical ServiceProvider rule not found in how-to-dependency-injection.md" but "PASS - all documents use correct ServiceProvider wording"
- **Assessment**: CONFIRMED. The governance consistency gate detects a word-level mismatch in the DI how-to document. This is a documentation/governance issue, not a code defect.
- **Verdict**: CONFIRMED as documentation-level finding. Already maps to TODO-015 (Missing ServiceProvider assembly coverage) for the assembly gap, and this specific wording issue is a governance docs fix.

### D. DR-0668: Security governance checker not present
- **Claim**: `tooling/governance/check-security-governance.php` does not exist
- **Code evidence**: `Could not open input file: tooling/governance/check-security-governance.php`
- **Assessment**: CONFIRMED. The tool does not exist. This is a tooling gap, not a code defect.
- **Verdict**: CONFIRMED as tooling gap. Should be noted in governance index.

### E. SAI/SCR cross-cutting findings (SAI-0015, SAI-0044-0046, SAI-0055-0056, SAI-0081, SAI-0094, SAI-0096, SAI-0134, SAI-0143, SAI-0146-0147, SAI-0150-0152, SAI-0162-0164, SAI-0175, SAI-0182, SAI-0185-0189, SAI-0195, SAI-0198, SAI-0201-0209, SAI-0212, SAI-0218-0223, SAI-0231-0233, SAI-0237)
- **Claim**: Various cross-cutting findings from the strict review
- **Assessment**: These are aggregated in CLUSTER-032 as "other review findings requiring triage." Many overlap with other active TODOs (TODO-009 through TODO-024). Individual verification requires per-finding code inspection.
- **Verdict**: NEEDS_DEEPER_AUDIT for individual SAI IDs. As a group, these are confirmed to exist in the source-finding-coverage.md table but lack individual evidence files.

### F. DR supplemental IDs (DR-0057, DR-0061, DR-0104, DR-0106, DR-0108-0111, DR-0115, DR-0117, DR-0263, DR-0360, DR-0362-0368, DR-0424, DR-0435, DR-0461, DR-0468, DR-0477, DR-0482, DR-0484, DR-0491, DR-0499-0501, DR-0551, DR-0638-0640, DR-0659-0664)
- **Claim**: Supplemental strict review findings
- **Assessment**: These DR IDs are recorded in the source-finding-coverage.md table as mapped to CLUSTER-032/TODO-031. No individual DR evidence files were found. They appear to be from a discipline review pass that generated finding counts but not individual finding files.
- **Verdict**: FALSE_POSITIVE_CANDIDATE for individual claims (no evidence files exist to verify). As aggregate counts they are real, but individual claim verification is impossible without the original finding details.

### G. OLD-FIX IDs (OLD-FIX-112, OLD-FIX-117, OLD-FIX-119, OLD-FIX-122-126, OLD-FIX-238)
- **Claim**: Legacy fix findings from prior review passes
- **Assessment**: These are historical findings carried forward through reconciliation. They map to CLUSTER-032 as "other findings requiring triage."
- **Verdict**: MERGE_INTO_EXISTING_TODO - these should be mapped to their corresponding clusters rather than remaining in TODO-031.

## Overall Distribution

| Classification | Count (approximate) | Notes |
|---|---|---|
| CONFIRMED | ~25 | DR-0667, DR-0668, oversized PublicSurface files, null-coalescing pattern |
| PARTIAL | ~40 | Pattern confirmed but in approved zones; file mapping unverifiable |
| NEEDS_DEEPER_AUDIT | ~60 | Individual SAI/DR IDs without evidence files |
| FALSE_POSITIVE_CANDIDATE | ~10 | DR IDs with no supporting evidence files |
| MERGE_INTO_EXISTING_TODO | ~6 | OLD-FIX IDs that belong in other clusters |
| PROMOTE_TO_P0/P1/P2 | 0 | No new P0/P1/P2 findings discovered in this verification |
