# Tool And Plugin Capability Isolation

Version: 1.0.0
Status: Normative
Scope: `.agents/governance/**`, `.agents/hooks/**`, `.agents/skills/**`

This document defines the minimum security and isolation rules for tools,
plugins, MCP servers, extensions, sandboxes, and code-execution surfaces used by
the Agent Harness.

It applies to:

- local execution tools
- MCP servers
- platform-native plugins and extensions
- sandboxed code runners
- workflow blocks or nodes that reach external systems

## Core Rule

No extension surface should receive more authority than its declared
capabilities require.

Treat tools and plugins as replaceable execution surfaces behind explicit trust,
approval, and isolation boundaries.

## Capability Manifest

Every non-trivial tool or plugin surface should have a documented capability
manifest, whether represented as code, config, or markdown.

The manifest should declare at least:

- stable tool or plugin identifier
- owner or maintaining team
- execution boundary: in-process, child process, worker, sandbox, remote service
- filesystem scope: read roots and write roots
- network scope: disabled, allowlisted domains, or unrestricted
- credential scope: none, named secret classes, or delegated user auth
- mutation scope: read-only, workspace write, external write, admin
- approval mode required
- audit events emitted
- kill-switch or disable path

## Isolation Levels

Prefer the strongest practical boundary for the capability:

| Level | Boundary | Typical Use |
|:---|:---|:---|
| `L0` | in-process, no side effects | local formatting, parsing, pure transforms |
| `L1` | child process with bounded workspace access | shell helpers and file utilities |
| `L2` | dedicated worker or task runner | long-running jobs, parallel execution, plugins |
| `L3` | sandboxed runtime with network and syscall restrictions | untrusted code execution |
| `L4` | remote isolated service with separate credentials and audit trail | external integrations and high-risk automation |

When two levels are possible, choose the more isolated one unless there is a
clear operational reason not to.

## Least-Privilege Rules

- Default new capabilities to read-only.
- Write access must be scoped to explicit roots or resource classes.
- Credentials must not be inherited implicitly by every tool.
- External network access should be disabled unless the capability actually
  requires it.
- Untrusted code execution must run in a sandbox or separately controlled worker.
- Policy and control-plane files must not be writable by third-party plugins by
  default.

## Registration Gate

Before a new tool, plugin, or MCP server becomes part of the default harness,
verify:

1. capability manifest exists
2. trust tier and approval mode are mapped
3. write and network scopes are documented
4. emitted audit events are defined
5. disable path exists
6. documentation, scaffold, and adapter references stay consistent

If these cannot be stated clearly, the capability is not ready for baseline
adoption.

## RPC And Schema Boundary

Use typed or schema-validated interfaces across execution boundaries whenever
possible.

- Inputs and outputs should be validated at the boundary.
- Invalid tool calls should be preserved for debugging, not silently coerced.
- Tool metadata should support cost, token, duration, or resource accounting
  when the runtime exposes it.
- Cross-process or remote calls should use stable machine-readable envelopes.

## Approval Mapping

Capability class should align with trust and approval posture:

| Capability | Minimum Posture |
|:---|:---|
| read local context | auto-allow if bounded |
| write workspace files | scoped approval or trusted baseline |
| external read | consented integration |
| external write | per-invocation approval unless explicitly delegated |
| secret use | named secret class and audit trail |
| untrusted code exec | sandbox plus bounded inputs and outputs |

## Audit Requirements

At minimum, log:

- capability identifier
- invocation timestamp
- actor or session identifier
- effective approval decision
- target scope summary
- result status
- trace identifier

## Relationship To Other Standards

| Standard | Relationship |
|:---|:---|
| `../execution/approvals/approval-policy.md` | approval and trust posture |
| `../execution/sandbox/sandbox-boundary-policy.md` | sandbox expectations |
| `../integrations/mcp/mcp-integration-policy.md` | MCP-specific registration and trust |
| `../delivery/operations/observability-and-error-envelope.md` | trace and audit evidence |
