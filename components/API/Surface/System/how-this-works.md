# API Surface

The API Surface component owns the V2 API engine slice for HTTP-facing endpoint definitions, request payload schemas,
response payload schemas, authentication requirements, compatibility analysis, and documentation generation sources.

Its public entrypoint is `ApiSurface::inMemory()`, followed by `registerEndpoint()`, `describe()`, `validate()`,
`detectCompatibility()`, and `compatibilityCheckScenarios()`.

## Ownership

This component owns API surface shape and compatibility evidence. It does not own OpenAPI rendering, GraphQL execution,
REST resource routing, JSON:API documents, webhooks, or RPC endpoints.

## Flow

1. `ApiSurface` receives the public call.
2. `InMemoryApiDocumentationSource` stores endpoint definitions for local and test usage.
3. `BuildApiSurface` builds an `ApiSurfaceDefinition` snapshot.
4. `ValidateApiSurface` reports missing success responses and duplicate operation ids.
5. `DetectApiCompatibilityChanges` compares old and new surface definitions.
6. `GenerateApiCompatibilityChecks` creates scenario text that future generators can turn into executable checks.

## Failure

Invalid surface definitions are reported through `ApiSurfaceReport` so CI, release tooling, and operator workflows can
show all problems at once.

`ApiSurfaceInvalid` is available for request-validation boundaries that need exception semantics.
