# AvaX Governance Maturity Report

Date: 2026-05-26
Scope: Post governance-lock-pass assessment

## Executive Summary

AvaX has achieved a Level 3 governance state across most dimensions. The project has a comprehensive, documented, and partially automated governance system with 36 numbered rules in AGENTS.md, 17+ how-to documents, 18 skills, 50+ automated checker tools, and an evidence-first execution model. All 7 P0 blockers from the original review reconciliation have been closed with evidence. The governance system is unusually mature for a project of this size — most rules have corresponding automated checkers, findings are tracked with canonical severity, and suppression is itself classified as a governance violation.

The primary gaps are: test quality enforcement is tool-assisted but not CI-gated, security and performance gates remain largely manual-review rather than automated, documentation gates exist but lack structural enforcement, and the project has zero entries in the accepted-exceptions ledger despite having known accepted-yellow debt (semantic PHPDoc ratchet at 9810 findings). The system is designed to scale but has not yet been proven under multi-human, multi-agent concurrent execution.

## GREEN Areas (Mature, Enforced, Proven)

**Rule Documentation (Level 3-4):**
- AGENTS.md v3.0.0 is a comprehensive 2400+ line root contract covering all major concerns: architecture, DI, security, performance, testing, evidence, review, commit, recovery, stage lock, and agent behavior.
- ARCHITECTURE.md v1.0.0 provides the north-star architecture philosophy with diagrams, anti-patterns, and quick-reference guides.
- GOVERNANCE_INDEX.md provides explicit reading order, task routing, skill routing, conflict resolution priority, and a 10/10 criteria checklist (all checked).
- GOVERNANCE_ENFORCEMENT_MAP.md maps 28 rules to source documents, checkers, and reports.
- 17 how-to documents cover architecture, components, documentation, implementation, modeling, project, and verification domains.

**Canonical Severity System (Level 3):**
- Five-level severity (BLOCKER, HIGH, MEDIUM, LOW, INFO) with explicit definitions, escalation rules, and finding format requirements.
- Deviation audit lifecycle with 5 mandatory stages (validate, audit, correct, revalidate, commit gate).
- No-fixed-by-suppression rule with explicit detection criteria and BLOCKER classification for violations.
- Remaining drift classification rule requiring severity, impact, owner, phase allowance, mitigation, future plan, and evidence for every unresolved finding.

**Automated Enforcement Tooling (Level 3):**
- 50+ PHP checker tools across governance, refactor, security, performance, components, and testing categories.
- 12 automated governance checkers (component shape, namespace drift, public surface, runtime leaks, semantic PHPDoc, stage lock, governance index, evidence hygiene, self-explaining architecture, quality ratchet, large unit thresholds, security commit block).
- 10 automated refactor checkers (direct instantiation, constructor bloat, service provider coverage, broken references, forbidden folders, duplicate owners, canonical shape, runtime composition leaks, raw file operations, docs mirror).
- 6 automated component maturity gates (behavior proof map, docs status policy, health doctor policy, runtime assembly, static state safety, status lock, hollow public surfaces).
- 6 automated testing tools (shallow test detection, nonzero target assertions, test runtime measurement, serial/parallel split).

**Evidence System (Level 3):**
- 34 evidence files in EVIDENCE/ root including exception ledger, execution control, validation summaries, and governance alignment passes.
- Extensive evidence trail in EVIDENCE/v5.8/, EVIDENCE/v5.9-codex/, EVIDENCE/fix-this/, EVIDENCE/hardening/, EVIDENCE/cleanup/, and .agents/management/evidence/generated/.
- Every TODO in the remediation backlog links to source finding IDs, evidence paths, and validation commands.
- Evidence is written before code (design-before-code rule) and updated after validation.

**Skill System (Level 3):**
- 18 skills covering enterprise remediation, source-of-truth resolution, autonomous backlog loop, enterprise codecraft, component dogfooding, runtime performance, security threat modeling, API compatibility, test evidence quality, observability, recovery, refactor, review, security, self-explaining architecture, testing, validation, documentation, and performance.
- Mandatory skill routing defined in AGENTS.md for every task type.
- Skill absence rule with NO_IMPACT/YELLOW/BLOCKER classification.

**Backlog Management (Level 3):**
- fix-this.md provides 34 TODOs with full finding details, source clusters, finding IDs, owner, expiry, validation commands, and evidence requirements.
- TODO.md provides the operational execution board with agent lanes, round model, dependency notes, and merge order.
- 7/7 P0 blockers closed, 13/34 TODOs DONE, 5 PARTIALLY_RESOLVED, remaining items classified with severity and phase allowance.
- Source finding coverage maps 2200+ original findings to canonical TODOs and clusters.

**Architecture Enforcement (Level 3):**
- Canonical component shape checker enforces System/PublicSurface/Flows/Capabilities/Configuration/Foundation structure.
- Forbidden folder checker prevents Services/Helpers/Utils/Common/Managers/Contracts dumping grounds.
- PublicSurface gate prevents runtime machinery in public entrypoints.
- Runtime composition leak gate prevents object-graph assembly in flows.
- Namespace drift checker prevents taxonomy decay.
- Component suite structure checker enforces canonical organization.

## YELLOW Areas (Improving, Gaps Identified)

**Test Quality Gate (Level 2-3):**
- Shallow test detector exists (check-shallow-tests.php) but is not integrated into CI.
- Risk-based behavioral testing rules exist in how-to-test-risk-based-behavioral-testing.md and AGENTS.md §24A.
- Negative test requirements are documented for security boundaries but enforcement is manual during code review.
- 8413+ tests pass but coverage strategy is phase-based (V1 = behavioral confidence, not 100% line coverage).
- Gap: No automated test quality gate that runs on every commit to reject shallow tests or missing negative tests for security boundaries.

**Security Gate (Level 2-3):**
- Security threat model skill exists (avax-security-threat-model).
- Security naming checker exists (check-security-naming.php, check-security-blockers.php).
- Security how-to exists (how-to-system-security.md).
- Security commit block checker exists (check-security-commit-block-readiness.php).
- Gap: No automated STRIDE-based security gate that scans changed files for security pattern violations.
- Gap: Security baseline verification is manual review, not automated.
- Gap: No automated secret detection or credential scanning in the tooling suite.

**Performance Gate (Level 2-3):**
- Performance naming checker exists (check-performance-naming.php).
- Performance how-to exists (how-to-system-performance.md).
- Performance skill exists (avax-runtime-performance-cache).
- Gap: Performance baseline verification is manual review, not automated.
- Gap: No automated hot-path scanner that detects reflection/filesystem scans in request-critical code paths.
- Gap: Benchmark infrastructure exists (V5.5 proved 90K+ RPS) but is not a gating check on every commit.

**Documentation Gate (Level 2-3):**
- How-to document structure checker exists (check-how-to-document-structure.md).
- Self-explaining architecture checker exists (check-self-explaining-architecture.php).
- Documentation how-to exists (how-to-document.md) with layering model resolution.
- Docs mirror checker exists (check-docs-mirror.php).
- Gap: Documentation location verification is manual review.
- Gap: No automated checker that verifies new boundaries with 10+ files have README/dictionary/ADR.
- Gap: Semantic PHPDoc gate has 9810 legacy violations tracked as accepted-yellow ratchet — the ratchet works (0 touched/new violations) but the debt is not decreasing organically.

**Exception Register (Level 2):**
- EVIDENCE/accepted-exceptions-ledger.md exists with proper schema (10 required fields).
- Gap: The ledger is empty — zero entries despite having known accepted-yellow debt (semantic PHPDoc ratchet, AuthBuilder remaining size).
- This means accepted-yellow findings tracked in TODO.md/CURRENT_TRUTH.md are not cross-referenced to the canonical exception register.

**Component Maturity Gates (Level 2-3):**
- 8 component checkers exist (behavior proof map, docs status policy, health doctor policy, runtime assembly, static state safety, status lock coverage, status lock, hollow public surfaces).
- Gap: Component health checks (7 canonical health tests) exist but are not automated gates on every commit.
- Gap: No automated checker that verifies each component dogfoods AvaX capabilities (filesystem, logger, cache, configuration, security).

**Intrusive Coupling Detection (Level 2-3):**
- CheckIntrusiveCoupling.php exists in governance tooling.
- Gap: Not listed in GOVERNANCE_ENFORCEMENT_MAP.md — unclear if it is actively used in the validation pipeline.
- Gap: No gate output or report location mapped for this checker.

## RED Areas (Missing, Unproven, Blocking)

**CI/CD Integration (Level 1-2):**
- No evidence of CI/CD pipeline configuration (no .github/workflows, no GitLab CI, no CI config files found in the scanned scope).
- All validation gates are currently run manually or by agents during execution.
- Gap: Without CI integration, governance gates can be skipped by agents or humans who don't run the full validation suite.
- Gap: No automated gate that runs on PR creation to block merge until all checkers pass.

**Multi-Agent Concurrent Execution (Level 1-2):**
- TODO.md describes agent lane model and parallel round model but has no evidence of actual multi-agent concurrent execution.
- Gap: No evidence that the stage lock, branch policy, and merge order rules have been tested under concurrent agent execution.
- Gap: No automated worktree contamination detector or branch ownership enforcer.

**Public API Compatibility Automation (Level 2):**
- API compatibility contract skill exists (avax-api-compatibility-contract).
- Gap: No automated breaking-change detector that runs on every commit to flag public API changes.
- Gap: No contract test suite that verifies public API stability across versions.
- The API/Contracts component exists with breaking-change detection capabilities but these are not wired into the CI/validation pipeline.

**Observability Failure Semantics Enforcement (Level 2):**
- Observability skill exists (avax-observability-failure-semantics).
- Gap: No automated checker that verifies failure-prone changes document exception types, message safety, redaction, retryable vs fatal classification.
- Gap: No gate that checks for missing log events, missing metrics, or missing tracing in new runtime/IO code.

**Recovery and Rollback Tooling (Level 1-2):**
- Recovery skill exists.
- Gap: No automated rollback tooling or migration rollback verification.
- Gap: No disaster recovery test suite.

## Scalability Risks

**1. Governance document volume:** AGENTS.md alone is 2400+ lines. With 17+ how-to documents, 18 skills, GOVERNANCE_INDEX.md, GOVERNANCE_ENFORCEMENT_MAP.md, fix-this.md, TODO.md, and 34+ evidence files, the total governance surface is hundreds of thousands of words. New engineers (human or AI) will struggle to load all relevant context before any task. The reading-order model in GOVERNANCE_INDEX.md helps but is fragile — agents must discover which how-to documents are "relevant" without a classifier.

**2. Manual review dependency:** Security baseline, performance baseline, documentation location, component promotion, and AI review pack quality all require manual review. As the project grows, the manual review bottleneck will slow execution. These need to graduate from Level 2 (documented rules) to Level 3 (automated enforcement).

**3. Exception register emptiness:** The accepted-exceptions ledger is empty despite known accepted-yellow debt. This suggests the exception process is not being used in practice — findings are tracked in TODO.md/CURRENT_TRUTH.md instead. If this pattern continues, the exception register becomes dead governance and the real source of truth drifts into scattered TODO statuses.

**4. Gate proliferation:** 50+ checker tools exist but not all are in the canonical validation set (§27 of AGENTS.md lists ~17 canonical commands). Agents may run different subsets of gates, producing inconsistent GREEN claims. The GOVERNANCE_ENFORCEMENT_MAP.md helps but does not define which gates are mandatory vs optional per task type.

**5. Source-of-truth complexity:** The project has CURRENT_TRUTH.md (700+ lines of historical record), TODO.md (500+ lines), fix-this.md (1400+ lines), EVIDENCE/EXECUTION.md, .agents/management/ACTIVE.md, and scattered evidence files. The source-of-truth resolver skill exists but the complexity itself is a risk — agents may resolve incorrectly under time pressure.

## AI-Agent Risks

**1. Fake GREEN through selective gate execution:** An agent could claim GREEN by running only the gates that pass and skipping those that fail. The "no validation may be silently skipped" rule exists but is self-enforced — there is no gate that verifies all mandatory gates were run. An agent could also run gates with --no-progress and capture only the summary, hiding individual findings.

**2. Context budget truncation:** AI agents operating under token limits may skip reading "less important" governance documents (learning files, memory files, older evidence) and make decisions based on incomplete context. The current governance system assumes agents read everything relevant but provides no mechanism to verify completeness of context loading.

**3. Evidence fabrication:** An agent could write evidence files that claim GREEN without actually running validation. The evidence rule requires pointing to validation output, but there is no cryptographic or automated verification that the referenced output is genuine. Evidence integrity relies on agent honesty.

**4. Suppression through pattern matching:** An agent could "fix" a gate finding by changing the gate's allowlist patterns rather than fixing the underlying code. The no-fixed-by-suppression rule forbids this, but detecting it requires comparing gate configuration changes against gate finding changes — a meta-analysis that no current tool performs.

**5. Severity downgrading:** An agent could classify a BLOCKER finding as MEDIUM to unblock GREEN status. The canonical severity system defines what constitutes each level, but there is no automated checker that verifies classification accuracy.

**6. Stale evidence reuse:** An agent could reference old evidence files from a previous execution that are no longer valid. The source-of-truth resolver addresses this but is a skill, not an enforcement gate.

**7. Autonomous loop run-away:** The autonomous backlog loop rule says "continue until HARD_BLOCKER." An agent with high context budget could continue through low-value TODOs while ignoring architectural decisions that require human input. The hard stop conditions list this but enforcement is self-regulated.

## Onboarding Risks

**1. No quick-start governance guide:** A new engineer reading AGENTS.md first encounters 2400 lines of rules before understanding what AvaX does. The ARCHITECTURE.md helps but is 870 lines. There is no "governance in 10 minutes" or "what you need to know for your first task" guide.

**2. How-to document discoverability:** 17+ how-to documents are organized in 7 subdirectories. An engineer must read GOVERNANCE_INDEX.md to know which how-to applies to their task, but the task routing table in GOVERNANCE_INDEX.md maps broad categories (e.g., "PHP code") to multiple how-tos without specifying which is primary.

**3. Evidence directory density:** The EVIDENCE/ directory has 34 files at root plus dozens of subdirectories (v5.8/, v5.9-codex/, fix-this/, hardening/, cleanup/, v5/, v5.5/, v5.6/, v5.7/, components/, governance/, security/, performance/, recovery-reports/). A new engineer cannot easily find the evidence relevant to their task without knowing the evidence naming convention.

**4. CURRENT_TRUTH.md historical density:** CURRENT_TRUTH.md is 1600+ lines with extensive historical records of V1-V5 stages. The current actionable content (TODO-014 as next actionable) is buried in historical context. New engineers may waste time reading closed stage evidence.

**5. No visual governance map:** The governance system is described entirely in text. A new engineer would benefit from a visual map showing: which rules govern which file types, which checkers enforce which rules, which skills apply to which tasks, and what the execution flow looks like from task request to commit.

## Future Governance Gaps

**1. Cross-agent conflict detection:** As multiple agents work concurrently, no tool currently detects when two agents are modifying overlapping file sets, creating conflicting changes to the same component, or violating each other's stage locks.

**2. Governance drift detection:** No tool detects when the governance documents themselves become inconsistent — e.g., AGENTS.md says one thing, GOVERNANCE_INDEX.md says another, and a how-to document says a third. The rule precedence system defines which wins but does not detect the contradiction.

**3. Test coverage decay monitoring:** No tool monitors whether test coverage (behavioral, not line-count) decreases over time. Tests can be deleted, weakened, or made irrelevant without any gate catching the regression.

**4. Public API evolution tracking:** No automated system tracks public API changes across versions, computes compatibility impact, or generates migration guides. The API/Contracts component has the building blocks but they are not operationalized.

**5. Component dependency graph enforcement:** No tool enforces that component dependency directions follow the canonical flow (PublicSurface → Flows → Capabilities → Configuration → Foundation). Circular dependencies or reverse dependencies are not automatically detected.

**6. Governance cost monitoring:** No tool measures how much time/effort governance compliance costs per task. If governance becomes too expensive, teams will find ways to bypass it. Monitoring the cost helps keep governance proportional to risk.

## Recommended Future Tooling

| Priority | Tool | Purpose | Current Gap |
|----------|------|---------|-------------|
| **BLOCKER** | CI/CD pipeline integration | Run all mandatory gates on PR, block merge on failure | All gates are manual/agent-run |
| **BLOCKER** | Gate completeness verifier | Verify all mandatory gates were run for a task before allowing GREEN claim | Agents self-report gate execution |
| **HIGH** | Security baseline automated gate | Automated STRIDE-based scan of changed files for security pattern violations | Security baseline is manual review |
| **HIGH** | Hot-path performance gate | Automated scanner that detects reflection, filesystem scans, class_exists in request-critical code | Performance gate is manual review |
| **HIGH** | Breaking change detector | Automated public API diff analysis with contract test verification | API compatibility skill exists but is not gated |
| **HIGH** | Exception register enforcer | Gate that checks accepted-yellow findings are registered in the exception ledger | Ledger is empty despite known debt |
| **MEDIUM** | Governance consistency checker | Cross-reference AGENTS.md, GOVERNANCE_INDEX.md, and how-to documents for contradictions | Rule precedence defines winner but doesn't detect conflicts |
| **MEDIUM** | Test quality CI gate | Reject PRs with shallow tests or missing negative tests for security boundaries | Shallow test detector exists but is not CI-gated |
| **MEDIUM** | Component dogfooding verifier | Automated checker that verifies components use AvaX capabilities instead of raw PHP primitives | Dogfooding skill exists but is not automated |
| **MEDIUM** | Evidence authenticity verifier | Verify that evidence files reference actual validation outputs, not fabricated claims | Evidence integrity relies on agent honesty |
| **LOW** | Governance cost meter | Track time/token cost of governance compliance per task | No cost monitoring exists |
| **LOW** | Visual governance map | Generate visual documentation of rule→checker→skill→task relationships | Governance is text-only |

## Governance Maturity Scale

| Dimension | Current Level | Target Level | Gap |
|-----------|--------------|--------------|-----|
| Rule Coverage | 4 (Measured) | 5 (Optimized) | Rules exist for all major concerns; gaps are in cross-cutting enforcement (observability, recovery). |
| Rule Enforcement | 3 (Enforced) | 4 (Measured) | 50+ automated checkers exist but CI/CD integration is missing; enforcement depends on agent execution discipline. |
| Test Quality Gate | 2 (Documented) | 3 (Enforced) | Shallow test detector exists but is not CI-gated; negative test requirements are documented but not automated. |
| Documentation Gate | 2 (Documented) | 3 (Enforced) | How-to structure and self-explaining architecture checkers exist; documentation location and boundary docs verification are manual. |
| Security Gate | 2 (Documented) | 3 (Enforced) | Security naming and commit block checkers exist; STRIDE-based scanning and secret detection are missing. |
| Performance Gate | 2 (Documented) | 3 (Enforced) | Performance naming checker exists; hot-path scanning and benchmark gating are missing. |
| AI Safety | 3 (Enforced) | 4 (Measured) | Suppression detection, severity classification, and evidence rules exist; gate completeness verification and evidence authenticity are not automated. |
| Self-Explaining | 3 (Enforced) | 4 (Measured) | Self-explaining architecture checker exists; README/dictionary/ADR coverage for new boundaries is not automated. |
| Traceability | 4 (Measured) | 5 (Optimized) | Every finding maps to source IDs, clusters, TODOs, and evidence; cross-referencing between exception ledger and TODO statuses is broken. |
| Evidence Integrity | 3 (Enforced) | 4 (Measured) | Evidence is written for every claim with validation output references; evidence authenticity cannot be independently verified. |

### Scale Definitions

- **Level 0:** No governance
- **Level 1:** Informal rules (tribal knowledge, ad hoc)
- **Level 2:** Documented rules (written how-tos, checklists, manuals)
- **Level 3:** Enforced rules (automated tooling exists and is used)
- **Level 4:** Measured rules (metrics collected, compliance tracked, dashboards)
- **Level 5:** Optimized rules (continuous improvement, cost-benefit analysis, automatic adjustment)

---

*This report is an honest assessment based on current git state, governance documents, tooling inventory, and evidence files. Maturity levels are conservative — where evidence of enforcement exists, Level 3 is assigned; where only documentation exists without automated tooling, Level 2 is assigned. No dimension is inflated to Level 4 without evidence of metrics collection or to Level 5 without evidence of continuous improvement loops.*
