# Term
Sandbox

# Classification
Infrastructure — Isolation Boundary

# Purpose
An isolated execution environment that restricts what running code can access, preventing it from affecting the host system, other processes, or unauthorized resources.

# Why Allowed
Sandboxing is a fundamental security and reliability primitive in infrastructure. Browsers sandbox web pages with same-origin policies and process isolation. Container runtimes sandbox processes with namespaces, cgroups, and seccomp filters. Code evaluation services sandbox untrusted code with VMs, Firecracker microVMs, or WASI sandboxes. CI/CD systems sandbox builds to prevent cross-contamination between jobs. Test frameworks sandbox individual test cases to prevent state leakage. A sandbox has a precise security purpose: it defines a boundary of trust, restricts capabilities (filesystem, network, syscalls, memory), and enforces isolation through OS-level or language-level mechanisms. It is not a general-purpose environment or a feature flag.

# Allowed Contexts
- Browser security sandboxes (same-origin policy, process isolation, Site Isolation)
- Container sandboxes (Docker namespaces, cgroups, seccomp, AppArmor)
- Code evaluation sandboxes (Firecracker microVMs, gVisor, WASI)
- CI/CD build sandboxes (isolated runners, ephemeral environments)
- Test execution sandboxes (isolated test processes, temporary filesystems)
- Plugin and extension sandboxes (VS Code extension host, browser extension isolation)
- Multi-tenant service sandboxes (namespace isolation, resource quotas)

# Forbidden Misuse
- Naming a staging or preview environment a "sandbox" when it provides no isolation guarantees
- Creating a Sandboxes/ folder for development or scratch directories
- Calling a configuration profile a "sandbox" when it only changes feature flags, not isolation
- Using "sandbox" to describe any test or development environment without actual security boundaries

# Ecosystem References
- https://developer.mozilla.org/en-US/docs/Web/Security/Same-origin_policy
- https://docs.docker.com/engine/security/
- https://github.com/firecracker-microvm/firecracker
- https://gvisor.dev/
- https://wasi.dev/
- https://code.visualstudio.com/api/extension-guides/web-extensions

# Allowed Patterns
- codeExecutionSandbox
- containerSandbox
- testIsolationSandbox
- pluginExecutionSandbox
- multiTenantSandbox
- wasmSandbox

# Forbidden Patterns
- Sandbox (as a folder name)
- SandboxManager
- DevSandbox (too vague unless it provides actual isolation)
- SandboxHelper
- GenericSandbox
