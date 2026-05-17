# V3 Hardening — Phase 7: MCP / Integration Neutralization

**Date**: 2026-05-16

## Integration Audit

| Integration | Core? | Optional profile? | Secret risk? | Action |
|---|---|---|---|---|
| GitHub MCP | NO | YES (opt-in) | YES — GITHUB_TOKEN | Moved concrete example to optional section in MCP-STACK.md |
| Brave Search MCP | NO | YES (opt-in) | YES — BRAVE_API_KEY | Same as above |
| PostgreSQL / DATABASE_URL | NO | YES (project-specific) | YES — DATABASE_URL | Removed from mandatory docs |
| Docker/Kubernetes | NO | YES (infra profile) | LOW | Already optional |
| `.agents/governance/integrations/mcp/mcp-integration-policy.md` | Generic policy only | YES | NO | Correct — no concrete providers |

## What Changed
`MCP-STACK.md` was updated to:
1. Mark itself explicitly as OPTIONAL INTEGRATION REFERENCE
2. Replace concrete secret examples with `VARIABLE_NAME=` (no real values)
3. Add opt-in framing at the top
4. Note that `install-mcp-stack.sh` is an optional helper, not OS requirement

## What Was Already Correct
- `.agents/governance/integrations/mcp/mcp-integration-policy.md` — already generic, trust-tier based, no concrete providers
- Parent core `.agents/governance/` has no hardcoded API keys

## Acceptance Criteria
- [x] Parent core stays neutral
- [x] MCP/platform integrations are opt-in
- [x] Secret handling policy is clear (never commit real values)
- [x] Missing integration is not a failure unless selected
