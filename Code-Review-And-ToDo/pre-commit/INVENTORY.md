# Pre-Commit Tooling Inventory

Created: 2025-01-XX
Purpose: Inventory of all tooling scripts for PreCommit system integration

## Tooling Scripts Analysis

### Root Level Scripts (tooling/)

| Script                                 | Language | Purpose                      | Status | Integration                 |
|----------------------------------------|----------|------------------------------|--------|-----------------------------|
| audit_broken_refs.php                  | PHP      | Find broken class references | Active | PreCommit Adapter candidate |
| check-namespaces.sh                    | Shell    | Check namespace validity     | Active | PreCommit Adapter candidate |
| check-superglobals.php                 | PHP      | Detect superglobal usage     | Active | Security check              |
| deep_audit.php                         | PHP      | Deep code audit              | Active | Standalone tool             |
| fix_namespaces.sh                      | Shell    | Fix namespace issues         | Active | Auto-fix candidate          |
| generate_missing_report.php            | PHP      | Generate missing report      | Active | Report tool                 |
| generate-report.sh                     | Shell    | Generate reports             | Active | Report tool                 |
| harness_installer.php                  | PHP      | Install test harness         | Active | Standalone                  |
| lint-mermaid.php                       | PHP      | Lint Mermaid diagrams        | Active | Standalone                  |
| lint-mermaid.py                        | Python   | Lint Mermaid (alt)           | Active | Standalone                  |
| migrate_application.sh                 | Shell    | Migrate app                  | Active | Standalone                  |
| move_prompts.php                       | PHP      | Move prompts                 | Active | Standalone                  |
| normalize_namespaces.php               | PHP      | Normalize namespaces         | Active | Auto-fix                    |
| replace_resolution_pipeline_methods.py | Python   | Replace methods              | Active | Standalone                  |
| revert_application.sh                  | Shell    | Revert app                   | Active | Standalone                  |
| revert_datastack.sh                    | Shell    | Revert datastack             | Active | Standalone                  |
| test.php                               | PHP      | Run tests                    | Active | Standalone                  |

### Architecture Scripts (tooling/architecture/)

| Script                      | Language | Purpose                 | Status | Integration     |
|-----------------------------|----------|-------------------------|--------|-----------------|
| check-docs-mirror.php       | PHP      | Check docs mirror       | Active | PreCommit check |
| check-duplicate-owners.php  | PHP      | Check duplicate owners  | Active | PreCommit check |
| check-forbidden-folders.php | PHP      | Check forbidden folders | Active | PreCommit check |
| check-namespace-drift.php   | PHP      | Check namespace drift   | Active | PreCommit check |
| check-public-surface.php    | PHP      | Check PublicSurface     | Active | PreCommit check |
| check-runtime-leaks.php     | PHP      | Check runtime leaks     | Active | PreCommit check |

### Docs Scripts (tooling/docs/)

| Script                          | Language | Purpose              | Status | Integration     |
|---------------------------------|----------|----------------------|--------|-----------------|
| validate-docs-mirror-source.php | PHP      | Validate docs source | Active | PreCommit check |
| validate-docs.php               | PHP      | Validate docs        | Active | PreCommit check |

### Refactor Scripts (tooling/refactor/)

| Script                                   | Language | Purpose                | Status | Integration     |
|------------------------------------------|----------|------------------------|--------|-----------------|
| check-component-suite-structure.php      | PHP      | Check structure        | Active | PreCommit check |
| check-docs-mirror.php                    | PHP      | Check docs mirror      | Active | PreCommit check |
| check-duplicate-owners.php               | PHP      | Check duplicate owners | Active | PreCommit check |
| check-forbidden-folders.php              | PHP      | Check forbidden        | Active | PreCommit check |
| check-namespace-drift.php                | PHP      | Check drift            | Active | PreCommit check |
| check-public-surface.php                 | PHP      | Check surface          | Active | PreCommit check |
| check-runtime-leaks.php                  | PHP      | Check leaks            | Active | PreCommit check |
| fix-composer-paths.php                   | PHP      | Fix composer paths     | Active | Auto-fix        |
| fix-database-namespace.sh                | Shell    | Fix DB namespace       | Active | Auto-fix        |
| fix-nested-system.php                    | PHP      | Fix nesting            | Active | Auto-fix        |
| freeze-component-taxonomy.php            | PHP      | Freeze taxonomy        | Active | Standalone      |
| freeze-recovered-components-taxonomy.php | PHP      | Freeze recovered       | Active | Standalone      |
| generate-datalayer.sh                    | Shell    | Generate datalayer     | Active | Standalone      |
| migrate-datafoundation.php               | PHP      | Migrate DataFoundation | Active | Migration       |
| migrate-datalayer-to-persistence.php     | PHP      | Migrate datalayer      | Active | Migration       |
| rename-applicationworkflow.php           | PHP      | Rename workflow        | Active | Rename          |
| rename-applicationworkflow.py            | Python   | Rename (alt)           | Active | Rename          |
| rename-applicationworkflow.sh            | Shell    | Rename (sh)            | Active | Rename          |
| rename-applicationworkflow2.sh           | Shell    | Rename v2              | Active | Rename          |
| repair-test-layer.php                    | PHP      | Repair tests           | Active | Repair          |

## PreCommit Validators (framework/System/Capabilities/PreCommit/Validators/)

| Validator                   | Purpose                  | Status | Blocking     |
|-----------------------------|--------------------------|--------|--------------|
| NamingConventionValidator   | Check naming conventions | Active | Yes          |
| HowToRulesValidator         | Check how-to-*.md rules  | Active | Yes          |
| PhpSyntaxValidator          | PHP syntax check         | Active | Yes          |
| FileStructureValidator      | Folder structure         | Active | Yes          |
| SecurityValidator           | Security patterns        | Active | Yes          |
| LegacyCodeValidator         | Legacy code detection    | Active | No (warning) |
| DeprecatedCodeValidator     | Deprecated code          | Active | No (warning) |
| TodoCommentValidator        | TODO/FIXME comments      | Active | No (warning) |
| ToolingIntegrationValidator | Tooling conventions      | Active | No (info)    |
| ScriptRunnerValidator       | Run tooling scripts      | Active | Yes          |

## Classification

### Blockers (critical - block commit)

- PhpSyntaxValidator (syntax errors)
- NamingConventionValidator (forbidden names)
- HowToRulesValidator (rule violations)

### Warnings (non-blocking - allow with warning)

- LegacyCodeValidator
- DeprecatedCodeValidator
- TodoCommentValidator

### Info (informational)

- ToolingIntegrationValidator
- ScriptRunnerValidator (pass = info, fail = warning)

## Recommendations

### Integrate into PreCommit Chain

1. check-forbidden-folders.php → FileStructureValidator enhancement
2. check-public-surface.php → SecurityValidator enhancement
3. check-namespace-drift.php → NamingConventionValidator enhancement
4. check-namespaces.sh → NamingConventionValidator enhancement
5. check-superglobals.php → SecurityValidator enhancement
6. audit_broken_refs.php → ReferenceValidator new

### Keep as Standalone Tools

- deep_audit.php (too slow for hook)
- migrate_*.sh scripts (migration tools)
- repair scripts (explicit repair operations)
- refactor scripts (structural changes)

### Deprecate

- Duplicate scripts in refactor/ (same as architecture/)
- Old rename scripts (duplicates)

## Next Steps

1. Create PreCommit infrastructure (Phase 2)
2. Integrate architecture scripts → validators (Phase 3)
3. Enhance avax CLI (Phase 3)
4. Add hook installer (Phase 3)
