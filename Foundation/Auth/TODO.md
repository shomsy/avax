# TODO: Avax Auth Refactor

Canonical active implementation queue for the architectural upgrade.

## Entries

- [ ] Zelim sledece:

    Da li mozes ostatak iz refactor.md da implementiras, dopunis, prosiris na max da bude eneterprise grade kvalitet, na zavidnom nivou!?

    Ono sto smatras da treba da bude izvan Auth komponente, slobodno napravi u components/Foundation tamo gde treba da stoji, ili napravi novi folder, smisleno. Isto tako zelim da ovde implementiras sve sto vec postoji u components/Foundation, umesto da se ponavljamo. 

    Takodje, zelim da se pozabavis OOP disciplinama, i to decoupling-om. Jer ne volim velike i nejasne klase, prenatrpane. Dakle, iz tog razloga.
    Ali i dalje treba da postujes pravila koja su u REFAKTOR.md u ENTERPRISE GRADE STANDARDS sekciji.


    Table of Contents
Acknowledgements....................................................................................................................................6
Preface........................................................................................................................................................7
P a rt I. P u t t in g t h e Do m a in Mo d e l t o Wo rk ................................ 1 2
1. Crunching Knowledge ..................................................................................................................15
2. Communication and the Use of Language ..................................................................................24
UBIQUITOUS LANGUAGE............................................................................................................25
Documents and Diagrams .........................................................................................................31
3. Binding Model and Implementation............................................................................................37
MODEL-DRIVEN DESIGN............................................................................................................38
Letting the Bones Show; Why Models Matter to Users..................................................................45
HANDS-ON MODELERS ..............................................................................................................47
P a rt II. Bu ild in g Blo c k s o f a Mo d e l-Drive n De s ig n ..................... 4 9
4. Isolating the Domain.....................................................................................................................51
LAYERED ARCHITECTURE..........................................................................................................52
The Domain Layer is Where the Model Lives..........................................................................56
SMART UI ANTI-PATTERN .........................................................................................................57
5. A Model Expressed in Software...................................................................................................60
Associations..............................................................................................................................61
ENTITIES (AKA REFERENCE OBJECTS)........................................................................................65
VALUE OBJECTS.........................................................................................................................70
SERVICES ...................................................................................................................................75
MODULES (AKA PACKAGES) ......................................................................................................79
Modeling Paradigms.......................................................................................................................84
6. The Lifecycle of a Domain Object ...............................................................................................88
AGGREGATES.............................................................................................................................89
FACTORIES.................................................................................................................................98
REPOSITORIES ..........................................................................................................................106
Designing Objects for Relational Databases...........................................................................113
7. Using the Language in an Example: A Cargo Shipping System.............................................115
P a rt III. Re fa c t o rin g To w a rd De e p e r In s ig h t .......................... 1 3 3
8. Breakthrough ..............................................................................................................................136
9. Making Implicit Concepts Explicit............................................................................................145
Listen to Language ..................................................................................................................145
Scrutinize Awkwardness.........................................................................................................148
Contemplate Contradictions....................................................................................................152
Read the Book.........................................................................................................................153
Try, Try Again ........................................................................................................................154
Expressing Less Obvious Categories of Concepts........................................................................155
Explicit Constraints.................................................................................................................155
Representing Processes as Domain Objects............................................................................157
SPECIFICATION.........................................................................................................................158
10. Supple Design ..............................................................................................................................170
INTENTION REVEALING INTERFACES .......................................................................................172
SIDE-EFFECT-FREE FUNCTIONS ...............................................................................................175
ASSERTIONS.............................................................................................................................179
CONCEPTUAL CONTOURS.........................................................................................................183
STANDALONE CLASSES............................................................................................................188
CLOSURE OF OPERATIONS........................................................................................................190
(Final Manuscript, April 15, 2003) © Eric Evans, 2003 4
Declarative Design ..................................................................................................................192
Extending SPECIFICATIONS in a Declarative Style ..................................................................193
Carve off Subdomains...................................................................................................................200
Draw on Established Formalisms, When You Can.......................................................................200
Example Integrating the Patterns: Shares Math ............................................................................200
11. Applying Analysis Patterns........................................................................................................207
12. Relating Design Patterns to the Model......................................................................................218
STRATEGY AKA POLICY ...........................................................................................................219
COMPOSITE ..............................................................................................................................223
Why Not FLYWEIGHT?..................................................................................................................229
13. Bringing the Pieces Together .....................................................................................................230
P a rt IV. S t r a t e g ic De s ig n ........................................................ 2 3 3
14. Maintaining Model Integrity......................................................................................................235
BOUNDED CONTEXT ................................................................................................................238
CONTINUOUS INTEGRATION.....................................................................................................242
CONTEXT MAP.........................................................................................................................244
Relationships Between BOUNDED CONTEXTS...............................................................................250
SHARED KERNEL......................................................................................................................251
CUSTOMER/SUPPLIER DEVELOPMENT TEAMS............................................................................252
CONFORMIST............................................................................................................................255
ANTICORRUPTION LAYER ........................................................................................................257
SEPARATE WAYS .....................................................................................................................261
OPEN HOST SERVICE................................................................................................................263
PUBLISHED LANGUAGE............................................................................................................264
Unifying an Elephant ....................................................................................................................266
Choosing Your Model Context Strategy.......................................................................................268
Transformations............................................................................................................................273
Merging CONTEXTS (SEPARATE WAYS SHARED KERNEL).................................................274
Merging CONTEXTS (SHARED KERNEL CONTINUOUS INTEGRATION).................................276
Phasing Out a Legacy System.................................................................................................277
OPEN HOST SERVICE PUBLISHED LANGUAGE .....................................................................278
15. Distillation....................................................................................................................................279
CORE DOMAIN .........................................................................................................................281
GENERIC SUBDOMAINS ............................................................................................................285
DOMAIN VISION STATEMENT...................................................................................................290
HIGHLIGHTED CORE.................................................................................................................292
COHESIVE MECHANISMS..........................................................................................................295
Distilling to a Declarative Style ..............................................................................................297
SEGREGATED CORE..................................................................................................................298
ABSTRACT CORE......................................................................................................................305
Deep Models Distill ......................................................................................................................306
Choosing Refactoring Targets.......................................................................................................306
16. Large-Scale Structure.................................................................................................................307
EVOLVING ORDER....................................................................................................................310
SYSTEM METAPHOR.................................................................................................................312
RESPONSIBILITY LAYERS .........................................................................................................314
KNOWLEDGE LEVEL ................................................................................................................326
PLUGGABLE COMPONENT FRAMEWORK ..................................................................................333
How Restrictive Should a Structure Be?.......................................................................................336
Refactoring Toward a Fitting Structure ........................................................................................337
17. Bringing the Strategy Together .................................................................................................339
Bringing Together Large-Scale Structures and BOUNDED CONTEXTS ..........................................339
(Final Manuscript, April 15, 2003) © Eric Evans, 2003 5
Bringing Together Large-Scale Structures and Distillation ..........................................................342
Assessment First ...........................................................................................................................343
Who Sets the Strategy...................................................................................................................344
P a rt V. Co n c lu s io n s ................................................................... 3 4 9
Epilogues................................................................................................................................................349
Looking Forward...................................................................................................................................352
Glossary..................................................................................................................................................353
References..............................................................................................................................................356
Appendix: Use of Patterns in This Book .............................................................................................358
PATTERN NAME .......................................................................................................................359

I sav taj decoupling treba da prati NAMING convention i arhitekturu i kvalitet opisan ovde:


  ````md

  You are a principal-level software architect and refactoring agent.

  Your task is to redesign and normalize my components. projects, code, anything into a screaming, vertical-slice, feature-first architecture with explicit system flows, shared capabilities, configuration, and foundation lanes.

  This is not a generic clean architecture exercise.
  This is an ownership, naming, and system-shape building and refactor.

  Use the following target architecture as the source of truth:


  # Architecture and Naming Standard

  ## 1. Purpose

  The purpose of this standard is to enforce **highest-quality simplicity**.

  The architecture must be:

  - easy to read
  - easy to explain
  - easy to review
  - easy to extend
  - easy to refactor safely
  - strong under growth and change
  - explicit in ownership
  - resistant to chaos

  This is not a style preference document.
  This is a structural decision framework.

  The system must read like a story of the domain, behavior, and responsibilities.
  It must not read like a warehouse of technical buckets.

  This standard follows:

  - screaming architecture
  - vertical slice architecture
  - feature-first thinking
  - explicit ownership
  - strong locality
  - honest modularity

  ---

  ## 2. Core Architectural Law

  The main architectural law is:

  **folder says flow or capability, unit says responsibility, function says exact action.**

  This is the primary rule.

  ### 2.1 Meaning of "unit"

  The word **unit** is intentionally neutral.

  Depending on language, platform, or system type, a unit may be:

  - a file
  - a class
  - a module
  - a package entry
  - a script
  - a service object
  - a function group
  - a component entry
  - another valid ownership boundary

  The standard must not depend on a specific programming language or framework.

  What matters is not the technical form.
  What matters is:

  - ownership
  - clarity
  - placement
  - meaning

  ---

  ## 3. Primary Reading Model

  The structure must be readable in this order:

  **flow -> feature slice -> unit -> functions**

  This is how the system should reveal itself to a reader.

  The reader should be able to understand the system like this:

  1. What main flow or capability exists here?
  2. What slice am I inside?
  3. What unit owns this slice?
  4. What exact actions happen here?

  Each deeper level must become more precise, not more confusing.

  ---

  ## 4. Architectural Goal

  The target is:

  **extreme simplicity with enterprise-grade quality**

  That means the architecture must stay simple while still being capable of supporting:

  - security
  - scale
  - performance
  - maintainability
  - safe change
  - modular growth
  - real-world operational pressure

  The point is not to look sophisticated.
  The point is to remain simple without collapsing under complexity.

  If something looks smart but reads worse, it failed.
  If something reads simply and survives real-world pressure, it succeeded.

  ---

  ## 5. Repo Root vs System Root

  The architecture must explicitly distinguish between **repo root** and **system root**.

  This distinction is mandatory.

  ### 5.1 Repo Root

  The **repo root** is the operational root of the repository.

  It describes how the repository is organized as a working container.

  It may contain:

  - system root
  - tests
  - docs
  - examples
  - tooling
  - build files
  - package metadata
  - CI/CD files
  - governance files
  - workspace files
  - developer workflow files

  Example:

  ```text
  Project/
    src/
    tests/
    docs/
    examples/
    tooling/
    README
    AGENTS
    package metadata
  ````

The repo root does **not** have to scream domain behavior.

Its job is to separate:

* production structure
* tests
* documentation
* examples
* tooling
* metadata
* operational files

### 5.2 System Root

The **system root** is the canonical root of the actual system structure.

Depending on the context, ecosystem, product model, or repository shape, the system root may be:

* `src/`
* `product/`
* `system/`
* `app/`
* `engine/`
* another clearly justified root

The name is less important than the role.

What matters is that the system root is the place where the real architectural law starts to apply.

Inside the system root, the structure must scream.

That is where the architecture must express:

* flow
* capabilities
* ownership
* exact responsibilities

In other words:

* **repo root says how the repository is organized**
* **system root says how the system is organized**

### 5.3 Rule of Preference

A project may use `src/` as the system root, but it is not required.

If another name better expresses the real product boundary, system boundary, or domain shape, that name is preferred.

The standard cares about:

* structural meaning
* ownership
* clarity
* predictability

It does **not** care about loyalty to a particular folder name.

  ---

## 6. When a Separate System Root Is Allowed

A dedicated system root is allowed when it has a real job.

It is justified when it:

* separates production structure from tests, docs, examples, and tooling
* improves package or publish discipline
* clarifies build or distribution boundaries
* matches a strong ecosystem convention
* reduces root-level noise
* makes the repository easier to navigate honestly

A separate system root is **not** justified when it only adds a generic hallway.

A folder like `src/`, `product/`, or `system/` must never exist just to make the tree look cleaner.
It must make the structure **meaningfully** clearer.

  ---

## 7. System Root Taxonomy

Inside the system root, only the following root slice categories are allowed by default:

1. Flow slices
2. Capability slices
3. Configuration slices
4. Foundation slices
5. A small number of stable public surface units when needed

If a root-level slice cannot honestly fit one of these categories, it should not exist there.

  ---

## 8. Flow Slices

### 8.1 Definition

Flow slices describe end-to-end system behavior.

They answer:

**What does the system do?**

Examples:

* Login
* Register
* Checkout
* CreateInvoice
* ProcessRefund
* ChangePassword
* ReadCurrentUser
* PublishArticle
* SyncCatalog

### 8.2 Role

Flow slices are the primary narrative of the system.

They should express:

* business movement
* user-facing behavior
* use-case completion
* action-oriented domain intent

### 8.3 Owner Rule

Every flow slice must have one obvious root owner unit.

That unit may be:

* a pipeline
* a facade
* an orchestrator
* a root command handler
* a root action entry
* another clearly justified owning entry point

If the flow is sequential, the root owner should gather the sequence.

If the flow is not sequential, the root owner should still make ownership of the slice obvious.

### 8.4 Locality Rule

A flow-local concern must stay in its flow until there is strong proof it belongs elsewhere.

Examples:

* login rate limiting belongs in login until proven broader
* registration validation belongs in registration
* refund-specific calculations belong in refund
* order-specific reconciliation belongs in order processing

Do not globalize a concern too early.

  ---

## 9. Capability Slices

### 9.1 Definition

Capability slices describe shared abilities, boundaries, mechanisms, or reusable domain-level enablers that support multiple flows.

They answer:

**What does the system use to make flows work?**

Examples:

* Access
* Identity
* Payments
* Notifications
* Search
* UserSource
* PasswordHashing
* Messaging
* Storage
* Routing

### 9.2 Role

Capability slices are not generic buckets.
They are shared system abilities with honest cross-flow ownership.

They may contain:

* cross-flow policies
* shared domain mechanisms
* stable boundaries
* reusable domain infrastructure
* system-wide operational abilities

### 9.3 Shared Last Rule

A capability slice exists only when the concern is truly shared.

Something may become a capability only when:

* it genuinely belongs to more than one flow
* keeping it local would become dishonest
* duplication is structural, not incidental
* extraction improves clarity, not speculative reuse

Shared is not the default.
Shared is the last responsible option.

### 9.4 Anti-Junk Rule

A capability slice must never become a junk drawer.

If a folder is merely collecting technical leftovers, it is not a capability.
It is a failure of ownership.

  ---

## 10. Configuration Slices

### 10.1 Definition

Configuration slices describe assembly, composition, setup, bootstrapping, or wiring.

They answer:

**How is the system assembled?**

Examples:

* Configuration
* Composition
* Bootstrap
* Wiring

### 10.2 Role

Configuration slices may contain:

* assembly entry points
* wiring rules
* dependency construction
* runtime composition
* bootstrapping policies
* composition boundaries

They must not absorb business behavior that belongs to flows or capabilities.

Configuration exists to assemble the system, not to become the system.

  ---

## 11. Foundation Slices

### 11.1 Definition

Foundation slices contain small, neutral, boring, low-noise primitives that do not deserve their own capability slice.

They answer:

**What stable primitives does the system stand on?**

Examples:

  ```text
  Foundation/
    Time/
      Clock
    Ids/
      IdGenerator
  ```

### 11.2 Role

Foundation is for:

* tiny primitives
* stable neutral building blocks
* narrow low-level helpers with clear ownership
* cross-system technical atoms that are too small for a capability slice

### 11.3 Strict Rule

Foundation must never become a disguised helper bucket.

It is **not** for:

* random utilities
* generic helpers
* domain logic
* cross-cutting dumping grounds
* loosely related functions
* speculative reuse

If something has real domain meaning, real policy meaning, or real cross-flow significance, it likely belongs in a capability or flow, not in Foundation.

  ---

## 12. Public Surface Units

The system root may contain a **small number of stable public surface units** if the project or package requires them.

Examples:

* package root entry
* public API entry
* facade entry
* main exported interface
* root public contract
* index entry

These units are allowed only when they represent intentional public surface.

They must remain:

* small
* stable
* explicit
* easy to understand
* separate from internal machinery

They must never become dumping grounds for unrelated logic.

  ---

## 13. Ownership Standard

Everything must have an owner.

Ownership must be visible from:

* location
* naming
* slice placement
* relationship to neighboring units

If a reader cannot tell who owns a responsibility, the architecture is unfinished.

### 13.1 Ownership Questions

Every folder and unit must answer:

1. Who owns this?
2. Why is it here?
3. Why is it not owned more honestly elsewhere?
4. What broader slice does it belong to?
5. What responsibility would break if this moved?

If the answer is weak, the placement is weak.

  ---

## 14. Hierarchy Rules

Subfolders are allowed only when they improve clarity.

A deeper structure is justified only when it:

* reflects a real subflow
* reflects a real sub-capability
* reduces noise
* improves scanning
* protects ownership
* avoids oversized flat structures

A deeper structure is not justified when it:

* hides weak naming
* creates cosmetic nesting
* introduces hallway folders
* duplicates a concept already expressed elsewhere
* exists only because the author felt the tree looked nicer

Every extra level must justify itself.

If the tree becomes deeper but not clearer, the tree got worse.

  ---

## 15. Locality Before Reuse

This standard prefers **local truth before shared abstraction**.

That means:

* keep things close to their most honest owner
* duplicate a small amount before extracting prematurely
* extract only when the extracted thing becomes clearer than the duplication
* do not centralize because something "might be reused later"

Premature shared structure creates fake clarity.
Real clarity comes from honest ownership.

  ---

## 16. Language-Agnostic and Project-Agnostic Rule

This standard must remain valid across:

* backend services
* frontend applications
* libraries
* packages
* SDKs
* plugins
* CLI tools
* monoliths
* modular systems
* microservices
* data pipelines
* event-driven systems
* workflow engines
* product repositories
* platform repositories

The standard must not depend on language-specific doctrine.

That is why it uses neutral terms such as:

* unit
* slice
* entry point
* flow
* capability
* repo root
* system root

Implementation technologies may vary.
Architectural meaning must remain stable.

  ---

## 17. Ecosystem Rule

This standard must be strong, but not blind.

If a language, framework, runtime, or ecosystem has a strong and legitimate convention, it may be respected **only if** it does not damage:

* ownership
* clarity
* screaming readability
* structural honesty
* mental load

Conventions are not automatically correct.
Custom structure is not automatically superior.

The rule is:

**prefer the shape that reduces noise and makes ownership clearer.**

  ---

## 18. Design Quality Constraints

The architecture must support strong engineering discipline.

This includes:

* SOLID
* DRY
* YAGNI
* KISS
* Composition Over Inheritance
* Law of Demeter
* Clean Code
* strong cohesion
* low coupling
* narrow interfaces
* explicit boundaries
* maintainable low-level design
* safe extension points
* honest modularity

These are design constraints, not excuses for complexity.

Good architecture remains simple while satisfying them.

  ---

## 19. System Quality Constraints

The architecture must also be capable of supporting:

* security by design
* clear authentication and authorization boundaries
* secure API boundaries
* data protection
* vulnerability awareness
* scalability
* flexibility
* interoperability
* cost efficiency
* observability where needed
* performance awareness
* cache where justified
* rate limiting where owned
* consistency awareness
* latency versus throughput tradeoff awareness
* operational clarity

These concerns must live where they are most honestly owned.

Examples:

* rate limiting belongs near the boundary or flow that owns it
* identity rules belong near identity or access capability
* composition belongs in configuration
* primitives do not belong inside business flows unless they are truly local
* business policies do not belong in foundation

  ---

## 20. Forbidden Structural Patterns

The following structural failures must be avoided:

* tight coupling
* ownership ambiguity
* fake abstraction
* insufficient abstraction
* over-engineering
* premature centralization
* parallel names for the same concept
* duplicated capabilities
* hierarchy without value
* technical junk drawers
* extraction without proof
* bucket folders without domain meaning
* hallway folders with no semantic value

  ---

## 21. Forbidden Generic Names

The following names are forbidden as default architectural buckets:

* Services
* Helpers
* Utils
* Common
* Misc
* Managers
* Stuff
* Shared
* Base
* Core
* SharedThings
* General
* InternalHelpers

These names are weak because they hide responsibility instead of clarifying it.

They may exist only if they describe a truly precise and justified architectural concept.
In practice, most of the time they should be rejected.

  ---

## 22. Naming Standard

Naming must be:

* simple
* banal
* intuitive
* predictive
* descriptive
* child-explainable

A name must make it obvious, even before opening the code:

* what this is
* why it exists
* when it is used
* what it owns
* what it does

### 22.1 Naming Law

* **folder says flow or capability**
* **unit says responsibility**
* **function says exact action**

### 22.2 Preferred Style

Prefer names that speak in the language of:

* the domain
* the system behavior
* the user or business flow
* the real responsibility

Good examples:

* Login
* Register
* ChangePassword
* RequirePermission
* ReadCurrentUser
* PasswordHashing
* Identity
* Clock
* IdGenerator
* CreateInvoice
* ProcessRefund
* AccessPolicy

Bad examples:

* ServiceManager
* CommonUtils
* SharedService
* CoreStuff
* DataHelpers
* BaseHandler
* MiscFunctions
* GenericProcessor

### 22.3 One Concept, One Name

A concept must have one name across the system.

Do not mix different names for the same concept.

Bad examples:

* RequireAuthentication and EnforceAuthentication
* CurrentUser and ReadCurrentUser
* UserLogin and Login
* BruteForceProtection and LoginRateLimit when they mean the same thing

If two names describe the same concept, choose one and delete the other.

  ---

## 23. Flow vs Capability Clarification

A flow slice is not the same as a capability slice.

A flow says:

* what happens
* what sequence is executed
* what action is performed
* what business movement occurs

A capability says:

* what the system uses repeatedly
* what shared boundary supports multiple flows
* what reusable mechanism or ability exists outside one single use case

Simple rule:

* **Flows say what the system does**
* **Capabilities say what the system uses to make that work**
* **Configuration says how the system is assembled**
* **Foundation says what tiny neutral primitives support the base**

  ---

## 24. Review Rule

Any proposed folder, extraction, rename, new root slice, or shared abstraction must answer these questions clearly:

1. What does this folder say?
2. What does this unit own?
3. Why is this not owned more honestly by a lower level?
4. Does this reduce noise or only move it?
5. Is this a real capability, or just a technical bucket?
6. Is this name obvious without opening the code?
7. Does this create parallel naming for the same concept?
8. Does this make the reading path clearer?
9. Is this local truth or speculative reuse?
10. Would a new team member understand this quickly?

If the answers are weak, the change is weak.

  ---

## 25. Decision Framework for Placement

When deciding where something belongs, apply this order:

### Step 1

Ask whether it belongs to **one flow only**.

If yes, keep it inside that flow.

### Step 2

Ask whether it is a **real shared ability or boundary** across flows.

If yes, consider a capability slice.

### Step 3

Ask whether it is only about **assembly or wiring**.

If yes, place it in configuration.

### Step 4

Ask whether it is only a **tiny neutral primitive**.

If yes, place it in foundation.

### Step 5

If none of the above feels honest, the structure is still wrong.
Re-think the model instead of creating a generic bucket.

  ---

## 26. Recommended Canonical Shape

This is a recommended pattern, not a blind template.

  ```text
  Project/
    <system-root>/
      Public surface units if needed
      Flow/
      Capabilities/
      Configuration/
      Foundation/
    tests/
    docs/
    examples/
    tooling/
    package metadata
    README
    AGENTS
  ```

Where `<system-root>` may be:

* `src/`
* `product/`
* `System/`
* `app/`
* another clearly justified root

Example:

  ```text
  Project/
    src/
      Auth
      AuthInterface

      Flows/
        Login/
        Register/
        ChangePassword/
        ReadCurrentUser/

      Capabilities/
        Access/
        Identity/
        User/
        UserSource/
        PasswordHashing/

      Configuration/
      Foundation/

    tests/
    docs/
    examples/
    tooling/
  ```

This is a strong default, not an unquestionable dogma.

The shape may adapt to context, but the laws of:

* ownership
* clarity
* screaming readability
* locality
* honest abstraction

must remain unchanged.

  ---

## 27. Final Goal

The final goal of this standard is:

**highest-quality simplicity**

The architecture must be simple enough to:

* read quickly
* explain quickly
* review honestly
* extend safely
* refactor with confidence

And strong enough to:

* survive growth
* survive change
* stay modular
* stay secure
* stay readable
* stay maintainable under real pressure

If a structure looks impressive but reads worse, it failed.
If a structure looks simple and remains strong under pressure, it succeeded.

  ```





---
Updated: 2026-04-06 14:50 CET
