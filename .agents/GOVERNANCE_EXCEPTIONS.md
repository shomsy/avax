# Governance Exceptions

Version: 1.0.0
Status: Normative

This file documents approved exceptions to AGENTS.md and how-to governance rules.
Every exception must have: rule, path, reason, risk, owner, expiry, required cleanup, approval.

---

## GE-001: `components/API/Contracts` — Forbidden Component Name

**Rule:** AGENTS.md Section 8 — "Contracts" is forbidden as a default folder/component name.
**Path:** `components/API/Contracts/`
**Reason:** This component manages API contract compatibility and versioning. "Contracts" is the established domain name
for this area. Renaming would break all cross-component references and external documentation.
**Risk:** LOW — component name is conceptually accurate (manages API contracts), not a technical dumping ground.
**Owner:** AvaX maintainer
**Expiry:** V5.9 review
**Required Cleanup:** None. Component behavior is legitimate; name is domain-accurate.
**Approval:** Cleanup Program — Phase B-C

## GE-002: `components/DeveloperTools/Diagnostics` — Forbidden Component Name

**Rule:** AGENTS.md Section 8 — "Diagnostics" is forbidden as a default folder/component name.
**Path:** `components/DeveloperTools/Diagnostics/`
**Reason:** This component is the developer-facing diagnostics hub. The name is domain-accurate. Renaming would break
tooling references.
**Risk:** LOW — component name accurately describes its purpose.
**Owner:** AvaX maintainer
**Expiry:** V5.9 review
**Required Cleanup:** None. Component behavior is legitimate diagnostics tooling.
**Approval:** Cleanup Program — Phase B-C

## GE-003: `components/Operations/Events` — Forbidden Component Name

**Rule:** AGENTS.md Section 8 — "Events" is forbidden as a default folder/component name.
**Path:** `components/Operations/Events/`
**Reason:** This component owns the event dispatch system. "Events" is the established domain name. Renaming would break
hundreds of references across the codebase.
**Risk:** LOW — component name accurately describes its domain (event dispatch system).
**Owner:** AvaX maintainer
**Expiry:** V5.9 review
**Required Cleanup:** None. Component behavior is legitimate event dispatch infrastructure.
**Approval:** Cleanup Program — Phase B-C
