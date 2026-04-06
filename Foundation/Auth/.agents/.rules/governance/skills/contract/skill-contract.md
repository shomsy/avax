# Skill Contract — Standardized Skill Definition

Version: 1.0.0
Status: Normative
Scope: `.agents/skills/**`

This document defines the standardized contract for skills within the Agent
Harness OS. All skills MUST conform to this contract to be discoverable,
parseable, and enforceable by agents.

Inspired by skill systems that define skills as discoverable directories with
structured metadata, scoped permissions, and automatic injection.

---

## 1) Skill File Format

### Required: SKILL.md
Every skill is a directory containing a `SKILL.md` file with YAML frontmatter
delimited by `---`:

```markdown
---
name: <string>                  # Required. Max 64 characters.
description: <string>           # Required. Max 1024 characters.
trust_tier: T0 | T1 | T2 | T3  # Optional. Default: T0. From approval-policy.md.
version: <semver>               # Optional. Default: 0.1.0.
tags: [<string>, ...]           # Optional. For discovery/search.
author: <string>                # Optional. Who wrote this skill.
---

<markdown body — the skill instructions>
```

### Optional: agents/openai.yaml
Structured metadata for tool and provider integration:

```yaml
# agents/openai.yaml
tools:
  - name: <tool-name>
    description: <tool-description>
    parameters:
      type: object
      properties:
        param1:
          type: string
          description: <param-description>

permissions:
  network: false
  file_write: false
  shell_exec: false
```

---

## 2) Skill Directory Structure

```
<skill-name>/
├── SKILL.md              # Required: instructions + frontmatter
├── agents/               # Optional: provider metadata
│   └── openai.yaml
├── scripts/              # Optional: executable helpers
│   ├── run.sh
│   └── validate.sh
├── tests/                # Optional: validation scripts
│   └── test.sh
├── examples/             # Optional: usage examples
│   └── example.md
└── resources/            # Optional: additional assets
    └── template.md
```

### Directory Semantics

| Directory | Purpose | Required |
|:---|:---|:---:|
| `SKILL.md` | Core instructions and metadata | Yes |
| `agents/` | Provider-specific integration configs | No |
| `scripts/` | Executable helpers the skill may reference | No |
| `tests/` | Validation scripts to verify skill correctness | No |
| `examples/` | Usage examples and reference implementations | No |
| `resources/` | Templates, assets, or supplementary files | No |

---

## 3) Discovery and Loading

### Discovery Roots
Skills are discovered by scanning these roots in priority order:

| Priority | Scope | Path | Override Behavior |
|:---:|:---|:---|:---|
| 1 | **Repo** | `.agents/skills/` | Wins over all |
| 2 | **Repo Alt** | `.codex/skills/` | Alternative repo path |
| 3 | **User** | `$HOME/.agents/skills/` | Per-user skills |
| 4 | **System** | `/etc/agents/skills/` | System-wide skills |

### Discovery Rules
1. **Depth limit**: Scan at most 6 levels deep from each root.
2. **Deduplication**: If the same skill name exists at multiple scopes,
   the highest-priority scope wins (Repo > User > System).
3. **Validation**: Skills without valid `SKILL.md` frontmatter are skipped
   with a warning.

### Name Resolution
The skill name is determined in this order:
1. The `name` field in YAML frontmatter (if present).
2. The directory name containing `SKILL.md`.

---

## 4) Skill Invocation

### Explicit Invocation
Users can reference skills by name in prompts:
- `$skill-name` — Dollar prefix notation.
- `skill://skill-name` — URI notation.
- Direct mention in structured input.

### Implicit Invocation
A skill is considered **implicitly active** when the agent:
1. Reads or executes a file under `<skill>/scripts/`.
2. References a file under `<skill>/resources/`.
3. Follows instructions that match a skill's description.

When implicitly invoked, the skill's `trust_tier` applies to the current
operation.

---

## 5) Permission Profiles

Each skill's `trust_tier` determines its maximum permissions, as defined
in `approval-policy.md`:

| trust_tier | File Access | Network | Shell |
|:---|:---|:---|:---|
| **T0** | Read workspace only | None | No |
| **T1** | Read/write workspace | None | Approved commands only |
| **T2** | Read/write + deps | Outbound | With approval |
| **T3** | Unrestricted | Unrestricted | With approval |

### Tier Ceiling Rule
A skill MUST NOT request operations above its declared `trust_tier`. If a
skill declares `trust_tier: T1` but attempts a network request, the
operation MUST be blocked.

---

## 6) Injection into Context

When a skill is invoked, its `SKILL.md` body (the markdown after the
frontmatter) is injected into the agent's working context as skill
instructions. The injection format:

```markdown
<skill-instructions name="<skill-name>" trust_tier="<tier>">
<SKILL.md body content>
</skill-instructions>
```

### Token Limits
- Individual skill injection: Max 10,000 tokens.
- Combined skill injections per session: Max 30,000 tokens.
- If limits are exceeded, newer/higher-priority skills take precedence.

---

## 7) Validation Contract

### Required Checks
Before a skill is loaded, the following MUST be validated:
1. `SKILL.md` exists and is readable.
2. YAML frontmatter is valid and contains `name` + `description`.
3. `name` is ≤ 64 characters.
4. `description` is ≤ 1024 characters.
5. `trust_tier` (if present) is one of: `T0`, `T1`, `T2`, `T3`.
6. `version` (if present) is valid semver.

### Failure Modes
- Missing `SKILL.md`: Skill directory is silently skipped.
- Invalid frontmatter: Warning logged, skill skipped.
- Name too long: Truncated to 64 characters with warning.
- Invalid trust_tier: Defaults to `T0` with warning.

---

## 8) Agent Skills Specification Alignment

This contract aligns with the emerging **Agent Skills standard** published
at [agentskills.io](https://agentskills.io/specification), as implemented
by the `anthropics/skills` reference repository.

### Description Optimization (CSO — Claude Search Optimization)
The `description` field is the primary matching surface. At runtime, the
agent matches user intent to skills based on this field. Optimize it:

1. **Front-load the verb**: Start with what the skill does.
   - ✅ "Creates Word documents from structured data"
   - ❌ "A skill for Word document creation"
2. **Include trigger phrases**: What would a user type to need this?
   - ✅ "Generates PDF reports, exports data to PDF, creates printable documents"
3. **Max 1024 characters**: Longer descriptions are truncated.
4. **No marketing language**: Be precise, not persuasive.
5. **Include file types/extensions**: If the skill handles specific formats.

### Marketplace Convention
Skills MAY be distributed via a marketplace registry. To participate,
include a `.claude-plugin/marketplace.json` at the skill collection root:

```json
{
  "schema_version": 1,
  "plugins": [
    {
      "name": "my-skill-collection",
      "description": "Collection of workflow skills",
      "skills": [
        {
          "path": "skills/my-skill",
          "name": "my-skill",
          "description": "Does a specific thing"
        }
      ]
    }
  ]
}
```

### Skill Creator Workflow
When creating a new skill, follow this process:

1. **Design**: Define the skill's purpose, triggers, and outputs
2. **Write SKILL.md**: Create the instruction file with valid frontmatter
3. **Add scripts/**: Include any helper scripts needed
4. **Test**: Create `tests/` with validation scenarios
5. **Optimize**: Refine the `description` for fuzzy matching (CSO)
6. **Validate**: Ensure frontmatter passes validation (§7)
7. **Benchmark**: Test with pressure scenarios to verify robustness
8. **Document**: Add usage examples to `examples/`

The harness ships a baseline generator at `scaffolds/create-skill.sh` to create
the directory layout and compliant frontmatter quickly.

### Scripts Directory
The `scripts/` directory MAY contain executable helpers:
- **Node.js scripts** (`.js`, `.cjs`): For complex operations
- **Shell scripts** (`.sh`): For system operations
- **Python scripts** (`.py`): For data processing
- Scripts MUST be platform-agnostic when possible
- Scripts inherit the skill's `trust_tier` permissions

---

## 9) Relationship to Other Standards

| Standard | Relationship |
|:---|:---|
| `approval-policy.md` | `trust_tier` maps to approval trust tiers |
| `feature-flags.md` | Skills respect active feature flags |
| `hooks-policy.md` | Skill invocation may trigger `PreTask` context injection |
| `memory-lifecycle.md` | Learned skills may be persisted to `learned_skills/` |
| `naming-standard.md` | Skill names MUST follow kebab-case convention |
| `continuous-learning.md` | Evolved skills from instincts must comply |
| `instincts-policy.md` | Instincts may graduate to skills |
| `platform-compatibility.md` | Skills must be platform-agnostic |
| `context-management.md` | Skill injection consumes P1 budget (max 3) |
