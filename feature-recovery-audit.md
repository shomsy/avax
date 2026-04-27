# Avax Feature Recovery Audit

## Goal

Provera da li je veliki refaktor i prelazak na *Screaming Architecture* sačuvao stvarnu snagu i funkcionalnosti starih
komponenti. Ovaj audit poredi mogućnosti starih komponenti (pronađenih u Trash-u i starom kodu) sa novom arhitekturom.

## Components To Audit

- Container
- Database
- DataFoundation -> Data
- DataLayer -> Persistence
- HTTP / Request / Response / Router / Middleware
- Auth
- Cache
- Filesystem
- Validation
- Console
- Events
- Logging

---

## Detaljan spisak šta nedostaje u odnosu na stari sistem

1. **Masivne "Collections" (Kolekcije)**
    - **Bilo je:** `Collection.php`, `LazyCollection.php`, `Arr.php`, `Enumerable.php`. Funkcije kao `map()`,
      `filter()`, `reduce()`, `flatMap()`.
    - **Sada:** Nema ih. Oslanjaš se na native PHP nizove ili svedenu `Data` komponentu.
2. **Manipulacija datumima (Carbon/Date)**
    - **Bilo je:** `Carbon.php`, `Date.php`. Ogromna biblioteka za timezone, diffForHumans, modifikacije.
    - **Sada:** Nema ih. Oslanjaš se na sirovi PHP `DateTime`.
3. **Bogat Templating Engine (Blade / BladeOne)**
    - **Bilo je:** `BladeOne.php`, `Blade.php`, kompajleri za layout-e, sekcije, komponente.
    - **Sada:** Svedena `View` komponenta, bez out-of-the-box magije za template-ovanje.
4. **Mail i Notifikacije**
    - **Bilo je:** `Mail.php`, `Mailer.php`, `Notification.php`, SMTP drajveri.
    - **Sada:** Potpuno obrisano.
5. **Poslovi u pozadini (Queues & Jobs)**
    - **Bilo je:** `Queue.php`, `Job.php`, `Batch.php`, `Task.php`, async coroutines.
    - **Sada:** Obrisano iz kutije. Prelazi se na čiste worker loop-ove u `framework/System`.
6. **ORM "Magija" i Query Builderi**
    - **Bilo je:** Masivni `Builder.php`, `Schema.php`, `SleekDB.php`, ActiveRecord/Eloquent stil.
    - **Sada:** Prebačeno na striktniji Data/Persistence model. Izgubljen je ogroman deo "lakoće" za napredne upite radi
      čistoće koda.
7. **String i "Text" Helperi**
    - **Bilo je:** `Str.php`, `Stringable.php`, `Inflector.php` (pluralizacija, snake_case).
    - **Sada:** Svedena `Text` komponenta bez stotina korisnih helpera.
8. **Lokalizacija (I18N)**
    - **Bilo je:** `Translator.php`, `Lang.php`.
    - **Sada:** Nema komponente za internacionalizaciju.
9. **Napredni Debugging (Ignition/Flare)**
    - **Bilo je:** Bogati error ekrani sa interaktivnim stack trace-ovima i predlozima.
    - **Sada:** Obrisano. Imamo samo `DumpDebugger`.
10. **Console UI i Interaktivnost**
    - **Bilo je:** `ProgressBar.php`, tabele, interaktivna pitanja u CLI.
    - **Sada:** Svedeno u `Commands` komponenti na grubu logiku.

---

## Feature Recovery Matrix

| Component       | Old Feature                                        | Old File                    | New Owner                | New File | Status      | Action                                              |
|-----------------|----------------------------------------------------|-----------------------------|--------------------------|----------|-------------|-----------------------------------------------------|
| **Container**   | Ownership metadata, access checks, slice manifests | `Container.php`             | `components/Container`   | TBD      | **Partial** | **Restore** (Vratiti napredne DI mogućnosti)        |
| **Collections** | Chainable methods, map/reduce, LazyLoad            | `Collection.php`, `Arr.php` | `components/Data`        | TBD      | **Lost**    | **Restore** (Kao čiste Data strukture)              |
| **Database**    | Query builder advanced ops, Schema builder         | `Builder.php`, `Schema.php` | `components/Database`    | TBD      | **Partial** | **Keep** (Održati odvojeno od entiteta)             |
| **Persistence** | UnitOfWork, Identity Map, Repositories             | `EntityManager`, itd.       | `components/Persistence` | TBD      | **Partial** | **Restore** (Ovo je srž novog sloja)                |
| **Dates/Time**  | Timezones, diffForHumans                           | `Carbon.php`                | N/A                      | N/A      | **Lost**    | **Drop** (Zameniti native PHP objektima)            |
| **Templating**  | Extends, sections, custom directives               | `BladeOne.php`              | `components/View`        | TBD      | **Partial** | **Restore** (Samo ako su napredni view-ovi ključni) |
| **Mail**        | SMTP, notifications                                | `Mail.php`                  | N/A                      | N/A      | **Lost**    | **Drop** (Rešavati naknadno)                        |
| **Queues/Jobs** | Redis queues, Batching                             | `Queue.php`, `Job.php`      | N/A                      | N/A      | **Lost**    | **Drop** (Koristiti čiste framework workere)        |
| **Strings**     | Pluralization, camelCase, slugger                  | `Str.php`, `Inflector.php`  | `components/Text`        | TBD      | **Lost**    | **Restore** (Kao čiste helper funkcije)             |
| **Auth**        | JWT, Gate, Policies                                | `JWT.php`, `Gate.php`       | `components/Auth`        | TBD      | **Partial** | **Restore** (Kroz izolovane Flow komponente)        |

---

## Summary Judgement

**Da li su stare komponente imale više feature-a?**
Da, nesumnjivo. Stari sistem je po svojoj širini i mogućnostima bio izuzetno blizu full-stack framework-a "batteries
included" tipa. Sadržao je hiljade linija koda za stvari poput manipulacije vremenom (Carbon), kolekcija, renderovanja
šablona (Blade), i debagovanja.

**Komponente koje su izgubile najviše funkcionalnosti:**
Mail, Queues/Jobs, Broadcasting, Dates (Carbon), I18N, Testing Helpers (Fakes), i napredni Debug ekrani. Ovi domeni su
trenutno potpuno uklonjeni.

**Komponente koje su postale arhitektonski čistije, ali znatno slabije (weaker):**
Database, Persistence, Auth, Console, Validation, View. Preživele su tranziciju, ali su im odstranjene "magične" moći u
korist jasnih granica (boundary-ja) i strict-type koda.

**Feature-i koje MORAMO vratiti (Restore):**

- Napredne opcije `Container`-a (ownership metadata, slice manifests).
- Jasna implementacija `Persistence` sloja (UnitOfWork, Repository patern) u odnosu na sirovi Database query.
- Radne strukture nad podacima (`Data` kolekcije i `Text` helperi) bez kojih je razvoj poslovne logike previše
  mukotrpan.
- Ciklusi u `Auth`-u.

**Feature-i koji MOGU ostati obrisani (Drop):**

- God-objekti i Facade patterni koji maskiraju odakle zavisnosti dolaze.
- Ogromni error ekrani (Ignition/Whoops) u korist čistijih sistemskih logova.
- Ugrađeni Queue/Job engine pun drajvera (bolje je osloniti se na event loop / runtime adaptere iz
  `framework/System/Capabilities/Runtime`).

## Suggested Restoration Order (Preporučeni redosled vraćanja)

1. **Container & Config** - Osigurati da Dependency Injection može da ponese napredne use-case zahteve framework-a.
2. **HTTP / Request / Router** - Stabilizacija životnog ciklusa.
3. **Data & Persistence** - Vraćanje čistih struktura podataka (Collections) i razdvajanje pisanja/čitanja baze (UoW,
   Repositories).
4. **Auth & Security** - Definisanje sigurnosnih polisa bez starih sprega.
5. **View & Text** - Minimalno vraćanje helpera neophodnih za rad.
