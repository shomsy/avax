# Avax PreCommit & Tooling System Implementation

## Phase 1: ScriptRunnerValidator (Dynamic Script Execution)

- [ ] Create ScriptRunnerValidator that dynamically discovers scripts in tooling/
- [ ] Support multiple script types: PHP, Python, Go, NodeJS, Shell (.sh)
- [ ] Return structured results from script execution
- [ ] Make it extensible for future script types

## Phase 2: Extend ToolingIntegrationValidator

- [ ] Use ScriptRunner to dynamically discover scripts
- [ ] Allow configuration via context
- [ ] Make it extensible

## Phase 3: Extend LegacyCodeValidator

- [ ] Add detection for more legacy patterns: DataFoundation, components/Legacy
- [ ] Add detection for legacy aliases in compat.php
- [ ] Add detection for legacy folders that should be deleted
- [ ] Add detection for shriomove and folder cleanup suggestions

## Phase 4: Avax CLI Integration

- [ ] Add avax tooling:run command
- [ ] Ensure avax validate calls tooling properly
- [ ] Add avax legacy:audit command

## Phase 5: Store Reports

- [ ] Keep validation history
- [ ] Generate TODO from failures

## Implementation Status:

- [ ] ScriptRunnerValidator created
- [ ] ToolingIntegrationValidator extended
- [ ] LegacyCodeValidator extended
- [ ] Avax CLI commands added
