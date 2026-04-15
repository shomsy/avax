Da — i **kod** i **ta granica** zajedno daju najrealniju sliku.

Iz onoga što se stvarno vidi u kodu, više nisi samo na “lepom auth planu”. Imaš **konkretne runtime komade** za ozbiljan
security posture: HTTP sender-constraint verifikaciju sa DPoP/mTLS i testovima, CSRF/session hardening, workload
`client_credentials` lane, audit/legal-hold rad, i adapter-ready persistence/jobs primere. To više nije samo
dokumentacija, nego stvarni izvedeni security surface.

## Status Summary (Updated: 2026-04-15)

Legenda:

- `[x]` done
- `[~]` partial / boundary-only / integrated, ali ne potpuno zatvoreno
- `[ ]` not implemented

### Done

- [x] Auth kernel hardening: password hashing, MFA, recovery, session hardening, audit, admin hardening, authorization
  separation, key lifecycle, release hardening i regresioni testovi.
- [x] Tenant i control-plane jezgro: tenant lifecycle, membership, invites, ownership transfer, tenant-owned OAuth
  client management, federation/SCIM/security-policy površine.
- [x] SCIM runtime core: `/Users`, `/Groups`, schema discovery, idempotency, drift remediation i bulk lane.
- [x] OAuth / sender-constrained runtime core: PKCE, refresh rotation, reuse detection, DPoP/mTLS-aware binding, client
  registry i tenant ownership.
- [x] Lifecycle orchestration i HTTP hardening: lifecycle capability, CSRF/session utilities, operativni docs i
  evidence.
- [x] **Quality gates automation**: 11/13 gates automated via `composer quality-gates`, uz package-owned source-truth i
  migration proveru
- [x] **Glossary**: Auth i Security glossary sa 90+ termina u `.rules/glossary/AUTH.md`, `SECURITY.md`
- [x] **Session revocation**: SQL + Redis backend
- [x] **Deployment trust boundary**: Proxy contract, smoke testovi, i operator checklist u
  `docs/deployment-trust-boundary.md`
- [x] **Capability matrix**: Ažuriran sa 30+ capabilities, evidence linkovima, i status legendom

### Partial

- [x] Source-of-truth cleanup: `docs/STATUS.md` je canonical state, `Auth.txt` je sveden na non-canonical merged
  artifact, a istorijski snapshot-i su prebačeni u `docs/archive/`
- [x] OIDC provider completeness: package-owned discovery, JWKS, logout, PAR, JARM, dynamic client registration HTTP
  surface, request-object claim validation, i confidential/public client-signed JAR verification sada imaju izvršivu
  evidenciju; standards certification i remote `jwks_uri` fetch ostaju van paketa
- [x] Deployment trust boundary: Proxy contract, trusted-header guard, unsafe-mode diagnostics, i executable smoke
  testovi postoje; edge certificate-chain execution ostaje eksplicitna deployment boundary odluka, ne paket rupa
- [x] Compatibility migration: major-boundary migration je dokumentovan, proverljiv i pokriven product-boundary testom
- [x] Support explainability: canonical docs i package-owned runtime “why” surface postoje kroz `AuthIssueExplainer` i
  `Auth` facade explain metode
- [~] External certification posture: conformance harness, certification profile, provenance, SBOM i evidence bundle
  postoje, ali external certification program ostaje van package scope
- [~] Mutation-quality posture: tooling radi lokalno, ali MSI/timeouts i dalje zahtevaju dodatno hardening zatvaranje
  pre release-quality tvrdnje

### Not Implemented

- [x] OIDC front-channel logout
- [x] OIDC back-channel logout
- [x] OIDC pairwise subject identifiers
- [x] OIDC JAR / PAR / JARM
- [ ] External certification program

---

## New Capabilities Since Last Report

- **Quality Gates Automation**: 11/13 gates automated
- **Redis Session Registry**: Package-owned Redis adapter
- **Deployment Trust Boundary Docs**: Proxy contract, smoke tests, diagnostics
- **Support Explainability**: canonical safe-failure i support docs
- **Capability Matrix v2**: canonical evidence matrix sa realnim putanjama
- **Migration Boundary**: automated migration checker + upgrade guide
- **Source Truth**: package-owned source-truth checker + archive split

Detaljni TODO ispod ostaje kanonski backlog; ova sekcija je brzi status za trenutno stanje.

Zato bih rekao da sada izgleda ovako:

**1. Jak auth kernel — da, vrlo jasno**
Tu si već stvarno jak. Imaš konkretan dokaz za:

* replay-resistant OAuth surface na HTTP granici
* CSRF/session zaštitu
* workload identity lane
* audit/diagnostics operativni sloj
* regression testove za neke od najbitnijih security rupa.

**2. Identity-capable kernel — takođe da**
Više nisi samo “login + MFA + refresh tokens”. Paket već ima federation / provisioning / risk / admin-realm pravac i
workload lane, što ga gura preko običnog auth modula. To je već ozbiljan identity core.

**3. Full identity product — još ne bih rekao**
Tu je granica. Prihvaćeni risk i boundary tekst i dalje govore da paket **nije** pun identity proizvod, nego kernel koji
pokriva mnogo identity stvari. Konkretno, i dalje se kao otvorena granica navode:

* nema pun **OIDC provider behavior**
* nema pun **SCIM runtime**
* nema pun **tenant/control-plane model**
* postoji rizik da timovi pomešaju kernel sa gotovim identity platform proizvodom.

Najbitniji insight je ovaj:

> **Kod pokazuje da si dosta prešao iz “auth library” u “identity kernel”.**
> **Ali boundary i dalje kaže da nisi još “full identity platform product”.**

To je zapravo zdravo stanje. Znači da si jak, ali još nisi počeo da lažeš sebe scope-om.

Jedina stvar na koju bih te upozorio je mala **nekonzistentnost između code-evidence i risk/blurb evidencije**.
Na primer, noviji taskovi tvrde da je `client_credentials` lane završen, workload identity runtime postoji, i dosta
operational gaps je zatvoreno; ali accepted risk još uvek govori kao da `client-credentials`, SCIM runtime i full
control-plane nisu deo isporučenog proizvoda. To meni deluje kao:

* ili je **risk register malo zastareo**
* ili je namerno zadržana konzervativna boundary poruka da se ne overclaim-uje scope.

Moja realna ocena sada:

* **auth kernel:** 9/10
* **identity kernel:** 8–8.5/10
* **full identity product:** 6.5–7.5/10

Dakle, **tačno si na granici**:

* sa jedne strane više nisi “samo auth”
* sa druge strane još nisi “Auth0/Okta/Keycloak-class product surface”

Najkraće:

**Sada izgledaš kao ozbiljan, near-perfect auth + identity kernel.**
**Još ne izgledaš kao kompletan identity platform proizvod.**

Ako hoćeš baš brutalno precizno, razlika između ta dva je sada uglavnom u ovome:

* provider/product surface
* tenant/control-plane ownership
* lifecycle/admin/operator completeness
* ecosystem/public product contracts

Plan i MUST HAVE ToDo:

According to a document from April 13, 2026: evo **jednog objedinjenog TODO-a** za sve što sam zapazio da treba *
*ispraviti, implementirati ili unaprediti** da iz “vrlo jakog auth/identity kernela” pređeš u **čist, dosledan, full
identity platform proizvod**. Napomena: ovde sam bio strog prema **najnovijem `Auth.txt` i code-evidence referencama
koje on pominje**, ali ne mogu pošteno da tvrdim da sam video tela svih PHP klasa — vidim status, boundary, verifikacije
i reference na konkretne fajlove/adapters, plus deo starijih review nalaza koji su ostali pomešani u istom fajlu.

# Jedan veliki TODO

## P0 — sredi source-of-truth haos pre svega

### 1) Očisti i razdvoji **trenutno stanje** od **istorijskih review snapshot-a**

Problem: isti `Auth.txt` sadrži i najnoviji risk/boundary status i stare review ostatke tipa `Implement AuthMiddleware`,
`Fix UserInterface`, pregled stare `Actions/ / Adapters/ / Contracts/` strukture. To zamućuje stvarno stanje i pravi
lažan osećaj da su otvoreni problemi možda i dalje aktivni, iako mogu biti istorijski artefakti.

**TODO**

* [x] Izvuci stare review snapshot-e iz `Auth.txt` i prebaci ih u archive / review history
* [x] Napravi **jedan kanonski status fajl** za:

    * [x] current delivered capabilities
    * [x] open risks
    * [x] accepted product choices
    * [x] explicit non-goals
* [x] Napravi **jedan kanonski boundary fajl**: “kernel vs full platform”
* [x] Napravi **jedan kanonski capability matrix** generisan iz koda/testova kad god je moguće
* [x] Ukloni kontradikcije između risk registra, roadmap-a i capability tabele
* [x] Dodaj pravilo da istorijski review snapshot nikad više ne živi u istom fajlu kao current-state truth

**Done kada**

* [x] neko ko otvori samo jedan status dokument može tačno da vidi šta je shipped, šta nije, i šta je namerno van
  scope-a
* [x] nema starih review TODO stavki u current-state dokumentima

---

## P0 — zaključaј stvarni proizvodni scope

### 2) Reši kontradikciju: **šta kernel stvarno već poseduje, a šta još nije product**

Najnoviji surface kaže da imaš praktičan OIDC provider lane sa discovery, JWKS, authorization code + `openid`, PKCE,
nonce, ID token issuance, userinfo i rollover; u isto vreme active risk i dalje kaže da “package still does not ship
OIDC provider behavior, client-credentials, SCIM runtime, or a full tenant/control-plane model”. To je najveći semantic
drift koji sam video.

**TODO**

* [x] Napravi tabelu sa kolonama:

    * [x] `kernel-owned and shipped`
    * [x] `kernel-owned but partial`
    * [x] `external package/app owned`
    * [x] `explicit non-goal`
* [x] Za OIDC posebno odluči:

    * [x] da li tvrdiš “practical OIDC provider lane”
    * [ ] ili “OIDC-adjacent auth kernel”
* [x] Za SCIM posebno odluči:

    * [x] da li imaš runtime
    * [ ] ili samo seams/docs/roadmap
* [x] Za workload/client-credentials posebno odluči:

    * [x] da li je shipped
    * [ ] ili samo design/docs
* [x] Ažuriraj risk `AUTH-RISK-006` da tačno odgovara kodu i capability tabeli
* [x] Dodaj release note sekciju: “what this package is not”

**Done kada**

* [x] risk register i capability matrix više ne pričaju različite priče
* [x] scope se može citirati bez objašnjavanja “pa mislio sam…”

---

## P0 — zatvori jedini jasno otvoren tehnički risk

### 3) Reši **global session revocation** kao package-owned priču

Ovaj risk je praktično zatvoren u package scope-u: multi-session/global revocation ima dokazivu konzistentnost na
podržanim registry backend-ima kroz package-owned SQL i Redis adaptere, fail-fast enterprise mode, i backend conformance
testove.

**TODO**

* [x] Odluči da li session registry postaje:

    * [ ] obavezni deo platforme
    * [ ] zvanični adapter paket
    * [x] i dalje app-owned dependency, ali eksplicitno mandatory za “enterprise mode”
* [x] Isporuči barem jedan package-owned durable registry:

    * [x] SQL
    * [x] Redis
* [x] Dodaj canonical session entity model:

    * [x] session id
    * [x] identity id
    * [x] created at
    * [x] last seen at
    * [x] idle expiry
    * [x] absolute expiry
    * [x] revoked at
    * [x] revoke reason
* [x] Dodaj package-owned flow-ove:

    * [x] revoke current session
    * [x] revoke specific session
    * [x] revoke all sessions for identity
    * [x] revoke all sessions after password/factor change
* [x] Dodaj adapter conformance testove za svaki session backend
* [x] Dodaj docs: šta znači “enterprise session revocation support”

**Done kada**

* [x] “logout everywhere” radi konzistentno na svim podržanim backend-ima
* [x] session revocation više nije deployment caveat nego package capability

---

## P0 — deployment trust boundary učini dokazivim, ne samo dokumentovanim

### 4) Učvrsti **sender-constrained** deployment priču

Mitigated risk za sender-constrained posture je dobar znak, ali i dalje eksplicitno kaže da certificate-chain trust i
reverse-proxy correctness ostaju deployment-owned. To je realno, ali za full platform treba da bude mnogo više od “docs
kažu”.

**TODO**

* [x] Napravi reference deployment profiles za:

    * [x] direct TLS termination
    * [x] trusted reverse proxy
    * [x] mTLS at edge
* [x] Dodaj explicit proxy contract:

    * [x] which headers are trusted
    * [x] which headers are forbidden from untrusted hops
    * [x] how certificate info is propagated
* [x] Dodaj integration smoke tests za:

    * [x] wrong forwarded headers
    * [x] missing client cert metadata
    * [x] broken DPoP proof
    * [x] cert/key mismatch
* [x] Dodaj operator checklist za mTLS chain validation
* [x] Dodaj "unsafe deployment mode" detection/warnings
* [x] Dodaj support diagnostics za sender-constraint failures

**Done kada**

* [x] deployment team ne mora da nagađa kako da bezbedno postavi proxy/edge
* [x] platform može da dokaže da sender-constrained flow nije slomljen pogrešnim reverse proxy-jem

---

## P1 — završi ono što te deli od **full identity platform**, ne samo jakog kernela

### 5) Dodaj **dynamic client registration**

OIDC dynamic client registration endpoint je sada package-owned kroz tanki
HTTP surface koji mapira na canonical OAuth client management flow-ove.
Standards certification, remote `jwks_uri` fetch, i software-statement
validation ostaju van paketa.

**TODO**

* [x] `System/Flow/Oidc/RegisterClient/`
  published through the thin `integrations/http/Oidc/ServeOidcHttpSurface.php` over the canonical OAuth registration
  flow
* [x] `System/Flow/Oidc/UpdateClient/`
  published through the thin OIDC registration HTTP surface over `System/Flow/OAuth/UpdateClient/`
* [x] `System/Flow/Oidc/DisableClient/`
  published through the thin OIDC registration HTTP surface over `System/Flow/OAuth/DisableClient/`
* [x] `Capability/OAuth/ClientRegistration/`
  folded into `Capability/OAuth/OAuthClientRegistryInterface` and concrete management flows to avoid a parallel bucket
* [x] Validacije:

    * [x] redirect URIs
    * [x] grant types
* [x] token endpoint auth method
    * [x] public vs confidential client
    * [x] PKCE rules
    * [x] sender-constraint posture
* [~] Audit diff za svaku client config promenu
  audit trail postoji, ali ne još i dedicated semantic diff explorer za client metadata
* [x] Tenant ownership nad client-ovima
* [x] Approval za high-risk client registration

**Done kada**

* [x] relying party može bez ručnog patchovanja koda da uđe u platformu kao regularan client

### 6) Dodaj **OIDC logout surface**

Front-channel i back-channel logout su sada kernel-supported kao lokalni logout i
session-correlation lane, ali RP fan-out i per-client propagation i dalje nisu
potpuno zatvoreni.

**TODO**

* [x] `System/Flow/Oidc/FrontChannelLogout/`
* [x] `System/Flow/Oidc/BackChannelLogout/`
* [x] RP session correlation
* [~] logout propagation retry model
  lokalni revoke + session correlation su zatvoreni, ali udaljeni RP retry orkestrator ostaje product-layer proširenje
* [x] logout failure audit
* [x] per-client logout support flags
* [x] compatibility test matrix

**Done kada**

* [x] platform zatvara lokalne i kernel-correlated RP sesije; spoljašnji retry/orchestration ostaje zaseban product
  layer

### 7) Dodaj **pairwise subject identifiers**

Osnovni pairwise subject identifier lane je implementiran; migration rules i rollout posture su još delimično
policy/docs posao.

**TODO**

* [x] `Capability/Oidc/SubjectIdentifiers/`
* [x] public vs pairwise strategy
* [x] sector identifier validation
* [x] pairwise generation policy
* [~] migration rules
  runtime je stabilan; rollout/migration posture ostaje policy/doc posao
* [x] per-client stable subject tests

### 8) Dodaj **JAR / PAR / JARM**

PAR, JARM, i package-owned JAR request-object signing su sada kernel-supported.

**TODO**

* [x] `System/Flow/Oidc/PushAuthorizationRequest/`
* [x] `System/Flow/Oidc/ValidateRequestObject/`
* [x] `System/Flow/Oidc/ReturnJwtAuthorizationResponse/`
* [x] replay protection
* [x] expiry rules
* [x] signed request validation
* [x] conformance tests

### 9) External certification program

Capability tabela eksplicitno kaže `external certification` nije podržan.

**TODO**

* [x] conformance harness
* [x] certification profile matrix
* [x] evidence bundle generator
* [~] signed release + test bundle
  package-owned provenance/signing postoji, ali external certification release program ostaje van paketa
* [x] repeatable certification environment profile

---

## P1 — tenant / control-plane pretvori u pravi proizvod

### 10) Završi **full tenant/control-plane model**

Latest risk i dalje kaže da full tenant/control-plane model nije deo paketa. To je centralna razlika između identity
kernela i identity platforme.

**TODO**

* [x] `Capability/Tenant/`
* [~] `Capability/TenantMembership/`
  membership je merged u `Capability/Tenant/` da ne pravi paralelno imenovanje za isti koncept
* [x] `System/Flow/Tenant/`
* [ ] Entiteti:

    * [x] tenant
    * [x] membership
    * [x] membership role
    * [x] invite
    * [x] owner transfer
    * [~] tenant policy state
      security policy state ostaje u `Capability/TenantSecurity/`, ne u `Capability/Tenant/`
* [ ] Flow-ovi:

    * [x] create tenant
    * [x] invite member
    * [x] accept invite
    * [x] remove member
    * [x] suspend member
    * [x] transfer owner
    * [x] read members
* [ ] Control-plane površine:

    * [x] federation connections
    * [x] SCIM directories
    * [x] OAuth clients
    * [x] access mappings
    * [x] security policies
    * [~] approval / rollback / diff explorer
      tenant security diff/apply/rollback postoji, ali nije još poseban explorer za sve tenant artifacts
* [~] Strict tenant crossing testovi
  tenant-scoped HTTP and flow coverage postoji, ali još nema exhaustive crossing matrix

**Done kada**

* [ ] “team/tenant/org” nije samo koncept u docs, nego pun product-owned boundary

### 11) SCIM product completeness

Ako želiš full platform, SCIM ne sme ostati “možda runtime, možda ne”. Latest risk još ga tretira kao deo onoga što nije
stvarno shipped kao platform feature.

**TODO**

* [x] Odluči: SCIM runtime je first-class ili nije
* [x] Ako jeste:

    * [x] `/Users`
    * [x] `/Groups`
    * [x] schema discovery
    * [x] idempotency
    * [x] drift remediation
    * [x] sync health
    * [x] per-directory throttling
    * [x] outage recovery
* [ ] Ako nije:

    * [ ] izbaci sve dvosmislene tvrdnje da “paket ima SCIM” iz current-state docs

### 12) Workload / client-credentials truth

Latest accepted risk još uvek navodi `client-credentials` kao nešto što paket “still does not ship”, dok noviji
razgovori i neki boundary opisi impliciraju workload lane. To moraš zatvoriti kao source-of-truth problem ili product
gap.

**TODO**

* [x] Verifikuj da li public surface za workload zaista postoji
* [x] Ako postoji:

    * [x] upiši ga u capability matrix kao supported
    * [x] dodaj tests/release evidence reference
* [~] canonical machine identity model još može da se proširi sa formalnim control-plane pravilima

---

## P1 — reši accepted product choices koje možda više nisu dovoljne

### 13) Trusted device / remembered device

Trenutno je eksplicitno “ne isporučujemo trusted-device support”. To je pošteno, ali za full identity platform to je ili
feature ili eksplicitno dugoročno odbijena odluka sa jakim razlogom.

**TODO**

* [x] Donesi konačnu odluku:

    * [ ] ulazi u platformu
    * [x] trajno van scope-a
* [x] Ako ulazi: ne primenjuje se jer je trajno van scope-a.
* [x] Ako ne ulazi:

    * [x] zapiši kao permanent product posture, ne “za sada”

### 14) Compatibility break za AvaxContainer namespace

Ovo je accepted risk. Za platform product to nije strašno, ali mora biti zatvoreno ili kao intentional vNext break, ili
kao migration shim.

**TODO**

* [ ] Dodaj BC shim ili alias
* [x] Ili digni major version i zaključi migration path
* [x] Dodaj automated upgrade test
* [x] Dodaj migration doc sa staro → novo mapiranjem namespace-a

---

## P2 — arhitektura i naming moraju prestati da curе stare slojevite priče

### 15) Dovrši feature-sliced shape i ukloni stare slojevite tragove

Stari review snapshot veoma jasno kaže da je projekat deklarativno feature-sliced, ali realna struktura je bila
slojevita (`Actions/`, `Adapters/`, `Contracts/`). To možda više nije potpuno aktuelno, ali dok god taj trag postoji u
current evidence, to je arhitektonski dug koji moraš zatvoriti do kraja.

**TODO**

* [x] Finalizuj root shape:

    * [x] flows/
    * [x] capabilities/
    * [x] configuration/
    * [x] foundation/
    * [x] integrations/ samo gde su stvarno adapteri
* [x] Ukloni preostale top-level tehničke fioke ako još postoje
* [~] Za svaki stari namespace napiši mapu:

    * [x] old location
    * [x] new owner
    * [x] migration status
* [x] Zabrani nove `Actions/Adapters/Contracts` top-level priče kao glavni repo story
* [x] Dodaj lint/check koji detektuje zabranjene junk-drawer foldere

### 16) Legacy auth review stavke: ili ih završi, ili ih arhiviraj kao obsolete

`Implement AuthMiddleware`, `Fix UserInterface`, `improve JWT adapter decoupling` i slične stavke ne smeju ostati u
limbu. Ili su još uvek realne, ili su istorija.

**TODO**

* [x] Proveri da li `AuthMiddleware` još postoji i da li je aktivan runtime surface
* [x] Ako postoji: ne primenjuje se, jer aktivan runtime surface ne postoji.
* [x] Ako ne postoji:

    * [x] obriši review stavku iz current-state dokumenta
* [x] Reši `UserInterface` mutability: immutable, bez pola-pola stanja.
* [x] Završi JWT adapter decoupling ako je još relevantan
* [x] Sve zastarele review nalaze premesti u archive

---

## P2 — testing, release, evidence

### 17) Zatvori environment/testing neizvesnost

Stari evidence snapshot pokazuje da je ranije release bio `hold` jer lokalni CLI nije mogao da pokrene PHPUnit/Infection
bez `dom`, `xml`, `xmlwriter`, `mbstring`. Ne znam da li je to još aktivno, ali to je previše važno da ostane
neprovereno.

**TODO**

* [ ] Proveri da li je full PHPUnit suite danas stvarno green u CI
* [ ] Proveri da li mutation/infection danas stvarno radi
* [ ] Ako ne radi:

    * [ ] popravi runtime image
    * [ ] popravi local dev bootstrap
    * [ ] dodaj preflight script koji proverava extensions
* [~] Dodaj release gate:

    * [x] syntax
    * [x] static analysis
    * [x] unit
    * [x] integration
    * [ ] mutation / high-value subset
* [x] Dodaj evidence bundle po release-u

### 18) Napravi canonical conformance dashboard

**TODO**

* [x] Capability matrix sa statusom:

    * [x] supported
    * [x] partial
    * [x] external
    * [x] non-goal
* [x] Link ka test fajlu ili evidence-u po capability-ju
* [x] Link ka ADR-u ili boundary doc-u po non-goal capability-ju
* [~] Auto-fail ako capability tabela nije usklađena sa test markerima i risk statusom

---

## P3 — operator i product polish

### 19) Support/admin explainability

**TODO**

* [x] "why access denied" surface
* [x] "why step-up required" surface
* [x] "why sender-constraint failed" surface
* [x] "why session revoke did/did not propagate" surface
* [x] support incident playbooks

### 20) Product packaging

**TODO**

* [x] jasno odvoj:

    * [ ] kernel package
    * [ ] integration packages
    * [ ] optional enterprise packages
* [x] napiši upgrade/migration guide
* [x] napiši supported deployment profiles
* [x] napiši “when to choose this vs external IdP” guide

---

# Moj prioritetni redosled

Ako hoćeš najpametniji redosled rada, idi ovako:

**Prvo:**

1. source-of-truth cleanup
2. scope truth table
3. session registry gap
4. deployment trust boundary hardening

**Zatim:**

5. dynamic client registration
6. OIDC logout
7. tenant/control-plane
8. SCIM truth + runtime completeness
9. workload/client-credentials truth

**Posle toga:**

10. trusted-device final decision
11. compatibility break cleanup
12. architecture cleanup
13. CI/evidence hardening
14. certification/conformance

---

# Najkraći iskren zaključak

Ono što sam zapazio nije “projekat je loš”.
Naprotiv — glavni problem više nije osnovna bezbednost, nego:

* **source-of-truth drift**
* **kernel vs product ambiguity**
* **nekoliko realnih platform gaps**
* **jedan stvarno otvoren session revocation gap**
* **par accepted odluka koje moraš ili da proizvodizuješ ili da trajno odbaciš**

To je dobar problem za imati. Znači da si izašao iz faze “da li auth uopšte valja” i ušao u fazu “da li je ovo zaista
gotov identity proizvod”.

ENTERPRISE GRADE STANDARDS:

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

Capability slices describe shared abilities, boundaries, mechanisms, or reusable domain-level enablers that support
multiple flows.

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

If something has real domain meaning, real policy meaning, or real cross-flow significance, it likely belongs in a
capability or flow, not in Foundation.

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

If a language, framework, runtime, or ecosystem has a strong and legitimate convention, it may be respected **only if**
it does not damage:

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
