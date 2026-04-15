Da. Za tvoj slučaj bih preporučio **protected rewrite**, ne čist “refactor in place” i ne big-bang rewrite.

Zašto baš to:

* sadašnji model ima više strukturnih problema odjednom: base class osovina, trait-sprawl, mešanje state-a i
  interpretacije, nejasan ownership, kontradiktoran header model
* ako radiš samo lokalni refactor, vući ćeš stare greške predugo
* ako radiš big-bang rewrite bez zaštite, rizik migracije je previsok

Zato je najbolji put:

**characterization shield -> novi flow/capability model paralelno -> postepeno prebacivanje -> sečenje starog modela**.
To je u skladu i sa tvojim governance pravilima i sa prethodnim ToDo planom.

---

# Preporučena strategija

## Izabrani pristup

**Staged rewrite behind a stable PSR-compatible surface**

To znači:

* spolja zadržavaš PSR-7 kompatibilnost
* iznutra gradiš novi ownership model
* staro i novo kratko vreme koegzistiraju
* svaki korak je zaštićen testovima
* sečeš stare delove tek kada novi već nose odgovornost

Ovo je najbolji odnos:

* sigurnost
* brzina
* čitljivost
* dugoročni kvalitet

---

# Finalni cilj request dela

Na kraju želiš sledeće stanje:

## Flow nivo

`IncomingHttp` je glavni flow.

Unutar njega `IncomingRequest` postaje flow koji poseduje sklapanje request-a.

## State nivo

`Request` je tanak, immutable, PSR-compatible request state owner.

## Capability nivo

Odvojeni ownership objekti:

* `RequestHeaders`
* `RequestedInputs`
* `RequestBody`
* `ParsedBody`
* `RequestCookies`
* `RequestAttributes`
* `UploadedFiles`
* `RequestSession`

## Action nivo

Odvojeni action owners:

* `NormalizeHeaders`
* `ReadRequestTarget`
* `NormalizeProtocolVersion`
* `ParseBodyByContentType`
* `ParseJsonBody`
* `ParseFormBody`
* `NormalizeUploadedFiles`
* `GuardUploadedFiles`
* `BearerTokenReader`
* `ResolveClientAddress`
* `ParseForwardedAddresses`

## Configuration nivo

Assembly owner:

* `PublicEntryPointRequest`
* `AssembleRequest`

To je poštena primena flow/capability/configuration/foundation modela i recursive ownership pravila koje si definisao.

---

# Odluka: refactor, rewrite ili hibrid

## Opcija A — Refactor in place

Ovo bih odbio kao glavni plan.

Dobro:

* manji short-term diff

Loše:

* predugo nosiš staru semantiku
* stari tipovi diktiraju novi dizajn
* lako završiš sa “new code inside old shape”

## Opcija B — Big-bang rewrite

Ovo bih takođe odbio kao glavni plan.

Dobro:

* čist dizajn od nule

Loše:

* visok migracioni rizik
* teško poređenje ponašanja
* lako napraviš neprimetne regresije

## Opcija C — Protected rewrite

**Ovo preporučujem.**

Dobro:

* novi dizajn dobija pošten shape
* migracija je testirana
* stari ugovori ostaju stabilni dok sečenje nije spremno
* možeš raditi capability po capability

To je najbolja opcija.

---

# Plan po fazama

## Faza 0 — Freeze the language

Prvo zamrzni jezik sistema, pre koda.

### Donosiš ove odluke

Zaključaj sledeće pojmove:

* flow: `IncomingHttp`
* subflow: `IncomingRequest`
* state owner: `Request`
* shared state/capability owners:

    * `RequestHeaders`
    * `RequestedInputs`
    * `RequestBody`
    * `ParsedBody`
    * `RequestCookies`
    * `RequestAttributes`
    * `UploadedFiles`
    * `RequestSession`
* action owners:

    * `NormalizeHeaders`
    * `ReadRequestTarget`
    * `NormalizeProtocolVersion`
    * `ParseBodyByContentType`
    * `ParseJsonBody`
    * `ParseFormBody`
    * `NormalizeUploadedFiles`
    * `GuardUploadedFiles`
    * `BearerTokenReader`
    * `ResolveClientAddress`
    * `TrustedProxyPolicy`
    * `ParseForwardedAddresses`
* configuration owners:

    * `PublicEntryPointRequest`
    * `AssembleRequest`

### Izričito zabrani

* `AbsoluteServerRequest`
* `BaseRequest`
* `RequestHelper`
* `RequestManager`
* `CommonRequest*`
* `ParameterBag` kao centralni ownership model
* traits za core request ponašanje

### Deliverables

* jedan ADR: `request-architecture-language.md`
* jedan ADR: `psr-surface-vs-internal-ownership.md`

### Definition of done

* nema više otvorenih sinonima za isti koncept
* tim koristi isti rečnik za dalje korake

---

## Faza 1 — Build the migration shield

Pre nego što napišeš novi model, zaštiti bitno ponašanje starog.

### Radiš characterization testove za:

* `getRequestTarget()`
* `withHeader()`
* `withAddedHeader()`
* `withoutHeader()`
* `withUri(... preserveHost)`
* `withUploadedFiles()`
* `withParsedBody()`
* `withQueryParams()`
* `withCookieParams()`
* `withAttribute()` / `withoutAttribute()`
* `getServerParams()`

### Radiš failing regression testove za:

* headeri moraju biti `list<string>`
* header lookup mora biti case-insensitive
* `SERVER_PROTOCOL=HTTP/1.1` mora dati `1.1`
* request target ne sme biti isto što i URI path mutation
* ne sme se slepo verovati forwarded headerima
* request više ne sme da zavisi od trait assembly logike

### Zašto ova faza postoji

Ne da zaštiti “sve što stari kod radi”, nego da zaštiti:

* bitne PSR ugovore
* ponašanje koje želiš da zadržiš
* regresije koje želiš da ubiješ

### Deliverables

* `tests/Characterization/...`
* `tests/Regression/...`

### Definition of done

* znaš šta tačno čuvaš
* znaš šta tačno menjaš

---

## Faza 2 — Introduce the new tree

Sada formiraš novi shape, ali bez masovne migracije logike.

Preporučeni tree:

```text id="d3qzj4"
src/
  IncomingHttp/
    IncomingRequest/
      Request.php
      PublicEntryPointRequest.php
      Configuration/
        AssembleRequest.php

      RequestHeaders/
        RequestHeaders.php
        NormalizeHeaders.php

      RequestedInputs/
        RequestedInputs.php

      RequestBody/
        RequestBody.php
        ParsedBody.php
        Parsers/
          ParseJsonBody.php
          ParseFormBody.php
          ParseBodyByContentType.php

      RequestCookies/
        RequestCookies.php

      RequestAttributes/
        RequestAttributes.php

      UploadedFiles/
        UploadedFiles.php
        NormalizeUploadedFiles.php
        GuardUploadedFiles.php

      RequestSession/
        RequestSession.php
        NullRequestSession.php

      ProtocolVersion/
        NormalizeProtocolVersion.php

      RequestTarget/
        ReadRequestTarget.php

  Access/
    BearerTokenReader.php

  Network/
    ResolveClientAddress.php
    TrustedProxyPolicy.php
    ParseForwardedAddresses.php
```

### Zašto baš ovako

* `IncomingHttp` govori glavni flow
* `IncomingRequest` govori lokalni subflow
* podfolderi prate ownership
* action owners su opisni
* state owners su odvojeni
* shared interpretacije van request state modela

### Definition of done

* tree postoji
* namespace pravila su zaključana
* bez migracije poslovne logike još

---

## Faza 3 — Build `RequestHeaders` first

Prvi pravi ownership extraction.

### Zašto prvo ovo

Jer ovde trenutno imaš:

* stvarnu grešku modela
* veliku arhitektonsku korist
* mali migracioni rizik

### Implementiraš

* `RequestHeaders`
* `NormalizeHeaders`

### Pravila

`RequestHeaders` interno poseduje:

* canonical lookup map
* public/original key map ako ti treba
* isključivo `array<string, list<string>>`

### TDD redosled

Pišeš jedan po jedan test:

* single header as list
* multiple values
* case-insensitive read
* empty list for missing
* line rendering
* replace values
* append values
* drop header
* expose all headers in public shape

### Definition of done

* header capability prolazi sama
* može da zameni internu header logiku starog request-a

---

## Faza 4 — Extract request reading semantics

Sada odvajaš stvari koje pripadaju “read request” subflow-u.

### Implementiraš

* `ReadRequestTarget`
* `NormalizeProtocolVersion`
* `RequestCookies`
* `RequestAttributes`

### Redosled

1. `ReadRequestTarget`
2. `NormalizeProtocolVersion`
3. `RequestCookies`
4. `RequestAttributes`

### Zašto

Ovo su mali, čisti ownership blokovi i ne vuku veliki dependency graf.

### Definition of done

* request target semantika je izdvojena
* protocol normalization nije više u request modelu
* cookie i attribute ownership su odvojeni od generičkih bag pristupa

---

## Faza 5 — Build input and body model

Ovo je najbitniji DSL deo.

### Implementiraš

* `RequestedInputs`
* `RequestBody`
* `ParsedBody`

### Pre koda zaključaj ponašanje

Moraš prvo testovima definisati:

* query vs body prioritet
* body array vs object vs null
* empty string semantiku
* bool/int/list parsing pravila

### Važna granica

`RequestedInputs` ne sme da postane novi `ParameterBag`.

On je:

* read-only
* convenience read model
* lokalni owner input reading semantike

Nije:

* universal bag
* mutable storage
* replacement za raw PSR state

### Definition of done

* raw request state i convenience input DSL su jasno razdvojeni
* semantika input reading-a je zaključana testovima

---

## Faza 6 — Build body parser sub-capability

Ovde koristiš opravdani podfolder `Parsers/`.

### Implementiraš

* `ParseJsonBody`
* `ParseFormBody`
* `ParseBodyByContentType`

### Pravila

* `ParseBodyByContentType` je lokalni action owner
* konkretni parseri su mali action owners
* `RequestBody` i `ParsedBody` ostaju state owners

### Security fokus

Dodaj:

* invalid JSON behavior
* empty body behavior
* unsupported content-type behavior
* eventualno size guard ako hoćeš hardening odmah

### Definition of done

* parsed body više nije “side effect mist”
* parsing je eksplicitan i testiran

---

## Faza 7 — Build uploaded files capability

Ovo je dobar kandidat za posebno odvajanje jer sadrži tri različite odgovornosti.

### Implementiraš

* `UploadedFiles`
* `NormalizeUploadedFiles`
* `GuardUploadedFiles`

### Jasna podela

* `UploadedFiles` = state owner
* `NormalizeUploadedFiles` = action owner
* `GuardUploadedFiles` = validation/action owner

### Definition of done

* uploaded files više nisu loose array chaos
* nested normalization i validacija su testirani

---

## Faza 8 — Build attached capabilities

Sada uvodiš capability-je koji nisu deo PSR ugovora, ali su legitimni attached owners.

### Implementiraš

* `RequestSession`
* `NullRequestSession`
* `BearerTokenReader`

### Važna granica

* `RequestSession` može biti attached to request context
* `BearerTokenReader` ostaje van `Request`

Ne radi:

* `Request::getClientIp()`
* `Request::readBearerToken()`

To bi opet vratilo interpretaciju u state owner.

### Definition of done

* session attached capability postoji
* bearer token reading je eksplicitna interpretacija, ne skrivena magija

---

## Faza 9 — Build network trust capability

Ovo je security-kritična faza.

### Implementiraš

* `TrustedProxyPolicy`
* `ParseForwardedAddresses`
* `ResolveClientAddress`

### Redosled

1. trusted proxy policy
2. forwarded parsing
3. final resolution

### Fokus

Ovde nemoj brzati. Ovo je deo gde “enterprise-grade” nije buzzword.

Moraš jasno definisati:

* kad veruješ forwarded headerima
* kojim headerima veruješ
* kako biraš client address iz lanca
* šta radiš sa invalid IP vrednostima
* kako se ponašaš bez trusted proxy konteksta

### Definition of done

* client address resolution je izvan `Request`
* sigurnosna pravila su testirana abuse-case testovima

---

## Faza 10 — Build the new `Request`

Tek sada sklapaš novi request state owner.

### Implementiraš

* `Request implements ServerRequestInterface`

### `Request` sme da radi

* čuva stanje
* delegira ownership objektima
* vraća nove immutable instance kroz PSR `with*()`

### `Request` ne sme da radi

* parsing
* proxy trust logic
* bearer parsing
* globals bootstrap
* session bootstrapping
* low-level normalization

### Dodatni convenience sloj

Kasnije može da ima:

* `headers()`
* `inputs()`
* `cookies()`
* `attributes()`
* `sessionState()`

Ali samo kao tanki accessors.

### Definition of done

* novi `Request` je tanak
* PSR surface je očuvan
* ownership haos nije vraćen unutra

---

## Faza 11 — Build request assembly flow

Sada uvodiš pravi pipeline owner za request construction.

### Implementiraš

* `PublicEntryPointRequest`
* `Configuration/AssembleRequest`

### Ownership granica

* `PublicEntryPointRequest` = public entry
* `AssembleRequest` = internal construction flow owner

### Pipeline koji sastavljaš

Praktično ovakav redosled:

```text id="6sc7ri"
Incoming raw HTTP environment
  -> read server parameters
  -> read method
  -> read uri
  -> normalize protocol version
  -> normalize headers
  -> create request body stream
  -> parse body by content type
  -> normalize uploaded files
  -> attach cookies
  -> attach attributes defaults
  -> attach session state if available
  -> build Request
```

Ovo je pravo mesto gde pipeline semantika treba da živi.

### Definition of done

* request construction više nije razbacan po state objektu
* assembly flow je jasan i testiran

---

## Faza 12 — Introduce IncomingHttp alignment

Pošto ti je flow jako bitan, sada poveži request model sa širim flow jezikom sistema.

### Arhitektonska odluka

`Request` više nije centar sistema.
`IncomingHttp` je centar sistema.

### Dokumentuješ

* `IncomingHttp` owns the flow
* `IncomingRequest` owns request construction
* `Request` owns request state
* `Access` owns token interpretation
* `Network` owns client address trust interpretation

### Definition of done

* request deo je ugrađen u širi flow model
* tree čita kao priča, ne kao skup tehničkih slojeva

---

## Faza 13 — Compatibility migration

Sada prebacuješ potrošače starog request modela.

### Radiš

* privremeni adapter ako treba
* postepeno preusmeravanje starog koda na novi `Request`
* uklanjanje zavisnosti od `AbsoluteServerRequest`
* uklanjanje trait-based ponašanja
* uklanjanje `ParameterBag` gde je ownership zamenjen novim objektima

### Pravilo

Ne prebacuj sve odjednom ako package ima više potrošača.
Radi compatibility seam pa seci postepeno.

### Definition of done

* ostatak sistema koristi novi request model
* stari modeli više nisu aktivni dependency

---

## Faza 14 — Removal and cleanup

Tek sada agresivno sečeš stari model.

### Brišeš

* `AbsoluteServerRequest`
* request traits
* `ParameterBag` kao request-center model
* staru `getClientIp()` logiku
* stare docblock noise komentare
* copy-paste dokumentaciju
* duple sinonime i compatibility layer-eve koji više nisu potrebni

### Definition of done

* nema mrtvog request ownership paralelizma
* ostao je samo jedan pošten model

---

# Konkretan sprint plan

## Sprint 1 — Architecture freeze + shield

* zamrzni naming
* napiši ADR-ove
* napiši characterization testove
* napiši regression testove

## Sprint 2 — Core request capabilities

* `RequestHeaders`
* `ReadRequestTarget`
* `NormalizeProtocolVersion`
* `RequestCookies`
* `RequestAttributes`

## Sprint 3 — Input and body

* `RequestedInputs`
* `RequestBody`
* `ParsedBody`
* `ParseJsonBody`
* `ParseFormBody`
* `ParseBodyByContentType`

## Sprint 4 — File and attached capabilities

* `UploadedFiles`
* `NormalizeUploadedFiles`
* `GuardUploadedFiles`
* `RequestSession`
* `NullRequestSession`
* `BearerTokenReader`

## Sprint 5 — Security/network + new request

* `TrustedProxyPolicy`
* `ParseForwardedAddresses`
* `ResolveClientAddress`
* novi `Request`

## Sprint 6 — Assembly + migration

* `PublicEntryPointRequest`
* `AssembleRequest`
* adapteri
* cutover
* removal

---

# Najvažnije tehničke odluke koje ne smeš da menjaš usput

## 1. Request nije pipeline

`Request` je state owner.
`AssembleRequest` je pipeline.

## 2. Interpretacija nije state

Bearer token i client address ostaju van `Request`.

## 3. PSR surface nije isto što i unutrašnji jezik

`with*()` ostaje zbog interoperabilnosti, ne zato što diktira interni model.

## 4. Recursive ownership važi svuda

Isti zakon čitanja važi u svakom folderu gde poboljšava jasnoću.

## 5. Small safe increments only

Ne radi 500 linija pa onda “posle ću testove”.

---

# Rizici i kako ih preseći

## Rizik 1 — Previše fajlova

Lek:

* zadrži owner class kao glavni oblik
* action classes uvodi samo kad stvarno nose novu odgovornost
* ne pravi subfolder bez realne priče

## Rizik 2 — DSL duplira PSR

Lek:

* PSR = interoperability surface
* DSL = read convenience
* bez paralelnih sinonima

## Rizik 3 — RequestedInputs postane novi junk drawer

Lek:

* read-only
* zero mutation
* jasno dokumentovan prioritet query/body

## Rizik 4 — Request ponovo postane god object

Lek:
Svaku novu metodu proveri pitanjem:

* da li je ovo state ownership?
* ili interpretacija / orchestration?

Ako je drugo, ne pripada `Request`-u.

---

# Definition of done za ceo plan

Ovaj rewrite/refactor je gotov kada:

* `Request` implementira PSR-7 i ostaje tanak
* `IncomingRequest` i `AssembleRequest` nose request construction flow
* `RequestHeaders`, `RequestedInputs`, `RequestBody`, `RequestCookies`, `RequestAttributes`, `UploadedFiles`,
  `SessionState` imaju jasne ownership granice
* `BearerTokenReader` i `ResolveClientAddress` su van request state modela
* više nema base-class osovine
* više nema trait-sprawl za core request behavior
* više nema generic request bag modela
* svi bitni behaviori imaju characterization/regression/unit tests
* tree čita kao flow/capability priča, ne kao technical warehouse.

# Moj konačni savet

Nemoj ovo zvati samo “request refactor”.

Nazovi ga:

**IncomingRequest and Request Model Reconstruction**

Jer to i jeste:

* deo je `IncomingHttp` flow-a
* nije samo preuređivanje klase
* nego rekonstrukcija ownership modela

