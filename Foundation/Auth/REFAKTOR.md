Ovo je tačan implementation TODO koji bih stavio u plan/ToDo za PHP enterprise auth.
Nije wishlist, nego redosled rada sa konkretnim modulima, tabelama, endpointima, poslovima u pozadini i security kriterijumima.
Za stvari koje su suvise obimne (koje ne vredi pisati od nule sto preferiram), koristi spoljasnje biblioteke, implementacije, 3rd API ukoliko je potrebno, samo me obavesti o tome da znam.

Arhitektura koju ciljaš je:

web app → server-side session cookie
API / mobile / third-party → OAuth 2.1 style access + refresh model
phishing-resistant future path → passkeys / WebAuthn
enterprise path → OIDC / SAML federation + SCIM/JIT provisioning
authorization odvojeno od authentication-a
audit/risk/admin realm kao zasebni podsistemi

To je redosled koji najbolje prati moderne smernice: jake sesije za web, PKCE i sigurniji token model za OAuth klijente, i phishing-resistant autentikacija kao viši cilj.

0. Izaberi pristup i zamrzni scope

Rangirano:

1. Balanced — preporuka
Praviš svoj auth core u PHP-u, ali za OAuth i WebAuthn koristiš proverene biblioteke. league/oauth2-server je standards-compliant OAuth 2.0 authorization server za PHP, a web-auth/webauthn-framework i web-auth/webauthn-lib postoje baš za FIDO2/WebAuthn integraciju u PHP aplikacijama.

2. Fast enterprise
Local accounts + session core zadržiš kod sebe, a SSO/federation nasloniš na spoljnog IdP-a.

3. Full custom identity platform
Radi samo ako ti je identity core product.

TODO

 Napiši 1 ADR: “Auth scope and trust boundaries”
 Odluči: monolith auth module ili zaseban auth service
 Odluči: local accounts only, local + enterprise SSO, ili full IdP ambitions
 Zamrzni v1 scope: password + session + MFA + refresh rotation + audit
 Zamrzni v2 scope: passkeys + federation + SCIM + risk engine
1. Napravi repo/module granice

Moduli koje treba da imaš:

identity
authentication
session
token
mfa
passkeys
authorization
audit
risk
admin-realm
federation
provisioning

TODO

 Napravi module namespaces i ownership granice
 Svaki modul dobija svoj facade/service entry
 Zabraniti “misc auth helpers” i “global services” bucket
 Definiši public contracts između modula
 Definiši koje stvari su sync request path, a koje idu kroz queue/jobs
2. Threat model i security baseline

Pre koda napiši napadače i failure scenarije.

Threat scenarios

credential stuffing
brute force
session hijack
stolen refresh token
MFA bypass
recovery takeover
admin account takeover
email change takeover
refresh token reuse
insider misuse
tenant isolation bug

TODO

 Napiši threat model dokument
 Mapiraj svaki threat na kontrolu
 Definiši severity matriks
 Definiši incident classes: auth incident, credential incident, session incident, admin incident
 Definiši recovery/rollback za compromise scenario
3. Data model — tabele koje moraš imati

Ovo je osnova. Ne kreći bez ovoga.

3.1 Identity
 users
id
tenant_id
email
email_normalized
email_verified_at
status (active, locked, disabled, pending)
password_hash
password_algo
password_changed_at
last_login_at
created_at
updated_at
 user_aliases
za future username/login aliases
 tenants
id, name, slug, status, created_at
3.2 Sessions
 sessions
id
user_id
tenant_id
session_secret_hash
ip_created
user_agent_created
last_seen_at
idle_expires_at
absolute_expires_at
revoked_at
revoke_reason
3.3 OAuth / API tokens
 oauth_clients
 oauth_access_tokens
 oauth_refresh_tokens
 oauth_refresh_token_families
 oauth_authorization_codes

Za refresh tokene čuvaj:

token_id
family_id
parent_token_id
user_id
client_id
issued_at
expires_at
used_at
revoked_at
reuse_detected_at

League OAuth2 Server ima jasan model authorization servera, access token repository-ja i refresh token repository-ja, pa ti može dati dobru bazu za ovaj sloj.

3.4 MFA
 mfa_factors
id
user_id
type (totp, webauthn, backup_code)
label
secret_encrypted
enabled_at
last_used_at
revoked_at
 backup_codes
čuvati hash, nikad plaintext
 mfa_challenges
id
user_id
session_candidate_id
challenge_type
expires_at
verified_at
attempt_count
blocked_until
3.5 Passkeys / WebAuthn
 webauthn_credentials
id
user_id
credential_id
public_key
sign_count
aaguid
transports
backup_eligible
backup_state
attestation_format
last_used_at
revoked_at
3.6 Audit / Risk
 audit_events
 security_events
 risk_signals
 admin_actions
3.7 Federation
 federation_connections
 federated_identities
 scim_tokens
 provisioning_jobs
4. Password subsystem

Passwordi nisu krajnji cilj, ali su baza za migration path. PHP nativno podržava password_hash() i Argon2id gde je dostupan, što je dobar temelj za password sloj.

TODO

 Standardizuj hashing na Argon2id gde je dostupan
 Dodaj fallback policy ako build ne podržava Argon2id
 Na login-u proveravaj da li hash treba rehash
 Password reset uvek invalidira:
sve aktivne web sessione
sve refresh token family-je
sve active login challenges
 Password change traži fresh auth
 Password reset tokeni:
random
single use
hashed in DB
short TTL
auditovani
 Generic failure poruke, bez user enumeration
 Rate limit za:
login
forgot password
reset confirmation

Acceptance criteria

 Stari session više ne važi nakon password reset-a
 Reset link ne može da se iskoristi dvaput
 Promena lozinke radi session/token revocation
5. Web session subsystem

Za browser web aplikacije idi na server-side sessions sa strogim cookie pravilima. OWASP posebno naglašava absolute timeout, session ID regeneration i jake cookie kontrole.

TODO

 Session ID regeneracija odmah nakon uspešnog login-a
 Session cookie:
HttpOnly
Secure
SameSite=Lax ili Strict gde može
 Idle timeout
 Absolute timeout
 Separate remember-me policy ako postoji
 Session revocation endpoint
 “Logout all devices”
 Device/session listing UI
 Last-used metadata po sesiji
 CSRF zaštita za cookie-auth web akcije

API/Methods

 POST /auth/login
 POST /auth/logout
 POST /auth/logout-all
 GET /auth/sessions
 DELETE /auth/sessions/{id}

Acceptance criteria

 Session fixation ne prolazi
 Logout ubija server-side session state
 Idle/absolute timeout stvarno invalidiraju sesiju
 Session list prikazuje sve aktivne sesije korisnika
6. MFA subsystem v1 — TOTP + backup codes

NIST i OWASP tretiraju authenticator lifecycle ozbiljno, ne samo proveru koda.

TODO

 TOTP enroll
 TOTP confirm
 TOTP disable uz fresh auth
 Backup code generation
 Backup code regeneration invalidira stare
 MFA challenge throttling
 Replay protection za challenge token
 Fresh-MFA zahtev za:
password change
email change
factor removal
recovery initiation
admin elevation

API

 POST /auth/mfa/totp/enroll
 POST /auth/mfa/totp/confirm
 POST /auth/mfa/totp/verify
 POST /auth/mfa/backup-codes/regenerate
 DELETE /auth/mfa/factors/{id}

Acceptance criteria

 Nije moguće ukloniti poslednji faktor bez dodatne provere
 Backup code je single-use
 MFA brute force je throttled i auditovan
7. Recovery subsystem

Ovo je critical path. Recovery često ruši inače dobar auth.

TODO

 Recovery flow odvoji od običnog password reset-a
 Recovery traži:
email verification
fresh password knowledge ili existing factor gde postoji
cooldown za promenu kritičnih atributa
 Promena email-a:
traži fresh auth
traži MFA ako je upaljen
odlaže finalizaciju kroz verify link
 Factor reset generiše security event
 Admin-assisted recovery mora imati approval i audit trail

Acceptance criteria

 Email takeover preko pending change flow-a nije moguć bez verifikacije
 Recovery reset invalidira stare recovery artefakte
 Suspicious recovery kreira alert
8. OAuth / API auth subsystem

Za SPA/mobile/public clients koristi Authorization Code + PKCE, a ne implicit flow. OAuth 2.1 i OAuth security BCP guraju moderniji i sigurniji tok, a RFC 9700 dodatno pojačava best practice posture.

TODO

 Uvedi Authorization Server
 Uvedi Resource Server middleware
 Podrži grantove:
Authorization Code + PKCE
Refresh Token
Client Credentials
 Access token kratkog TTL-a
 Refresh token rotation
 Refresh reuse detection
 Family revocation pri reuse detection
 Revocation endpoint
 Introspection endpoint ako treba
 Scope model
 Client registry
 Separate confidential vs public clients

League OAuth2 Server već ima authorization server, resource server i repository interfejse za access i refresh tokene, što ga čini dobrim osnovom za PHP implementaciju.

API

 GET /oauth/authorize
 POST /oauth/token
 POST /oauth/revoke
 POST /oauth/introspect
 GET /oauth/clients
 POST /oauth/clients

Acceptance criteria

 Refresh token ne može biti ponovo korišćen bez family revocation-a
 PKCE obavezan za public clients
 Access token kratak i verifikovan na resource layer-u
9. Passkeys / WebAuthn subsystem

Passkeys su phishing-resistant i bazirani na public-key kriptografiji; FIDO i NIST ih jasno guraju kao jači pravac od lozinki/TOTP-only pristupa.

TODO

 Dodaj WebAuthn registration start
 Dodaj WebAuthn registration finish
 Dodaj authentication start
 Dodaj authentication finish
 Više credentiala po useru
 Credential rename/revoke
 Step-up sa passkey faktorom za privilegovane akcije
 Admin policy:
admins must have phishing-resistant factor
 Device migration UX
 Fallback path za legacy korisnike

Za PHP su relevantne biblioteke web-auth/webauthn-framework i web-auth/webauthn-lib; postoji i metadata service podrška u istom ekosistemu.

API

 POST /auth/passkeys/register/options
 POST /auth/passkeys/register/verify
 POST /auth/passkeys/auth/options
 POST /auth/passkeys/auth/verify
 GET /auth/passkeys
 DELETE /auth/passkeys/{id}

Acceptance criteria

 Origin/RPID validacija stroga
 Credential replay ne prolazi
 Revoked credential više ne autentifikuje korisnika
10. Authorization subsystem

Authn i authz ne smeju biti isti projekat.

TODO

 Uvedi RBAC kao bazu
 Tabele:
roles
permissions
role_permissions
user_roles
tenant_roles
 Claims samo za coarse-grained pristup
 Policy engine za fine-grained odluke
 Resource ownership checks
 Tenant isolation checks
 Fresh auth / fresh MFA kao authz condition, ne hardcoded svuda
 Admin elevation kao poseban state

Acceptance criteria

 Zabranjeno oslanjanje samo na frontend role checks
 Tenant crossing testovi prolaze
 Sensitive actions traže step-up kad policy to traži
11. Federation / Enterprise SSO

Kad dođeš do enterprise kupaca, ovo postaje P0 feature.

TODO

 Tenant-level OIDC connection model
 SAML connection model ako targetiraš enterprise IT
 Domain verification
 Login discovery po domenu
 JIT provisioning
 Optional SCIM provisioning
 Group-to-role mapping
 Federation metadata rotate/update
 Separate policy za:
local-only tenant
SSO-only tenant
hybrid tenant

Acceptance criteria

 Tenant ne može greškom ući u tuđi connection config
 Group mapping ne može eskalirati privilegije bez eksplicitne politike
 SSO login ostavlja puni audit trail
12. Admin realm

Admin auth mora biti odvojen i tvrđi.

TODO

 Poseban admin login surface ili poseban policy realm
 Kraći session timeout za admine
 Obavezan MFA, kasnije obavezan passkey
 Separate audit stream za admin akcije
 IP allowlist ili stronger network controls gde ima smisla
 Break-glass nalozi
 Break-glass access runbook
 JIT admin elevation
 Approval flow za high-impact admin akcije

Acceptance criteria

 Admin nije samo “user sa role=admin”
 Admin logout-all radi odvojeno
 Break-glass pristup je strogo auditovan
13. Audit & security events

Bez ovoga nemaš enterprise.

TODO

 Central audit_events
 Event taxonomy:
login succeeded
login failed
password reset requested
password reset completed
MFA enrolled
MFA removed
passkey added
passkey removed
session revoked
refresh reuse detected
tenant SSO updated
admin elevation
 Correlation ID po request-u
 Actor, subject, tenant, session, IP, UA polja
 Export/integration ka SIEM-u
 Tamper-evident storage strategy ako može

Acceptance criteria

 Svaka security-sensitive akcija ima audit event
 Događaji imaju dovoljno konteksta za forenziku
 PII masking policy definisana
14. Risk engine v1

Nemoj AI magiju. Počni sa deterministic pravilima.

Signals

 new country
 new ASN/IP range
 impossible travel
 too many failed logins
 refresh reuse detected
 factor removed + password changed kratko jedan za drugim
 admin login from new environment

Actions

 allow
 require MFA
 require passkey
 soft lock
 revoke sessions
 revoke refresh family
 open incident ticket

Acceptance criteria

 Risk rules ne blokiraju legitimne flow-ove bez observability-ja
 Svaka risk odluka ima razlog u logu
 False positive review loop postoji
15. Provisioning / lifecycle

Enterprise auth nije gotov bez lifecycle-a.

TODO

 User create/disable/delete posture
 Tenant membership model
 Role assignment workflow
 Deprovisioning invalidira:
sessions
refresh tokens
API clients gde je potrebno
 Group sync iz federation izvora
 SCIM token auth i rotation
 Suspend vs delete semantika

Acceptance criteria

 Disabled user više ne može ništa sa starom sesijom ili refresh tokenom
 Deprovisioning je idempotentan
 Membership drift se vidi u audit-u
16. Infra / secrets / keys

TODO

 Private keys za OAuth van koda i repo-a
 KMS/HSM plan gde ima smisla
 Encryption-at-rest za MFA secrets
 Key rotation plan
 Secret rotation playbook
 Separate signing keys po environment-u
 Clock sync / NTP hygiene
 Redis ili DB strategy za rate limiting i session/risk state

Acceptance criteria

 Nema hardcoded secrets
 Rotation test dokumentovan
 Recovery od izgubljenog signing key incidenta postoji
17. Background jobs

TODO

 Cleanup expired sessions
 Cleanup expired reset tokens
 Cleanup expired MFA challenges
 Revoke token families after reuse detection
 Send security emails
 Sync federation metadata
 SCIM sync jobs
 Risk aggregation jobs
 Audit export jobs
18. Testing backlog
Unit
 password hasher
 TOTP validator
 recovery token validator
 refresh family state machine
 policy engine
 risk rules
Integration
 login + MFA
 logout-all
 password reset invalidates sessions
 refresh rotation
 refresh reuse detection
 WebAuthn register/auth
 OIDC tenant discovery
 SCIM create/update/disable
Security
 session fixation test
 CSRF test
 brute force/rate limit test
 enumeration test
 replay test
 tenant isolation test
 privilege escalation test
 stale admin session test
19. Go-live gates

Ne puštaj u produkciju dok ovo nije zeleno:

 Session regeneration radi
 Absolute + idle timeout rade
 Password reset ubija sesije i refresh family-je
 MFA remove traži fresh auth
 Refresh rotation + reuse detection rade
 Audit coverage za sve security-critical akcije postoji
 Admin realm ima jaču politiku
 Tenant isolation testovi prolaze
 Incident runbook postoji
 On-call zna kako da revoke-uje user/session/client/family/tenant pristup
20. Redosled isporuke po sprintovima
Sprint 1
 ADR + threat model
 tabele users, sessions, audit_events
 password hashing
 basic login/logout
 secure cookies
 session regeneration
 rate limiting v1
Sprint 2
 forgot/reset password
 logout-all
 active sessions UI/API
 basic audit coverage
 CSRF protection
Sprint 3
 TOTP enroll/verify
 backup codes
 MFA challenges
 fresh auth middleware
Sprint 4
 OAuth client registry
 access token issuance
 refresh token issuance
 resource server validation
Sprint 5
 refresh rotation
 reuse detection
 family revocation
 suspicious token security events
Sprint 6
 RBAC base
 tenant isolation hardening
 admin realm v1
Sprint 7
 passkeys registration/auth
 passkey management UI/API
 admin passkey-required policy
Sprint 8
 OIDC tenant federation
 domain discovery
 JIT provisioning
Sprint 9
 SCIM v1
 risk engine v1
 SIEM export

21. Tri najveće rupe koje moraš sprečiti

1. Previše rano uvodiš federation i passkeys, a session/recovery ti još nisu tvrdi.
To pravi lep demo, ali lošu bezbednost. OWASP session discipline i NIST authenticator lifecycle su osnova, ne ukras.

2. Pokušavaš da isti auth model služi i browseru i API klijentima.
Za browser je session često prirodniji, za OAuth klijente authorization code + PKCE i token model.

3. TOTP proglasiš “gotovom MFA pričom”.
TOTP je dobar, ali passkeys/WebAuthn su jači cilj jer su phishing-resistant.

Najkraći zaključak

Ako hoćeš najbolji ROI, uradi ovim redom:

password + session hardening
MFA + recovery
OAuth token hygiene
RBAC + admin realm
passkeys
federation + provisioning
risk engine

To je najzdraviji put od “jak PHP auth” do “enterprise-grade identity system”.


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



