# TODO Mapping: TODO-031 Supplemental Claims

## Claims that map to existing active TODOs

### Map to TODO-004 (Runtime class loading / dynamic instantiation)
- SAI-0152 (primary CLUSTER-032 finding)
- All `class_exists()+new` and dynamic `new $class` findings in CLUSTER-032
- Rationale: These are runtime composition leaks already covered by TODO-004

### Map to TODO-006 (Framework public entrypoints compose runtime graphs)
- HTD-0392, HTD-0394 equivalents found in CLUSTER-032
- `Avax.php`, `BootDsl.php`, `App.php`, `RunApplication.php` oversized PublicSurface files
- Rationale: These are public entrypoint composition issues already covered by TODO-006

### Map to TODO-009 through TODO-013 (PublicSurface construction across components)
- Oversized PublicSurface files: `AvaxCache.php`, `Saga.php`, `Session.php`, `Request.php`, `Command.php`, `Text.php`, `Router.php`, `Pipeline.php`, `SecureRequest.php`, `GraphQLSchema.php`, `Console.php`
- Rationale: These are component-level PublicSurface construction issues covered by TODO-009 through TODO-013

### Map to TODO-014 (Constructor bloat / default parameter instantiation)
- All null-coalescing fallback instances in non-Configuration zones:
  - `DependencyRegistry.php:122,274`
  - `ServiceRegistry.php:122,273`
  - `ResolveDependencies.php:92`
  - `FunctionCaller.php:62`
- Rationale: Constructor default parameter instantiation is covered by TODO-014

### Map to TODO-015 (Missing ServiceProvider assembly)
- DR-0667 (ServiceProvider governance consistency wording gap)
- All MISSING ServiceProvider findings from `check-service-provider-coverage.php`
- Rationale: ServiceProvider assembly gaps are covered by TODO-015

### Map to TODO-019 (Global helper service-locator shortcuts)
- `shortcuts.php` files exceeding size thresholds
- Rationale: Global helper shortcuts are covered by TODO-019

### Map to TODO-020 (Constructor bloat / large units)
- Large PublicSurface files that also have constructor bloat
- Rationale: Constructor bloat and large unit classification is covered by TODO-020

### Map to TODO-021 (Missing or weak behavior test proof)
- SAI findings about missing tests for components in CLUSTER-032
- Rationale: Test coverage gaps are covered by TODO-021

### Map to TODO-022 (Forbidden concept folder names)
- SAI findings about naming violations in CLUSTER-032
- Rationale: Naming governance is covered by TODO-022

### Map to TODO-023 (Duplicate ownership / duplicate classes)
- SAI findings about duplicates in CLUSTER-032
- Rationale: Duplication is covered by TODO-023

### Map to TODO-024 (Hidden superglobal/env/IO access)
- SAI findings about hidden IO in CLUSTER-032
- Rationale: Hidden IO access is covered by TODO-024

## Claims that should remain in TODO-031

### Governance documentation consistency (DR-0667)
- The specific wording mismatch in `how-to-dependency-injection.md` regarding "ServiceProvider" vs "service provider" is a documentation-level issue
- Action: Fix wording in DI how-to document

### Tooling gap (DR-0668)
- `check-security-governance.php` does not exist
- Action: Note in governance index as PLANNED/NOT IMPLEMENTED, or create the tool

## Claims that cannot be mapped (no evidence files)

### Individual DR IDs (40+ IDs)
- DR-0057, DR-0061, DR-0104, DR-0106, DR-0108-0111, DR-0115, DR-0117, DR-0263, DR-0360, DR-0362-0368, DR-0424, DR-0435, DR-0461, DR-0468, DR-0477, DR-0482, DR-0484, DR-0491, DR-0499-0501, DR-0551, DR-0638-0640, DR-0659-0664
- Action: These should be audited during the remediation of their target clusters, not held in TODO-031 indefinitely

### Individual SAI IDs (40+ IDs)
- SAI-0015, SAI-0044-0046, SAI-0055-0056, SAI-0081, SAI-0094, SAI-0096, SAI-0134, SAI-0143, SAI-0146-0147, SAI-0150-0152, SAI-0162-0164, SAI-0175, SAI-0182, SAI-0185-0189, SAI-0195, SAI-0198, SAI-0201-0209, SAI-0212, SAI-0218-0223, SAI-0231-0233, SAI-0237
- Action: These should be audited during the remediation of their target clusters

### Individual OLD-FIX IDs
- OLD-FIX-112, OLD-FIX-117, OLD-FIX-119, OLD-FIX-122-126, OLD-FIX-238
- Action: These historical findings should be reconciled with current clusters

### Individual SCR IDs (36+ IDs)
- SCR-0056, SCR-0060, SCR-0103, SCR-0105, SCR-0107-0110, SCR-0114, SCR-0116, SCR-0262, SCR-0359, SCR-0361-0367, SCR-0392, SCR-0394, SCR-0423, SCR-0434, SCR-0460, SCR-0467, SCR-0476, SCR-0481, SCR-0483, SCR-0490, SCR-0498-0500, SCR-0550, SCR-0637, SCR-0638, SCR-0639, SCR-0658-0663
- Action: These should be audited during the remediation of their target clusters
