# TODO-006 Slice A High-Level Design

## System Capability Affected

Framework runtime application dispatch and zero-configuration application creation.

## Ownership Boundary

- PublicSurface: `App` receives user route registration calls and delegates request handling.
- Flow: `RunApplication` executes route dispatch and response normalization.
- Configuration: `BuildRunApplication`, `CreateApplication`, and `BootDslEngine` assemble runtime dependencies.

## Lifecycle Phase

- boot/configuration: default dispatch pipeline assembly
- request runtime: dispatch execution only

## Public Entrypoints

- `Avax::create()`
- `BootDsl::create()`
- `App::handle()`
- `App::run()`

## Non-Functional Requirements

- security: unchanged; no new untrusted-input path
- performance: boot-time object graph assembly remains boot/configuration time; request-time lazy assembly is removed
- reliability: required dispatcher dependency is available at `App` construction
- observability: existing optional metrics collector path preserved
- testability: improved because `App` accepts a dispatcher dependency
- backward compatibility: public method signatures remain unchanged

## Trade-Off

`CreateApplication` and `BootDslEngine` still assemble broader runtime/App graphs. That is accepted for Slice A because they are configuration/creation boundaries and will be handled in later TODO-006 slices.

## Out Of Scope

No broad cleanup and no TODO-007 work.
