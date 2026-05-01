# PreCommit Enhancement Plan

## Information Gathered

### Existing PreCommit System (framework/System/Capabilities/PreCommit/)

- **PreCommitValidator.php** - Main entry point with CLI options
- **ValidationChain.php** - Chain of Responsibility pattern
- **Validators/** - 5 current validators:
    - NamingConventionValidator - forbids Services, Helpers, Utils, etc.
    - HowToRulesValidator - parses .agents/how-to/*.md rules
    - PhpSyntaxValidator - PHP syntax checking
    - FileStructureValidator - architecture structure checks
    - SecurityValidator - security patterns
- **Report/** - ReportStorage saves to .agents/reports/validation/
- **Todo/** - TodoGenerator creates .agents/management/TODO.md

### Tooling Scripts (tooling/)

- audit_broken_refs.php - detects broken class references
- deep_audit.php - avax.txt vs components/ audit
- check-namespaces.sh - namespace validation
- architecture/check-*.php - various architecture checks
- refactor/check-*.php - refactoring checks

### Current CLI (avax)

- Has `validate` command calling PreCommitValidator
- Has `architecture:check` calling tooling/architecture/*.php

## Plan

### Phase 1: Add New Validators to PreCommit Chain

1. **Create LegacyCodeValidator** (`Validators/LegacyCodeValidator.php`)
    - Detect legacy folders: DataFoundation, components/Legacy, etc.
    - Detect legacy aliases in compat.php
    - Report files that need cleanup

2. **Create DeprecatedCodeValidator** (`Validators/DeprecatedCodeValidator.php`)
    - Detect @deprecated markers in code
    - Track deprecated classes/interfaces/methods
    - Warn when using deprecated code

3. **Create TodoCommentValidator** (`Validators/TodoCommentValidator.php`)
    - Detect TODO, FIXME, XXX, HACK comments in staged files
    - Flag incomplete implementations

4. **Create ToolingIntegrationValidator** (`Validators/ToolingIntegrationValidator.php`)
    - Execute tooling scripts as part of validation chain
    - Parse output and integrate into validation report

### Phase 2: Update PreCommitValidator

5. **Update configureChain() method**
    - Add new validators to the chain
    - Allow enabling/disabling via options

### Phase 3: Update CLI and Tooling Integration

6. **Update avax CLI**
    - Add commands for individual tooling calls
    - Ensure validate command includes all checks

### Phase 4: Code Quality & Testing

7. **Add unit tests for validators**
8. **Verify integration works**

## Dependent Files

- framework/System/Capabilities/PreCommit/PreCommitValidator.php
- framework/System/Capabilities/PreCommit/ValidationChain/ValidationChain.php
- framework/System/Capabilities/PreCommit/Validators/ValidatorInterface.php
- avax (CLI entry point)

## Followup Steps

1. Create LegacyCodeValidator
2. Create DeprecatedCodeValidator
3. Create TodoCommentValidator
4. Update PreCommitValidator to add new validators
5. Test the chain
6. Verify CLI integration
