# PreCommit Inventory

## Phase 1: Analysis Report

### 1.1 Framework PreCommit Namespace

Location: `framework/System/Capabilities/PreCommit/`

| Component                           | Status   | Notes                           |
|-------------------------------------|----------|---------------------------------|
| PreCommit.php                       | ✅ Exists | Main orchestrator, ~230 lines   |
| Configuration/PreCommitConfig.php   | ✅ Exists | Central configuration           |
| Capabilities/*.php                  | ✅ Exists | 13 capability checks            |
| Models/*.php                        | ✅ Exists | PreCommitIssue, PreCommitResult |
| Report/ReportStorage.php            | ✅ Exists | Report storage                  |
| Reports/PreCommitReportWriter.php   | ✅ Exists | Report generation               |
| Todo/TodoGenerator.php              | ✅ Exists | ToDo generation                 |
| ValidationChain/ValidationChain.php | ✅ Exists | Check orchestration             |
| Validators/*.php                    | ✅ Exists | 11 validators                   |

### 1.2 Tooling Scripts

Location: `tooling/`

| Script                                 | Language | Purpose            | Status   |
|----------------------------------------|----------|--------------------|----------|
| pre-commit/run-pre-commit.php          | PHP      | PreCommit runner   | ✅ Exists |
| audit_broken_refs.php                  | PHP      | Reference audit    | ✅ Exists |
| check-namespaces.sh                    | Shell    | Namespace check    | ✅ Exists |
| check-superglobals.php                 | PHP      | Superglobal audit  | ✅ Exists |
| deep_audit.php                         | PHP      | Deep code audit    | ✅ Exists |
| fix_namespaces.sh                      | Shell    | Namespace fixer    | ✅ Exists |
| generate_missing_report.php            | PHP      | Missing report gen | ✅ Exists |
| generate-report.sh                     | Shell    | Report generation  | ✅ Exists |
| lint-mermaid.php                       | PHP      | Mermaid linting    | ✅ Exists |
| lint-mermaid.py                        | Python   | Mermaid linting    | ✅ Exists |
| migrate_application.sh                 | Shell    | App migration      | ✅ Exists |
| normalize_namespaces.php               | PHP      | Namespace norm.    | ✅ Exists |
| replace_resolution_pipeline_methods.py | Python   | Method replacer    | ✅ Exists |
| revert_application.sh                  | Shell    | App revert         | ✅ Exists |
| revert_datastack.sh                    | Shell    | Datastack revert   | ✅ Exists |
| test.php                               | PHP      | Test runner        | ✅ Exists |

### 1.3 Missing Components

According to original TODO requirements:

| Component                           | Required | Status           |
|-------------------------------------|----------|------------------|
| CLI command (`php avax pre-commit`) | Yes      | ✅ **DONE**      |
| Git hook installer script           | Yes      | ✅ **DONE**      |
| ExternalToolRunner adapter          | Yes      | ✅ Exists        |
| Phased implementation               | Yes      | ✅ **DONE**      |

### 1.4 Implementation Phases Status

| Phase | Description            | Status     |
|-------|------------------------|------------|
| 1     | Analyze existing state | ✅ Complete |
| 2     | Propose new structure  | ✅ Complete |
| 3     | Connect tooling + CLI  | ✅ Complete |
| 4     | Add additional checks  | ✅ Complete |
| 5     | Add auto-fix + delete  | ⚠️ Partial (model exists, UI not implemented) |
| 6     | Add tests              | ✅ **DONE** |
| 7     | Document               | ✅ **DONE** |

### 1.5 Bugs Fixed

| Bug | File | Fix |
|-----|------|-----|
| `array_any()` not a PHP function | `RunExternalToolingScripts.php` | Replaced with `foreach` loop |

### 1.6 Architecture Notes

The PreCommit system uses TWO validation approaches:

1. **Capabilities** (`Capabilities/*.php`): Used by `PreCommit.php` - run checks sequentially, return `list<PreCommitIssue>`
2. **Validators** (`Validators/*.php`) + `ValidationChain`: Alternative system via `PreCommitValidator.php` - Chain of Responsibility pattern

Both systems coexist. `ValidationChain` is NOT used by `PreCommit.php` - it is used by `PreCommitValidator.php` which is a separate entry point for the `avax validate` command.

### 1.7 Conclusion

**Existing state**: ~95% of PreCommit system is now complete and functional.

**Completed work**:
- CLI command: `php avax pre-commit [--fix] [--full]`
- Git hook installer: `php tooling/pre-commit/install-hook.php [--force|--uninstall]`
- Unit tests for `PreCommitIssue`, `PreCommitResult`, `PreCommitConfig`
- Bug fix: `array_any()` replaced with proper PHP loop

**Remaining**:
- Auto-fix UI not fully implemented (model exists)
