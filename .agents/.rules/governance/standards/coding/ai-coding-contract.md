# AI Coding Contract & Anti-Cheap-OOP Governance (V4.2 Hardened)

Version: 4.2.0
Status: Normative / Universal
Scope: `./**`

## Purpose

To enforce extreme semantic honesty, visual clarity, and direct execution pathways in the codebase. This contract actively deters "decorative architecture"—the tendency of AI models to generate empty boilerplate, superficial abstractions, and textbook Java-like patterns that degrade maintainability and expand code size without increasing functional capability.

---

## 1. Deep Anti-Pattern Directory (The Banned Ten)

### A. Unjustified Suffixes (`Manager`, `Service`, `Helper`, `Util`)
- **WHY it is bad**: These generic nouns serve as "dumping grounds." They dilute component boundaries, conceal implicit dependencies, and lead to class bloat where state and logic are decoupled.
- **Better Naming & Shape**: Use specific domain actions or cohesive capability nouns.
  - Instead of `SessionManager`, use `SessionRegistry` (tracks active sessions) or `SessionAuthenticator` (validates them).
- **Good Local Example**: [install-os.sh](file:///home/shomsy/projects/agent-harness/install-os.sh) contains flat, functional steps rather than being wrapped in an `InstallationServiceManager` class.

### B. Fake Value Objects
- **WHY it is bad**: Wrapping basic data structures or primitive values (like a string or integer) in a heavy, custom class with zero real behavior or state validation. This introduces unnecessary instantiation cost and syntax noise (e.g. `user.getName().getValue()`).
- **Better Naming & Shape**: Use native language primitives, standard associative arrays, or lightweight, immutable records/tuples.
- **Good Local Example**: Pass pure strings for paths and directory variables rather than custom `Path` objects.

### C. Wrapper-Only Abstractions (Anemic Wrappers)
- **WHY it is bad**: Classes or files that merely forward calls to another underlying class without adding validation, transformation, caching, or security checks. This increases call stack depth and cognitive load for zero engineering return.
- **Better Naming & Shape**: Call the underlying module directly at the call site. Do not build an adapter unless you are actually reconciling two mismatched third-party interfaces.
- **Good Local Example**: Directly executing standard Unix tools (like `cmp`, `wc`, `git`) via `run_command` in our test suites rather than building a custom `GitExecuter` wrapper class.

### D. Nested Constructor Hell (Call-Site Fragility)
- **WHY it is bad**: Forcing the consumer of a class to instantiate 5 to 10 nested dependencies manually at the call site just to execute a single, stateless function. This makes unit testing incredibly fragile, requiring extensive mock setups for simple behaviors.
- **Better Naming & Shape**: Flatten dependencies. Inject simple configuration records or pass primitive parameters directly to stateless static functions.
- **Good Local Example**: `verify-governance.sh` reads config parameters directly from environmental variables or CLI inputs rather than taking a massive `VerifyConfigurationContext` class.

### E. Accidental DSL Cleverness
- **WHY it is bad**: Developing customized, fluent builders or private domain-specific languages (DSLs) when standard native language syntax (e.g. simple if-else, maps, loops) is shorter and universally understood. This creates a high learning curve for new developers.
- **Better Naming & Shape**: Use standard native loops and structures.
- **Good Local Example**: [tests/adoption-proof.sh](file:///home/shomsy/projects/agent-harness/tests/adoption-proof.sh) uses standard bash testing blocks instead of importing an elaborate proprietary assertion DSL.

### F. Semantic Drift Naming
- **WHY it is bad**: Naming a class or function something generic that drifts away from its true runtime behavior. For example, a function named `getUser()` that secretly executes a write-to-disk or starts an external subprocess.
- **Better Naming & Shape**: Absolute semantic honesty. Name functions exactly for what they do, side effects included.
  - Instead of `checkRule()`, use `verifyBaselineAndExitOnError()`.
- **Good Local Example**: `sync_file()` inside `install-os.sh` does exactly what it says: compares and synchronizes a file.

### G. Fake Facades
- **WHY it is bad**: Creating a facade class that exposes 20 different methods from different subsystems, pretending to offer a simplified interface but secretly masking a deeply tangled web of cross-dependencies.
- **Better Naming & Shape**: Keep subsystem interfaces separate and let the client coordinate them, or establish high-isolation micro-modules with clear, small APIs.
- **Good Local Example**: Keeping `verify-governance.sh` and `install-os.sh` completely decoupled, running them as independent pipeline steps rather than wrapping them under a unified `HarnessFacade`.

### H. Hollow Architecture Layers
- **WHY it is bad**: Building layered structures (e.g. Controller -> Service -> Repository -> Entity) for simple operations where a single direct database query or function call is perfectly sufficient. This represents dogmatic architecture compliance over practical simplicity.
- **Better Naming & Shape**: Design thin, direct pathways. If a component merely reads from a file, let the controller read the file directly rather than passing it through four empty layers.
- **Good Local Example**: [migrate-governance.sh](file:///home/shomsy/projects/agent-harness/migrate-governance.sh) directly reads and modifies legacy rule paths without introducing a complex migration adapter layer.

### I. Public Surfaces Containing Real Runtime Logic
- **WHY it is bad**: Exposing internal mutable variables, implementation details, or helper methods on the public interface of a module, allowing external consumers to modify the internal state unsafely.
- **Better Naming & Shape**: Keep the public interface minimal. Hide helper methods inside private modules or local script functions.
- **Good Local Example**: In `install-os.sh`, helper functions like `escape_sed_replacement()` are declared locally and are not exposed as global external operations.

### J. Abstraction Inflation
- **WHY it is bad**: Defining abstract classes or interfaces for capabilities that have only **one** real concrete implementation in the entire project. This adds visual noise and double edits (editing both the interface and the class) for no polymorphic benefit.
- **Better Naming & Shape**: Write a concrete class first. Only introduce an interface when a second, distinct concrete implementation is actively created and integrated.
- **Good Local Example**: The [verify-governance.sh](file:///home/shomsy/projects/agent-harness/verify-governance.sh) runtime is a concrete, single-implementation shell script with zero abstraction wrappers.

---

## 2. Pragmatic Abstraction vs. Decorative Abstraction

AI models must constantly distinguish between high-value pragmatic architecture and low-value decorative boilerplate:

| Dimension | Pragmatic Abstraction | Decorative Abstraction (Cheap OOP) |
|:---|:---|:---|
| **Justification** | Solves a real runtime variance or performance need. | Added "just in case" or because a textbook recommended it. |
| **Call Site Cost** | Low. Directly callable with minimal setup. | High. Requires nested instantiation or mocking. |
| **Locality** | Localized. Placed exactly where the capability runs. | Global. Placed in generic folders (`shared`, `common`). |
| **Change Frequency** | Interface changes rarely; implementations vary. | Every rule change requires editing multiple wrapper files. |

---

## 3. Mandatory AI Self-Review Protocol

Before committing or claiming a task is complete, the executing agent **must** run this linter-like review on every new file and function:

1. **Does this class use banned suffixes (`Manager`, `Service`, `Helper`, `Util`)?** If yes, rename to represent the active capability.
2. **Did I introduce an interface with only one implementation?** If yes, remove it and make the implementation concrete.
3. **Does a call to this function require instantiating other classes?** If yes, can we pass simple data structures or primitive values instead?
4. **Is this code direct, highly scannable, and clean of empty boilerplate?** Ensure the code is visually tight, utilizing standard language features.

*No offload recommended for this step.*
