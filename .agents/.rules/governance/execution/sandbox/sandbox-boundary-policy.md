---
description: "Agent Code Execution Sandbox Boundaries"
version: 1.0.0
---

# Code Execution Boundaries

It is a massive security and stability risk to execute dynamically-generated agent code directly on the host machine. The Agent OS mandates strict boundaries around all external script execution.

## 1. Untrusted Code Rule

Any code authored dynamically by the Agent OS (e.g., by the `Implementer` or `CodeExecutorAgent`) that is *not* a predefined hook, established script, or validated `SKILL.md` function is **Untrusted Code**.

## 2. Sandboxing Mandates

When an agent needs to execute Untrusted Code to verify tests, compile applications, or test algorithms, it MUST ensure it happens in an isolated environment:

1. **Docker Container (Preferred)**: Run the code inside an ephemeral Docker container (e.g., `docker run --rm -v $(pwd):/workspace python:3.10 python /workspace/agent_test.py`).
2. **Virtual Environments (Fallback)**: If Docker is unavailable, strict venv isolation (e.g., `/opt/venv-a0` or project-local isolated `.venv`) must be utilized. An agent must *never* execute untrusted python scripts via the global host interpreter.

## 3. Network & Resource Restrictions

- **Network Restrictions**: Code run in a sandbox should have minimal to no network egress unless it is explicitly testing an external API interaction approved by the user. If possible, utilize an SSRF proxy.
- **Timeouts**: All dynamic execution commands must be wrapped in a timeout mechanism (e.g., `timeout 30s npm run test-generated`) to prevent infinite horizontal expansion.
- **Read-Only Contexts**: Where possible, mount the primary source directory as read-only to the sandbox container, piping build artifacts to isolated temporary directories (`/tmp/agent-build/`).

## 4. Handling Failures

Code execution failures inside the sandbox must map back to the `Instincts` engine as negative reinforcement data points to prevent identically generated buggy code in future invocations.
