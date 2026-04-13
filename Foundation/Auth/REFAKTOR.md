Napravi ovo da bude na enterprise grade, world class nivou, da bude u visini kvaliteta poznatih framework-a, programskih jezika, biblioteka. Da nam svi oni zavide zapravo. Za sam kvalitet koda i arhitekture pogledaj dole STANDARDI I KVALITET KOJI ZAHTEVAM sekciju i pridrzavaj se striktno pravila agents.md odnosno .agents harness-a.

Evo **tačnog TODO-a za “baš perfect auth system”**.

> Status audit: 2026-04-13
>
> Legenda:
> - `[x]` implementirano u repo-u
> - `[~]` delimično implementirano ili zatvoreno kroz docs/boundary, bez punog runtime-a
> - `[ ]` nije implementirano

## P0 — zatvori sve što je ostalo do “perfect auth kernel”

### 1) Browser/session deployment hardening

* [x] Napiši **cookie-auth deployment guide** za web:

  * `Secure`, `HttpOnly`, `SameSite`
  * CSRF model po surface-u
  * origin/referer pravila
  * session fixation test proceduru
* [x] Dodaj **reference middleware primer** za CSRF zaštitu
* [x] Dodaj **persistent session registry primer**:

  * DB schema
  * revoke one
  * logout-all
  * revoke on password reset
  * revoke on factor reset
* [x] Dodaj **session compromise runbook**
* [x] Dodaj **browser storage policy**: zabrani dugotrajne tokene u browser storage-u za session-based surface

Ovo je must-have jer OWASP traži eksplicitan CSRF posture za browser-attached credentials, realnu invalidaciju sesije i stroge cookie kontrole, ne samo “login radi”.   ([OWASP Cheat Sheet Series][1])

### 2) Sender-constrained tokeni moraju biti stvarno operativni, ne samo metadata

* [x] Implementiraj **DPoP proof verification adapter**
* [x] Implementiraj **mTLS-bound token verification adapter**
* [x] Dodaj testove za:

  * `[x]` replay sa istim proof-om
  * `[x]` proof/token mismatch
  * `[x]` wrong `htu` / `htm`
  * `[x]` key rotation
* [x] Dodaj policy switch:

  * `[x]` standard bearer
  * `[x]` DPoP required
  * `[x]` mTLS required
* [x] Dodaj audit event za proof failure i sender-constraint mismatch

Tvoj kernel već ima sender-constraint metadata i high-security posture, ali za “perfect” moraš zatvoriti i punu proof verifikaciju. DPoP je standard baš za sender-constraining i replay detekciju, a OAuth BCP tretira sender-constrained tokene kao jači posture od običnih bearer tokena.   ([IETF Datatracker][2])

### 3) Audit/export mora biti SIEM-grade

* [x] Dodaj **rich audit exporter adaptere**:

  * `[x]` JSON lines
  * `[x]` syslog
  * `[x]` webhook
  * `[x]` queue export
* [x] Dodaj **security notification adaptere**:

  * `[x]` refresh reuse detected
  * `[x]` admin elevation
  * `[x]` factor removed
  * `[x]` password changed after suspicious activity
* [x] Dodaj **correlation-id propagation** kroz sve auth flow-ove
* [x] Dodaj **tamper-evident export strategy**
* [x] Dodaj **PII masking verification tests**

Logging i audit nisu finiš bez operativnog izvoza i discipline oko osetljivih podataka. OWASP logging smernice i tvoj privacy policy idu baš u tom pravcu.   ([OWASP Cheat Sheet Series][3])

### 4) Federation operativa

* [x] Implementiraj **domain verification**
* [x] Implementiraj **federation metadata sync**
* [x] Dodaj **stronger tenant connection policy**
* [x] Dodaj **connection health checks**
* [x] Dodaj **group-to-role mapping validator**
* [x] Dodaj **tenant isolation tests** za connection config
* [x] Dodaj **break-glass bypass policy** kad je IdP nedostupan
* [x] Dodaj **SSO cutover runbook**

Roadmap ti već navodi domain verification, metadata sync i jači tenant connection policy kao naredni sprint. Bez toga federation postoji, ali nije “baš perfect”. 

### 5) High-assurance admin posture examples

* [x] Dodaj gotov primer za **admin passkey-required policy**
* [x] Dodaj gotov primer za **tenant-admin phishing-resistant policy**
* [x] Dodaj primer za **fresh-MFA / fresh-passkey enforcement**
* [x] Dodaj primer za **approval path** za high-impact admin akcije
* [x] Dodaj primer za **SoD** pravilo u policy engine-u

Tvoj assurance matrix već kaže da admin i tenant-admin moraju imati phishing-resistant faktor; za AAL3 NIST traži phishing-resistant autentikator sa ne-eksportabilnim ključem. Za “perfect” treba da ovo bude ne samo dokumentovano nego i isporučeno kao gotov policy paket.  ([NIST Publications][4])

## P1 — zatvori otvorene accepted-risk stavke

### 6) Trusted device / remembered device

* [x] Odluči da li trusted-device **ulazi** ili se **trajno odbija**  
  Odluka: funkcija je eksplicitno odbijena u ovoj iteraciji.
* [ ] Ako ulazi:

  * device token model
  * device binding
  * revocation
  * per-device audit
  * suspicious device re-challenge
* [x] Ako ne ulazi:

  * `[x]` napiši explicit policy da je to svesno odbijena funkcija zbog sigurnosti
  * `[x]` ukloni sve future ambiguity iz UX-a i docs-a

Trenutno je trusted-device podrška eksplicitno “accepted risk / not yet shipped”. Za “perfect” to mora biti zatvoreno na jedan ili drugi način. 

### 7) Complete persistence-contract examples

* [x] Dodaj pune DB primere za:

  * `[x]` `SessionRegistry`
  * `[x]` refresh token family store
  * `[x]` token revocation store
  * `[x]` MFA challenge store
  * `[x]` audit export cursor store
* [x] Dodaj migration primere
* [x] Dodaj locking/concurrency scenarije za refresh reuse detection
* [x] Dodaj cleanup jobs i TTL primere

Ovo nije glamurozno, ali bez toga timovi lako pogrešno implementiraju sigurnosni model i “pokvare” kernel na adapter sloju.  

## P2 — ako hoćeš “perfect identity platform”, ne samo perfect kernel

### 8) OIDC provider behavior

* [x] Implementiraj kernel-local OIDC provider lane

  * `[x]` discovery
  * `[x]` JWKS
  * `[x]` id token issuance
  * `[x]` userinfo
  * `[x]` RP metadata posture
* [x] Dodaj conformance test matrix
* [x] Dodaj key rollover/JWKS overlap runbook
* [~] Formal OIDC product surface i certification ostaju zaseban adapter/paket  
  Napomena: kernel runtime, `.well-known` publishing adapter, conformance matrix i rollover runbook su isporučeni; logout i formalna certification staza nisu package-owned.

### 9) Full machine identity lane

* [x] Dodaj **client_credentials / workload identity** runtime
* [x] Inventory svih non-human identities
* [x] Audience/issuer/transport validation
* [x] Rotation posture bez emergency redeploy-a
* [x] Per-service scope boundaries

### 10) SCIM runtime

* [x] Implementiraj SCIM CRUD runtime
* [x] SCIM token auth + rotation
* [x] Group sync
* [x] disable vs suspend semantics
* [x] idempotency + drift detection
* [x] audit za provisioning akcije
* [~] Full SCIM HTTP RFC 7644 surface ostaje zaseban adapter/paket  
  Napomena: kernel runtime, schema metadata, token-authenticated `/Users` CRUD adapter i audit su isporučeni; bulk i potpuna RFC 7644 produktizacija nisu package-owned.

### 11) Tenant/control-plane

* [~] Tenant admin UI/API za:

  * `[x]` SSO config
  * `[x]` SCIM config
  * `[x]` domain verification references
  * `[x]` group mapping
  * `[x]` policy posture
  * `[x]` dedicated HTTP surface
* [x] Approval path za tenant security changes
* [x] Config change audit diff
* [x] safe rollout / rollback po tenantu

Kernel-local tenant security workflow i framework-neutral admin HTTP surface su sada isporučeni. Ono što ostaje van punog scope-a je tenant-admin UI i širi tenant membership/control-plane proizvod. 

## P3 — regulated / top-tier ops sloj

### 12) Crypto & incident rigor

* [x] Uvedi **multi-key verification** u runtime adapteru
* [x] Uvedi **automated key rollover drill**
* [x] Uvedi **forced key compromise drill** kao testiran playbook
* [x] Uvedi **evidence preservation under legal hold**
* [x] Uvedi **crypto agility acceptance tests**

### 13) Supply-chain i release hardening

* [x] Dependency review gate
* [x] SBOM generisanje
* [x] artifact signing
* [x] secret scanning u CI
* [x] release provenance
* [x] rollback dokaz za auth-sensitive release

### 14) Verification matrix za final perfection

* [x] login success/failure
* [x] MFA replay
* [x] passkey replay
* [x] refresh reuse
* [x] DPoP replay
* [x] mTLS mismatch
* [x] CSRF za browser flows
* [x] session fixation
* [x] tenant crossing
* [x] admin elevation abuse
* [x] break-glass auditability
* [x] deprovisioning revokes everything
* [x] legal hold overrides anonymization correctly

Tvoj crypto lifecycle je već dokumentovan, ali “perfect” znači i redovno uvežbane operativne drill-ove i release discipline. OAuth BCP i NIST smernice su tu dobar sever, ali ovo je već deployment-grade rigor, ne samo feature lista.   ([NIST Publications][4])

## Moj iskren prioritetni redosled

**Zatvoreno u kernelu:**

1. CSRF/browser hardening
2. DPoP/mTLS proof verification
3. audit exporters + security notifications
4. domain verification + metadata sync
5. trusted-device decision closure
6. OIDC provider
7. SCIM runtime
8. client_credentials / workload identity
9. tenant control-plane

**Preostalo van kernela:**

1. OIDC logout/certification lane
2. SCIM bulk i potpuna RFC 7644 produktizacija
3. tenant-admin UI i širi membership/billing control-plane proizvod

To je trenutno najkraći put od “baš jak auth kernel” do “kompletna identity platforma”.


[1]: https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html?utm_source=chatgpt.com "Session Management Cheat Sheet"
[2]: https://datatracker.ietf.org/doc/rfc9700/?utm_source=chatgpt.com "RFC 9700 - Best Current Practice for OAuth 2.0 Security"
[3]: https://cheatsheetseries.owasp.org/cheatsheets/Logging_Cheat_Sheet.html?utm_source=chatgpt.com "Logging Cheat Sheet"
[4]: https://nvlpubs.nist.gov/nistpubs/SpecialPublications/NIST.SP.800-63B-4.pdf?utm_source=chatgpt.com "NIST.SP.800-63B-4.pdf"




STANDARDI I KVALITET KOJI ZAHTEVAM:


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
