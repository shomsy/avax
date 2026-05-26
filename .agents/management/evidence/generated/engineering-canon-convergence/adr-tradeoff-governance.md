# ADR & Trade-off Governance Report

This report documents the audit and verification of the Architectural Decision Record (ADR) and tradeoff validation systems in AvaX.

## Verification Matrix

### 1. Template & Checker Synchronization
- **Template**: `.agents/templates/evidence/adr-tradeoff-decision.md`
- **Checker**: `check-adr-tradeoff-evidence.php`
- **Mandatory Headings Aligned**:
  - `Task`
  - `Decision`
  - `Context`
  - `Forces`
  - `Options Considered`
  - `Comparison Matrix`
  - `Chosen Option`
  - `Consequences`
  - `Reversibility`
  - `Fitness Function`
  - `Coupling Impact`
  - `Data Impact`
  - `Security / Runtime Impact`
  - `Accepted Debt`
  - `Owner`
  - `Review Date`

### 2. Strict Governance Enforcement
- In `--strict` mode, any changes to governance maps, indexes, checkers, runners, or core configuration files without corresponding `adr-tradeoff-decision.md` evidence are blocked and fail.
- Non-strict mode triggers a warning (`YELLOW`) when architecture-sensitive files are altered.

### 3. Fail-Path Tests
- Hardened in unit tests (`AdrTradeoffEvidenceCheckTest.php`) verifying:
  - Missing evidence in strict mode triggers exit code `1`.
  - Malformed template headings (e.g. missing `Review Date` or others) fail.
  - Complete template headings result in clean `GREEN` validation.

## Conclusion
The ADR and tradeoff decision governance plane is fully synchronized and operational.
