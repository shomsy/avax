# Avax PreCommit & Tooling System Implementation

This is the operational TODO for the PreCommit discipline system implementation.

---

## Current State : 2026-01-15

### What's Already Done

| Component                   | Location                                                                    | Status                            |
|-----------------------------|-----------------------------------------------------------------------------|-----------------------------------|
| PreCommit Main Orchestrator | `framework/System/Capabilities/PreCommit/PreCommit.php`                     | ✅ ~230 lines                      |
| Configuration               | `framework/System/Capabilities/PreCommit/Configuration/PreCommitConfig.php` | ✅ Complete                        |
| 13 Capability Checks        | `framework/System/Capabilities/PreCommit/Capabilities/*.php`                | ✅ Implemented                     |
| Models                      | `framework/System/Capabilities/PreCommit/Models/`                           | ✅ PreCommitIssue, PreCommitResult |
| Report Writer               | `framework/System/Capabilities/PreCommit/Reports/PreCommitReportWriter.php` | ✅ JSON + Markdown                 |
| Todo Generator              | `framework/System/Capabilities/PreCommit/Todo/TodoGenerator.php`            | ✅ Implemented                     |
| Tooling Runner              | `tooling/pre-commit/run-pre-commit.php`                                     | ✅ Works as script                 |

### What's Missing

| Component                           | Status              | Priority |
|-------------------------------------|---------------------|----------|
| CLI Command (`php avax pre-commit`) | ❌ Missing           | P0       |
| Git Hook Installer                  | ❌ Missing           | P0       |
| ExternalToolRunner Adapter          | ⚠️ Partially exists | P1       |
| Full External Tool Integration      | ⚠️ Partial          | P1       |
| Tests                               | ❌ Missing           | P2       |

---

## Implementation Plan

### Phase 1: CLI Command Integration (P0)

#### 1.1 Register PreCommit Commands in BuildApplication

File: `framework/System/Configuration/BuildApplication/ApplicationBuilder.php`

Add:

```php
// Add to consoleCommands registration
'pre-commit' => fn($args) => handlePreCommitCommand($args),
```

#### 1.2 Create PreCommit Command Handler

File: `framework/System/Flows/RunConsoleCommand/RunConsoleCommand.php`

Extend to handle pre-commit subcommands:

- `php avax pre-commit` - Run with touched files
- `php avax pre-commit --dry-run` - Dry-run mode (default)
- `php avax pre-commit --fix` - Auto-fix mode
- `php avax pre-commit --full` - Full project check
- `php avax pre-commit --report` - Show latest report
- `php avax pre-commit --install-hook` - Install git hook

#### 1.3 Update ConsoleKernel

File: `framework/System/PublicSurface/Console/ConsoleKernel.php`

Route pre-commit commands to PreCommit flow.

### Phase 2: Git Hook Installer (P0)

#### 2.1 Create Hook Installer Script

File: `tooling/pre-commit/install-hook.sh`

```bash
#!/bin/bash
# Installs git pre-commit hook

HOOK_PATH=".git/hooks/pre-commit"
SCRIPT_PATH="tooling/pre-commit/run-pre-commit.php"

# Create hook if not exists
if [ ! -f "$HOOK_PATH" ]; then
    echo "#!/bin/bash" > "$HOOK_PATH"
    echo "php $SCRIPT_PATH" >> "$HOOK_PATH"
    chmod +x "$HOOK_PATH"
    echo "✅ Installed git pre-commit hook"
else
    echo "⚠️ Hook already exists. Remove it first or manually update."
fi
```

#### 2.2 Add PHP-based Installer

File: `tooling/pre-commit/install-git-hook.php`

```php
#!/usr/bin/env php
<?php
// Installs git pre-commit hook via PHP
```

### Phase 3: Enhanced Tooling Integration (P1)

#### 3.1 ExternalToolRunner Adapter

Improve `framework/System/Capabilities/PreCommit/Capabilities/RunExternalToolingScripts.php`
to properly wrap external scripts with proper exit code handling.

#### 3.2 Tool Registry

Create `tooling/pre-commit/tool-registry.json` to document available external tools:

- `tooling/audit_broken_refs.php`
- `tooling/check-superglobals.php`
- `tooling/docs/validate-docs.php`

### Phase 4: Tests (P2)

Add tests for:

- `tests/Unit/Framework/System/Capabilities/PreCommit/PreCommitTest.php`
- `tests/Unit/Framework/System/Capabilities/PreCommit/PreCommitResultTest.php`
- `tests/Unit/Framework/System/Flows/RunConsoleCommand/PreCommitCommandTest.php`

---

## Tasks

### TODO Items

- [ ] **1.1** Add pre-commit command registration in `ApplicationBuilder.php`
- [ ] **1.2** Extend `RunConsoleCommand.php` to handle pre-commit subcommands
- [ ] **1.3** Update `ConsoleKernel.php` to route pre-commit commands
- [ ] **2.1** Create `tooling/pre-commit/install-hook.sh`
- [ ] **2.2** Create `tooling/pre-commit/install-git-hook.php`
- [ ] **3.1** Enhance `RunExternalToolingScripts.php` adapter
- [ ] **3.2** Create `tooling/pre-commit/tool-registry.json`
- [ ] **4.1** Add `PreCommitTest.php`
- [ ] **4.2** Add `PreCommitResultTest.php`
- [ ] **4.3** Add `PreCommitCommandTest.php`

### Commands to Run

After implementation:

```bash
# Test CLI integration
php avax pre-commit --help
php avax pre-commit

# Install git hook
php avax pre-commit --install-hook
# OR
php tooling/pre-commit/install-git-hook.php

# Run manually
php tooling/pre-commit/run-pre-commit.php
php tooling/pre-commit/run-pre-commit.php --full
php tooling/pre-commit/run-pre-commit.php --fix
```

---

## Related Files

- `framework/System/Capabilities/PreCommit/` - Core framework
- `tooling/pre-commit/run-pre-commit.php` - Standalone runner
- `Code-Review-And-ToDo/pre-commit/INVENTORY.md` - Detailed inventory

---

## Notes

- The framework PreCommit system is well-structured and follows the flow-oriented architecture
- CLI integration is the main missing piece
- Git hook can be installed via the script directly without CLI
- Reports save to `Code-Review-And-ToDo/pre-commit/`
- Touched files mode is default for git hooks, full mode for manual runs
