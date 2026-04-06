# Universal Architecture Standard

## Purpose

This document defines pragmatic architectural rules for organizing code, folders, files and functions so that the repo is predictable, readable and maintainable in any language, framework or project.

The goal is not for the architecture to look smart.
The goal is for it to be banally clear, stable and easy to develop over time.

In the shortest terms:

- **folder says flow or capability**
- **file says responsibility**
- **function says exact action**

The repo should read like a story of the system, not like a warehouse of technical drawers.

This standard is intentionally vertical-slice and feature-first.
It is designed to keep the surface absurdly simple while still carrying enterprise-grade quality underneath.

## Scope

This is a reusable baseline standard.

- applies to any project, language or framework
- language and framework architecture profiles may narrow this baseline
- project-level architecture documents may narrow and concretize it
- project-level documents must not weaken it
- if an exception is needed, it must be recorded in the repo-specific architecture document
- this is not a workflow contract; workflow belongs in repo-operative documents

## Architecture Law

This standard stands on one simple law:

- folder says flow or capability
- file says responsibility
- function says exact action

The same law applies to:

- business feature slices
- technical runtime slices
- infrastructure components
- framework-owned adapter lanes

The target feeling is banal clarity:

- titles should be intuitive
- names should be predictive
- the codebase should read like a story from the first feature to the last
- a smart developer should know where to look next without guessing

This is screaming architecture in the simplest honest form.
The goal is not visual cleverness.
The goal is simplicity of top quality.

## Vertical Slice Intent

This standard treats the repo as a vertical slice system.

- feature-first is the default orientation
- the same reading model applies to business features and technical slices
- each slice should be understandable from root entry to final effect
- folder names should communicate business flow or technical capability
- file names should communicate responsibility
- function names should communicate exact action
- a reader should be able to predict the purpose of a folder, file or function before opening it
- the same flow-first reading order must work for the product app and for the technical runtime
- the reader should be able to follow the business flow from the first feature to the last feature without changing the mental model
- root folder and slice names should feel intuitive enough that they can be
  explained at a table without jargon

---

## Core Idea

The architecture is organized by the following principle:

**flow -> feature slice -> file -> function**

But this rule is applied reasonably, not religiously.

More accurate version:

- **at product or application level:** flow-first
- **at feature level:** slice-first
- **within feature:** responsibility-first
- **at function and method level:** action-first

This means you see the system flow from the outside, and clear responsibilities from the inside.
This also means the same mental model works for both product-facing slices and technical slices.

---

## Three Types of Rules That Must Not Be Mixed

### 1. Structure Rules
How the repo looks and how it reads.

### 2. Design Rules
How responsibilities, dependencies, reuse and boundaries between modules are divided.

### 3. System Quality Rules
Performance, security, scaling, observability, interoperability, cache, costs and similar.

These must be separated.

For example:

- SOLID, DRY, KISS, YAGNI are design rules
- Composition over inheritance, Law of Demeter, clean code principles and LLD are design rules
- OWASP, auth, authorization, encryption, vulnerability management, secure APIs, rate limiting, cache, CAP, consistency patterns, latency vs throughput trade-offs, long polling vs WebSockets, usability, flexibility, scalability and cost efficiency are quality and technical requirements
- folder structure is a separate discipline

If everything is mixed into one rule, the document becomes too broad and stops guiding.

---

## Main Architectural Rules

### 1. Top-level structure should shout what the system does
Do not organize the repo by technical layers as the default rule.

Good direction:

```text
product/
system/
foundation/
```

or:

```text
create/
configure/
run/
deploy/
inspect/
```

Worse direction as default:

```text
controllers/
services/
models/
utils/
helpers/
```

The latter talks about how the system is technically sliced.
The former talks about what the system does.

---

### 2. Each feature slice must have one flow owner at the root
At the root of each feature slice there must be one main entry that wraps the whole thing.

This can be:

- `*.pipeline` when the flow is sequential
- `*.facade` when the feature provides a simple entry into a complex interior
- `*.orchestrator` when coordinating multiple flows, branches or events
- a single root function or single root file when that is the clearest composition root
- a banal main name, if it's more readable than a pattern name

The point is not to use a pattern for the pattern's sake.
The point is to have one place that clearly says:

**from here this feature begins**
**from here this feature is fully owned**

If none of the named patterns is the clearest answer, use the smallest construct that can still own the whole feature slice.

---

### 3. Subfolder exists only if it represents a real step or real subfeature
Subfolder is not introduced to make the architecture look serious.

Subfolder exists only when:

- it represents a real step in a flow
- it represents a real sub-capability
- it genuinely reduces mental noise

Good example:

```text
deploy/
  prepare/
  validate/
  release/
```

Bad example:

```text
deploy/
  services/
  helpers/
  managers/
```

If an extra folder doesn't help reading, it shouldn't exist.

---

### 4. Each file carries one responsibility
File should not be a thematic bucket.
It should carry one clear responsibility.

Good:

- `read_release_request`
- `build_release_bundle`
- `verify_runtime_checksum`
- `publish_release_proof`

Worse:

- `deploy_utils`
- `release_manager`
- `misc_helpers`

If the filename doesn't explain why it exists, the name is bad or the file is too broad.

---

### 5. Each function does one exact action
Function or method must have a verb name that tells exactly what they do.

Good:

- `readRequestedProfile`
- `planDatabaseChanges`
- `writeRuntimeManifest`
- `verifyReleaseChecksum`

Worse:

- `handle`
- `process`
- `execute`
- `doWork`

Generic names are allowed only when the context is extremely clear.

---

### 6. Shared is the last option, not the first
The biggest enemy of this architecture is early extraction of everything into:

```text
shared/
common/
utils/
helpers/
misc/
```

Such places almost always become technical waste.

Shared code may exist only when it is:

- stable
- generic
- without product meaning
- boring but useful to everyone

Good candidates are:

- checksum
- filesystem primitives
- retry policy
- clock
- logging primitives
- process execution
- id generation

If something still carries product meaning, it doesn't belong in shared.

---

### 7. Root feature must be small and readable
When you open a root feature folder, it must immediately be clear:

- what is the main entry
- what are the main steps
- where do you go next

Root feature must not look like a registry chaos with a bunch of unrelated files.

---

### 8. Pattern is chosen by the nature of the flow, not by fashion
Pattern is not the goal. Clarity is the goal.

Use:

#### `pipeline`
When there is a clear sequence of steps:

- A then B then C
- one step feeds the next
- failure flow is mostly sequential

#### `facade`
When the feature is not one flow but one stable public entry into multiple internal things.

Use it when:

- external callers need one obvious stable API
- internal complexity should stay behind an honest boundary
- backwards compatibility matters more than exposing internal composition

The facade stays thin.
It names and delegates.
It does not become a second hidden implementation.

#### `kernel`
When one component must own the full ingress lifecycle, runtime coordination, or
exception boundary for a surface.

Use it when:

- requests, jobs, or events must pass through one controlled runtime boundary
- middleware, validation, dispatch, or policy stages need one owner
- unexpected failures must be caught and mapped in one place

Kernel is an ingress governor, not a business-logic dump.
It should orchestrate and contain failure, then delegate the real work.

#### `orchestrator`
When there is coordination of multiple branches, events, parallel flows or multiple subflows.

#### banal main name
When the feature is small and a simpler name is better than a pattern name.

If a pattern makes more noise than it helps, it shouldn't be used.

### 9. Public surface and internal machinery must not be confused

If a slice has a public API, CLI, route DSL, event contract, or reusable
library boundary:

- keep the public surface small and stable
- keep internal mechanics replaceable without breaking the public contract
- do not leak internal folder structure as the public API by accident
- document which layer is compatibility-sensitive and which layer is internal
- keep registration, runtime resolution, and execution as separate concerns when
  the system has all three

### 10. Ingress must have one obvious failure boundary

For request-driven, event-driven, or job-driven systems:

- one owned entry boundary should validate, delegate, and map failure honestly
- unexpected exceptions should be caught at that ingress boundary, not leak
  randomly through the stack
- degradations, retries, and stop conditions should be visible at the same
  ownership boundary
- the business core should not know transport-specific failure mapping details
- if there is no single ingress owner, recovery and observability will drift

### 11. Clean architecture belongs inside the slice, not above the story

Clean architecture is welcome here, but only when it helps the code explain:

- dependency direction
- public versus internal boundaries
- policy versus adapter ownership
- testability and replacement seams

In this standard that means:

- the repo still screams features or capabilities first
- clean layers live inside the owning feature or package root
- one slice may contain interior layers such as public surface, kernel,
  pipeline, domain or policy core, adapters, and diagnostics
- small slices should collapse layers instead of creating empty shells
- the dependency rule matters more than using fashionable layer names

Good direction:

```text
billing/
  issue_invoice.ts
  domain/
  persistence/
  integrations/

router/
  Router.php
  registration/
  resolution/
  execution/
```

Bad default direction:

```text
controllers/
use_cases/
repositories/
entities/
```

If the top of the repo no longer tells the reader what the system does, the
clean architecture interpretation is too abstract.

### 12. Declaration, resolution, execution, and failure mapping are separate lanes

If a system has DSL, registration, route definition, policy registration,
container wiring, or other declaration phases:

- declaration should validate early and become stable after bootstrap
- resolution should read declared state without becoming the place that owns
  business side effects
- execution should happen only after successful resolution or admission
- failure mapping should converge at the ingress or public boundary owner
- caches, manifests, compiled artifacts, and diagnostics are derived from the
  canonical declarations

This rule matters for routers, containers, command systems, queues, webhook
ingress, and other infrastructure-like components.

---

## Naming Convention

### Folder
Folder should be capability, step or flow.

Names should be banal, intuitive, descriptive and predictable.
They should read like plain language explanations, as if you were explaining the system to a child at a table.

Examples:

- `deploy`
- `prepare`
- `validate`
- `release`
- `database`
- `gateway`
- `runtime`

### File
File should be verb plus object of responsibility.

The filename should tell why the file exists, not merely what bucket it belongs to.

Examples:

- `read_release_request`
- `build_release_bundle`
- `verify_bundle_integrity`
- `write_gateway_config`

### Function
Function or method should be exact action.

The function name should make the action obvious without opening the file.

Examples:

- `readReleaseRequest`
- `buildReleaseBundle`
- `verifyBundleIntegrity`
- `writeGatewayConfig`

### Operation Vocabulary
Before you choose the exact file or function name, classify the work in this order:

1. CRUD
2. Query
3. Business
4. System
5. State transition

These buckets are the first filter for file and function names.

Pick the narrowest honest bucket first. If a more specific business or state verb exists, prefer it over a generic `update` or `change`.

#### CRUD
Use this when the code creates, reads, updates or deletes one concrete record.

This is the "I touch one record" bucket.

Examples:

- `create_user`
- `read_user`
- `update_user`
- `delete_user`

Use it for simple record work, admin panels, small modules and generic storage tasks.

#### Query
Use this when the code finds, filters, sorts, paginates, groups or aggregates data without changing it.

This is the "I look at data" bucket.

Examples:

- `search_orders`
- `filter_orders_by_status`
- `sort_orders_by_created_at`
- `paginate_orders`
- `aggregate_revenue`

Use it for listing screens, search screens, reports and read-only views.

#### Business
Use this when the code performs a domain action the business cares about.

This is the "I do the real business thing" bucket.

Examples:

- `approve_invoice`
- `publish_article`
- `assign_task`
- `archive_project`
- `restore_document`

Use it when the verb should sound like the business, not like the database.

#### System
Use this when the code imports, exports, syncs, retries, queues, validates, transforms, backs up, restores, logs or notifies around the system.

This is the "I help the system move" bucket.

Examples:

- `import_users_from_csv`
- `sync_products_from_api`
- `retry_failed_webhook`
- `export_invoices_to_pdf`
- `validate_payload`

Use it for integration jobs, background jobs, pipelines, adapters and maintenance flows.

#### State transition
Use this when the code moves an entity from one named state to another and that move matters to domain rules.

This is the "I move the thing to the next state" bucket.

Examples:

- `issue_invoice`
- `mark_invoice_as_paid`
- `cancel_order`
- `reopen_task`
- `activate_user`

Use it when `update_status` would hide the real meaning.

Avoid whenever there's a better name:

- manager
- handler
- processor
- service
- helper
- util

These names often hide that the responsibility is not clearly defined.

---

## Quality Goals

These are first-class goals, not optional polish:

- SOLID
- DRY
- KISS
- YAGNI
- Composition over inheritance
- Law of Demeter / principle of least knowledge
- clean code principles
- low-level design (LLD) clarity
- performance as a first-class requirement
- security by default
- OWASP
- authentication
- authorization
- data encryption
- vulnerability management
- secure APIs
- usability
- cost efficiency
- interoperability
- flexibility
- scalability
- cache only where it solves a real problem
- rate limiting where resources are expensive or externally exposed
- checksum and integrity where relevant
- latency vs throughput trade-offs chosen deliberately
- CAP and consistency patterns made explicit
- long polling vs WebSockets chosen deliberately when transport behavior matters

The standard goal is simple-looking architecture with enterprise-grade quality behind it.
If a rule improves quality but makes the structure harder to read, it must be justified explicitly.

## Modularity Rules

### 1. Composition over inheritance
Give preference to composition and collaboration of small modules.

### 2. Law of Demeter
Module should know as little as possible about the internals of other modules.

### 3. Tight coupling is prohibited
Feature must not depend on another feature's internal implementation.

### 4. Reuse must not be premature
Premature code sharing often produces bad abstractions.

### 5. Abstraction must reduce noise
If an abstraction is not clearer than the concrete code it replaces, it's bad.

### 6. Over-engineering is a defect
Every additional layer must justify its existence.

### 7. Reusability must be deliberate
Do not ignore reusability when a primitive is stable, generic and clearly useful in more than one place.

### 8. Abstraction must be sufficient
Insufficient abstraction is also a problem when it forces the same coupling, duplication or branching to repeat across slices.

---

## Quality Rules That Follow Architecture

These are not folder structure rules, but must follow every feature:

- security by least-privilege principles
- clearly separated authentication and authorization
- data protection in transit and at rest when relevant
- input validation and output sanitization
- observability: logs, metrics, traces, diagnostics
- performance: reasonable balance of latency and throughput, with latency vs throughput chosen deliberately
- cache only where it solves a real problem
- rate limiting where there is external or expensive resource
- checksum, integrity and reproducibility where they make sense
- cost efficiency and operational simplicity
- interoperability and evolvability of interfaces

The point is:

**quality must live within the feature, but doesn't have to determine its folder name**

---

## When a Rule Must Not Be Forced

This standard is general. It must not be applied blindly when it works against language, ecosystem or framework.

### General Exception Rule
If the ecosystem is very idiomatic and has strong conventions that make reading, debugging, tool support and onboarding easier, those conventions take precedence over forcibly renaming everything.

In other words:

**don't break language idioms just so the architectural doctrine looks consistent**

### Pattern Compatibility Rule

Architectural patterns such as MVC, Clean Architecture, Hexagonal Architecture,
CQRS, and similar may be used when they reduce noise and clarify ownership.

But they are subordinate to the screaming feature-first law:

- pattern names must not erase the business or capability story of the repo
- framework terms such as controller, model, view, job, listener, or channel
  may exist as local implementation lanes
- those terms should not become the whole personality of the repository by
  default
- if a pattern is useful, keep it inside the feature or inside a clearly owned
  technical slice
- if a pattern makes naming less intuitive, reduce it or rename the boundary

---

## Language and Ecosystem Exceptions

When the repo needs more concrete ecosystem guidance, apply the relevant
architecture profile from `profiles/**` before writing a repo-specific
`ARCHITECTURE.md`.

### Functional Programming and Non-OOP Languages
Some languages and ecosystems are not class-first.

In those cases, low-level design does not disappear.
It moves into functional design with the same clarity bar.

Rules:

- use pure functions as the smallest meaningful design unit when possible
- keep inputs explicit and outputs deterministic
- isolate side effects at the edges
- prefer immutable data and data transformation pipelines
- keep modules small, composable and easy to test
- use composition, higher-order functions and explicit dependency passing instead of class-based patterns
- if a feature slice is sequential, a pipeline root still owns the flow
- if a feature slice is not sequential, use the smallest root composition unit that makes the whole slice obvious

**In non-OOP environments, LLD becomes functional clarity, not a weaker version of architecture.**

### Go
Go likes small packages, short names and strong connection between package boundary and responsibility.

Rules:

- don't create deep hierarchy without need
- package name should stay short and idiomatic
- don't force suffixes like `.pipeline.go` if they break idiom and make package reading difficult
- if `deploy_pipeline.go` is readable in the team, use it
- if more idiomatic solution says `deploy.go` inside package `deploy`, readability in Go world takes precedence

**For Go, package boundary is more important than overly descriptive filename.**

---

### Java and C#
These ecosystems like clear namespaces, types and often stronger connection between class and file.

Rules:

- don't break idiom of one main type per file when it's the project standard
- feature slice can live across package or namespace boundaries
- root feature owner can be a class like `DeployPipeline`, `DeployFacade` or `DeployOrchestrator`
- don't try to make everything look like script-like file structure if the ecosystem naturally leads to types

**For Java and C#, feature is often more naturally organized through package and type, not just through filename.**

---

### Python
Python likes readable modules, packages and often simpler hierarchy.

Rules:

- don't create unnecessarily deep packages
- use modules with clear names and small package units
- don't introduce abstract layers just so the structure looks enterprise
- if `deploy_pipeline.py` helps, use it
- if simpler `deploy.py` in `deploy/` package is valid, that's valid

**In Python, it's more important that import path stays natural than that every filename carries maximum words.**

---

### Rust
Rust strongly ties structure to modules, crate boundaries and explicitness.

Rules:

- respect idioms of `mod`, `lib`, `main`, `crate` boundaries
- don't create structure that fights against the module system
- feature slice can be a crate, module or submodule, depending on feature complexity
- use responsible modules rather than folder depth for folder depth's sake

**In Rust, module boundary is often more important than the story the folder itself tells.**

---

### JavaScript and TypeScript
These ecosystems easily go into chaos without good structure, so this standard often works very well here.

Rules:

- feature-first structure is usually a good choice
- avoid early `utils`, `helpers`, `services` as buckets
- for UI and Web Components, keep clear boundaries between shell, state, view, events and effects
- for framework projects, also respect local idioms if they bring tooling benefit

**In JS/TS world this standard is often the most natural, but don't create depth that increases file jumping.**

---

### Ruby, Elixir, Phoenix, Rails and similar opinionated frameworks
These ecosystems often come with strong conventions-over-configuration rules.

Rules:

- don't break framework expectations without strong reason
- introduce feature slice where it doesn't kill framework ergonomics
- if framework strongly expects certain places for controller, view, model, job or channel, respect them
- screaming architecture can be introduced through modules, namespaces and feature groups, not necessarily through completely breaking framework layout

**When framework has strong operational conventions, first preserve team productivity, then introduce your own doctrine.**

---

### Frontend UI Projects and Component Systems
For UI systems and design systems, you shouldn't force business flow where component capability naturally exists.

Rules:

- feature folder can be component or capability
- within component, follow real boundaries: shell, state, render, events, effects
- don't create micro-folders if one component doesn't deserve it
- root component file must clearly say where the component begins

**For components: feature outside, responsibility inside.**

---

## What a Healthy Decision Looks Like

### Good thinking pattern

- Does this top-level folder tell the story of the system?
- Does this feature have one flow owner?
- Is this subfolder a real step or real subfeature?
- Does this file carry one clear responsibility?
- Does this function do one exact action?
- Does new abstraction reduce mental noise?
- Am I breaking language or framework idioms without strong reason?

If the answers are clear, the structure is good.

---

## Red Flags

If you see this, the architecture is probably rotting:

- too much `shared`, `common`, `utils`, `helpers`
- root feature with too many unrelated files
- generic names like `manager`, `processor`, `handler`
- subfolders that don't represent real steps
- deep hierarchy that doesn't reduce noise
- pattern chosen by fashion, not by nature of flow
- feature that depends on another feature's internals
- abstractions that hide simple things instead of clarifying them

---

## Standard Summary

1. Repo is organized by flows and capabilities, not by technical layers as default.
2. Each feature slice has one flow owner at the root.
3. Root feature uses pipeline, facade, orchestrator or banal main name, depending on the nature of the flow.
4. Subfolder exists only if it represents a real step or real subfeature.
5. Each file has one responsibility.
6. Each function does one exact action and carries a verb name.
7. Shared is avoided and used only for truly generic technical primitives.
8. Product or app layer tells the story of the system. Technical layers tell the story of execution. Foundation carries boring primitives.
9. Pattern is chosen by the nature of the flow, not by fashion.
10. If an extra folder, file or abstraction doesn't reduce mental noise, it is not introduced.
11. Clean architecture, when used, lives inside the slice rather than replacing the feature-first story of the repo.
12. Declaration, resolution, execution, and failure mapping stay separate when the system has those lanes.
13. Language and framework idioms take precedence when rigid application of this standard would worsen readability or tooling.
14. The ultimate goal is not complexity. The ultimate goal is simplicity of top quality.

---

## One Final Sentence

Good architecture doesn't need to impress from a distance.
It needs to be so clear that a person can open the repo and immediately know:

- where they are
- what's happening here
- what order things flow
- where they should go next

That's the whole standard.
