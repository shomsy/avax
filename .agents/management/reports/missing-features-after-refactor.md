# 🔬 Definitivni Feature-Level Audit: Šta Avax ume, a šta ne ume

**Izvori:** `avax.txt` + `avax-backup.txt` → `components/` (trenutno stanje)

> Ovaj izveštaj ne broji fajlove. On poredi **logičke sposobnosti** — šta framework kao sistem ume da uradi za
> developera.

---

## 🟢 Sposobnosti koje IMAMO (Pod-sistemi koji rade)

### 1. Dependency Injection Container

Framework ume da:

- ✅ Registruje servise (singleton, transient, factory)
- ✅ Resoluje zavisnosti rekurzivno (autowiring)
- ✅ Kompajlira kontejner u statički PHP fajl
- ✅ Generiše dijagnostičke izveštaje (GraphExporter, RuntimeReport)
- ✅ Podržava scope-ove (request, session)
- ✅ Injektuje preko atributa (`#[Inject]`, `#[RuntimeInput]`)

### 2. HTTP Request/Response ciklus

- ✅ Parsira dolazni HTTP zahtev iz PHP globala (`$_SERVER`, `$_GET`, `$_POST`)
- ✅ Gradi tipiziran Request DTO
- ✅ Kreira Response objekte (text, JSON, redirect)
- ✅ Dispečuje zahtev ka kontroleru (callable, [Class, method], invokable)
- ✅ Resoluje argumente kontrolera iz DI containera i URL parametara

### 3. Routing

- ✅ Registruje rute (GET, POST, itd.)
- ✅ Matchuje URL na registrovanu rutu
- ✅ Generiše URL-ove iz imena ruta
- ✅ Dispečuje matchovanu rutu

### 4. Data manipulacija (Arrhae, Collection)

- ✅ Fluent API za manipulaciju nizovima (map, filter, reduce, pluck)
- ✅ Dot-notation pristup (`data.user.name`)
- ✅ Normalizacija raznorodnih ulaza u kanonički niz

### 5. Persistence / EntityManager

- ✅ UnitOfWork pattern (flush, clear, commit)
- ✅ IdentityMap za praćenje entiteta
- ✅ Repository interfejs

### 6. Osnovna Session

- ✅ Startovanje sesije (kreiranje ID-a, učitavanje stanja)

### 7. Config

- ✅ Učitavanje konfiguracije iz PHP array fajlova
- ✅ Repository pattern za pristup vrednostima
- ✅ `config()` helper funkcija

### 8. Auth (Identity Suite)

- ✅ Login/Logout/Register tokovi
- ✅ Password hashing
- ✅ MFA (enroll, verify, recover, backup kodovi)
- ✅ OAuth/OIDC token management
- ✅ Multi-tenancy (Tenancy capability)
- ✅ External Identity providers

---

## 🔴 Sposobnosti koje NEMAMO (Pod-sistemi koji fale ili su osakaćeni)

### 1. 🍪 Session: Čitanje/Pisanje/Brisanje

**Trenutno stanje:** Session ume samo da se startuje. Ne ume da čita, piše, briše ili regeneriše vrednosti.

**Šta je framework pre umeo:**

- Upisivanje vrednosti u sesiju (`StoreSessionValue`)
- Čitanje vrednosti po ključu (`ReadSessionValue`)
- Brisanje pojedinačnih ključeva (`ForgetSessionValue`)
- Čišćenje cele sesije (`ClearSession`)
- Uništavanje sesije potpuno (`DestroySession`)
- Regenerisanje session ID-a (zaštita od fixation napada) (`RegenerateSession`)

**Uticaj:** Bez ovoga, Auth ne može da zapamti login, CSRF ne može da funkcioniše, wizard forme ne rade.

---

### 2. 🛡️ Validation Engine

**Trenutno stanje:** Postoji skeleton struktura ali nema funkcionalan validation engine.

**Šta je framework pre umeo:**

- Validacija DTO-ova preko PHP atributa (`#[Required]`, `#[Email]`, `#[MinLength(3)]`, `#[PasswordComplexity]`)
- Prikupljanje grešaka u `ValidationResult` objekat
- `ValidateInput` flow koji prima sirove podatke i vraća čiste ili greške
- `ValidateDto` engine koji čita atribute sa klase i primenjuje pravila

**Uticaj:** Bez ovoga, svaki kontroler mora ručno da validira input — nema automatske zaštite od loših podataka.

---

### 3. 🔔 Event System (Pub/Sub)

**Trenutno stanje:** Postoji folder struktura ali EventDispatcher i ListenerRegistry nemaju implementacije u
`components/`.

**Šta je framework pre umeo:**

- Registrovati listenere za named evente sa prioritetima
- Dispatch-ovati event objekat ili string sa podacima
- Podržavati propagation stop (listener može da zaustavi lanac)
- Flush-ovati sve listenere

**Uticaj:** Bez Event sistema, komponente ne mogu da komuniciraju "labavo" (loosely coupled). Auth ne može da emituje
`UserLoggedIn`, Cache ne može da sluša `EntityUpdated`.

---

### 4. 🗄️ Database: Napredni Connection Management

**Trenutno stanje:** Bazični Database interfejs za jednu konekciju.

**Šta je framework pre umeo:**

- **Connection Pooling** sa podrškom za MySQL, MongoDB, Cassandra, Elasticsearch, ClickHouse, Neo4j
- **Lazy Connection Pool** — konekcija se otvara tek kad treba
- **Multi-Tenant Pool** — automatsko rutiranje na drugu bazu po tenantu
- **Pool Metrics** — praćenje zdravlja pool-a (borrowed, idle, failed)
- **Schema Builder** — kreiranje tabela iz koda
- **Migrations engine** — versioning šeme (migrate, rollback, seed, fresh, status)

**Uticaj:** Framework ne može da radi sa više od jedne baze, nema migracije, nema connection reuse.

---

### 5. ⚡ Cache: Distribuirano keširanje

**Trenutno stanje:** Bazični cache interfejs.

**Šta je framework pre umeo:**

- **ConsistentHashRing** za distribuciju keša na klaster nodova
- **Cache Replication** — primary/replica za čitanje
- **Health Detection** — automatsko isključivanje nesposobnih nodova
- **Compiled Cache** — keširanje u statičke PHP fajlove (optimizacija za config, rute)
- **Cache Manifest** — praćenje svežine kompajliranog keša

**Uticaj:** Na produkciji, framework nema načina da podeli keš preko više servera niti da keš preživi restart procesa.

---

### 6. 🗂️ Filesystem: Diskovi i operacije

**Trenutno stanje:** Bazični Storage proxy bez ikakve napredne logike.

**Šta je framework pre umeo:**

- **Disk abstraction** — Local, S3, FTP, itd. (driver pattern)
- **File operacije** — Read, Write, Copy, Move, Delete, Append sa failure DTO-ovima
- **Directory operacije** — Create, Clear, Delete, List, EnsureExists, EnsureWritable
- **Path operacije** — chmod, PathExists, PathIsWritable, PathIsDirectory
- **Async IO** interfejs za neblokirajuće operacije

**Uticaj:** Framework ne može da piše logove na disk pouzdano, ne može da uploaduje na cloud, nema directory management.

---

### 7. 🌐 HTTP: Context, URI, CSRF, Enums

**Trenutno stanje:** HTTP Suite nema URI parser, nema CSRF zaštitu, nema HTTP kontekst, nema enum-ove.

**Šta je framework pre umeo:**

- **HttpContext** — pristup globalnim PHP varijablama kroz čist interfejs (testabilnost!)
- **URI Parser** — razbijanje URL-a na delove: `Scheme`, `Host`, `Port`, `Path`, `Query`, `Fragment`, `Authority`,
  `UserInfo`
- **CSRF zaštita** — generisanje i verifikacija tokena (`CsrfTokens`, `VerifyCsrfToken`)
- **HTTP Enums** — `HttpMethod`, `HttpStatusCode`, `HttpReasonPhrase`, `RequestOption`

**Uticaj:** Forme nemaju CSRF zaštitu, URL-ovi se parsiraju ručno, HTTP metode su magic stringovi umesto tipiziranih
enum-ova.

---

### 8. 📧 Logging: Globalna greška i Writers

**Trenutno stanje:** Postoji struktura ali nema funkcionalne writere niti global error handler.

**Šta je framework pre umeo:**

- **RotatingFileLogWriter** — automatska rotacija log fajlova po datumu sa retencijom
- **HandleGlobalError** — hvatanje PHP fatal error-a, uncaught exception-a
- **ErrorLogger** — strukturirano logovanje grešaka sa kontekstom
- **`logger()` helper** — globalna funkcija za logovanje bilo odakle

**Uticaj:** Greške "padaju u prazno". Nema automatskog hvatanja fatalnih grešaka niti rotiranja logova.

---

### 9. 🎨 View / Templating Engine

**Trenutno stanje:** `BladeTemplateEngine.php` postoji kao sirovi fajl van Screaming Architecture pravila.

**Šta je framework pre umeo:**

- **Blade rendering** — kompajliranje `.blade.php` šablona sa direktivama
- **View sharing** — globalne varijable za sve view-ove
- **Template existence check**
- **RenderView flow** — strogi tok koji prima ime šablona i podatke

**Uticaj:** Server-side rendering ne radi. Ne možemo prikazati HTML stranicu sa dinamičkim podacima.

---

### 10. 💻 CLI Console & Code Generators

**Trenutno stanje:** Potpuno izbrisan modul.

**Šta je framework pre umeo:**

- **`MakeControllerCommand`** — generisanje kontrolera iz terminala
- **`MakeEntityCommand`** — generisanje entiteta sa propertyma
- **`MakeRepositoryCommand`** — generisanje repository klasa
- **`MakeServiceCommand`** — generisanje servisa
- **ProgressBar & Table** — UI elementi za CLI output
- **CommandDefinitions** — registar svih dostupnih komandi

**Uticaj:** Developer mora sve da piše ručno. Nema `php avax make:controller` iskustva.

---

### 11. ⚙️ ApplicationWorkflow / Saga

**Trenutno stanje:** Samo prazan `ApplicationWorkflow.php`. Saga engine potpuno fali.

**Šta je framework pre umeo:**

- **Definisanje Saga-a** — višekoračni workflow sa step-ovima i kompenzacijama
- **Kompenzacije** — automatski rollback kad step fejluje
- **Idempotency** — zaštita od duplikata komandi
- **Inspekcija** — timeline, report, event tracing za debugging
- **Resume** — nastavak saga-e nakon oporavka od greške
- **Runtime Config** — MessageBus, StepRunner, SagaStore

**Uticaj:** Framework ne može da orkestira složene poslovne procese (npr. "kreiraj narudžbinu → naplati → pošalji →
obavesti").

---

### 12. 🏗️ Facade System

**Trenutno stanje:** Potpuno izbrisan.

**Šta je framework pre umeo:**

- **BaseFacade** — magičan static proxy koji delegira u Container
- Gotove fasade: `Auth::`, `Request::`, `Route::`, `Session::`, `Storage::`

**Uticaj:** Bez fasada, svaki pristup servisu zahteva eksplicitni `$container->get()` poziv.

---

### 13. 📊 DataLayer (Napredni data access)

**Trenutno stanje:** Potpuno izbrisan (namerno spojen sa Database/Persistence).

**Šta je framework pre umeo:**

- **Read acceleration** — Bloom filteri, materijalizovani view-ovi, read cache
- **Transactions** — Two-phase commit, deadlock detekcija, isolation levels
- **Consistency coordination** — CAP tradeoff politike, eventual consistency, conflict resolution
- **Slow query detection** — automatsko detektovanje sporih upita

**Uticaj:** Ovo su napredni enterprise feature-i. Većina se može odložiti, ali transaction management i isolation levels
su kritični za produkciju.

---

### 14. 🔒 Security: Encryption implementacija

**Trenutno stanje:** Samo prazan `EncrypterInterface`. Nema stvarnu enkripciju.

**Šta je framework pre umeo:**

- AES enkripcija/dekripcija
- Rotacija ključeva

**Uticaj:** Framework ne može da šifruje osetljive podatke (tokeni, cookie vrednosti, itd.).

---

### 15. 📬 HTTP Client (Outbound zahtevi)

**Trenutno stanje:** Ne postoji.

**Šta je framework pre umeo:**

- Slanje HTTP zahteva ka eksternim servisima (`HttpClientServiceProvider`)

**Uticaj:** Framework može samo da PRIMA zahteve, ali ne može da ŠALJE (npr. pozovi Stripe API, pošalji webhook).
