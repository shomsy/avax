# V2 API Naming And Ownership Refactor Report

Date: 2026-05-07
Stage: V2 Engine Implementation Phase
Status: YELLOW / VALIDATION BLOCKED

## Scope

This pass refactored the active V2 API engine naming away from generic technical language and toward AvaX
capability-oriented ownership language.

No V3 or V4 implementation was performed.
No placeholder class was added.
No behavior was intentionally changed.

## Renamed Ownership

- `components/API/Descriptions` -> `components/API/Surface`
- `tests/Unit/Components/API/Descriptions` -> `tests/Unit/Components/API/Surface`
- `docs/components/API/Contracts` -> `docs/components/API/Surface`

## Renamed Capabilities

- `AuthRequirements` -> `Authentication`
- `EndpointDescriptions` -> `EndpointDefinitions`
- `RequestDescriptions` -> `RequestSchemas`
- `ResponseDescriptions` -> `ResponseSchemas`
- `BreakingChanges` -> `Compatibility`
- `ReadApiDescriptions` -> `GenerateApiDocumentation`

## Renamed Responsibilities

- `ApiContracts` -> `ApiSurface`
- `ApiContract` -> `ApiSurfaceDefinition`
- `ApiContractReport` -> `ApiSurfaceReport`
- `ApiContractInvalid` -> `ApiSurfaceInvalid`
- `EndpointContract` -> `EndpointDefinition`
- `RequestDtoContract` -> `RequestSchema`
- `RequestValidationContract` -> `RequestPayloadValidator`
- `ResponseDtoContract` -> `ResponseSchema`
- `ErrorResponseContract` -> `ErrorResponseSchema`
- `BreakingChange*` -> `CompatibilityChange*`
- `ReadApiDescriptions` -> `ApiDocumentationSource`
- `ReadFakeApiDescriptions` -> `InMemoryApiDocumentationSource`
- `DescribeHttpContracts` -> `BuildApiSurface`
- `DetectBreakingApiChanges` -> `DetectApiCompatibilityChanges`
- `GenerateApiContractTests` -> `GenerateApiCompatibilityChecks`
- `ValidateApiContracts` -> `ValidateApiSurface`
- `OpenAPI::fromContracts()` -> `OpenAPI::fromSurface()`
- `OpenApiComparisonReport::hasBreakingChanges()` -> `hasCompatibilityIssues()`

## Architecture Reasoning

`Surface` is the component owner because this slice owns the visible HTTP API surface: endpoint definitions, payload
schemas, authentication requirements, compatibility analysis, and documentation source material.

`Schema` and `Authorization` remain acceptable domain terms for API/GraphQL ownership. `Contract` and passive
`Description` naming was removed from the active API source, tests, and docs because those names were too abstract for
the AvaX screaming architecture rule.

## Static Checks Completed

```bash
git diff --check
rg -n "Contracts|Contract|Descriptions|Description|AuthRequirements|EndpointDescriptions|RequestDescriptions|ResponseDescriptions|ReadApiDescriptions|BreakingChanges|fromContracts|contractTestScenarios|detectBreakingChanges|hasBreakingChanges|hasCompatibility\\(|oldContract|newContract|apiContract|\\$contracts|apiContracts|describeHttpContracts|breakingChangeDetector|GenerateApiSurfaceDefinitionTests" components/API tests/Unit/Components/API docs/components/API tooling/refactor/check-component-suite-structure.php
class/file name shell scan for components/API and tests/Unit/Components/API
namespace/path shell scan for components/API
```

Result:

- `git diff --check`: PASS
- weak-name scan over active API source/tests/docs: PASS, no matches
- class/file name scan: PASS, no mismatches
- namespace/path scan: PASS, no mismatches

## Validation Blocker

Canonical PHP/Composer validation could not be rerun after this naming refactor.

The attempted command was:

```bash
composer dump-autoload -o
```

It was rejected before execution by the approval reviewer because the current session hit the escalation usage limit.
Because this command did not run, the following required commands remain NOT PROVEN for the current workspace:

```bash
composer dump-autoload -o
vendor/bin/phpunit --no-coverage
vendor/bin/phpstan analyse components/API tests/Unit/Components/API --memory-limit=1G --error-format=raw --no-progress
vendor/bin/phpstan analyse framework components tests --memory-limit=1G --error-format=raw --no-progress
```

## Remaining Weak Names

No weak `Contract(s)`, passive `Description(s)`, or `BreakingChanges` naming remains in the active API source, focused
API tests, or API docs scanned in this pass.

Historical evidence files may still mention the old names and were intentionally not rewritten.

## Next Allowed Action

Rerun validation as soon as Composer/PHP tooling is available:

```bash
composer dump-autoload -o
vendor/bin/phpunit --no-coverage tests/Unit/Components/API
vendor/bin/phpstan analyse components/API tests/Unit/Components/API --memory-limit=1G --error-format=raw --no-progress
vendor/bin/phpunit --no-coverage
vendor/bin/phpstan analyse framework components tests --memory-limit=1G --error-format=raw --no-progress
```

If validation fails, repair only the smallest API naming/namespace/type issue required to make the gate pass.
