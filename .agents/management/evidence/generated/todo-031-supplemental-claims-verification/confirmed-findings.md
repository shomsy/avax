# Confirmed Findings: TODO-031

## CF-001: DR-0667 - ServiceProvider Governance Consistency Wording Gap
- **Source**: DR-0667
- **Severity**: MEDIUM
- **Claim**: `check-serviceprovider-governance-consistency.php` reports canonical rule MISSING in DI how-to
- **Evidence**: Command output: `[BLOCKER] Canonical ServiceProvider rule not found in how-to-dependency-injection.md`
- **Status**: CONFIRMED
- **Nature**: Documentation/governance consistency issue, not a code defect
- **Maps to**: TODO-015 (Missing ServiceProvider assembly) for the assembly gap; governance wording fix is separate

## CF-002: DR-0668 - Security Governance Checker Missing
- **Source**: DR-0668
- **Severity**: LOW
- **Claim**: `tooling/governance/check-security-governance.php` does not exist
- **Evidence**: `Could not open input file: tooling/governance/check-security-governance.php`
- **Status**: CONFIRMED
- **Nature**: Tooling gap - a planned governance checker has not been implemented
- **Action**: Should be noted in governance index as PLANNED/NOT IMPLEMENTED

## CF-003: Oversized PublicSurface Files (subset of HTD/SCR claims)
- **Sources**: HTD-0423, HTD-0434, HTD-0460, HTD-0467, HTD-0476, HTD-0481, HTD-0483, HTD-0490, HTD-0498, HTD-0499, HTD-0500, HTD-0550, HTD-0637, HTD-0638, HTD-0639, HTD-0658, HTD-0659, HTD-0660, HTD-0661
- **Severity**: MEDIUM to HIGH (depending on file)
- **Claim**: PublicSurface files exceed 150-line threshold
- **Evidence**: `wc -l` confirms 16 files exceed 150 lines:
  - `SystemDesignKit.php`: 491 (labs/, experimental)
  - `App.php`: 366 (framework)
  - `AvaxCache.php`: 334
  - `Saga.php`: 304
  - `Session.php`: 232
  - `Request.php`: 230
  - `Command.php`: 222
  - `Text.php`: 218
  - `shortcuts.php` (Text): 215
  - `Avax.php`: 198
  - `Router.php`: 190
  - `BootDsl.php`: 177
  - `Pipeline.php`: 172
  - `SecureRequest.php`: 167
  - `GraphQLSchema.php`: 165
  - `Console.php`: 162
- **Status**: CONFIRMED for these files
- **Nature**: Governance deviation - PublicSurface should be small and delegate
- **Note**: Individual HTD-to-file mapping unverifiable (no individual HTD evidence files exist), but the overall claim category is confirmed

## CF-004: Null-Coalescing Fallback Instantiation Pattern Exists
- **Sources**: HTD-0056, HTD-0060, HTD-0103-0110, HTD-0114, HTD-0116, HTD-0262, HTD-0359-0367
- **Severity**: MEDIUM to HIGH (depending on zone)
- **Claim**: `?? new SomeClass()` pattern used for fallback construction
- **Evidence**: 15 real instances found:
  - Configuration/Assembly zone (acceptable): `AssembleAuthIdentityGraph.php` (7), `BootDslBuilder.php` (1)
  - Container internals (deliberate governance): `DependencyRegistry.php` (2), `ServiceRegistry.php` (2), `ResolveDependencies.php` (1), `FunctionCaller.php` (1)
  - Boundary case: `BootDsl.php:150` (1)
- **Status**: CONFIRMED pattern exists
- **Nature**: Most instances are in approved Configuration/Assembly zones. The pattern is legitimate in those zones per DI governance rules.

## CF-005: Runtime Composition Leaks (direct instantiation)
- **Sources**: SAI-0152 (primary for CLUSTER-032)
- **Severity**: HIGH
- **Claim**: `class_exists()+new` and dynamic `new $class` patterns
- **Evidence**: `check-direct-instantiation.php` found many instances across components/Application/Cache
- **Status**: CONFIRMED
- **Maps to**: TODO-004 (runtime class loading) and TODO-014 (constructor bloat/default parameters)
