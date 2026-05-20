# TODO-006 Context Loaded

## Agent Context Loaded

- generated at: `2026-05-20 19:50:35 CEST`
- current branch: `architecture/todo-006-framework-entrypoint-object-graph`
- current worktree: `/home/shomsy/projects/avax-todo-006`
- base commit: `683e6fb250acc18ede8cda109f5b3fcc8a8efda7`
- dirty status before evidence completion: scoped TODO-006 Slice A files and evidence only
- AGENTS.md read: YES
- .agents/AGENTS.md read: ABSENT
- .agents/GOVERNANCE_INDEX.md read: YES
- how-to files discovered: 22
- how-to files read/applied: 19
- skills discovered: 18
- skills used: `avax-enterprise-remediation`, `avax-source-of-truth-resolver`, `avax-autonomous-backlog-loop`, `avax-enterprise-codecraft`, `avax-component-dogfooding`, `avax-runtime-performance-cache`, `avax-api-compatibility-contract`, `avax-test-evidence-quality`, `avax-observability-failure-semantics`, `avax-security-threat-model`, `review`, `validation`, `testing`, `refactor`, `performance`, `security`
- skills skipped with reason: `recovery` skipped because no rollback/stash/old-code restoration is part of Slice A; `documentation` skill referenced by index but file absent
- memory/learning files discovered: `.agents/management/learning/README.md`, `.agents/management/memories/README.md`, `.agents/memory/.gitkeep`
- memory/learning files used: `.agents/management/learning/README.md`, `.agents/management/memories/README.md`
- project truth files read: `AGENTS.md`, `.agents/GOVERNANCE_INDEX.md`, `TODO.md`, `fix-this.md`, `CURRENT_TRUTH.md`, `EVIDENCE/EXECUTION.md`, `.agents/management/CURRENT_TRUTH.md`, `.agents/management/TODO.md`, `.agents/management/ACTIVE.md`
- review evidence read: review-reconciliation source inventory, finding clusters, source-finding coverage, security-runtime escalation, post-round-002 merge and backlog reconciliation summaries
- assigned fix-this TODO: `TODO-006`
- source clusters read: `CLUSTER-007`, `CLUSTER-009`, `CLUSTER-010`
- source finding IDs read: `SCR-0388`, `SCR-0391`, `SCR-0393`, `SCR-0410`, `SCR-0420`, `HTD-0388`, `HTD-0391`, `HTD-0392`, `HTD-0393`, `HTD-0394`, `HTD-0410`, `HTD-0420`, `SAI-0167`, `SAI-0168`, `SAI-0169`, `SAI-0171`, `SAI-0172`, `SAI-0173`, `SAI-0174`, `SAI-0176`, `SAI-0177`, `SAI-0213`, `SAI-0214`, `SAI-0225`, `SAI-0226`, `SAI-0230`, `OLD-FIX-047`, `OLD-FIX-048`, `OLD-FIX-049`, `OLD-FIX-050`, `OLD-FIX-162`, `OLD-FIX-163`, `OLD-FIX-165`, `OLD-FIX-166`
- applicable governance warnings: PublicSurface must delegate; runtime flows execute; Configuration/Builders assembles; no hidden fallback construction; no fake GREEN; PARTIAL continues after this slice
- accepted YELLOW constraints: pre-existing ProcessPool failures, pre-existing PHPStan findings, pre-existing runtime-composition leaks, pre-existing direct-instantiation findings outside Slice A
- pre-existing dirty files: none on main; TODO-006 branch dirty only with scoped Slice A changes

## Classification Of Discovered Resources

- MANDATORY_BOOT: `AGENTS.md`, `.agents/GOVERNANCE_INDEX.md`, `.agents/skills/avax-enterprise-remediation/SKILL.md`, `.agents/skills/index.md`, `.agents/how-to/how-to-use-ai-assisted-execution.md`, `TODO.md`, `fix-this.md`
- TASK_RELEVANT_SKILLS: all skills listed in Skills Used
- TASK_RELEVANT_HOW_TO: AI-assisted execution, code review, architecture, design components, runtime composition, dependency injection, modern PHP, clean code, coding standards, code style, security, performance, unit test, production readiness, document, advanced architecture, DDD extension, git, dogfooding
- PROJECT_LEARNING: `.agents/management/learning/README.md`
- PROJECT_MEMORY: `.agents/management/memories/README.md`, placeholders only under `.agents/memory`
- CURRENT_TRUTH: current git state plus `TODO.md`, `fix-this.md`, latest evidence; root `CURRENT_TRUTH.md` is stale
- EVIDENCE: post-round-002 merge/backlog evidence, review-reconciliation evidence, TODO-006 Slice A evidence
- ADVISORY_ONLY: older V5.9/AuthBuilder material until TODO-007
- STALE_OR_SKIP_WITH_REASON: archives, aggregate dumps, old generated reports outside TODO-006 scope, binary security key, absent documentation skill

## Learning And Memory Impact

- PublicSurface leakage lesson changed the Slice A design by requiring `App` to receive a ready dispatcher instead of lazily creating it.
- PHPUnit named-argument lesson keeps assertions positional in modified tests.
- Forbidden folder lesson allows `Configuration/Builders` only because local governance explicitly allows it for assembly builders.
- Memory README says memory is advisory and validation proves truth; no memory file overrides current git/evidence.
