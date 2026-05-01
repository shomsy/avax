# Approval Policy — Graduated Trust Model

Version: 1.0.0
Status: Normative
Scope: `.agents/governance/**`

This document defines the graduated trust model that governs agent autonomy
within the Agent Harness OS. It establishes trust tiers, dangerous operation
detection, and approval escalation rules.

Inspired by dual-policy security systems that separate *what is physically
possible* (sandbox) from *what requires human authorization* (approval).

---

## 1) Trust Tiers

All agent operations fall into one of four trust tiers. The tier determines
the maximum autonomy the agent has and when human approval is required.

| Tier | Name | File Access | Network | Approval Required |
|:---|:---|:---|:---|:---|
| **T0** | ReadOnly | Read workspace only | None | Never |
| **T1** | WorkspaceWrite | Read/write workspace | None | On destructive operations |
| **T2** | ExtendedWrite | Read/write + dependencies | Outbound only | On new dependencies or external calls |
| **T3** | FullAccess | Unrestricted | Unrestricted | Always on first use per session |

### Tier Assignment
The default tier is assigned based on the task lane:

| Task Lane | Default Tier | Rationale |
|:---|:---|:---|
| Investigation / Analysis | T0 | Read-only is sufficient |
| Documentation | T1 | Needs to write docs to workspace |
| Bugfix | T1 | Scoped changes within workspace |
| Feature Implementation | T1 | Scoped changes within workspace |
| Dependency Update | T2 | May download external packages |
| Infrastructure / CI | T2 | May modify build tooling |
| Release / Deploy | T3 | Full access required, always gated |
| Security Patch | T2 | May need external access for CVE data |

### Tier Escalation
An agent MAY request a tier upgrade during execution. The upgrade:
1. MUST be logged with rationale.
2. MUST be approved by human if escalating to T2 or T3.
3. Is scoped to the current task only — it does not persist.

---

## 2) Dangerous Operation Detection

The following patterns are considered **dangerous** regardless of the current
trust tier. They ALWAYS require explicit human approval:

### File Operations
- `rm -rf` with any path outside the current workspace.
- `rm -rf /` or `rm -rf ~` (absolute catastrophic deletion).
- `chmod 777` or `chmod -R` on non-workspace paths.
- `chown` on any path.
- Overwriting `.git/hooks/`, `.agents/`, or `.codex/` directories.
- Deleting any file in `.agents/governance/`.

### Process Operations
- Any `sudo` invocation.
- `kill -9` or `killall` commands.
- Modifying system services (`systemctl`, `service`).

### Network Operations
- `curl | bash`, `wget | sh`, or any pipe-to-shell pattern.
- HTTP requests to non-HTTPS endpoints (except `localhost`).
- Uploading files to external services.

### Database Operations
- `DROP TABLE`, `DROP DATABASE`, `TRUNCATE TABLE`.
- `DELETE FROM` without a `WHERE` clause.
- Schema migrations on production databases.

### Dependency Operations
- Adding dependencies from untrusted registries.
- Running arbitrary `postinstall` scripts.
- Upgrading major versions of core dependencies.

---

## 3) Approval Modes

### Never (T0 default)
No approval needed. The agent operates within read-only constraints.

### OnDangerous (T1 default)
Approval required only when the agent attempts a dangerous operation
(see §2). All other operations proceed without interruption.

### OnExternal (T2 default)
Approval required for:
- All dangerous operations (§2).
- Any network request to external services.
- Any new dependency installation.
- Any file modification outside the workspace root.

### Always (T3 default)
Approval required for every operation on first use per session. After the
first approval, the agent may propose auto-approval rules (see §4).

### Granular
For advanced configurations, approval can be toggled independently:
- `approve_shell_commands`: true/false
- `approve_file_writes`: true/false
- `approve_network_access`: true/false
- `approve_dependency_changes`: true/false

---

## 4) Auto-Approval Rules

Frequently approved operations can be recorded as auto-approval rules to
reduce friction. Rules are stored in:

```
.agents/governance/security/approved-commands.rules
```

### Rule Format
```
# Auto-approved command prefixes
# Added by human approval — agents MUST NOT modify this file directly

# Build commands
ALLOW: npm run build
ALLOW: npm run test
ALLOW: npm run lint

# Git read commands
ALLOW: git status
ALLOW: git log
ALLOW: git diff
ALLOW: git show

# File viewing
ALLOW: cat
ALLOW: head
ALLOW: tail
ALLOW: wc
ALLOW: find . -name

# Safe package operations
ALLOW: npm list
ALLOW: npm outdated
```

### Rule Management
1. When an agent requests approval and the human approves, the human MAY
   opt to add an auto-approval rule for that command prefix.
2. Agents MUST NOT add or modify rules in `approved-commands.rules`.
3. Rules are prefix-matched: `ALLOW: npm run` approves `npm run build`,
   `npm run test`, etc.
4. Rules persist across sessions within the same project.
5. Dangerous operations (§2) can NEVER be auto-approved.

---

## 5) Approval Request Protocol

When an agent needs approval, it MUST present:

1. **Operation**: The exact command or action to be performed.
2. **Trust Tier**: The current tier and whether escalation is needed.
3. **Risk Level**: Low / Medium / High / Critical.
4. **Rationale**: Why this operation is necessary.
5. **Alternatives**: Less privileged alternatives, if any.

### Example
```
🔒 Approval Required (Tier T2 → T3 escalation)

Operation: npm install lodash@latest
Risk Level: Medium
Rationale: Required for deep merge functionality in config system.
Alternatives: Could use native Object.assign() with custom deep merge.

Approve? [yes / yes+rule / no]
```

### Response Options
- **yes**: Approve this operation once.
- **yes+rule**: Approve and add auto-approval rule for this prefix.
- **no**: Deny the operation. Agent must find an alternative.

---

## 6) Relationship to Other Standards

| Standard | Relationship |
|:---|:---|
| `execution-policy.md` | Provides the task lane used for tier assignment |
| `security/**` | Contains `approved-commands.rules` and security contracts |
| `hooks-policy.md` | `PreTask` hook assigns trust tier; approval fires at runtime |
| `feature-flags.md` | `approval_required` flag can globally toggle this policy |
| `quality-gates.md` | Approval is one of the DoD gates for T2+ operations |
