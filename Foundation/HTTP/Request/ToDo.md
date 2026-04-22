According to a document from April 19, 2026, tvoj smer je sada **veoma dobar**, a ideja da `Request` bude **FormRequest-like request-backed DTO koji ulazi kroz autowiring** je **ispravna**. U trenutnom kodu već postoji jasna podela na `ServerRequest` kao PSR-7 surface, `AssembleIncomingRequest` i `PrepareRequest` kao creation flow, `RequestedInputs` kao read model, i `Request` kao DTO sloj nad `AbstractDTO`. Ali baš zbog toga sada treba da presečeš nekoliko važnih granica da bi ovo bilo **10/10**, a ne samo “veoma dobro”. 

Najvažnija stvar: **`Request` više ne sme da bude “legacy PSR bridge” u glavi sistema**. Ako ti je cilj Laravel-ov `FormRequest` osećaj, onda `Request` treba da bude **validated, autowired, request-backed DTO**, a ne sekundarni mutable HTTP request API. Trenutno `Request` hidrira DTO iz `ServerRequest->inputs()->all()`, ali i dalje pokušava da delegira HTTP operacije poput `withMethod()`, `withHeader()`, `withUri()` preko internog `$serverRequest`. Istovremeno, `fromInputs()` pravi DTO bez vezanog `ServerRequest`, a `MapRequestedInputsToDto` gradi DTO preko `new $dtoClass($inputs->all())` bez injection-a samog `ServerRequest`. To znači da je FormRequest ideja dobra, ali još nije formalizovana do kraja. 

## ToDo za 10/10 idealan Request

### 1. Zaključaj uloge zauvek

1. `ServerRequest` = jedini transport / PSR-7 request.
2. `RequestedInputs` = source-aware read model nad query/body.
3. `Request` = autowired, validated DTO za controller/action layer.
4. `PrepareRequest` / `AssembleIncomingRequest` = construction flow.
5. `InputSanitizer`, `ResolveClientAddress`, `NormalizeHeaders`, parseri = action owners, nikad deo DTO request-a. 

### 2. Formalizuj `Request` kao pravi FormRequest, ne kao pseudo-PSR request

Ovo je najvažniji arhitektonski korak.

* `Request` neka bude **request-backed DTO**, ne drugi HTTP request.
* Zadrži mu pristup ka underlying `ServerRequest`, ali ga koristi za:

  * `serverRequest()`
  * `method()`
  * `uri()`
  * `header()`
  * `clientAddress()` ako odlučiš da bude convenience delegat
* Nemoj da FormRequest DTO nosi ceo `with*()` mutacioni PSR surface kao da je sam transport objekat.
* Ako želiš mutacije, neka budu na `ServerRequest`, ne na DTO request-u.
* Uvedi jasan lifecycle:

  1. resolve current `ServerRequest` iz DI scope-a
  2. kreiraj `Request` subclass iz njega
  3. hydrate DTO
  4. validate DTO
  5. ako validacija padne: exception / response policy
  6. injectuj gotov DTO u controller action. 

### 3. Reši fundamentalni bug u DTO mapping/autowiring priči

Ovo je trenutno najveća logička rupa.

* `MapRequestedInputsToDto` sada radi `new $dtoClass($inputs->all())`.
* To je u redu za običan `AbstractDTO`.
* To **nije dovoljno** za `Avax\HTTP\Request\Request` subclass, jer FormRequest treba i attached `ServerRequest`.
* Zbog toga uvedi poseban tok:

  * ako `$dtoClass` extends `Avax\HTTP\Request\Request`, koristi **specialized RequestDtoFactory**
  * taj factory mora da primi:

    * `ServerRequest`
    * `RequestedInputs`
    * eventualno validator / hydrator
  * onda pravi instancu i attach-uje underlying request bez rupe u inicijalizaciji.
* `RequestedInputs::as()` ne sme generički praviti FormRequest kao običan DTO. 

### 4. Uvedi pravi autowiring resolver za FormRequest

Pošto ti je cilj FormRequest osećaj, ovo mora postati first-class flow.

Dodaj:

* `RequestArgumentResolver` ili `ResolveControllerRequestDto`
* pravilo: kada action parametar extends `Avax\HTTP\Request\Request`, container/dispatcher:

  * uzima scoped `ServerRequest`
  * pravi DTO kroz `RequestDtoFactory`
  * izvršava hydration + validation
  * injectuje spreman objekat

Dodaj i failure policy:

* validation exception
* 400 vs 422 odluka
* error payload contract
* per-action override ako treba. 

### 5. Reši “detached DTO” problem u `fromInputs()`

Trenutno `fromInputs()` pravi DTO bez attached `ServerRequest`, dok druge metode pretpostavljaju da `serverRequest` postoji. To je opasno.

Uradi jedno od ova dva, ali eksplicitno:

* ili `fromInputs()` ostaje samo za testiranje i DTO-only slučajeve, pa `Request` metodi koji traže underlying request bacaju jasan exception kada request nije attached
* ili `fromInputs()` puni neki `DetachedServerRequest` / null object
* ili ga prebaci u zaseban test/helper factory i ne tretiraj ga kao pun FormRequest lifecycle entry. 

### 6. Uskladi parsed body semantiku do kraja

Ovde još imaš jednu veoma bitnu semantičku rupu.

* `ParsedBody` state owner dozvoljava `array|object|null`.
* `ParseBodyByContentType` i `ParseJsonBody` trenutno efektivno rade sa `array|null`.
* `PrepareRequest::stageParseBody()` tretira `[]` kao “nema parsed body”, jer vraća prazan `ParsedBody` kada je rezultat `[]`.
* Time gubiš razliku između:

  * praznog JSON objekta / praznog JSON niza
  * praznog form body-ja
  * “nema parsed body” / unsupported parser / empty raw body

Za 10/10 moraš da zaključaš jednu tačnu semantiku:

* `null` = parser nije primenjen ili nema parsiranog sadržaja
* `[]` = validno parsirano, ali prazno
* `{}` / object support ili izbaci iz tipa ili ga stvarno podrži svuda

Isto pravilo važi u:

* `ParsedBody`
* `ParseJsonBody`
* `ParseBodyByContentType`
* `PrepareRequest`
* PSR tests. 

### 7. Zaključaj body-reading policy

`PrepareRequest::captureRawBody()` trenutno čita raw body samo za `POST`, `PUT`, `PATCH`, `DELETE`. To je praktično, ali nije maksimalno pošteno.

Za idealnu verziju odluči:

* da li body dopuštaš po HTTP metodi
* ili po prisustvu sadržaja / content-length / content-type
* ili uvodiš policy object tipa `BodyAllowancePolicy`

Bitno je da ovo bude:

* testirano
* dokumentovano
* predvidivo. 

### 8. Uskladi PSR surface sa characterization testovima

Već imaš PSR compliance testove i to je odlično. Ali za 10/10 idi do kraja:

* dodaj pun characterization suite za sve `with*()` metode
* proveri da li `getHeaderLine()` i test očekivanja koriste istu rendering semantiku
* proveri `withRequestTarget()` + `withUri()` interakciju
* proveri `withUri(... preserveHost)`
* proveri da li `ServerRequest` uvek vraća nove instance
* proveri da `RequestInit` ne gubi state pri svakom copy step-u.

### 9. Dovrši `RequestedInputs` bez da postane junk drawer

Tvoj `RequestedInputs` je sada mnogo bolji nego ranije, ali je upravo na granici da postane “svet za sve”.

Zadrži ga kao:

* read-only
* source-aware
* typed
* map/sanitize convenience layer

Ne dodaj u njega:

* validation rules
* authorization
* domain coercion
* persistence awareness
* error bag state
* mutation API

Dodatno:

* standardizuj `string/int/float/bool/array/enum`
* reši `bool()` tako da jasno tretira `true/false/1/0/on/off`
* `enum()` treba da bude robustan i pri type mismatch-u
* `InputValue` i `Inputs` neka imaju testove za:

  * missing
  * present null
  * present empty
  * source body/query
  * only/except/value semantics.

### 10. Ojačaj `InputSanitizer`

Tu još imaš tihih ivica.

* `json_encode()` može vratiti neuspeh
* `preg_replace()` može vratiti `null`
* `mb_convert_encoding()` može dati neočekivano ponašanje nad lošim inputom

Za 10/10:

* uvedi striktne fallback-e
* garantuj da svaka metoda stvarno vrati string
* napiši edge-case testove za binarne, null-byte, invalid UTF-8 i nested inpute
* jasno odvoji “sanitization for output context” od “validation / normalization”. 

### 11. Reši trusted proxy model do enterprise-grade nivoa

Network deo je sada dobar, ali još nije finalan.

* odluči da li podržavaš samo IPv4 (`TrustedIpv4ProxyPolicy`) ili i IPv6
* ako ostaje IPv4-only, dokumentuj to brutalno jasno
* `ResolveClientAddress` neka bude potpuno determinističan i abuse-tested
* dodaj testove za:

  * untrusted remote addr
  * spoofed X-Forwarded-For
  * trusted proxy chain
  * invalid IP entries
  * CIDR edge cases
  * wildcard policy ako je podržana
* proveri da test očekivanja za spoofed chain zaista odgovaraju kodu i trust modelu, da ne zaključavaš pogrešno ponašanje testovima.

### 12. Dovrši uploaded files do kraja

Ovo je već dosta dobro, ali za 10/10 dodaj:

* validaciju `tmp_name`
* ponašanje kada source stream nije validan
* ponašanje kada upload error nije `UPLOAD_ERR_OK`
* nested tree hardening
* behavior tests za CLI vs real SAPI
* contract test da `GuardUploadedFiles` i `NormalizeUploadedFiles` zajedno daju samo validna stabla
* property-based test ili data provider za duboko ugnježdene oblike.

### 13. Uvedi `ServerEnvironment` u stvarni flow ili ga izbaci iz glavnog toka

`ServerEnvironment` kao typed DTO za `$_SERVER` je dobra ideja, ali samo ako stvarno postane first-class deo toka.

Ili:

* `PrepareRequest` prvo pravi `ServerEnvironment`
* pa sve stage metode rade nad tim typed source object-om

Ili:

* nemoj ga držati kao polu-živ koncept

Za 10/10 ne smeš imati capability koji postoji “jer deluje korisno”, a nije stvarno owner u flow-u. 

### 14. Očisti deprecated/stale duplikate kada canonical tok bude zaključen

U pretrazi se vide stale/deprecated duplicate network fajlovi koji samo emit-uju deprecation i upućuju na canonical `ServerRequest/Network/*`. Kada završiš migraciju, ukloni ih iz aktivnog surface-a da ne ostane polu-mrtav paralelni svet. 

### 15. Napravi pravi quality gate za “idealni Request”

Za 10/10 ti ne treba samo “radi”, nego dokaz.

Dodaj obavezne gate-ove:

* full lint
* PSR characterization suite
* immutability regression suite
* requested-input semantics suite
* trusted proxy abuse suite
* uploaded files hardening suite
* DTO/FormRequest autowiring suite
* benchmark smoke za request creation
* static analysis na strožem nivou
* mutation testing makar za kritične request delove.

## Moj konačni savet za FormRequest ideju

Ovo je prava forma:

* `ServerRequest` ostaje **transport truth**
* `Request` postaje **validated action DTO**
* controller/action ne dobija raw input array nego autowired `CreateUserRequest extends Request`
* `RequestDtoFactory` pravi DTO iz scoped `ServerRequest`
* validacija se desi pre ulaska u action
* DTO ima pristup underlying request-u, ali ne glumi PSR request

To je, po meni, **idealni model** za ono što želiš: Laravel-like ergonomija, ali sa tvojom ownership arhitekturom i bez god object-a. 

## Ako hoćeš brutalno kratak “master ToDo”

1. Pretvori `Request` u pravi FormRequest DTO contract.
2. Uvedi `RequestDtoFactory` + controller parameter resolver za autowiring.
3. Spreči da generic `MapRequestedInputsToDto` pravi polu-inicijalizovan FormRequest.
4. Zaključaj parsed body semantiku (`null` vs `[]` vs unsupported).
5. Zaključaj body-reading policy.
6. Dovedi PSR characterization suite do kraja.
7. Zadrži `RequestedInputs` read-only i strogo ograničen.
8. Ojačaj `InputSanitizer` fallback ponašanja.
9. Završi trusted proxy/security hardening.
10. Završi uploaded files hardening.
11. Uključi ili izbaci `ServerEnvironment` iz glavnog toka.
12. Počisti stale duplicate surface kada canonical tok bude gotov. 

