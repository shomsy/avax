# Term
Runtime

# Classification
Infrastructure — Execution Environment

# Purpose
The environment in which code executes, providing the language semantics, standard library, memory management, and system interfaces available to running programs.

# Why Allowed
The runtime concept is central to all software execution. In the JavaScript world, Node.js provides a V8-based server-side runtime. Bun provides a Zig-based runtime with built-in tooling. Deno provides a secure-by-default runtime with native TypeScript support. In infrastructure, runtimes include language VMs (JVM, CLR), container runtimes (containerd, crun, gVisor), WebAssembly runtimes (Wasmtime, Wasmer), and serverless runtimes (AWS Lambda runtime environment, Cloudflare Workers isolate runtime). A runtime defines what code can do: what APIs are available, how memory is managed, how I/O is performed, and what security boundaries exist. It is not a build tool, a framework, or a library — it is the execution substrate itself.

# Allowed Contexts
- Language runtimes (Node.js, Bun, Deno, JVM, CLR, Python interpreter)
- Container runtimes (containerd, crun, gVisor, Kata Containers)
- WebAssembly runtimes (Wasmtime, Wasmer, WasmEdge)
- Serverless runtimes (AWS Lambda, Cloudflare Workers, Vercel Edge Runtime)
- Browser JavaScript runtimes (V8 in Chrome, SpiderMonkey in Firefox, JavaScriptCore in Safari)
- PHP runtimes (PHP-FPM, FrankenPHP, RoadRunner, Swoole, ReactPHP)
- Application runtimes (Erlang/OTP BEAM, Elixir)

# Forbidden Misuse
- Naming a library or framework a "runtime" when it does not provide an execution environment
- Creating a Runtimes/ folder for configuration files or deployment scripts
- Calling a build-time dependency a "runtime" when it only affects compilation, not execution
- Using "runtime" to describe a single configuration value or environment variable

# Ecosystem References
- https://nodejs.org/
- https://bun.sh/
- https://deno.com/
- https://containerd.io/
- https://wasmtime.dev/
- https://developers.cloudflare.com/workers/

# Allowed Patterns
- nodeRuntime
- containerdRuntime
- wasiRuntime
- edgeWorkerRuntime
- phpFpmRuntime
- isolateRuntime

# Forbidden Patterns
- Runtime (as a folder name)
- RuntimeManager
- AppRuntime (too vague — should specify which runtime environment)
- RuntimeHelper
- GenericRuntime
