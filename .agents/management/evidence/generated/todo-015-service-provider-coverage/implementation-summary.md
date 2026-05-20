# TODO-015: Add missing ServiceProvider assembly owners

## Summary

Closed TODO-015: Added 24 missing ServiceProviders across 8 component suites.

All active components now have a ServiceProvider that registers their dependencies, eliminating the fallback anti-pattern where runtime code had to construct dependencies inline.

## Scope

- 24 new ServiceProviders created
- 2 new Register*Defaults builders created (CallableSerialization, Identity/Security)
- 19 new ServiceProvider tests (5 API tests already existed)
- 37 builder files copied to complete ServiceProvider delegation
- 134 total tests, 190 assertions — all passing
- PHPStan clean on all changed files
- ServiceProvider coverage gate: PASS (76/76 components OK)

## Components Affected

| Component | ServiceProvider | Builder | Tests |
|-----------|----------------|---------|-------|
| API/ApiBlueprint | Yes | No (inline) | 6 |
| API/Contracts | Yes | No (inline) | Already existed |
| API/GraphQL | Yes | RegisterGraphQLDefaults (copied) | 11 |
| API/OpenAPI | Yes | No (inline) | 9 |
| API/SchemaGeneration | Yes | RegisterSchemaGenerationDefaults (copied) | 11 |
| Application/FeatureFlags | Yes | No (inline) | 2 |
| Application/Localization | Yes | No (inline) | 4 |
| CLI/Console | Yes | No (inline) | 2 |
| DataStack/Data | Yes | No (inline) | 8 |
| DataStack/DataTransfer | Yes | No (inline) | 1 |
| DataStack/Persistence | Yes | No (inline) | 1 |
| DeveloperTools/CodeGeneration | Yes | RegisterCodeGenerationDefaults (copied) | 1 |
| DeveloperTools/Diagnostics | Yes | No (inline) | 1 |
| DeveloperTools/DumpDebugger | Yes | No (inline) | 1 |
| DeveloperTools/Dx | Yes | No (inline) | 1 |
| DeveloperTools/TestSupport | Yes | No (inline) | 1 |
| Foundation/CallableSerialization | Yes | RegisterCallableSerializationDefaults (new) | 7 |
| HTTP/AfterResponse | Yes | No (inline) | 1 |
| HTTP/ContentNegotiation | Yes | No (inline) | 4 |
| HTTP/Context | Yes | No (inline) | 3 |
| HTTP/Dispatcher | Yes | No (inline) | 4 |
| HTTP/Security | Yes | No (inline) | 2 |
| HTTP/URI | Yes | No (inline) | 2 |
| Identity/Security | Yes | RegisterSecurityDefaults (new) | 5 |
| Operations/Filesystem | Yes | No (inline) | 6 |

## Pattern Followed

Each ServiceProvider follows the established AuthServiceProvider pattern:
- Thin ServiceProvider delegates to Register*Defaults builder (or inline for simple components)
- All dependencies registered as singletons
- Interface bindings where applicable
- No boot-time logic needed for these components

## Validation

- PHPUnit: 134 tests, 190 assertions — OK
- PHPStan: Clean on all changed files
- ServiceProvider coverage gate: PASS (76 components)

## Source Finding IDs

TODO-015 from fix-this.md — 25 missing ServiceProviders across API, DeveloperTools, HTTP, Application, DataStack, CLI, Foundation, Identity, Operations components.
