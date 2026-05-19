# Repository Entropy Control Policy (V3)

Version: 3.0.0
Status: Normative
Scope: `.agents/governance/**`

## 1. Anti-Chaos Governance
Over time, large Agent Harness OS deployments accumulate dead rules, stale templates, and orphaned evidence. This policy mandates continuous entropy reduction.

## 2. Stale Detection Rules
- **Stale Evidence**: Any file in `.agents/management/evidence/raw/` older than 14 days without an active task ID is marked for archival.
- **Dead Templates**: Any template in `.agents/templates/` not used in the last 6 months must be deleted.
- **Unused Profiles**: Language or framework profiles in `profiles/` that are not declared in `project.json` or inferred by the active codebase must be removed from the local `.agents/.rules/` index.

## 3. Orphan & Duplicate Truth Detection
- **Duplicate Truth**: `CURRENT.md` is the sole source of truth. If any `README.md` or secondary doc duplicates the state of `CURRENT.md`, the duplicate must be removed and replaced with a hyperlink.
- **Contradictory Rules**: The Recursive Governance Review MUST explicitly check for contradictions between local `AGENTS.md` and the inherited OS stack. Local overrides win, but if they break security/architecture BLOCKERS, they must be flagged as `HIGH` risk.

## 4. Enforcement
Agents operating in `T2` (ExtendedWrite) or `T3` (FullAccess) modes MUST periodically run entropy checks and propose deletion PRs. "Less code, less chaos."
