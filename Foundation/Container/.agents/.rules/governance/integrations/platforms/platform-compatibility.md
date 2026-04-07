# Platform Compatibility — Multi-Platform Adapter Registry

Version: 1.0.0
Status: Normative
Scope: `.agents/governance/**`

This document defines how the Agent Harness OS adapts to different AI coding
platforms. It establishes a compatibility layer that maps the harness's
`.agents/` directory structure to platform-specific configuration formats.

Inspired by Superpowers' multi-platform integration (Claude Code, Codex,
OpenCode, Cursor, Gemini CLI) and ECC's cross-platform adapters with
platform-specific manifests.

---

## 1) Core Principle: Single Source, Multiple Targets

The `.agents/` directory is the **single source of truth**. Platform-specific
configurations are derived from it, not the other way around.

```
.agents/                             Platform Adapters
├── AGENTS.md        ──────────►    CLAUDE.md / .cursorrules / .codex/INSTALL.md / GEMINI.md
├── governance/      ──────────►    shared rules and policies
├── skills/          ──────────►    reusable skill instructions
└── management/      ──────────►    memory, evidence, and backlog artifacts
```

---

## 2) Platform Adapter Matrix

| Feature | Claude Code | Cursor | Codex CLI | Gemini CLI | Antigravity |
|:---|:---|:---|:---|:---|:---|
| **Rules File** | `CLAUDE.md` | `.cursorrules` | `.codex/INSTALL.md` | `GEMINI.md` | `AGENTS.md` |
| **Hook Config** | Optional `.claude/settings.json` | Optional `.cursor-plugin/plugin.json` | Optional client hook or approval config | — | Runtime hooks |
| **Skill Discovery** | `.agents/skills/` + plugins | `.agents/skills/` | `.agents/skills/` | `.agents/skills/` | `.agents/skills/` |
| **Session Storage** | `~/.claude/sessions/` | IDE-managed | Per-session | — | Platform-managed |
| **Trust Model** | `autoAllowBashIfSandboxed` | IDE permissions | `--approval-mode` | — | `approval-policy.md` |
| **Memory** | `.claude/memories/` | IDE workspace | Session-scoped | — | `.agents/management/memories/` |

## 2b) Subagent-Native Clients

| Client | Open Source | Best Fit | Harness Adapter |
|:---|:---:|:---|:---|
| **OpenCode** | Yes | Primary/subagent orchestration with task permissions and hidden helper agents | `opencode.json` + `.opencode/agents/**` |
| **Cline** | Yes | Read-only exploration, long tasks, and context trimming | `.clinerules/**` + `.clineignore` |
| **Blackbox** | No | Remote multi-agent execution and MCP-driven branch tasks | Hosted remote tasks / MCP briefs |

OpenCode is the best fit when the harness needs explicit `primary` and
`subagent` roles with `permission.task` gating. Cline is the best fit when the
goal is to keep the main context small and let read-only subagents map the
codebase. Blackbox is useful as a hosted option for remote orchestration, but
it should not be treated as the local open-source baseline.

---

## 3) Platform Adapters

### Claude Code Adapter

To deploy the harness to a Claude Code project:

1. **Rules**: Create `CLAUDE.md` at project root that sources `.agents/AGENTS.md`:
   ```markdown
   # CLAUDE.md
   Read and follow all rules in `.agents/AGENTS.md`
   ```

2. **Skills**: Skills in `.agents/skills/` are auto-discovered.

3. **Managed Settings**: Optional organization-level overrides can still live in
   the client-native config if needed.

---

### Cursor Adapter

To deploy the harness to a Cursor project:

1. **Rules**: Create `.cursorrules` at project root:
   ```
   Read and follow all rules in .agents/AGENTS.md
   ```

2. **Skills**: Skills in `.agents/skills/` are referenced from the rules file.

---

### Codex CLI Adapter

To deploy the harness to an OpenAI Codex CLI project:

1. **Rules**: Create `.codex/INSTALL.md` that references `.agents/AGENTS.md`:
   ```markdown
   # Codex Project Setup
   Read and follow all rules in `.agents/AGENTS.md`.

   Keep the Codex approval mode aligned with the harness trust tier and the
   current task lane.
   ```

2. **Trust Mapping**:
   | Codex Mode | Harness Trust Tier |
   |:---|:---|
   | `suggest` | T0 (ReadOnly) |
   | `auto-edit` | T1 (WorkspaceWrite) |
   | `full-auto` | T2 (NetworkAccess) |

3. **Skills**: Referenced via AGENTS.md content injection.

---

### Gemini CLI Adapter

To deploy the harness to a Gemini CLI project:

1. **Rules**: Create `GEMINI.md` at project root:
   ```markdown
   Read and follow all rules in .agents/AGENTS.md
   ```

2. **Skills**: Skills in `.agents/skills/` remain the shared source of truth.

---

### Antigravity Adapter (Native)

The harness runs natively on Antigravity. No adapter needed:
- `AGENTS.md` at project root is the canonical entry point
- `.agents/AGENTS.md` is the global contract
- Skills are auto-discovered from `.agents/skills/`
- Hooks are runtime-managed

---

## 4) Bootstrap Script Integration

The `install-os.sh` bootstrap script SHOULD generate the text-based platform
adapter files when deploying to a new project:

```bash
# install-os.sh (excerpt)
generate_claude_adapter
generate_cursor_adapter
generate_codex_adapter
generate_gemini_adapter
generate_opencode_adapter
generate_cline_adapter
```

Optional platform-native hook or plugin configs can still be added by hand if a
specific client needs them. The portable hook entrypoints live in
`.agents/hooks/` so client-specific adapters can point at the same runtime
scripts instead of duplicating enforcement logic.

Any platform-native plugin, extension, or sidecar config still inherits the
least-privilege and capability-manifest rules from
`../../security/tool-and-plugin-capability-isolation.md`.

---

## 5) Cross-Platform Skill Compatibility

Skills MUST be written in platform-agnostic markdown. Platform-specific
behavior is handled through conditional sections:

```markdown
---
name: my-skill
description: A cross-platform skill
---

## Instructions

Common instructions here...

## Platform Notes

### Claude Code
Special handling for Claude Code...

### Cursor
Special handling for Cursor...
```

---

## 6) Session Lifecycle Differences

| Event | Claude Code | Cursor | Codex | Gemini | Antigravity |
|:---|:---|:---|:---|:---|:---|
| **Session Start** | Hook fires | Plugin init | Task start | Command start | Runtime |
| **Context Inject** | Via hook stdout | Via rules | Via INSTALL.md | Via GEMINI.md | Via AGENTS.md |
| **Compaction** | `/compact` command | IDE-managed | Per-task | Auto | Runtime |
| **Session End** | Hook fires | Plugin cleanup | Task end | Command end | Runtime |
| **Memory Save** | Via hook | Manual | Session-scoped | Manual | Via hook |

---

## 7) Trust Model Mapping

Map the harness trust tiers to each platform's permission model:

| Harness Tier | Description | Claude Code | Codex | Cursor |
|:---:|:---|:---|:---|:---|
| T0 | ReadOnly | Default | `suggest` | Default |
| T1 | WorkspaceWrite | Per-tool allow | `auto-edit` | Per-tool allow |
| T2 | NetworkAccess | Explicit allow | — | Explicit allow |
| T3 | FullAccess | `autoAllowBashIfSandboxed` | `full-auto` | Admin mode |

---

## 8) Relationship to Other Standards

| Standard | Relationship |
|:---|:---|
| `approval-policy.md` | Trust tiers map to platform permissions |
| `hooks-policy.md` | Hook events map to platform lifecycle |
| `skill-contract.md` | Skills must be platform-agnostic |
| `feature-flags.md` | Platform detection can toggle features |
