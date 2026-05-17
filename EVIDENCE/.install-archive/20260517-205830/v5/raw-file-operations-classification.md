# Raw File Operations Classification — V5 Dogfooding Closure Pass 01

**Date:** 2026-05-10
**Gate:** `php tooling/security/check-raw-file-operations.php` — 241 violations (classified below)

## Classification

### Category 1: Filesystem Component (canonical owner) — ALLOWED

The Filesystem component is the canonical owner of file I/O. Its internal file operations are expected.

| Component                            | Count | Notes                                                            |
|--------------------------------------|------:|------------------------------------------------------------------|
| FileCacheStore.php                   |    11 | File-based cache storage — filesystem behavior                   |
| FileSessionStore.php                 |     6 | File-based session storage — filesystem behavior                 |
| RotatingFileWriter.php (Logging)     |     6 | Log file writing — logging-specific file behavior                |
| RotatingFileWriter.php (Ops/Logging) |     3 | Duplicate path reference                                         |
| StoreObjectsOnLocalFilesystem.php    |     5 | Object storage on local filesystem — explicit filesystem adapter |

### Category 2: Application Container compilation/cache — NEEDS MIGRATION

| Component                      | Count | Notes                                         |
|--------------------------------|------:|-----------------------------------------------|
| RegistrationMetadata.php       |    19 | Metadata file reads for container compilation |
| CreateContainerConfig.php      |    15 | Config file reads for container setup         |
| CompileContainer.php           |    14 | Container compilation writes compiled PHP     |
| BlueprintCache.php             |     8 | Blueprint cache file I/O                      |
| AtomicCompiledCacheWrite.php   |     6 | Atomic cache writes                           |
| WriteCompiledCacheManifest.php |     4 | Cache manifest writes                         |
| ClearCompiledCache.php         |     3 | Cache clearing with file deletes              |
| CompiledCacheManifest.php      |     3 | Manifest file reads                           |

**Total: 72 violations** — Container compilation is a special runtime context. These should migrate to Filesystem but
are not urgent security risks (they read/write the app's own compiled cache directory).

### Category 3: PreCommit/Tooling — ALLOWED

| Component                    | Count | Notes                                  |
|------------------------------|------:|----------------------------------------|
| PreCommitReportWriter.php    |     8 | Report generation — tooling context    |
| PreCommitValidator.php       |     4 | Validation framework — tooling context |
| ScriptRunnerValidator.php    |     3 | Script execution — tooling context     |
| LegacyCodeValidator.php      |     3 | Code analysis — tooling context        |
| Various PreCommit validators |   ~25 | All read source files for analysis     |

**Total: ~43 violations** — Pre-commit validation tooling reads source files for analysis. Allowed as tooling context.

### Category 4: Container tooling scripts — ALLOWED

| Component                      | Count | Notes          |
|--------------------------------|------:|----------------|
| generate-runtime-artifacts.php |     8 | Tooling script |
| generate-analysis-hints.php    |     8 | Tooling script |
| graph.php                      |     4 | Tooling script |

**Total: 20 violations** — Tooling scripts in `components/Application/Container/tools/`. Allowed.

### Category 5: Observability file writers (already use Security/Redaction) — NEEDS MIGRATION

| Component            | Count | Notes                |
|----------------------|------:|----------------------|
| FileAuditWriter.php  |     4 | NDJSON audit writes  |
| FileLogWriter.php    |     4 | NDJSON log writes    |
| FileMetricWriter.php |     4 | NDJSON metric writes |
| FileTraceWriter.php  |     4 | NDJSON trace writes  |

**Total: 16 violations** — These use raw `file_put_contents()` for NDJSON output. Should migrate to Filesystem.

### Category 6: Production runtime violations — MUST MIGRATE (Part 5)

| Component                                  | Count | Operation                                                                   | Severity                           |
|--------------------------------------------|------:|-----------------------------------------------------------------------------|------------------------------------|
| BladeTemplateEngine.php                    |     1 | `unlink()` compiled view cache                                              | HIGH                               |
| DatabaseExporter.php                       |     2 | `mkdir()`, `file_put_contents()` for SQL dumps                              | HIGH                               |
| MigrationGenerator.php                     |     3 | `mkdir()`, `file_put_contents()`, `file_get_contents()` for migration stubs | HIGH                               |
| MigrateCommand.php                         |     1 | `file_get_contents()` for migration files                                   | HIGH                               |
| CacheRouteTable.php                        |     3 | `mkdir()`, `file_put_contents()` for route cache                            | MEDIUM                             |
| LoadCachedRoutes.php                       |     2 | `unlink()` for stale cache                                                  | MEDIUM                             |
| CodeGenerator.php                          |     3 | `mkdir()`, `file_put_contents()` for generated code                         | MEDIUM                             |
| RunApplicationOnPhpBuiltInServer.php       |     3 | `mkdir()`, `file_put_contents()`, `fclose()`                                | LOW (dev server)                   |
| RegisterConfigCommands.php                 |     3 | `mkdir()`, `file_put_contents()` for config                                 | LOW (setup)                        |
| FileBackedHmacKeyRingCodec.php             |     1 | `file_get_contents()` for key ring                                          | MEDIUM                             |
| WebhookDispatcher.php + DeliverWebhook.php |     2 | `file_get_contents()` for HTTP calls                                        | LOW (HTTP wrapper, not filesystem) |
| DataExporter.php                           |     2 | `fopen('php://temp')`, `fclose()`                                           | LOW (stream, not filesystem)       |

**Total: ~26 production runtime violations that should migrate to Filesystem.**

### Category 7: Legitimate non-filesystem I/O — ALLOWED

| Component            | Count | Notes                                                  |
|----------------------|------:|--------------------------------------------------------|
| App.php              |     1 | `file_get_contents('php://input')` — HTTP request body |
| NativeYamlParser.php |     1 | YAML file read for schema validation                   |
| RenamePasskey files  |     3 | `rename` method name, not filesystem rename            |

### Category 8: Observability/HOW_THIS_WORKS docs — ALLOWED

Documentation references to file operations, not code.

## Priority for Migration (Part 5)

1. **BladeTemplateEngine.php** — `unlink()` for compiled view cache (security: stale compiled code)
2. **DatabaseExporter.php** — SQL dump writes (data safety)
3. **MigrationGenerator.php** — Migration stub writes (code generation safety)
4. **CacheRouteTable.php / LoadCachedRoutes.php** — Route cache management (routing correctness)
5. **CodeGenerator.php** — Generated code writes (code generation safety)

Lower priority (tooling, dev server, setup):

- PreCommit validators (tooling context)
- RunApplicationOnPhpBuiltInServer (dev server)
- RegisterConfigCommands (one-time setup)
