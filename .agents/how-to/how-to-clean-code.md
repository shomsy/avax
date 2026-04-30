# CleanCodeBible.md

## AI Governance for Clean Code, Refactoring, Design, Testing, and Maintainability

**Version:** 1.0  
**Status:** Working master document  
**Audience:** Humans and AI coding agents  
**Goal:** One practical governance document that consolidates the most influential clean-code schools into a single
operational standard.

---

## 1. What this document is

This is a **best-effort, high-coverage governance canon**, not a claim that one document can literally capture every
rule ever written by every software author in history.

Its job is simpler and more useful:

1. Gather the strongest and most durable rules from the major clean-code schools.
2. Normalize them into one decision system.
3. Tell humans and AI what to do when respected authors disagree.
4. Turn abstract principles into **enforceable review behavior**.

Use it as a governing layer above style tools, code review comments, and AI prompts.

---

## 2. How to use this document

Use this in four modes:

### 2.1 Authoring mode

When writing new code, prefer the strictest rule that improves readability, safety, testability, and changeability
without creating ceremony.

### 2.2 Refactoring mode

When changing existing code, improve the local design but do not perform speculative rewrites. Preserve behavior unless
behavior change is explicitly requested.

### 2.3 Review mode

Judge code by:

- correctness first
- readability second
- changeability third
- consistency fourth
- cleverness last

### 2.4 AI mode

Treat this document as a **governance contract**. AI must not cherry-pick principles that justify cleverness,
overengineering, or style vanity.

---

## 3. Normative language

The following keywords are normative:

- **MUST**: mandatory
- **MUST NOT**: prohibited
- **SHOULD**: strong default
- **SHOULD NOT**: generally avoid
- **MAY**: acceptable when justified

---

## 4. Order of precedence

When rules conflict, use this priority order:

1. **Correctness and safety**
2. **Project-specific architecture and conventions**
3. **Domain clarity**
4. **Readability and maintainability**
5. **Testability**
6. **Simplicity**
7. **Performance**
8. **Local stylistic preference**
9. **Personal taste**

This matters because many "clean code" arguments are really taste arguments disguised as principles.

---

## 5. Universal governance rules

These are the baseline rules that override author-specific preferences unless explicitly justified.

### 5.1 Correctness

- Code MUST do the right thing before it does the pretty thing.
- Behavior-changing refactors MUST be treated as feature changes, not formatting changes.
- Error cases MUST be handled intentionally.

### 5.2 Readability

- Code MUST optimize for the next reader, not the current writer.
- The reader SHOULD understand intent without mentally simulating the whole program.
- Cleverness that reduces clarity MUST be rejected.

### 5.3 Simplicity

- Prefer the simplest design that satisfies today's known requirements.
- Simplicity is not minimal line count. Simplicity is low cognitive load.
- Hidden magic SHOULD be treated with suspicion.

### 5.4 Naming

- Names MUST reveal intent, role, and domain meaning.
- Names MUST NOT leak accidental implementation details unless that detail matters.
- Abbreviations SHOULD be avoided unless they are standard in the domain.
- "Manager", "Helper", "Utils", "Stuff", "Data", "Info", "Processor", "Handler" SHOULD be rejected unless they describe
  a real boundary and not a naming failure.

### 5.5 Function design

- Each function SHOULD do one coherent thing.
- A function MUST have one obvious reason to exist.
- Deep nesting SHOULD be reduced.
- Boolean flag arguments SHOULD be treated as a design smell.
- Output SHOULD be obvious from the function name and signature.

### 5.6 Class and module design

- Modules SHOULD own a clear responsibility.
- Cohesion SHOULD be high.
- Coupling SHOULD be low.
- Public surfaces SHOULD be small and stable.
- Internal detail SHOULD stay internal.

### 5.7 Data and invariants

- Invalid states SHOULD be unrepresentable where practical.
- Use value objects, enums, and typed boundaries when they reduce ambiguity.
- Primitive obsession SHOULD be reduced when it harms meaning or safety.

### 5.8 Error handling

- Failures MUST be explicit.
- Error messages MUST explain what failed and what matters next.
- Silent swallowing of failures SHOULD be forbidden except in intentionally idempotent or best-effort contexts.

### 5.9 Testing

- Tests MUST verify behavior, not implementation trivia.
- Tests SHOULD make refactoring safer, not harder.
- Over-mocked tests that freeze internals SHOULD be rejected.
- The tighter the logic and risk, the stronger the test expectation.

### 5.10 Refactoring

- Refactoring SHOULD preserve behavior.
- Large refactors SHOULD be decomposed into small safe moves.
- Duplication SHOULD be removed only when the abstraction is truly stable.

### 5.11 Performance

- Do not guess. Measure.
- Do not optimize by default.
- Do optimize when a real bottleneck, cost, or scale condition is known.

### 5.12 Documentation

- Comments SHOULD explain why, not restate what.
- Public APIs SHOULD document guarantees, constraints, and failure conditions.
- Complex business rules SHOULD be documented close to the code that enforces them.

### 5.13 Security

- Security-sensitive paths MUST favor explicitness over convenience.
- Validate inputs at boundaries.
- Escape, sanitize, or parameterize according to context.
- Secrets MUST NOT be hard-coded.

### 5.14 Concurrency and side effects

- Shared mutable state SHOULD be minimized.
- Time, randomness, IO, and environment access SHOULD be isolated behind boundaries when testability matters.
- Side effects SHOULD be visible at the orchestration layer, not buried in deep helpers.

### 5.15 Consistency

- A codebase SHOULD feel internally coherent.
- Local consistency usually beats imported ideology.

---

## 6. Author canon

## 6.1 Robert C. Martin canon

**Core concern:** discipline, readability, SRP, boundaries, professionalism.

### Adopt

- Functions SHOULD be small.
- Functions SHOULD do one thing well.
- Names MUST reveal intent.
- Duplication SHOULD be removed.
- Formatting SHOULD support reading flow.
- Modules SHOULD have one reason to change.
- Dependency direction SHOULD favor high-level policy over low-level detail.
- Boundaries matter. Keep external frameworks and details from polluting the core model.

### Do not over-apply

- "Smaller is always better" is not universally true.
- Tiny methods that fracture reading flow SHOULD be rejected.
- Extreme extraction that destroys local comprehension SHOULD be rejected.
- Purity rhetoric MUST NOT overrule practical delivery.

### Governance interpretation

Use Martin as the default discipline engine, but filter it through:

- Fowler for refactoring judgment
- Beck for simplicity
- Feathers for legacy pragmatism
- McConnell for engineering balance

---

## 6.2 Martin Fowler canon

**Core concern:** refactoring, code smells, changeability, evolutionary design.

### Adopt

- Refactoring is a disciplined technique for restructuring code without changing observable behavior.
- Make changes in small safe steps.
- Treat code smells as signals, not proofs.
- Remove duplication, simplify conditionals, improve data organization, and make intent explicit.
- Prefer designs that are easier to evolve.

### Do not over-apply

- A smell is not a conviction.
- Do not refactor simply because a pattern catalog says you can.
- Not every repeated line is harmful duplication.
- Do not weaponize refactoring against shipping.

### Governance interpretation

If code is hard to change, hard to understand, and smells cluster together, Fowler's school has priority.

---

## 6.3 Kent Beck canon

**Core concern:** simple design, TDD, incrementalism, YAGNI-friendly design.

### Adopt

- Pass all tests.
- Reveal intent.
- Eliminate duplication.
- Minimize elements to the simplest sufficient design.
- Prefer code that is easy to change today.
- Introduce design pressure through tests and feedback, not prediction.

### Do not over-apply

- TDD is a tool, not a religion.
- Over-fragmented micro-steps that obscure the resulting design SHOULD be avoided.
- "Just enough design" does not mean "no design."

### Governance interpretation

When in doubt, ask:

- does it work?
- is the intent obvious?
- is there unnecessary duplication?
- is there anything here we do not need yet?

If yes, simplify.

---

## 6.4 Michael Feathers canon

**Core concern:** legacy code, safe change, test seams, rescue operations.

### Adopt

- Legacy code is code without tests.
- Before changing risky code, find or create seams.
- Add characterization tests when behavior is unclear.
- Make the change easier, then make the easy change.
- Reduce blast radius before ambition.

### Do not over-apply

- Do not refuse all cleanup because "legacy."
- Do not chase perfect coverage before any change.
- Do not rewrite a stable ugly system without a hard business reason.

### Governance interpretation

Feathers has priority whenever:

- behavior is unclear
- tests are weak
- the code is old and business-critical
- the cost of a wrong refactor is high

---

## 6.5 Steve McConnell canon

**Core concern:** software construction as a disciplined engineering activity.

### Adopt

- Prefer readability over terseness.
- Manage complexity deliberately.
- Use defensive programming where it reduces risk.
- Use standards and conventions to reduce cognitive friction.
- Structure code for maintainability, not ego.

### Do not over-apply

- Defensive code should not become noise.
- Standards should not become ritualized bureaucracy.

### Governance interpretation

McConnell is the balancing school. Use it to stop ideological excess from either the "tiny pure code" camp or the "ship
anything" camp.

---

## 6.6 Andrew Hunt and David Thomas canon

**Core concern:** pragmatism, DRY, orthogonality, responsibility, automation.

### Adopt

- Do not live with broken windows.
- Keep knowledge in one authoritative place.
- Prefer orthogonal designs.
- Automate repetitive work.
- Use tracer bullets when exploring.
- Treat code as communication and craft.
- Fix root causes instead of repeatedly patching symptoms.

### Do not over-apply

- DRY is about duplicated knowledge, not every repeated syntax fragment.
- Premature abstraction in the name of DRY SHOULD be rejected.

### Governance interpretation

Use this school to fight:

- copy-paste domain rules in multiple places
- manual repetitive workflows
- architectural tangles where one change leaks everywhere

---

## 6.7 Joshua Bloch canon

**Core concern:** API design, correctness, immutability, composition, robustness.

### Adopt

- Prefer clear, hard-to-misuse APIs.
- Favor immutability where practical.
- Prefer composition over inheritance.
- Validate parameters and define contracts.
- Design types so misuse is difficult.
- Minimize mutability and expose the least dangerous surface.

### Do not over-apply

- Do not build a cathedral around a small use case.
- Excessive wrapper types without real invariants SHOULD be rejected.

### Governance interpretation

Bloch has priority in library code, public APIs, reusable components, and places where misuse cost is high.

---

## 6.8 Sandi Metz canon

**Core concern:** small objects, low coupling, high cohesion, message-driven design.

### Adopt

- Keep objects small.
- Reduce knowledge between collaborators.
- Prefer message passing over invasive inspection.
- Make code easier to change than to explain.

### Heuristic rules often associated with her

- Small classes
- Very small methods
- Limited parameter counts
- Controllers or orchestration layers that know little and delegate clearly

### Do not over-apply

- Tiny methods that destroy narrative flow SHOULD be rejected.
- Hard numeric limits are exercises, not universal law.

### Governance interpretation

Use Metz when objects start knowing too much about each other or classes become junk drawers.

---

## 6.9 Eric Evans canon

**Core concern:** domain-driven design, ubiquitous language, model integrity.

### Adopt

- The code SHOULD speak the language of the business domain.
- Important domain concepts SHOULD be explicit in types and names.
- Boundaries between models MUST be clear.
- Core domain logic SHOULD not be buried under framework noise.
- Distinguish entities, value objects, aggregates, and domain services when doing so clarifies the model.

### Do not over-apply

- Not every CRUD app needs a full DDD ceremony.
- Do not create fake domain poetry around simple data plumbing.

### Governance interpretation

Evans has priority when the business domain is complex, long-lived, and central to product value.

---

## 6.10 Bertrand Meyer canon

**Core concern:** contracts, correctness, explicit obligations, robust OO design.

### Adopt

- Preconditions, postconditions, and invariants SHOULD be explicit where valuable.
- APIs SHOULD define obligations and guarantees.
- Callers and callees SHOULD have clear responsibilities.
- Correctness rules belong near the code that enforces them.

### Do not over-apply

- Formal contract style should not overwhelm simple application code.
- Assertions should clarify guarantees, not duplicate obvious checks everywhere.

### Governance interpretation

Meyer has priority for foundational components, financial rules, security-sensitive logic, and any place where ambiguity
is dangerous.

---

## 6.11 Law of Demeter canon

**Core concern:** least knowledge, shallow navigation, controlled collaboration.

### Adopt

- Talk to immediate collaborators, not strangers several objects away.
- Avoid long call chains.
- Prefer asking the right object to do the work instead of navigating through internals.
- Reduce knowledge leakage between modules.

### Do not over-apply

- Blind obedience can create pointless forwarding methods.
- Fluent APIs and legitimate composition should not be banned dogmatically.

### Governance interpretation

If you see chains that expose deep structure knowledge, treat that as a warning sign.

---

## 6.12 Ron Jeffries and YAGNI canon

**Core concern:** do not build what you do not need yet.

### Adopt

- Build for current proven requirements.
- Defer speculative generalization.
- Let tests and real pressure justify new abstraction.
- The simplest working solution is often correct for now.

### Do not over-apply

- YAGNI is not an excuse to ignore obvious upcoming requirements that are already committed.
- It is not permission to ship brittle code that obviously blocks the next step.

### Governance interpretation

Reject:

- speculative extension points
- fake generic frameworks
- interfaces created only in case we need another implementation someday

---

## 6.13 Jeff Bay and Object Calisthenics canon

**Core concern:** training exercises that force better OO habits.

### Common rules

- one level of indentation per method
- avoid else when clarity improves
- wrap primitives and strings when meaning matters
- first-class collections
- one dot per line
- do not abbreviate
- keep entities small
- few instance variables
- avoid trivial getters and setters as a design default

### Governance interpretation

Treat object calisthenics as **training pressure**, not binding law.
Use it to discover better design. Do not force it everywhere.

---

## 6.14 Tim Peters and the Pythonic canon

**Core concern:** readability, explicitness, practical elegance.

### Adopt

- Explicit is better than implicit.
- Simple is better than complex.
- Complex is better than complicated.
- Readability counts.
- Errors should never pass silently unless explicitly silenced.
- In the face of ambiguity, refuse the temptation to guess.

### Governance interpretation

This is bigger than Python. These rules are broadly excellent for AI-generated code.

---

## 7. Language and ecosystem canons

## 7.1 PHP canon

Primary governance:

- PSR-12 and relevant accepted PSRs
- framework conventions only when they improve clarity in that codebase

### Rules

- Follow accepted ecosystem standards.
- Prefer explicit typing and strict mode where available.
- Use language-native constructs idiomatically.
- Avoid framework magic where it hides domain intent or weakens analyzability.

---

## 7.2 Python canon

Primary governance:

- PEP 8
- PEP 20
- idiomatic Python over transplanted Java or C# patterns

### Rules

- Readability and explicitness take priority.
- Do not force object-heavy patterns where functions or simple modules are clearer.
- Prefer Pythonic clarity over enterprise cosplay.

---

## 7.3 Go canon

Primary governance:

- Effective Go
- Go Code Review Comments
- Go doc comment guidance

### Rules

- Favor clear, idiomatic, direct code.
- Keep interfaces small and consumer-driven.
- Error handling should be explicit.
- Do not import heavyweight OO habits that fight the language.
- Comments and naming should align with Go conventions.

---

## 7.4 Java and Google-style ecosystems

Primary governance:

- Effective Java
- Google Java Style when used by the codebase

### Rules

- API design quality matters.
- Immutability, contracts, and type safety matter.
- Prefer boring, clear Java over pattern-heavy Java theater.

---

## 7.5 C++ canon

Primary governance:

- C++ Core Guidelines
- project-specific style guide, often Google C++ Style or equivalent

### Rules

- Favor safety and clarity over low-level heroics.
- Resource ownership must be obvious.
- Undefined behavior risk must be taken seriously.
- Modern idioms SHOULD replace legacy habits when they improve safety and readability.

---

## 8. Synthesis rules across all schools

These are the "if everything above were compressed into one page" rules.

### 8.1 Structure

- Organize code by meaningful responsibility.
- Keep related behavior together.
- Expose a small public surface.

### 8.2 Naming

- Use names that reveal domain meaning, role, and effect.
- Avoid vague containers and fake abstractions.

### 8.3 Abstraction

- Extract only when the abstraction is more stable than the duplication.
- Avoid speculative generalization.
- Prefer concrete code until variation is real.

### 8.4 Functions

- Keep them focused.
- Minimize flags, side effects, and deep nesting.
- Make the happy path visible.

### 8.5 Objects and modules

- High cohesion, low coupling.
- Hide internal detail.
- Move behavior to the owner of the knowledge.

### 8.6 Error handling

- Be explicit.
- Fail loudly enough to be diagnosable.
- Do not bury failures.

### 8.7 Tests

- Protect behavior.
- Keep tests readable.
- Avoid brittle tests that forbid healthy refactoring.

### 8.8 Refactoring

- Small safe steps.
- Preserve behavior.
- Improve code near the change.

### 8.9 Legacy

- Create seams.
- Add characterization tests.
- Reduce risk before ambition.

### 8.10 APIs

- Hard to misuse.
- Easy to read.
- Easy to evolve.

### 8.11 Domain

- Let the code speak the business language where domain complexity matters.

### 8.12 Team reality

- Conventions that the team can keep are better than ideals nobody will follow.

---

## 9. Conflict resolution matrix

### Case 1: Small methods vs readable narrative flow

- Prefer readable narrative flow.
- Split only when extraction clarifies intent, reuse, or testability.

### Case 2: DRY vs premature abstraction

- Prefer minor duplication over premature abstraction.
- Abstract only duplicated knowledge with stable meaning.

### Case 3: YAGNI vs foreseeable extension

- Do not build generic infrastructure for hypothetical futures.
- Do leave obvious, cheap, non-invasive room for the next committed requirement.

### Case 4: Purity vs delivery

- Correctness and maintainability win.
- Purity is optional unless impurity creates real damage.

### Case 5: Domain model purity vs framework productivity

- Keep core business rules explicit and testable.
- Accept framework ergonomics at the edges.

### Case 6: Defensive checks vs noisy code

- Keep checks where they protect real invariants or trust boundaries.
- Do not repeat defensive clutter inside already trusted internal flows.

### Case 7: Performance vs readability

- Default to readability.
- Switch only when profiling or system constraints justify it.

### Case 8: Local style guide vs imported author preference

- The codebase convention wins unless it is actively harmful.

---

## 10. AI governance rules

This section is operational. Use it as a direct instruction set for coding agents.

### 10.1 AI MUST do the following

- Preserve existing behavior unless a behavior change is explicitly requested.
- Prefer the smallest correct change that improves clarity.
- Match the host project's conventions.
- Use names that reflect intent and domain.
- Keep public APIs stable unless change is explicitly requested.
- Explain tradeoffs when a rule conflict exists.
- Reduce cognitive load, not just line count.
- Avoid speculative abstractions.
- Prefer explicit error handling.
- Add or update tests when risk justifies it.
- Flag uncertainty honestly.

### 10.2 AI MUST NOT do the following

- invent abstractions without evidence
- refactor unrelated areas in the same patch without strong justification
- introduce magic or indirection just to appear "senior"
- change names to more abstract names when concrete names are clearer
- replace readable code with pattern theater
- optimize without evidence
- use comments as a bandage for bad structure
- silently change observable behavior

### 10.3 AI SHOULD default to this change sequence

1. understand the requirement
2. locate the true owner of the behavior
3. identify invariants and side effects
4. add or preserve safety checks
5. implement the smallest correct change
6. refactor locally if it clearly improves maintainability
7. verify with tests or reasoning
8. report tradeoffs and residual risks

---

## 11. AI review checklist

Use this for PR reviews, self-reviews, and codegen audits.

### 11.1 Correctness

- Is the code correct on the happy path?
- Are error paths intentional?
- Were invariants preserved?

### 11.2 Readability

- Does the code explain itself through names and structure?
- Is the main flow visible?
- Is any part clever without need?

### 11.3 Design

- Is responsibility placed in the right owner?
- Is coupling reasonable?
- Is any abstraction premature?

### 11.4 Maintainability

- Will the next change be easier or harder?
- Is there duplication of knowledge?
- Is hidden complexity accumulating?

### 11.5 API quality

- Is misuse difficult?
- Are contracts clear?
- Is mutability controlled?

### 11.6 Test quality

- Do tests verify behavior rather than internals?
- Are edge cases covered proportionally to risk?
- Will the tests still be useful after refactoring?

### 11.7 Operational risk

- Any security impact?
- Any performance risk?
- Any migration or compatibility risk?

---

## 12. Severity model for violations

### Level 0 - acceptable variation

Different style, still clear and safe.

### Level 1 - minor smell

Readable today, but likely to age poorly.

### Level 2 - meaningful maintainability issue

Naming, cohesion, duplication, coupling, or test weakness that will slow future work.

### Level 3 - design hazard

High coupling, misleading abstractions, risky side effects, or unclear invariants.

### Level 4 - correctness or safety risk

Bug risk, security risk, data loss risk, undefined behavior, or impossible-to-reason-about behavior.

Review severity should match this scale. Do not use "clean code" language to inflate Level 1 issues into drama.

---

## 13. Non-negotiable bans

Reject code that does any of the following without strong written justification:

- hides critical side effects behind innocent names
- swallows exceptions or errors without intent
- introduces generic frameworks for single concrete use cases
- uses vague names that obscure meaning
- creates giant god classes or junk-drawer modules
- duplicates important business rules in multiple places
- mixes unrelated responsibilities in one unit
- relies on deep object navigation to reach distant knowledge
- uses comments to compensate for confusing structure
- mutates shared state invisibly
- introduces tests that freeze private implementation details without need
- weakens type safety or contracts for convenience
- performs speculative optimization or speculative abstraction

---

## 14. Approved tradeoffs

These are explicitly allowed when justified.

- some duplication to avoid a bad abstraction
- a slightly longer method to preserve local readability
- a simple procedural flow instead of artificial object decomposition
- framework conventions at the edges for delivery speed
- targeted defensive checks at trust boundaries
- temporary adapters around legacy code
- intentionally boring code instead of elegant but surprising code

---

## 15. Suggested default review comments

### Naming

"The name describes the container but not the intent. Rename it so the responsibility is obvious."

### Function shape

"This function appears to do multiple things. Split only if the resulting flow becomes easier to read."

### Abstraction

"This abstraction seems to arrive before the variation exists. Keep it concrete for now."

### Duplication

"This looks like duplicated knowledge, not just repeated syntax. Consider a single authoritative source."

### Error handling

"The failure path is not explicit enough. Make the contract and outcome obvious."

### Domain clarity

"This reads like infrastructure code, but it is actually business logic. Pull the domain meaning to the surface."

### Legacy safety

"Before changing this behavior, add a characterization test or create a seam."

### API design

"Make the safe path the easy path. Tighten the contract so misuse is harder."

---

## 16. Practical heuristics

When you are unsure, apply these tests.

### 16.1 The newcomer test

Could a competent engineer understand this quickly?

### 16.2 The change test

Would the next requirement be easier or harder after this patch?

### 16.3 The blast-radius test

If this breaks, how expensive is the failure?

### 16.4 The honesty test

Do the names and structure truthfully describe what the code does?

### 16.5 The boredom test

Is the code boring in the good way?
Boring code is often excellent production code.

---

## 17. Governance profile presets

## 17.1 Conservative production profile

Use when stability matters more than novelty.

- prioritize correctness
- minimize abstraction churn
- require explicit errors
- prefer stable APIs
- make refactors incremental

## 17.2 Library profile

Use when misuse cost is high.

- prioritize contracts
- prioritize immutability
- minimize public surface
- prefer composition
- document guarantees and failure modes

## 17.3 Legacy rescue profile

Use when the code is fragile.

- add characterization tests
- create seams
- reduce blast radius
- avoid broad rewrites
- improve incrementally

## 17.4 Rapid product iteration profile

Use when speed matters but chaos must stay controlled.

- keep design simple
- apply YAGNI aggressively
- avoid speculative extensibility
- protect domain-critical logic with tests
- accept local duplication when abstraction would slow delivery

---

## 18. What this document rejects

This governance rejects:

- aesthetic absolutism
- pattern theater
- cargo-cult SOLID
- pseudo-enterprise abstraction
- false DRY
- purity without payoff
- code golf
- framework idolatry
- "smart" code that hides behavior
- PRs that optimize style while leaving risk untouched

---

## 19. Minimum acceptance bar for AI-generated code

AI-generated code is acceptable only if it is:

1. correct
2. readable
3. locally consistent with the project
4. explicit about contracts and errors
5. free of speculative abstraction
6. safe to modify
7. proportionately tested or verifiable

If it fails these, reject it even if it looks polished.

---

## 20. Drop-in master prompt for coding agents

```md
You are operating under CleanCodeBible governance.

Follow this priority order:

1. correctness and safety
2. project conventions and architecture
3. domain clarity
4. readability and maintainability
5. testability
6. simplicity
7. performance
8. stylistic preference

Rules:

- Preserve behavior unless behavior change is explicitly requested.
- Prefer the smallest correct change.
- Use names that reveal intent and domain meaning.
- Avoid speculative abstractions, fake genericity, and pattern theater.
- Prefer explicit contracts, explicit errors, and obvious control flow.
- Keep responsibilities cohesive and public APIs small.
- Prefer minor duplication over wrong abstraction.
- Refactor in small safe steps.
- For legacy code, create seams and characterization tests before risky change.
- Match the host codebase style and idioms.
- Be honest about tradeoffs and uncertainty.
- Do not optimize without evidence.
- Do not hide side effects.
- Do not use comments to excuse confusing structure.
- Tests should protect behavior, not freeze internals.

When rules conflict:

- favor correctness over purity
- favor readability over cleverness
- favor domain clarity over framework cleverness
- favor maintainability over local elegance
- favor concrete code over speculative generalization

In your output:

- state key tradeoffs briefly
- mention any unresolved risk
- keep the solution boring, explicit, and production-friendly
```

---

## 21. Source canon and provenance

This document synthesizes guidance from the following major schools and official ecosystem standards.

### Major author schools

- Robert C. Martin
- Martin Fowler
- Kent Beck
- Michael Feathers
- Steve McConnell
- Andrew Hunt and David Thomas
- Joshua Bloch
- Sandi Metz
- Eric Evans
- Bertrand Meyer
- Ron Jeffries
- Jeff Bay
- Tim Peters
- Karl Lieberherr / Ian Holland tradition for the Law of Demeter

### Ecosystem standards

- PHP-FIG PSRs, especially PSR-12
- PEP 8 and PEP 20
- Effective Go
- Go Code Review Comments
- Google style guides
- C++ Core Guidelines

### Official or primary references consulted

1. Martin Fowler, *Refactoring* and related refactoring material at martinfowler.com
2. Martin Fowler, *Code Smell* and *Beck Design Rules* references
3. Kent Beck, *Implementation Patterns* and *Extreme Programming Explained*
4. Michael Feathers, *Working Effectively with Legacy Code*
5. Steve McConnell, *Code Complete* and related author materials
6. Andrew Hunt and David Thomas, *The Pragmatic Programmer*
7. Joshua Bloch, *Effective Java*
8. Eric Evans, *Domain-Driven Design*
9. Sandi Metz rule summaries and talks
10. Bertrand Meyer, Design by Contract references
11. Northeastern Demeter project references for the Law of Demeter
12. Ron Jeffries writings on YAGNI
13. Jeff Bay, Object Calisthenics
14. PHP-FIG PSR documentation
15. Python PEP documentation
16. Go official documentation and code review guidance
17. Google official style guides
18. C++ Core Guidelines official repository and published guidelines

---

## 22. Final position

If you remember only one sentence from this entire document, remember this:

**Clean code is not code that looks morally pure. Clean code is code that is honest, readable, safe, testable, and cheap
to change.**

That is the real standard.
