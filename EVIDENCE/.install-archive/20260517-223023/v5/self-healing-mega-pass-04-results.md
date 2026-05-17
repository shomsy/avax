# V5 Self-Healing Mega Pass 04 — Results

**Date:** 2026-05-11
**Branch:** main

## Part 1: Metadata-Type File Operation Gap Audit

### Scope

All production source files (`components/` and `framework/`) using metadata-type file operations:
`is_dir`, `is_file`, `file_exists`, `glob`, `scandir`

Excludes: `tests/`, `tooling/`, `benchmarks/`, `EVIDENCE/`, `.qoder/`, `vendor/`

### Total Count

| Metric                                        | Count |
|-----------------------------------------------|-------|
| Total raw occurrences                         | 106   |
| Unique files affected                         | 75    |
| Files in Filesystem component (ALLOWED_OWNER) | 18    |
| Files outside Filesystem component            | 57    |

### Classification by Component Area

#### 1. Application/Cache — 11 occurrences, 9 files

| File                               | Function    | Classification                                 |
|------------------------------------|-------------|------------------------------------------------|
| CompiledCacheFreshness.php:166     | file_exists | MIGRATE_TO_FILESYSTEM                          |
| CompiledCacheFreshness.php:189     | file_exists | MIGRATE_TO_FILESYSTEM                          |
| CompiledCacheFreshness.php:207     | file_exists | MIGRATE_TO_FILESYSTEM                          |
| CompiledCacheManifest.php:164      | file_exists | MIGRATE_TO_FILESYSTEM                          |
| CompiledCacheManifestEntry.php:133 | file_exists | MIGRATE_TO_FILESYSTEM                          |
| CheckCompiledCacheIsFresh.php:23   | file_exists | MIGRATE_TO_FILESYSTEM                          |
| CompiledCacheDirectory.php:26      | is_file     | ALLOWED_BOOTSTRAP (already classified in gate) |
| CompiledCacheSource.php:25         | file_exists | MIGRATE_TO_FILESYSTEM                          |
| CompiledCacheSource.php:36         | file_exists | MIGRATE_TO_FILESYSTEM                          |
| ClearCompiledCache.php:73          | glob        | MIGRATE_TO_FILESYSTEM                          |
| ReadCompiledCache.php:64           | file_exists | MIGRATE_TO_FILESYSTEM                          |

#### 2. Application/Config — 9 occurrences, 7 files

| File                       | Function               | Classification                             |
|----------------------------|------------------------|--------------------------------------------|
| ConfigLoader.php:13        | is_dir                 | ALLOWED_BOOTSTRAP (config bootstrap)       |
| ConfigLoader.php:23        | glob                   | ALLOWED_BOOTSTRAP (config bootstrap)       |
| ConfigLoader.php:35        | file_exists            | ALLOWED_BOOTSTRAP (config bootstrap)       |
| FileLoader.php:58          | file_exists            | MIGRATE_TO_FILESYSTEM                      |
| PHPArrayFileLoader.php:18  | file_exists            | MIGRATE_TO_FILESYSTEM                      |
| ConfigFileLoader.php:56    | file_exists            | MIGRATE_TO_FILESYSTEM                      |
| AppPath.php:37             | file_exists            | ALLOWED_BOOTSTRAP (project root discovery) |
| LoadConfiguration.php:37   | file_exists            | MIGRATE_TO_FILESYSTEM                      |
| EnvironmentDetector.php:63 | is_file('/.dockerenv') | ALLOWED_BOOTSTRAP (runtime detection)      |

#### 3. Application/Container — 3 occurrences, 3 files

| File                              | Function            | Classification                      |
|-----------------------------------|---------------------|-------------------------------------|
| CreateDependencyBlueprint.php:116 | is_file + filemtime | MIGRATE_TO_FILESYSTEM               |
| CreateServiceBlueprint.php:119    | is_file + filemtime | MIGRATE_TO_FILESYSTEM               |
| tools/graph.php:17                | is_file             | ALLOWED_TOOLING (container tooling) |

#### 4. DataStack/Database — 5 occurrences, 3 files

| File                   | Function    | Classification        |
|------------------------|-------------|-----------------------|
| MigrationLoader.php:24 | file_exists | MIGRATE_TO_FILESYSTEM |
| MigrationLoader.php:46 | is_dir      | MIGRATE_TO_FILESYSTEM |
| MigrationEngine.php:19 | glob        | MIGRATE_TO_FILESYSTEM |
| SeederCommand.php:13   | is_dir      | MIGRATE_TO_FILESYSTEM |
| SeederCommand.php:17   | glob        | MIGRATE_TO_FILESYSTEM |

#### 5. HTTP/Session — 1 occurrence, 1 file

| File                    | Function | Classification        |
|-------------------------|----------|-----------------------|
| FileSessionStore.php:89 | is_dir   | MIGRATE_TO_FILESYSTEM |

#### 6. Identity/Tokens — 1 occurrence, 1 file

| File                              | Function              | Classification                                 |
|-----------------------------------|-----------------------|------------------------------------------------|
| FileBackedHmacKeyRingCodec.php:51 | is_file + is_readable | ALLOWED_BOOTSTRAP (already classified in gate) |

#### 7. Operations/Logging — 2 occurrences, 2 files

| File                      | Function | Classification        |
|---------------------------|----------|-----------------------|
| RotatingFileWriter.php:77 | is_file  | MIGRATE_TO_FILESYSTEM |
| RotatingFileWriter.php:55 | glob     | MIGRATE_TO_FILESYSTEM |

#### 8. Presentation/View — 1 occurrence, 1 file

| File                       | Function | Classification        |
|----------------------------|----------|-----------------------|
| BladeTemplateEngine.php:44 | glob     | MIGRATE_TO_FILESYSTEM |

#### 9. SystemDesign — 2 occurrences, 1 file

| File                    | Function    | Classification        |
|-------------------------|-------------|-----------------------|
| SystemDesignKit.php:457 | file_exists | NEEDS_DESIGN_DECISION |
| SystemDesignKit.php:464 | file_exists | NEEDS_DESIGN_DECISION |

#### 10. Framework/Doctor — 10 occurrences, 6 files

| File                       | Function    | Classification                                 |
|----------------------------|-------------|------------------------------------------------|
| CheckAutoload.php:16       | is_file     | ALLOWED_TOOLING (doctor is diagnostic tooling) |
| CheckAutoload.php:26       | is_file     | ALLOWED_TOOLING                                |
| CheckConfiguration.php:25  | is_file     | ALLOWED_TOOLING                                |
| CheckConfiguration.php:29  | is_file     | ALLOWED_TOOLING                                |
| CheckMemoryGuard.php:25    | is_file     | ALLOWED_TOOLING                                |
| CheckRuntimeMode.php:24-40 | is_dir (5x) | ALLOWED_TOOLING                                |
| CheckWarmSafety.php:26     | is_file     | ALLOWED_TOOLING                                |

#### 11. Framework/PreCommit — 52 occurrences, 28 files

All PreCommit files are diagnostic/tooling behavior. Classification: **ALLOWED_TOOLING**

| File                                            | Function                  |
|-------------------------------------------------|---------------------------|
| PreCommit.php:160,215                           | is_dir                    |
| PreCommitValidator.php:192,254                  | is_dir                    |
| ValidationReport.php:160                        | is_dir                    |
| TodoGenerator.php:78,83                         | is_dir, file_exists       |
| ReportStorage.php:69,83,87                      | file_exists, is_dir, glob |
| PreCommitReportWriter.php:42,68,190,213,225,229 | is_dir, file_exists, glob |
| CheckHowToRules.php:30,34,102                   | is_dir, glob, file_exists |
| CheckFileStructure.php:36                       | file_exists               |
| CheckForbiddenWords.php:36                      | file_exists               |
| CheckNamingConventions.php:44                   | file_exists               |
| CheckPhpSyntax.php:32                           | file_exists               |
| CheckPublicSurfaceRules.php:28                  | file_exists               |
| DetectArchitectureViolations.php:35,42          | is_dir, file_exists       |
| DetectDeprecatedCode.php:40                     | file_exists               |
| DetectLegacyAliases.php:33,73                   | file_exists               |
| DetectLegacyCode.php:46,94,110                  | file_exists, is_dir       |
| DetectTodoComments.php:33                       | file_exists               |
| DetectToolingScripts.php:33,53                  | file_exists, is_file      |
| RunExternalToolingScripts.php:40                | is_dir                    |
| HowToRulesValidator.php:37,41,113               | is_dir, glob, file_exists |
| DeprecatedCodeValidator.php:33                  | file_exists               |
| FileStructureValidator.php:264,272              | is_dir                    |
| LegacyCodeValidator.php:72,254,307,310,320,327  | file_exists, is_dir, glob |
| NamingConventionValidator.php:50                | file_exists               |
| PhpSyntaxValidator.php:40                       | file_exists               |
| ScriptRunnerValidator.php:90,461                | is_dir                    |
| SecurityValidator.php:57,232                    | file_exists               |
| TodoCommentValidator.php:33                     | file_exists               |
| ToolingIntegrationValidator.php:31              | file_exists               |

#### 12. Framework/Runtime — 8 occurrences, 3 files

| File                                     | Function    | Classification                                 |
|------------------------------------------|-------------|------------------------------------------------|
| RunApplicationOnPhpBuiltInServer.php:45  | is_dir      | ALLOWED_BOOTSTRAP (already classified in gate) |
| RunApplicationOnPhpBuiltInServer.php:57  | file_exists | ALLOWED_BOOTSTRAP                              |
| RunApplicationOnPhpBuiltInServer.php:146 | file_exists | ALLOWED_BOOTSTRAP                              |
| Server.php:22                            | file_exists | ALLOWED_BOOTSTRAP (already classified in gate) |
| Server.php:57                            | file_exists | ALLOWED_BOOTSTRAP                              |
| Server.php:70                            | is_dir      | ALLOWED_BOOTSTRAP (already classified in gate) |

#### 13. Framework/Configuration — 2 occurrences, 2 files

| File                                | Function | Classification    |
|-------------------------------------|----------|-------------------|
| LoadApplicationConfiguration.php:50 | is_file  | ALLOWED_BOOTSTRAP |
| LoadRuntimeConfiguration.php:50     | is_file  | ALLOWED_BOOTSTRAP |

#### 14. Framework/Flows — 1 occurrence, 1 file

| File                               | Function | Classification    |
|------------------------------------|----------|-------------------|
| ConfiguredRoutesHttpHandler.php:48 | is_file  | ALLOWED_BOOTSTRAP |

#### 15. Framework/Foundation — 1 occurrence, 1 file

| File               | Function | Classification    |
|--------------------|----------|-------------------|
| ProjectPath.php:21 | is_dir   | ALLOWED_BOOTSTRAP |

### Summary Counts

| Category                                      | Count |
|-----------------------------------------------|-------|
| ALLOWED_OWNER (Filesystem component)          | 18    |
| ALLOWED_TOOLING (PreCommit, Doctor)           | 58    |
| ALLOWED_BOOTSTRAP (config, runtime, security) | 22    |
| MIGRATE_TO_FILESYSTEM                         | 30    |
| NEEDS_DESIGN_DECISION                         | 2     |

### MIGRATE_TO_FILESYSTEM Items (30)

These are production runtime code doing metadata file I/O that should use Filesystem API:

1. CompiledCacheFreshness.php (3x file_exists)
2. CompiledCacheManifest.php (1x file_exists)
3. CompiledCacheManifestEntry.php (1x file_exists)
4. CheckCompiledCacheIsFresh.php (1x file_exists)
5. CompiledCacheSource.php (2x file_exists)
6. ClearCompiledCache.php (1x glob)
7. ReadCompiledCache.php (1x file_exists)
8. FileLoader.php (1x file_exists)
9. PHPArrayFileLoader.php (1x file_exists)
10. ConfigFileLoader.php (1x file_exists)
11. LoadConfiguration.php (1x file_exists)
12. CreateDependencyBlueprint.php (1x is_file + filemtime)
13. CreateServiceBlueprint.php (1x is_file + filemtime)
14. MigrationLoader.php (1x file_exists, 1x is_dir)
15. MigrationEngine.php (1x glob)
16. SeederCommand.php (1x is_dir, 1x glob)
17. FileSessionStore.php (1x is_dir)
18. RotatingFileWriter.php (1x is_file, 1x glob)
19. BladeTemplateEngine.php (1x glob)
20. SystemDesignKit.php (2x file_exists — NEEDS_DESIGN_DECISION)

### Filesystem API Gaps Confirmed

Current Filesystem API supports: exists, isReadable, isWritable
Missing for metadata operations:

- `isFile()` — distinguish file from directory
- `isDirectory()` — distinguish directory from file
- `listFiles()` — list files with optional pattern (glob equivalent)
- `listDirectory()` — already exists but not used everywhere

### Decision Required

The 30 MIGRATE_TO_FILESYSTEM items need one of:

1. **Filesystem API expansion** — add `isFile()`, `isDirectory()`, `listFiles()` capabilities
2. **Direct migration** — use existing `exists()`, `listDirectory()` where possible
3. **ALLOWED classification** — justify why specific uses are acceptable

Part 2 will add missing Filesystem metadata capabilities.
Part 3 will update the raw file gate to include metadata functions.

## Next Action

Part 2: Add `isFile()`, `isDirectory()`, `listFiles()` to Filesystem component
