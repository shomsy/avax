# Integration Profile: MCP Example
**ID**: PROFILE-MCP-EXAMPLE
**Status**: Experimental

## Sandbox & Trust Policy
- **Trust Level**: `SANDBOXED`
- **Permissions**: `read_only` on project files, `no_network`.
- **Secret Handling**: All secrets must be passed via environment variables prefixed with `AGENT_MCP_`. NEVER hardcode secrets in profiles.

## Capabilities
- **File Indexing**: Fast recursive indexing for large monorepos.
- **Dependency Graph**: Visualization of inter-component dependencies.

## Failure Mode
If the MCP server is unreachable, the agent MUST fallback to local `grep/find` strategies and record the degradation in `EVIDENCE/FLOW.md`.
