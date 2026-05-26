# Diagrams

This directory contains Mermaid diagram source files that visualize the Identity component architecture and flows.

## Current Diagrams

Diagrams are embedded within the flow documentation files:

- [Login Flow](../flows/login-flow.md) - Sequence diagram of the complete login flow
- [Token Validation Flow](../flows/token-validation-flow.md) - Sequence diagram of token validation
- [Request Authentication Lifecycle](../flows/request-authentication-lifecycle.md) - Sequence diagram of per-request authentication
- [Tenant Resolution Lifecycle](../flows/tenant-resolution-lifecycle.md) - Flowchart of tenant resolution

## Adding New Diagrams

When adding new diagrams:

1. Create a `.mmd` file in this directory with the Mermaid source
2. Reference the diagram from the relevant documentation file
3. Keep diagrams focused on a single concept or flow
4. Use consistent styling across diagrams

## Diagram Conventions

- Use `sequenceDiagram` for interaction flows
- Use `flowchart TD` for decision flows
- Use green (`#e1f5e1`) for success paths
- Use red (`#ffe1e1`) for failure paths
- Use orange (`#fff3e1`) for conditional/alternative paths
- Label all participants and decision points clearly
