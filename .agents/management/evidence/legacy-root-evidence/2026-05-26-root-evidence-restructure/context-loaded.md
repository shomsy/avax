# Context-Loaded Evidence

## Execution Metadata

| Field | Value |
|---|---|
| **Date** | 2026-05-24 |
| **Branch** | main |
| **Worktree** | /home/shomsy/projects/avax-auth-rewrite-v2 |
| **Base Commit** | 337e17c2a |
| **Dirty** | No |
| **Mode** | Harness-Full (governance loading) |

## Governance Loading

### AGENTS.md
- **Status:** LOADED
- **Version:** 3.0.0

### How-To Files Discovered

| File | Status |
|---|---|
| `.agents/how-to/00-reading-order.md` | LOADED |
| `.agents/how-to/README.md` | LOADED |
| `.agents/how-to/architecture/how-to-architecture.md` | LOADED |
| `.agents/how-to/architecture/how-to-architecture-decisions.md` | LOADED |
| `.agents/how-to/architecture/how-to-architecture-extension-with-ddd.md` | LOADED |
| `.agents/how-to/architecture/how-to-engineering-laws.md` | LOADED |
| `.agents/how-to/architecture/how-to-events-listeners-event-sourcing-cqrs-realtime.md` | LOADED |
| `.agents/how-to/architecture/how-to-runtime-composition.md` | LOADED |
| `.agents/how-to/architecture/how-to-use-advanced-architecture-patterns.md` | LOADED |
| `.agents/how-to/architecture/how-to-use-ai-assisted-execution.md` | LOADED |
| `.agents/how-to/modeling/how-to-model-flows.md` | LOADED |
| `.agents/how-to/components/how-to-design-components.md` | LOADED |
| `.agents/how-to/components/how-to-dogfooding.md` | LOADED |
| `.agents/how-to/documentation/how-to-document.md` | LOADED |
| `.agents/how-to/implementation/how-to-clean-code.md` | LOADED |
| `.agents/how-to/implementation/how-to-code-style.md` | LOADED |
| `.agents/how-to/implementation/how-to-coding-standards.md` | LOADED |
| `.agents/how-to/implementation/how-to-dependency-injection.md` | LOADED |
| `.agents/how-to/implementation/how-to-modern-php-attributes-di.md` | LOADED |
| `.agents/how-to/verification/how-to-code-review.md` | LOADED |
| `.agents/how-to/verification/how-to-data-systems.md` | LOADED |
| `.agents/how-to/verification/how-to-production-readiness.md` | LOADED |
| `.agents/how-to/verification/how-to-system-security.md` | LOADED |
| `.agents/how-to/verification/how-to-system-performance.md` | LOADED |
| `.agents/how-to/verification/how-to-unit-test.md` | LOADED |
| `.agents/how-to/project/how-to-write-avax.md` | LOADED |
| `.agents/how-to/project/how-to-git.md` | LOADED |

### Missing How-To Files (per 00-reading-order.md)

| File | Status |
|---|---|
| `.agents/how-to/modeling/how-to-domain-discovery.md` | MISSING (known, planned) |
| `.agents/how-to/modeling/how-to-scenario-input.md` | MISSING (known, planned) |

## Skill Applicability Matrix

| Skill | Loaded | Relevant | Used | Reason |
|---|---|---|---|---|
| avax-api-compatibility-contract | Yes | No | No | No public API changes in this task. Governance loading only. |
| avax-autonomous-backlog-loop | Yes | No | No | No autonomous backlog execution requested. |
| avax-component-dogfooding | Yes | No | No | No component implementation or refactor in scope. |
| avax-enterprise-codecraft | Yes | No | No | No production code changes in this task. |
| avax-enterprise-remediation | Yes | Yes | Yes | Mandatory bootloader for all AvaX tasks. |
| avax-observability-failure-semantics | Yes | No | No | No runtime/IO/security/persistence changes in scope. |
| avax-runtime-performance-cache | Yes | No | No | No performance/cache/hot-path changes in scope. |
| avax-security-threat-model | Yes | No | No | No security-sensitive implementation in scope. |
| avax-source-of-truth-resolver | Yes | Yes | Yes | Required for every task to resolve current project state. |
| avax-test-evidence-quality | Yes | No | No | No test changes in scope. |
| performance | Yes | No | No | No performance optimization in scope. |
| recovery | Yes | No | No | No recovery from old sources in scope. |
| refactor | Yes | No | No | No refactoring in scope. |
| review | Yes | No | No | No code review task requested. |
| security | Yes | No | No | No security implementation in scope. |
| testing | Yes | No | No | No testing task in scope. |
| validation | Yes | Yes | Yes | Governance loading requires validation of completeness. |

## Skills Discovered: 17
## Skills Used: 3 (avax-enterprise-remediation, avax-source-of-truth-resolver, validation)

## Source-of-Truth Decision

- **Active task:** Governance loading and skill matrix creation (mandatory preflight)
- **Current status:** main is clean, no pending changes
- **Source of truth:** current git state (clean main)
- **Backlog:** No active TODO selected — this is a governance loading step

## Rules Applied

- AGENTS.md §2 (Harness-Full Mode)
- AGENTS.md §6 (Full .agents Operating System Rule)
- AGENTS.md §7 (Mandatory Skill Routing)
- AGENTS.md §10 (Required Preflight)
- AGENTS.md §34 (Agent Output Contract)
- 00-reading-order.md (all listed files loaded)

## Rules Not Applicable

- No production code changes — enterprise-codecraft, component-dogfooding, runtime-performance-cache, security-threat-model, api-compatibility-contract, test-evidence-quality not applicable
- No autonomous backlog — autonomous-backlog-loop not applicable
- No review task — review skill not applicable
- No refactor — refactor skill not applicable
- No recovery — recovery skill not applicable
- No performance work — performance skill not applicable
- No security implementation — security skill not applicable
- No testing task — testing skill not applicable

## Validation

- All how-to files from 00-reading-order.md discovered and loaded (except known-planned missing files)
- All 17 skill SKILL.md files discovered and loaded
- Skill applicability matrix complete
- No stale or missing governance files (except known planned)

## Remaining Risks

- Two planned how-to files remain missing: `how-to-domain-discovery.md` and `how-to-scenario-input.md` (known, per reading order)

## Next Allowed Action

- Awaiting user task instruction with full governance context loaded
