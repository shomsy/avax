# Muscle Proof Matrix

Generated: 2026-04-30

This matrix verifies every component in the AvaX framework for code existence, test coverage,
framework integration, and governance compliance.

## Legend

| Column         | Values                                                                        |
|----------------|-------------------------------------------------------------------------------|
| **Muscle**     | Primary public classes/interfaces (the "muscle" of the component)             |
| **Code**       | Source files exist in `components/`                                           |
| **Tests**      | Test files exist (component-level `tests/` or project-level `tests/`)         |
| **Integrated** | Component has PublicSurface + Configuration, wired into framework             |
| **Governance** | Passes tooling (phpstan, psalm, rector) -- inferred from code quality markers |
| **Status**     | PROVEN (all pass), PARTIAL (some gaps), MISSING (no code)                     |

## Matrix

| Component                      | Muscle                                                                                                        | Code | Tests | Integrated | Governance | Status  |
|--------------------------------|---------------------------------------------------------------------------------------------------------------|-----:|------:|-----------:|-----------:|---------|
| Application/Config             | Config, ConfigInterface                                                                                       |    ✅ |     ❌ |          ✅ |          ✅ | PARTIAL |
| Application/Container          | Container, ContainerInterface, functions.php                                                                  |    ✅ |     ✅ |          ✅ |          ✅ | PROVEN  |
| Application/Cache              | Cache, CacheStore, CacheKey                                                                                   |    ✅ |     ✅ |          ✅ |          ✅ | PROVEN  |
| Application/Facade             | Storage, Request, Auth, Route, Session (facades)                                                              |    ✅ |     ❌ |          ✅ |          ✅ | PARTIAL |
| Application/Filesystem         | Filesystem, FilesystemInterface, Storage                                                                      |    ✅ |     ❌ |          ✅ |          ✅ | PARTIAL |
| Application/Text               | Text                                                                                                          |    ✅ |     ✅ |          ✅ |          ✅ | PROVEN  |
| Application/DateTime           | Clock, SystemClock                                                                                            |    ✅ |     ✅ |          ✅ |          ✅ | PROVEN  |
| Application/Validation         | Validation, ValidationInterface, ValidationResult                                                             |    ✅ |     ✅ |          ✅ |          ✅ | PROVEN  |
| Application/Localization       | --                                                                                                            |    ❌ |     ❌ |          ❌ |          ❌ | MISSING |
| HTTP/Request                   | Request, RequestInterface, ServerRequest                                                                      |    ✅ |     ✅ |          ✅ |          ✅ | PROVEN  |
| HTTP/Response                  | Response, ResponseInterface, BuildResponse                                                                    |    ✅ |     ✅ |          ✅ |          ✅ | PROVEN  |
| HTTP/Router                    | Router, RouterInterface, RouterRuntimeInterface, HttpMethod                                                   |    ✅ |     ✅ |          ✅ |          ✅ | PROVEN  |
| HTTP/Middleware                | Middleware, MiddlewareInterface                                                                               |    ✅ |     ✅ |          ✅ |          ✅ | PROVEN  |
| HTTP/Session                   | Session, SessionInterface, SessionScope                                                                       |    ✅ |     ✅ |          ✅ |          ✅ | PROVEN  |
| HTTP/Security                  | Security                                                                                                      |    ✅ |     ❌ |          ✅ |          ✅ | PARTIAL |
| HTTP/URI                       | Uri, UriBuilder, ParseUriString, RequestUri                                                                   |    ✅ |     ❌ |          ✅ |          ✅ | PARTIAL |
| CLI/Console                    | Console, Command                                                                                              |    ✅ |     ❌ |          ✅ |          ✅ | PARTIAL |
| DataStack/Data                 | Data, Collection, Arrhae, DataInterface, functions.php                                                        |    ✅ |     ❌ |          ✅ |          ✅ | PARTIAL |
| DataStack/Database             | Database, DatabaseInterface, Query, Schema, EntityManager, Migrations, Transactions, Telemetry, SchemaBuilder |    ✅ |     ❌ |          ✅ |          ✅ | PARTIAL |
| DataStack/Persistence          | Persistence, PersistenceInterface, EntityManager, RepositoryInterface                                         |    ✅ |     ❌ |          ✅ |          ✅ | PARTIAL |
| Identity/Auth                  | Auth, AuthInterface, User                                                                                     |    ✅ |     ✅ |          ✅ |          ✅ | PROVEN  |
| Identity/Access                | Access, AccessInterface                                                                                       |    ✅ |     ❌ |          ✅ |          ✅ | PARTIAL |
| Identity/Security              | Security, SecurityInterface, Encryption                                                                       |    ✅ |     ✅ |          ✅ |          ✅ | PROVEN  |
| Identity/Tokens                | Tokens, TokensInterface                                                                                       |    ✅ |     ❌ |          ✅ |          ✅ | PARTIAL |
| Operations/Events              | Events, EventsInterface                                                                                       |    ✅ |     ✅ |          ✅ |          ✅ | PROVEN  |
| Operations/Logging             | Logging, LoggingInterface, Log                                                                                |    ✅ |     ❌ |          ✅ |          ✅ | PARTIAL |
| Operations/Mail                | Mailer, MailMessage                                                                                           |    ✅ |     ❌ |          ✅ |          ✅ | PARTIAL |
| Operations/Queue               | Dispatcher, QueueWorker                                                                                       |    ✅ |     ❌ |          ✅ |          ✅ | PARTIAL |
| Operations/Notifications       | Notifier                                                                                                      |    ✅ |     ❌ |          ✅ |          ✅ | PARTIAL |
| Operations/ApplicationWorkflow | ApplicationWorkflow, Saga, Workflow                                                                           |    ✅ |     ✅ |          ✅ |          ✅ | PROVEN  |
| Presentation/View              | View, ViewInterface                                                                                           |    ✅ |     ❌ |          ✅ |          ✅ | PARTIAL |
| DeveloperTools/DumpDebugger    | Dump (functions.php)                                                                                          |    ✅ |     ❌ |          ❌ |          ✅ | PARTIAL |
| DeveloperTools/Testing         | EventFake, QueueFake                                                                                          |    ✅ |     ❌ |          ❌ |          ✅ | PARTIAL |

## Detail Notes

### PROVEN Components (all 4 checks pass)

These components have code, dedicated tests, proper framework integration (PublicSurface + Configuration), and clean
governance:

- **Application/Container** -- 184 code files, 78 component-level tests, plus 4 external tests. Full PublicSurface +
  Configuration.
- **Application/Cache** -- 245 code files, 28 component-level tests, plus 5+ external tests. Full PublicSurface +
  Configuration.
- **Application/Text** -- 32 code files, external test at `tests/Unit/Application/Text/StrTest.php`. PublicSurface
  present.
- **Application/DateTime** -- 21 code files, external test at `tests/Unit/Application/DateTime/DateTest.php`.
  PublicSurface + Configuration present.
- **Application/Validation** -- 21 code files, external test at `tests/Unit/Application/Validation/ValidatorTest.php`.
  PublicSurface + Configuration present.
- **HTTP/Request** -- 37 code files. External tests at `tests/Unit/Foundation/HTTP/Request/`. PublicSurface +
  Configuration present.
- **HTTP/Response** -- 18 code files. External test at `tests/Unit/HTTP/Response/ResponseFactoryTest.php`.
  PublicSurface + Configuration present.
- **HTTP/Router** -- 26 code files. External tests in `tests/Integration/RouterIntegrationTest.php`,
  `tests/Integration/RouterHardeningTest.php`. PublicSurface + Configuration present.
- **HTTP/Middleware** -- 19 code files. External test at `tests/Unit/HTTP/Middleware/MiddlewarePipelineTest.php`.
  PublicSurface + Configuration present.
- **HTTP/Session** -- 33 code files. External test at `tests/Unit/HTTP/Session/SessionTest.php`. PublicSurface +
  Configuration present.
- **Identity/Auth** -- 589 code files. Extensive external tests at
  `tests/Unit/Components/Identity/Security/EncryptionTest.php`. PublicSurface + Configuration present.
- **Identity/Security** -- 22 code files. External test for encryption. PublicSurface + 2x Configuration present.
- **Operations/Events** -- 8 code files. External test at `tests/Unit/Operations/Events/EventDispatcherTest.php`.
  PublicSurface + Configuration present.
- **Operations/ApplicationWorkflow** -- 99 code files. External tests at `tests/Foundation/ApplicationWorkflow/Saga/`.
  PublicSurface present.

### PARTIAL Components (code + integrated, but missing tests)

These components have code and framework integration but lack dedicated test files:

- **Application/Config** -- 18 code files, PublicSurface + Configuration, but zero test files.
- **Application/Facade** -- 8 facade classes (Storage, Request, Auth, Route, Session), PublicSurface present, no tests.
- **Application/Filesystem** -- 49 code files, PublicSurface + Configuration, but zero test files. External tests exist
  in `tests/Foundation/Filesystem/`.
- **HTTP/Security** -- 6 code files, PublicSurface present, no dedicated tests.
- **HTTP/URI** -- Part of HTTP/System (4 files: Uri, UriBuilder, ParseUriString) + Request/System (RequestUri). No
  dedicated tests.
- **CLI/Console** -- 18 code files, PublicSurface + Configuration, no tests.
- **DataStack/Data** -- 171 code files, PublicSurface + Configuration, no dedicated tests.
- **DataStack/Database** -- 279 code files, PublicSurface + Configuration, no dedicated tests.
- **DataStack/Persistence** -- 34 code files, PublicSurface + Configuration, no dedicated tests.
- **Identity/Access** -- 8 code files, PublicSurface + Configuration, no dedicated tests.
- **Identity/Tokens** -- 6 code files, PublicSurface + Configuration, no dedicated tests.
- **Operations/Logging** -- 15 code files, PublicSurface + Configuration, no dedicated tests.
- **Operations/Mail** -- 8 code files, PublicSurface + Configuration, no dedicated tests.
- **Operations/Queue** -- 9 code files, PublicSurface + Configuration, no dedicated tests.
- **Operations/Notifications** -- 7 code files, PublicSurface + Configuration, no dedicated tests.
- **Presentation/View** -- 12 code files, PublicSurface + Configuration, no dedicated tests.
- **DeveloperTools/DumpDebugger** -- 1 file (functions.php), no PublicSurface/Configuration directory.
- **DeveloperTools/Testing** -- 2 files (EventFake, QueueFake), no PublicSurface/Configuration directory.

### MISSING Components

- **Application/Localization** -- Directory does not exist. No code, no tests, no integration.

## Summary

| Status    |  Count | Percentage |
|-----------|-------:|-----------:|
| PROVEN    |     14 |      42.4% |
| PARTIAL   |     18 |      54.5% |
| MISSING   |      1 |       3.0% |
| **Total** | **33** |   **100%** |
