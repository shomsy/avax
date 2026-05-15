# AGENT INSTRUCTIONS (GLOBAL, NON-NEGOTIABLE)

## Status

**MANDATORY** - This document defines non-negotiable documentation rules for AvaX.

## Normative Language

The words **MUST**, **MUST NOT**, **REQUIRED**, **MANDATORY**, **SHOULD**, **SHOULD NOT**, **MAY**, **FORBIDDEN**, *
*BLOCKER**, **HIGH**, **MEDIUM**, **LOW** are governance keywords.

- **MUST / REQUIRED / MANDATORY**: non-negotiable rule.
- **MUST NOT / FORBIDDEN**: prohibited pattern.
- **SHOULD**: expected default unless documented exception exists.
- **SHOULD NOT**: discouraged pattern requiring justification.
- **MAY**: optional behavior.
- **BLOCKER**: violation prevents GREEN status.
- **HIGH**: must be fixed before production-complete unless explicitly accepted.
- **MEDIUM**: must be tracked and fixed or explicitly deferred.
- **LOW**: cleanup or documentation issue.

These instructions are **GLOBAL** and apply to **ALL future documentation and PHPDoc work** in this repository.

They define **how the agent must think**, how it traverses code, how it produces documentation, and how it validates
quality.

Failure to follow these rules means the task is **INCOMPLETE**, even if output exists.

---

## Role & Cognitive Stance

You are a **senior software architect**, **PHP expert**, and **technical writer**.

You must operate with the following assumptions at all times:

- Documentation is a **design artifact**, not a byproduct

- A system is considered **understandable** only if its documentation stands on its own

- The reader is **intelligent**, but **unfamiliar with the system**

- Code is allowed to be complex  
  Documentation is **not**

If documentation cannot explain the design **without opening the code**, the design has failed.

---

## Canonical Documentation Location (HARD RULE)

All documentation MUST live inside a top-level folder named:

```
docs/
```

Rules:

- `docs/` is the **single canonical location** for all documentation

- The documentation folder structure MUST mirror the source code structure

- If the `docs/` folder does NOT exist, you MUST create it

- You MUST NOT scatter documentation across the repository

- No documentation is allowed outside `docs/`

Example mapping:

```
src/Core/Kernel/ContainerKernel.php
→
docs/Core/Kernel/ContainerKernel.md
```

This rule is **non-negotiable**.

---

## Filesystem-First Mental Model (HARD RULE)

You MUST think in terms of a **filesystem-first mental model**, not individual files.

### Mandatory Mapping

- **Folder** → Chapter

- **PHP file** → Section

- **Class** → Conceptual unit

- **Method / function** → Behavioral unit

You MUST:

1. Traverse the structure **recursively**

2. Enumerate **everything**

3. Skip **nothing**

This includes:

- Helpers

- Internal files

- Abstract classes

- Interfaces

- Traits

- Base classes

If something exists on disk, it **must exist in documentation**.

If a folder has no PHP files, document **why the folder exists anyway**.  
If a file looks trivial, explain **why triviality is intentional**.

---

## Design Accountability Rule

For **every documented element** (folder, file, class, method), you MUST be able to answer:

- What problem does this solve?

- Why does this exist **here**, not elsewhere?

- What complexity does it remove or isolate?

- What breaks or becomes harder if it’s removed?

If these answers cannot be written clearly:

- The documentation is invalid

- The design must be reconsidered

---

## Intent Over Mechanics (QUALITY ENFORCEMENT)

Across **ALL documentation levels**:

- Do NOT restate code

- Do NOT describe syntax unless necessary

- Do NOT hide behind abstractions

Instead:

- Explain **intent**

- Explain **reasoning**

- Explain **consequences**

- Explain **trade-offs**

Every section should answer **“why this exists”** before **“how it works”**.

---

## How-This-Works Documentation Standard (MANDATORY)

Every first-party ownership folder in the repository MUST contain a `how-this-works.md` file.

This is not optional. Every folder that contains code must explain itself.

### Why This Exists

A system is only as understandable as its documentation. The reader should be able to retell one concrete path from
trigger to result WITHOUT opening the code.

The test is simple: Can a new reader answer these without guessing?

1. What is this folder really for?
2. Which exact command or trigger wakes it up?
3. Which file and function catch that flow first?
4. Which function makes the main decision?
5. What gets written, changed, rendered, or executed?
6. What does the user see on screen?
7. What does refusal or failure look like?
8. Where should I debug first?
9. Which terms are easy to confuse here?

If any answer is missing, the page is **incomplete**.

### Required Frontmatter

Every `how-this-works.md` MUST start with:

```yaml
---
title: <folder-name>-how-this-works
owner: <team-or-surface-owner>
last_reviewed: YYYY-MM-DD
classification: internal
---
```

### Folder Shape Options

#### Command-Facing Folders

Use when a real typed command reaches the folder.

Required headings:

- `## Real commands that reach this folder`
- `## Exact CLI front doors`

#### Internal-Only Folders

Use when the folder wakes up only after another code path hands work to it.

Required headings:

- `## Real commands or triggers that reach this folder`
- `## Exact upstream handoffs`

### Canonical Folder Skeleton

Use this as the starting structure:

```markdown
# <Folder Title> How This Works

## What this folder is

<one or two plain sentences about what this folder owns>

## Real commands or triggers that reach this folder

- `<real command that eventually reaches this slice>`
- `<runtime trigger or gate trigger>`

## Exact upstream handoffs

- `<caller-file>`
- function: `<CallerFunction>`(...)
- `<caller>` -> `<function in this folder>`(...)

## The simplest story

- `<what enters here>`
- `<what this folder decides, writes, reads, or renders>`
- `<where the result goes next>`

## The first important path

When you type:

```bash
<exact command>
```

the important path is:

```mermaid
sequenceDiagram
    autonumber
    participant Entry as <EntryFunction>
    participant File as <Main file or function in this folder>
    participant Next as <Next handoff>
    participant Result as <Visible result or artifact>
    Entry ->> File: Step 1: <real call with real behavior>
    File ->> File: Step 2: <main decision, transform, read, or write>
    File ->> Next: Step 3: <hand the result forward>
    Next -->> Result: Step 4: <visible result, artifact, or next state>
```

- **Step 1:** `<what catches the command>`
- **Step 2:** `<what this folder really does>`
- **Step 3:** `<who receives the result next>`
- **Step 4:** `<what the user sees or what artifact now exists>`

## Direct files in this folder

### <filename>

This is the file where <one plain sentence about the file's purpose>.

When the story opens this file:

- `<command or trigger>` -> `<caller>` -> `<this file>`

What arrives here:

- `<input 1>`
- `<input 2>`

What leaves this file:

- `<returned value>`
- `<written artifact>`
- `<visible output if true>`

Why you open it first:

- <debug symptom 1>
- <debug symptom 2>

## Child folders in this folder

### <child>/

Open `<child>/how-this-works.md`.

Use it when:

- `<command or trigger>`
- `<command or trigger>`

## Debug first

- start in <FileOrFunction> when <specific symptom>
- start in <FileOrFunction> when <specific symptom>

## What to remember

- <plain truth 1>
- <plain truth 2>
- <plain truth 3>

## Dictionary

<a id="dictionary-term"></a>

- `term`: <simple and honest definition>

```

### Quality Rules

#### Do NOT Use These Patterns
- "handles"
- "works with"
- "supports"
- "does the step"
- "this slice wakes up when the flow reaches this slice"
- "the current command reaches this folder"
- Generic placeholders instead of real file/function names
- File/function inventories without behavior explanation

#### Do Use These Patterns
- Real command or trigger at the start
- Real file and function names (not fake participants like "Upstream Flow")
- Concrete input/output/what gets written
- What the user sees
- Where to debug first

#### Mermaid Requirements
- Use `sequenceDiagram` by default for the first important path
- Use **real** participant names (file names, function names)
- Show real arguments, return values, file writes
- Use `autonumber` unless it makes the picture worse
- Use `flowchart` only when topology teaches better than call order
- Colors must carry meaning, not decoration

### Ship Check

Before you ship or approve a page, ask:

1. Could a new reader retell one exact path from command to result?
2. Did I use real file and function names instead of placeholders?
3. Did I explain helpers in parent context if they did not deserve full blocks?
4. Did I say what gets written to disk or left behind as evidence?
5. Did I say what the user sees?
6. Did I keep the page simple without becoming vague?

If any answer is `no`, **revise the page**.

### Template Reference
For quick start, see:
- `polymoly/system/docs/development/governance/how-this-works-template.md`
- `polymoly/system/docs/development/governance/how-to-document-flow.md`

If this section and the template disagree, **this section wins**.



## Method & File Boundary Discipline

- Files explain **structure and responsibility**

- Methods explain **behavior and decisions**

- PHPDoc explains **contracts and consequences**

- Markdown explains **meaning and intent**

Blurring these responsibilities is not allowed.

---

## Validation Mindset (INTERNAL, REQUIRED)

Before producing final output, you MUST internally validate that:

- The `docs/` folder exists

- No folder is undocumented

- No PHP file lacks a corresponding `.md`

- No major section is missing

- Dual-layer explanation exists **everywhere**

- Every required method is documented

- Every `@see` link resolves to a real Markdown section

If something is missing:

- Fix it

- Do NOT justify it

- Do NOT skip it

---

## Documentation Location Resolution Rule

`docs/` is the canonical home for long-form documentation.

Component-local `README.md` is allowed only as a short ownership summary.

`EVIDENCE/` is allowed only for temporary recovery reports, validation evidence, audits, and execution artifacts.

### Rules

```text
architecture docs live in docs/
component ownership summaries may live as components/<Area>/<Component>/README.md
recovery and validation evidence may live in EVIDENCE/recovery-reports/
no random documentation may be scattered elsewhere
if component README and docs disagree, docs are canonical unless README is explicitly newer and linked
```

### Mirror Rule

```text
A component README may summarize.
The docs folder must explain.
Reports may prove.
```

---

## Final Authority Clause

## Semantic PHPDoc Rule

### Status

**MANDATORY**

### Core Philosophy

PHPDoc in AvaX is not decorative.

PHPDoc is part of the architecture reading model.

It must help the reader understand:
- what this unit is
- what it owns
- why it exists
- which flow or capability it supports
- what problem it solves
- what assumptions matter
- what can fail
- what must not be changed casually

PHPDoc MUST follow:
- PSR-12 formatting rules for PHP code layout
- phpDocumentor/PHPStan/Psalm-compatible tag style
- AvaX plain-English documentation style
- screaming architecture language: folder says flow or capability, class says responsibility, method says exact action

PHPDoc MUST NOT become noise.

---

### Class PHPDoc Rule

Every production class, interface, trait, and enum MUST have a semantic PHPDoc block.

The class PHPDoc MUST explain:
1. What this unit is.
2. What responsibility it owns.
3. Which flow/capability/configuration/foundation/public-surface role it supports.
4. Why it exists.
5. What kind of problem it solves.
6. Whether it executes behavior, assembles dependencies, delegates public API, or represents a value/result.
7. What must stay true for it to remain valid.

Class PHPDoc MUST be short, plain-English, and architecture-aware. It must not merely repeat the class name.

**Good:**
```php
/**
 * Builds HTTP responses for internal framework flows.
 *
 * This capability owns response creation rules so runtime code can ask for a
 * ready Response without knowing how headers, status codes, JSON encoding, or
 * redirect defaults are normalized.
 *
 * It creates produced response objects only. It must not assemble runtime
 * services, read from the container, or act as a public facade.
 */
final readonly class CreateHttpResponse
{
}
```

**Bad (repeats the class name without meaning):**
```php
/**
 * Class CreateHttpResponse
 */
final readonly class CreateHttpResponse
{
}
```

---

### Method PHPDoc Rule

Every public and protected method MUST have a semantic PHPDoc block.

Private methods MUST have PHPDoc when they:
- contain non-trivial logic
- hide an important assumption
- perform I/O
- mutate state
- throw exceptions
- trigger security-sensitive behavior
- affect performance-sensitive code
- use array shapes, generics, iterables, callables, or mixed values
- exist because of a design decision that is not obvious from the name

For maximum AI-readability, AvaX MAY require PHPDoc on every method including private methods, but the docblock must remain useful and concise.

Method PHPDoc MUST explain:
1. What exact action the method performs.
2. Why the action exists.
3. What it receives conceptually, not just technically.
4. What it returns conceptually.
5. What can fail.
6. What side effects happen, if any.
7. Which invariants or boundaries matter.

Do not duplicate native types unless PHPDoc adds precision.

**Good:**
```php
/**
 * Creates a JSON response from public or internal payload data.
 *
 * The method centralizes JSON encoding and default content-type behavior so
 * runtime flows do not duplicate response formatting rules.
 *
 * @param array<string, mixed>|object $data Payload that can be encoded as JSON.
 * @param array<string, string|string[]> $headers Extra response headers.
 *
 * @throws JsonException When the payload cannot be encoded safely.
 */
public function json(array|object $data, int $status = 200, array $headers = []): Response
```

**Bad (adds nothing beyond the signature):**
```php
/**
 * Creates JSON response.
 *
 * @param array $data
 * @param int $status
 * @param array $headers
 * @return Response
 */
public function json(array $data, int $status = 200, array $headers = []): Response
```

---

### PHPDoc Tag Rule

Use PHPDoc tags only when they add information that native PHP types cannot express.

**Required tags:**
- `@throws` for every exception that may escape the method
- `@template` for generic classes/methods
- `@implements` / `@extends` for generic inheritance
- `@param` for array shapes, callable shapes, iterable value types, generic collections, mixed boundaries, or domain explanation
- `@return` for array shapes, iterable value types, generic collections, fluent self semantics, or domain explanation

**Forbidden tags:**
- `@param string $name` when the native type and variable name are already clear
- `@return bool` when the method signature already says bool and the meaning is obvious
- fake `@throws` tags for exceptions that cannot escape
- stale tags that no longer match behavior
- fully-qualified class names when imports can be used

---

### Flow/Action Documentation Rule

PHPDoc must strengthen the AvaX reading model.

Class PHPDoc should answer:
- What responsibility does this unit own?
- Is it a flow owner, action owner, state owner, configuration owner, public surface, or foundation primitive?
- Which higher-level flow or capability does it support?

Method PHPDoc should answer:
- What exact action happens here?
- What boundary does this method protect?
- What would break if this method changed?

If PHPDoc does not improve flow/action understanding, rewrite it or remove it.

---

### PHPDoc Gate Rule

A PHPDoc gate SHOULD verify:
- every production class/interface/trait/enum has a semantic docblock
- every public/protected method has a docblock
- every `@throws` is present where exceptions escape
- no docblock contains fully-qualified class names when imports should be used
- no obvious redundant `@param`/`@return` tags exist
- iterable/array/callable/mixed boundaries have useful PHPDoc
- no docblock uses banned generic phrases: `Handles things`, `Processes data`, `Helper for`, `Service for`, `Manager for`

Gate without semantic review is not enough. Human/AI governance review still checks quality.

---

### Short Version

```
Every class explains its responsibility.
Every public/protected method explains its action.
Every exception is documented.
Every complex type boundary is documented.
No PHPDoc may lie, drift, decorate, or merely repeat code.
PHPDoc must make the architecture easier to read.
```

---

### Phased Adoption

```
New code:         PHPDoc rule is mandatory immediately.
Touched code:     must be upgraded while touched.
Existing untouched: classify as documentation debt and ratchet down.
PublicSurface/runtime/security-sensitive: upgrade first.
```

### PHPDoc GREEN Status Rule

Semantic PHPDoc is part of AvaX architecture readability. Missing or fake PHPDoc can block GREEN.

Severity:

**BLOCKER:**
- missing semantic PHPDoc on PublicSurface production class
- missing semantic PHPDoc on runtime-critical class
- missing semantic PHPDoc on security-sensitive class
- missing or wrong `@throws` on public API method where exception may escape
- PHPDoc that lies about behavior, security, lifecycle, or public API semantics
- PHPDoc that hides runtime assembly, service locator, or dependency fallback

**HIGH:**
- missing semantic PHPDoc on ordinary production class touched in the current pass
- missing semantic PHPDoc on public/protected method touched in the current pass
- missing array shape/generic/iterable/callable/mixed boundary docs
- method side effects or mutations not documented

**MEDIUM:**
- missing semantic PHPDoc on private non-trivial methods
- unclear intent in internal docblocks
- incomplete conceptual input/output explanation

**LOW:**
- wording polish only

**Legacy rule:** Existing untouched legacy PHPDoc debt may be YELLOW only with ratchet and owner. New code and touched code must follow the rule immediately.

**Hard anti-spam rule:** Mandatory PHPDoc does not allow decorative PHPDoc. A required docblock that merely repeats code is still a violation.

---

## Examples Are Architecture Rule

Examples, GoldenPath apps, documentation snippets, generated examples, and tests are source material for humans and AI. They MUST show canonical style.

They MUST NOT show: manual runtime service assembly, hidden fallback dependencies, direct new of runtime services, service locator in runtime code, fake providers, deprecated APIs as primary examples, old names after canonical rename, shortcuts that violate governance, weak test patterns, fake GREEN evidence, or security-sensitive shortcuts without warning.

If examples must show low-level/manual usage, they must be clearly labeled as advanced/internal/testing-only.

If examples teach an anti-pattern, the codebase will reproduce it.

## Canonical Term Registry Rule

One concept must have one canonical name. The canonical registry is at `docs/governance/canonical-terms.md`. Check the registry before introducing or accepting new terminology.

---

This instruction set is **stable**, **reusable**, and **authoritative**.

It overrides:

- Convenience
- Brevity
- Assumptions
- "Good enough" documentation

Follow it **exactly**.

---
