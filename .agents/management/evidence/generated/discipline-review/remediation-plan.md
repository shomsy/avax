# Discipline Review Remediation Plan

Generated: 2026-05-19T22:16:56+02:00

Priority order uses only severity, security/runtime risk, public API risk, architectural drift, test weakness, and ease of safe remediation. Roadmap, V5.9, Boot DSL, and release criteria are intentionally excluded.

## 1. components/

### components/Identity/Auth

- Current decision: BLOCKED_BY_GOVERNANCE
- Highest severity: BLOCKER
- Cleanup objective: resolve CONFIGURATION_DI first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Large builder split/classification batch (`DR-0603`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/API/ApiBlueprint

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0341`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/API/ApiBlueprint/System/PublicSurface/ApiBlueprint.php:29,31,33,34,35,36,38`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/API/Contracts

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0344`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/API/Contracts/System/PublicSurface/ApiContracts.php:22,27,38,49,57,65,80`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/API/GraphQL

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0322`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/API/GraphQL/System/PublicSurface/GraphQL.php:17,24,39,52`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/API/OpenAPI

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0346`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/API/OpenAPI/System/PublicSurface/OpenAPI.php:22,23,31,36,44,49`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Application/Cache

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0049`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Application/Cache/System/PublicSurface/CompiledCache.php:31,59,60,61`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Application/Container

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve MAINTAINABILITY first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Constructor responsibility review batch (`DR-0443`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Application/Container/System/Capabilities/Composition/CreateContainerConfig.php:87`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Application/Filesystem

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0120`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Application/Filesystem/System/PublicSurface/Filesystem.php:39,44,49,54,59,64,69,74...`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Application/Storage

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0101`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Application/Storage/System/PublicSurface/Storage.php:37,56,65,84,93,101,109,117...`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Application/Text

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0098`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Application/Text/System/PublicSurface/Text.php:29,34,49,54,59,64,69,74...`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Application/Validation

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve CONFIGURATION_DI first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Direct default-instantiation cleanup batch (`DR-0118`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Application/Validation/System/PublicSurface/Validation.php:18`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/CLI/Console

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve CONFIGURATION_DI first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Direct default-instantiation cleanup batch (`DR-0225`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/CLI/Console/System/PublicSurface/Console.php:26`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/DataStack/DataTransfer

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0287`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/DataStack/DataTransfer/System/PublicSurface/DataTransfer.php:67,84,92,97,102,110,118,141...`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/DataStack/Database

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0267`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/DataStack/Database/System/PublicSurface/shortcuts.php:48,67,86`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/DataStack/Persistence

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0266`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/DataStack/Persistence/System/PublicSurface/DataLayer.php:31,32,33,35`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/DeveloperTools/Diagnostics

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0234`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/DeveloperTools/Diagnostics/System/PublicSurface/HealthCheck.php:27,28,29,39,52,71,110`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/DeveloperTools/Documentation/Api

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve CONFIGURATION_DI first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Direct default-instantiation cleanup batch (`DR-0228`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/DeveloperTools/Documentation/Api/System/Flows/RenderSwaggerUi/RenderSwaggerUi.php:13`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Foundation/CallableSerialization

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0237`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Foundation/CallableSerialization/System/PublicSurface/CallableSerialization.php:28,66,72`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/HTTP

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0143`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/HTTP/System/PublicSurface/Response.php:38,46,51`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/HTTP/Client

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0126`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/HTTP/Client/System/PublicSurface/HttpClient.php:14,19,34`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/HTTP/ContentNegotiation

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0122`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/HTTP/ContentNegotiation/System/PublicSurface/ContentNegotiation.php:23,30,44,45,46,47`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/HTTP/Dispatcher

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve CONFIGURATION_DI first, then reduce same-unit lower-severity debt.
- First safe remediation batch: ServiceProvider coverage batch (`DR-0034`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/HTTP/Dispatcher`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/HTTP/Request

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve CONFIGURATION_DI first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Direct default-instantiation cleanup batch (`DR-0127`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/HTTP/Request/System/PublicSurface/Request.php:33`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/HTTP/Router

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0146`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/HTTP/Router/System/PublicSurface/Router.php:46,49,107,117,130`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/HTTP/SecureRequest

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0144`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/HTTP/SecureRequest/System/PublicSurface/SecureRequest.php:80,87,90,99,108,116`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/HTTP/Security

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve CONFIGURATION_DI first, then reduce same-unit lower-severity debt.
- First safe remediation batch: ServiceProvider coverage batch (`DR-0035`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/HTTP/Security`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/HTTP/Session

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0140`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/HTTP/Session/System/PublicSurface/Session.php:30,91,92,129,130`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/HTTP/System

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Broken reference semantics batch (`DR-0039`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/HTTP/System`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-broken-reference-semantics.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Identity/Access

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve MAINTAINABILITY first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Constructor responsibility review batch (`DR-0631`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Identity/Access/System/Capabilities/Policy/IdentityPolicy.php:28`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Identity/Credentials

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve CONFIGURATION_DI first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Direct default-instantiation cleanup batch (`DR-0349`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Backup/GenerateBackupCodes.php:35`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Identity/ExternalIdentity

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve CONFIGURATION_DI first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Direct default-instantiation cleanup batch (`DR-0355`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/UpdateClient/UpdateClient.php:31`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Identity/Security

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve CONFIGURATION_DI first, then reduce same-unit lower-severity debt.
- First safe remediation batch: ServiceProvider coverage batch (`DR-0037`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Identity/Security`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Identity/Tenancy

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve CONFIGURATION_DI first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Direct default-instantiation cleanup batch (`DR-0351`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Identity/Tenancy/System/Capabilities/Runtime/TenantSecurity/BeginChange/BeginTenantSecurityChange.php:36`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Identity/Tokens

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0382`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Identity/Tokens/System/PublicSurface/Tokens.php:33,34,35,50,51,52,56,60`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Operations/ApplicationWorkflow

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Broken reference semantics batch (`DR-0040`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Operations/ApplicationWorkflow`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-broken-reference-semantics.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Operations/BackgroundProcesses

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0185`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Operations/BackgroundProcesses/System/PublicSurface/BackgroundProcesses.php:21,26,31,36,44,52,60,62...`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Operations/Concurrency

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0169`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Operations/Concurrency/System/PublicSurface/Concurrency.php:56,87,95`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Operations/Delivery

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0165`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Operations/Delivery/System/PublicSurface/Delivery.php:16,21,26,31`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Operations/Events

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0150`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Operations/Events/System/PublicSurface/Events.php:18,31,32,39`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Operations/Filesystem

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0196`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Operations/Filesystem/System/PublicSurface/Filesystem.php:18,23,28,33,38,46`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Operations/Mail

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0192`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Operations/Mail/System/PublicSurface/Mailer.php:15,31,46,51,117`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Operations/MemoryLifecycle

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0201`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Operations/MemoryLifecycle/System/PublicSurface/MemoryLifecycle.php:15,20,25`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Operations/MessageBus

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0182`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Operations/MessageBus/System/PublicSurface/MessageBus.php:56,57,58,59`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Operations/Notifications

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve CONFIGURATION_DI first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Direct default-instantiation cleanup batch (`DR-0157`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Operations/Notifications/System/PublicSurface/Notifier.php:20`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Operations/Observability

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0189`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Operations/Observability/System/PublicSurface/Observability.php:18,23,28,33,41,50`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Operations/Queue

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0177`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Operations/Queue/System/PublicSurface/Tasks.php:14,20,26,40`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Operations/Realtime

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0164`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Operations/Realtime/System/PublicSurface/Realtime.php:22,31,51`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Operations/RuntimeSupervision

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve CONFIGURATION_DI first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Direct default-instantiation cleanup batch (`DR-0200`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Operations/RuntimeSupervision/System/Capabilities/Supervision/Supervisor.php:28`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Operations/Scheduler

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0183`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Operations/Scheduler/System/PublicSurface/Scheduler.php:19,32,45`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Operations/Tasks

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0159`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Operations/Tasks/System/PublicSurface/Tasks.php:20,25,30,38,43,51,61`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Security/DataProtection

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0312`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Security/DataProtection/System/PublicSurface/DataProtection.php:18,23,28,36,44,52,57,62`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Security/Privacy

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0307`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Security/Privacy/System/PublicSurface/Privacy.php:19,24,29,37,47,57,63`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Security/Redaction

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0297`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Security/Redaction/System/PublicSurface/Redaction.php:20,25,30,35,46,56,64,69`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Security/Secrets

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve CONFIGURATION_DI first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Direct default-instantiation cleanup batch (`DR-0305`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Security/Secrets/System/Capabilities/Stores/EncryptedSecretStore.php:17`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/SystemDesign

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0239`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/SystemDesign/System/PublicSurface/SystemDesignKit.php:56,65,70,99,116,134,152,169...`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/API/SchemaGeneration

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0319`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/API/SchemaGeneration/System/PublicSurface/SchemaGeneration.php:89`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Application/Config

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve CONFIGURATION_DI first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Direct default-instantiation cleanup batch (`DR-0114`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Application/Config/System/Configuration/AppConfigurator.php:23`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Application/DateTime

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0099`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Application/DateTime/System/PublicSurface/SystemClock.php:19,24`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Application/FeatureFlags

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0100`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Application/FeatureFlags/System/PublicSurface/FeatureFlags.php:26`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Application/Localization

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve CONFIGURATION_DI first, then reduce same-unit lower-severity debt.
- First safe remediation batch: ServiceProvider coverage batch (`DR-0020`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Application/Localization`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Application/Pipeline

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface size classification batch (`DR-0461`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Application/Pipeline/System/PublicSurface/Pipeline.php`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/DataStack/Data

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0240`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/DataStack/Data/System/PublicSurface/Json.php:35`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/DeveloperTools

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0236`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/DeveloperTools/System/PublicSurface/DeveloperTools.php:11`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/DeveloperTools/CodeGeneration

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve CONFIGURATION_DI first, then reduce same-unit lower-severity debt.
- First safe remediation batch: ServiceProvider coverage batch (`DR-0025`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/DeveloperTools/CodeGeneration`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/DeveloperTools/DumpDebugger

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve TEST_PROOF first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Test proof batch for component (`DR-0003`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/DeveloperTools/DumpDebugger`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `vendor/bin/phpunit --no-coverage --filter <component>` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/DeveloperTools/Dx

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0232`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/DeveloperTools/Dx/System/PublicSurface/Dx.php:14,19`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/DeveloperTools/TestSupport

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0233`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/DeveloperTools/TestSupport/System/PublicSurface/Testing.php:18`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/HTTP/AfterResponse

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0142`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/HTTP/AfterResponse/System/PublicSurface/AfterResponse.php:17,23`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/HTTP/Context

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0135`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/HTTP/Context/System/PublicSurface/HttpContext.php:27`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/HTTP/Response

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0124`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/HTTP/Response/System/PublicSurface/shortcuts.php:18`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/HTTP/URI

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve CONFIGURATION_DI first, then reduce same-unit lower-severity debt.
- First safe remediation batch: ServiceProvider coverage batch (`DR-0036`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/HTTP/URI`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Identity

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0388`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Identity/System/PublicSurface/Identity.php:11`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Integration/ObjectStorage

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0318`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Integration/ObjectStorage/System/PublicSurface/ObjectStorage.php:23,53`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Operations

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0195`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Operations/System/PublicSurface/Operations.php:11`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Operations/Logging

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve CONFIGURATION_DI first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Direct default-instantiation cleanup batch (`DR-0166`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Operations/Logging/System/Capabilities/Logger/ErrorLogger.php:41`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Operations/Parallelism

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0198`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Operations/Parallelism/System/PublicSurface/Parallel.php:19,65`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Operations/Resilience

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0160`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Operations/Resilience/System/PublicSurface/Resilience.php:15,20`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Presentation

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0296`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Presentation/System/PublicSurface/Presentation.php:11`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### components/Security/Cryptography

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0306`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `components/Security/Cryptography/System/PublicSurface/Cryptography.php:56,58`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

## 2. framework/

### framework/System/Capabilities/Benchmarks

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve CONFIGURATION_DI first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Direct default-instantiation cleanup batch (`DR-0410`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `framework/System/Capabilities/Benchmarks/MicroBenchmarkRunner.php:17`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### framework/System/Capabilities/Doctor

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve CONFIGURATION_DI first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Direct default-instantiation cleanup batch (`DR-0404`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `framework/System/Capabilities/Doctor/ExecuteDoctorChecks.php:21`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### framework/System/Capabilities/ExternalState

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0412`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `framework/System/Capabilities/ExternalState/System/PublicSurface/ExternalState.php:34,37,54,57,74,77,94,97...`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### framework/System/Capabilities/FailureBoundary

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve MAINTAINABILITY first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Constructor responsibility review batch (`DR-0654`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `framework/System/Capabilities/FailureBoundary/Foundation/FailurePolicy.php:17`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### framework/System/Capabilities/PreCommit

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve CONFIGURATION_DI first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Direct default-instantiation cleanup batch (`DR-0405`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `framework/System/Capabilities/PreCommit/PreCommitValidator.php:46`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### framework/System/Capabilities/Runtime

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve CONFIGURATION_DI first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Direct default-instantiation cleanup batch (`DR-0417`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `framework/System/Capabilities/Runtime/WarmApplication/HandleWarmRequest.php:48`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### framework/System/Capabilities/Security

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve CONFIGURATION_DI first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Direct default-instantiation cleanup batch (`DR-0414`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `framework/System/Capabilities/Security/PolicyEngine/DefinePolicy.php:34`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### framework/System/Configuration/BootDsl

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve CONFIGURATION_DI first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Hidden fallback construction cleanup batch (`DR-0395`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `framework/System/Configuration/BootDsl/BootDslBuilder.php:143`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### framework/System/Configuration/Builders

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve SECURITY first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Raw filesystem migration batch (`DR-0044`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `framework/System/Configuration/Builders/BuildDispatchConfiguredRoute.php:46`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-raw-file-operations.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### framework/System/Flows/BootApplication

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve CONFIGURATION_DI first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Direct default-instantiation cleanup batch (`DR-0396`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `framework/System/Flows/BootApplication/BuildApplicationState.php:32`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### framework/System/Flows/HandleIncomingHttp

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve CONFIGURATION_DI first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Direct default-instantiation cleanup batch (`DR-0401`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `framework/System/Flows/HandleIncomingHttp/MatchHttpRoute.php:29`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### framework/System/Flows/RunApplication

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve CONFIGURATION_DI first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Direct default-instantiation cleanup batch (`DR-0397`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `framework/System/Flows/RunApplication/RunApplication.php:61`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### framework/System/PublicSurface

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0389`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `framework/System/PublicSurface:207,211,254,271,272,274,275,285...`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### framework/System/PublicSurface

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0392`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `framework/System/PublicSurface:72,74,75,76,77,78,79,80...`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### framework/System/PublicSurface

- Current decision: NEEDS_HIGH_REMEDIATION
- Highest severity: HIGH
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0394`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `framework/System/PublicSurface:55,59,150,151,155,156,159,164`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### framework/System/Capabilities/ContainerIntelligence

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve MAINTAINABILITY first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Constructor responsibility review batch (`DR-0647`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `framework/System/Capabilities/ContainerIntelligence/ContainerDependencyExplanation.php:15`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### framework/System/Capabilities/ResourceGovernance

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0411`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `framework/System/Capabilities/ResourceGovernance/System/PublicSurface/ResourceGovernor.php:45,77`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### framework/System/Capabilities/RuntimeIsolation

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve RUNTIME_SAFETY first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Runtime-specific dependency classification batch (`DR-0664`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `framework/System/Capabilities/RuntimeIsolation/RuntimeIsolationGuard.php:[11, 12, 13]`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-runtime-leaks.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### framework/System/Capabilities/RuntimeSafety

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve PUBLIC_API first, then reduce same-unit lower-severity debt.
- First safe remediation batch: PublicSurface assembly cleanup batch (`DR-0416`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `framework/System/Capabilities/RuntimeSafety/StatelessBoundary/System/PublicSurface/StatelessBoundary.php:45`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### framework/System/Capabilities/ServeModes

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve RUNTIME_SAFETY first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Runtime-specific dependency classification batch (`DR-0663`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `framework/System/Capabilities/ServeModes/ServeMode.php:[16, 17, 18, 19, 28, 29]`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-runtime-leaks.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### framework/System/Configuration/BuildApplication

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve MAINTAINABILITY first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Constructor responsibility review batch (`DR-0643`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `framework/System/Configuration/BuildApplication/Builders/ApplicationBuilder.php:40`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### framework/System/Configuration/ConfigureRuntime

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve RUNTIME_SAFETY first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Runtime-specific dependency classification batch (`DR-0659`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `framework/System/Configuration/ConfigureRuntime/RuntimeConfiguration.php:[13, 14, 15, 16, 79, 80]`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-runtime-leaks.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### framework/System/Configuration/Foundation

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve MAINTAINABILITY first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Constructor responsibility review batch (`DR-0642`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `framework/System/Configuration/Foundation/RuntimeConfiguration.php:14`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### framework/System/Flows/CreateApplication

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve MAINTAINABILITY first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Constructor responsibility review batch (`DR-0644`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `framework/System/Flows/CreateApplication/CreateApplication.php:41`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

### framework/System/Flows/RunDoctor

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve RUNTIME_SAFETY first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Runtime-specific dependency classification batch (`DR-0660`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `framework/System/Flows/RunDoctor/RunDoctor.php:[70]`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/refactor/check-runtime-leaks.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

## 3. cross-cutting findings

### cross-cutting

- Current decision: NEEDS_MEDIUM_REMEDIATION
- Highest severity: MEDIUM
- Cleanup objective: resolve EVIDENCE first, then reduce same-unit lower-severity debt.
- First safe remediation batch: Governance gate truthfulness cleanup batch (`DR-0667`).
- Forbidden changes: no unrelated public API changes, no mechanical renames, no feature work, no tests-only greenwashing.
- Files likely involved: `tooling/governance/check-serviceprovider-governance-consistency.php output`
- Tests to add/update later: behavior and negative tests proving the corrected boundary.
- Validation commands: `php tooling/governance/check-serviceprovider-governance-consistency.php` plus focused PHPUnit/PHPStan for touched files.
- Evidence needed: update a cleanup evidence report linking to finding IDs and validation output.
- Commit gate: no production/test changes outside the batch; no generated noise staged; focused validation passes or failure is classified.
- Expected status after cleanup: one severity level lower for this unit; not GREEN unless all findings are closed and evidence exists.

## Global Priority Order

1. DR-0603 [BLOCKER] `components/Identity/Auth` — Configuration builder is 797 lines (>300). (CONFIGURATION_DI)
2. DR-0044 [HIGH] `framework/System/Configuration/Builders` — Raw `is_file()` in framework route-dispatch builder is classified MIGRATE_TO_FILESYSTEM. (SECURITY)
3. DR-0039 [HIGH] `components/HTTP/System` — Active broken reference: `Avax\Components\HTTP\System\Capabilities\MiddlewarePipeline\System\Foundation\Failure\MiddlewareFailure`. (PUBLIC_API)
4. DR-0040 [HIGH] `components/Operations/ApplicationWorkflow` — Active broken reference: `Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\CompensationExecutor`. (PUBLIC_API)
5. DR-0041 [HIGH] `components/Operations/ApplicationWorkflow` — Active broken reference: `Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\IdempotencyStore`. (PUBLIC_API)
6. DR-0042 [HIGH] `components/Operations/ApplicationWorkflow` — Active broken reference: `Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\SagaState`. (PUBLIC_API)
7. DR-0043 [HIGH] `components/Operations/ApplicationWorkflow` — Active broken reference: `Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\SagaStep`. (PUBLIC_API)
8. DR-0049 [HIGH] `components/Application/Cache` — PublicSurface directly instantiates collaborators (4 `new` expressions detected). (PUBLIC_API)
9. DR-0050 [HIGH] `components/Application/Cache` — PublicSurface directly instantiates collaborators (4 `new` expressions detected). (PUBLIC_API)
10. DR-0098 [HIGH] `components/Application/Text` — PublicSurface directly instantiates collaborators (25 `new` expressions detected). (PUBLIC_API)
11. DR-0101 [HIGH] `components/Application/Storage` — PublicSurface directly instantiates collaborators (11 `new` expressions detected). (PUBLIC_API)
12. DR-0120 [HIGH] `components/Application/Filesystem` — PublicSurface directly instantiates collaborators (21 `new` expressions detected). (PUBLIC_API)
13. DR-0122 [HIGH] `components/HTTP/ContentNegotiation` — PublicSurface directly instantiates collaborators (6 `new` expressions detected). (PUBLIC_API)
14. DR-0126 [HIGH] `components/HTTP/Client` — PublicSurface directly instantiates collaborators (3 `new` expressions detected). (PUBLIC_API)
15. DR-0140 [HIGH] `components/HTTP/Session` — PublicSurface directly instantiates collaborators (5 `new` expressions detected). (PUBLIC_API)
16. DR-0143 [HIGH] `components/HTTP` — PublicSurface directly instantiates collaborators (3 `new` expressions detected). (PUBLIC_API)
17. DR-0144 [HIGH] `components/HTTP/SecureRequest` — PublicSurface directly instantiates collaborators (6 `new` expressions detected). (PUBLIC_API)
18. DR-0146 [HIGH] `components/HTTP/Router` — PublicSurface directly instantiates collaborators (5 `new` expressions detected). (PUBLIC_API)
19. DR-0150 [HIGH] `components/Operations/Events` — PublicSurface directly instantiates collaborators (4 `new` expressions detected). (PUBLIC_API)
20. DR-0159 [HIGH] `components/Operations/Tasks` — PublicSurface directly instantiates collaborators (7 `new` expressions detected). (PUBLIC_API)
21. DR-0164 [HIGH] `components/Operations/Realtime` — PublicSurface directly instantiates collaborators (3 `new` expressions detected). (PUBLIC_API)
22. DR-0165 [HIGH] `components/Operations/Delivery` — PublicSurface directly instantiates collaborators (4 `new` expressions detected). (PUBLIC_API)
23. DR-0169 [HIGH] `components/Operations/Concurrency` — PublicSurface directly instantiates collaborators (3 `new` expressions detected). (PUBLIC_API)
24. DR-0177 [HIGH] `components/Operations/Queue` — PublicSurface directly instantiates collaborators (4 `new` expressions detected). (PUBLIC_API)
25. DR-0182 [HIGH] `components/Operations/MessageBus` — PublicSurface directly instantiates collaborators (4 `new` expressions detected). (PUBLIC_API)
26. DR-0183 [HIGH] `components/Operations/Scheduler` — PublicSurface directly instantiates collaborators (3 `new` expressions detected). (PUBLIC_API)
27. DR-0185 [HIGH] `components/Operations/BackgroundProcesses` — PublicSurface directly instantiates collaborators (10 `new` expressions detected). (PUBLIC_API)
28. DR-0189 [HIGH] `components/Operations/Observability` — PublicSurface directly instantiates collaborators (6 `new` expressions detected). (PUBLIC_API)
29. DR-0192 [HIGH] `components/Operations/Mail` — PublicSurface directly instantiates collaborators (5 `new` expressions detected). (PUBLIC_API)
30. DR-0196 [HIGH] `components/Operations/Filesystem` — PublicSurface directly instantiates collaborators (6 `new` expressions detected). (PUBLIC_API)
31. DR-0201 [HIGH] `components/Operations/MemoryLifecycle` — PublicSurface directly instantiates collaborators (3 `new` expressions detected). (PUBLIC_API)
32. DR-0205 [HIGH] `components/Operations/ApplicationWorkflow` — PublicSurface directly instantiates collaborators (8 `new` expressions detected). (PUBLIC_API)
33. DR-0234 [HIGH] `components/DeveloperTools/Diagnostics` — PublicSurface directly instantiates collaborators (7 `new` expressions detected). (PUBLIC_API)
34. DR-0237 [HIGH] `components/Foundation/CallableSerialization` — PublicSurface directly instantiates collaborators (3 `new` expressions detected). (PUBLIC_API)
35. DR-0239 [HIGH] `components/SystemDesign` — PublicSurface directly instantiates collaborators (18 `new` expressions detected). (PUBLIC_API)
36. DR-0266 [HIGH] `components/DataStack/Persistence` — PublicSurface directly instantiates collaborators (4 `new` expressions detected). (PUBLIC_API)
37. DR-0267 [HIGH] `components/DataStack/Database` — PublicSurface directly instantiates collaborators (3 `new` expressions detected). (PUBLIC_API)
38. DR-0268 [HIGH] `components/DataStack/Database` — PublicSurface directly instantiates collaborators (7 `new` expressions detected). (PUBLIC_API)
39. DR-0273 [HIGH] `components/DataStack/Database` — PublicSurface directly instantiates collaborators (4 `new` expressions detected). (PUBLIC_API)
40. DR-0287 [HIGH] `components/DataStack/DataTransfer` — PublicSurface directly instantiates collaborators (10 `new` expressions detected). (PUBLIC_API)
41. DR-0297 [HIGH] `components/Security/Redaction` — PublicSurface directly instantiates collaborators (8 `new` expressions detected). (PUBLIC_API)
42. DR-0307 [HIGH] `components/Security/Privacy` — PublicSurface directly instantiates collaborators (7 `new` expressions detected). (PUBLIC_API)
43. DR-0312 [HIGH] `components/Security/DataProtection` — PublicSurface directly instantiates collaborators (8 `new` expressions detected). (PUBLIC_API)
44. DR-0322 [HIGH] `components/API/GraphQL` — PublicSurface directly instantiates collaborators (4 `new` expressions detected). (PUBLIC_API)
45. DR-0323 [HIGH] `components/API/GraphQL` — PublicSurface directly instantiates collaborators (7 `new` expressions detected). (PUBLIC_API)
46. DR-0328 [HIGH] `components/API/GraphQL` — PublicSurface directly instantiates collaborators (8 `new` expressions detected). (PUBLIC_API)
47. DR-0341 [HIGH] `components/API/ApiBlueprint` — PublicSurface directly instantiates collaborators (7 `new` expressions detected). (PUBLIC_API)
48. DR-0342 [HIGH] `components/API/ApiBlueprint` — PublicSurface directly instantiates collaborators (7 `new` expressions detected). (PUBLIC_API)
49. DR-0344 [HIGH] `components/API/Contracts` — PublicSurface directly instantiates collaborators (7 `new` expressions detected). (PUBLIC_API)
50. DR-0346 [HIGH] `components/API/OpenAPI` — PublicSurface directly instantiates collaborators (6 `new` expressions detected). (PUBLIC_API)
51. DR-0382 [HIGH] `components/Identity/Tokens` — PublicSurface directly instantiates collaborators (8 `new` expressions detected). (PUBLIC_API)
52. DR-0389 [HIGH] `framework/System/PublicSurface` — PublicSurface directly instantiates collaborators (11 `new` expressions detected). (PUBLIC_API)
53. DR-0392 [HIGH] `framework/System/PublicSurface` — PublicSurface directly instantiates collaborators (35 `new` expressions detected). (PUBLIC_API)
54. DR-0394 [HIGH] `framework/System/PublicSurface` — PublicSurface directly instantiates collaborators (8 `new` expressions detected). (PUBLIC_API)
55. DR-0412 [HIGH] `framework/System/Capabilities/ExternalState` — PublicSurface directly instantiates collaborators (9 `new` expressions detected). (PUBLIC_API)
56. DR-0424 [HIGH] `components/Application/Cache` — PublicSurface file is 334 lines (>150). (PUBLIC_API)
57. DR-0491 [HIGH] `components/Operations/ApplicationWorkflow` — PublicSurface file is 304 lines (>150). (PUBLIC_API)
58. DR-0501 [HIGH] `components/SystemDesign` — PublicSurface file is 491 lines (>150). (PUBLIC_API)
59. DR-0638 [HIGH] `framework/System/PublicSurface` — PublicSurface file is 366 lines (>150). (PUBLIC_API)
60. DR-0016 [HIGH] `components/API/GraphQL` — ServiceProvider coverage gate reports real code but no component ServiceProvider. (CONFIGURATION_DI)
61. DR-0017 [HIGH] `components/API/OpenAPI` — ServiceProvider coverage gate reports real code but no component ServiceProvider. (CONFIGURATION_DI)
62. DR-0023 [HIGH] `components/DataStack/DataTransfer` — ServiceProvider coverage gate reports real code but no component ServiceProvider. (CONFIGURATION_DI)
63. DR-0030 [HIGH] `components/Foundation/CallableSerialization` — ServiceProvider coverage gate reports real code but no component ServiceProvider. (CONFIGURATION_DI)
64. DR-0034 [HIGH] `components/HTTP/Dispatcher` — ServiceProvider coverage gate reports real code but no component ServiceProvider. (CONFIGURATION_DI)
65. DR-0035 [HIGH] `components/HTTP/Security` — ServiceProvider coverage gate reports real code but no component ServiceProvider. (CONFIGURATION_DI)
66. DR-0037 [HIGH] `components/Identity/Security` — ServiceProvider coverage gate reports real code but no component ServiceProvider. (CONFIGURATION_DI)
67. DR-0064 [HIGH] `components/Application/Cache` — Constructor default parameter instantiates a dependency. (CONFIGURATION_DI)
68. DR-0065 [HIGH] `components/Application/Cache` — Constructor default parameter instantiates a dependency. (CONFIGURATION_DI)
69. DR-0066 [HIGH] `components/Application/Cache` — Constructor default parameter instantiates a dependency. (CONFIGURATION_DI)
70. DR-0067 [HIGH] `components/Application/Cache` — Constructor default parameter instantiates a dependency. (CONFIGURATION_DI)
71. DR-0068 [HIGH] `components/Application/Cache` — Constructor default parameter instantiates a dependency. (CONFIGURATION_DI)
72. DR-0069 [HIGH] `components/Application/Cache` — Constructor default parameter instantiates a dependency. (CONFIGURATION_DI)
73. DR-0070 [HIGH] `components/Application/Cache` — Constructor default parameter instantiates a dependency. (CONFIGURATION_DI)
74. DR-0071 [HIGH] `components/Application/Cache` — Constructor default parameter instantiates a dependency. (CONFIGURATION_DI)
75. DR-0072 [HIGH] `components/Application/Cache` — Constructor default parameter instantiates a dependency. (CONFIGURATION_DI)
76. DR-0073 [HIGH] `components/Application/Cache` — Constructor default parameter instantiates a dependency. (CONFIGURATION_DI)
77. DR-0074 [HIGH] `components/Application/Cache` — Constructor default parameter instantiates a dependency. (CONFIGURATION_DI)
78. DR-0075 [HIGH] `components/Application/Cache` — Constructor default parameter instantiates a dependency. (CONFIGURATION_DI)
79. DR-0076 [HIGH] `components/Application/Cache` — Constructor default parameter instantiates a dependency. (CONFIGURATION_DI)
80. DR-0077 [HIGH] `components/Application/Cache` — Constructor default parameter instantiates a dependency. (CONFIGURATION_DI)

## Validation Command Summary

Validation run after artifact generation:

| Command | Result | Notes |
|---|---|---|
| `composer validate --no-check-publish` | PASS | `./composer.json is valid`. |
| `composer dump-autoload -o` | PASS | Generated optimized autoload files with 9346 classes; composer reported existing PSR-4 skip for `xhp_` in `framework/System/Foundation/compat.php`. |
| `php tooling/refactor/check-component-suite-structure.php` | PASS | Output: `PASS`. |
| `php tooling/refactor/check-duplicate-owners.php` | PASS | Output: `PASS`. |
| `php tooling/refactor/check-namespace-drift.php` | PASS | Output: `PASS`. |
| `php tooling/refactor/check-public-surface.php` | PASS | Output: `PASS`. |
| `php tooling/refactor/check-runtime-composition-leaks.php` | PASS | Output: `PASS`. |
| `php tooling/governance/check-governance-index-current.php` | PASS | Output: `GREEN: Governance index is current.` |
| `php tooling/governance/check-root-evidence-hygiene.php` | PASS | Output: `GREEN: Root evidence hygiene PASSED.` |
| `bash verify-governance.sh .` | PASS | Output ended with `Governance Verified: FULL_GREEN_EXECUTABLE_GOVERNANCE_RUNTIME_READY`; script updated pre-existing generated governance event/provenance/summary files, which are not in this audit commit scope. |

Review scan commands that intentionally exposed backlog findings:

| Command | Result | Impact |
|---|---|---|
| `php tooling/refactor/check-direct-instantiation.php` | FAIL | Seeded direct dependency construction findings in component/framework reviews. |
| `php tooling/refactor/check-constructor-bloat.php` | FAIL | Seeded constructor responsibility findings. |
| `php tooling/refactor/check-raw-file-operations.php` | FAIL | Seeded raw filesystem migration/design-decision findings. |
| `php tooling/governance/check-large-unit-thresholds.php` | FAIL | Seeded AuthBuilder BLOCKER and large-unit review findings. |
| `php tooling/refactor/check-service-provider-coverage.php` | FAIL | Seeded missing ServiceProvider findings. |
| `php tooling/refactor/check-broken-reference-semantics.php` | FAIL | Seeded five active broken-reference findings. |
| `php tooling/governance/check-semantic-phpdoc.php` | PASS_WITH_YELLOW_RATCHET | Seeded accepted YELLOW semantic PHPDoc ratchet finding. |
| `php tooling/governance/check-security-governance.php` | PLANNED / NOT IMPLEMENTED | Command is unavailable; available substitutes are `tooling/security/check-security-blockers.php`, `tooling/security/check-security-naming.php`, and `tooling/security/check-raw-file-operations.php`. |
