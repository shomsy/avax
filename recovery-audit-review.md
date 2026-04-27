# Recovery Audit Review & Next Steps

Da, ovo što si dobio je korisno, ali nije dovoljno precizno za **enterprise-grade decision**. Ima dobar osećaj, ali meša
tri različite stvari:

1. Feature koji stvarno pripada Avax-u
2. Vendor/library feature koji je možda samo bio ubačen u repo
3. Convenience/magic feature koji deluje moćno, ali kvari runtime-safe arhitekturu

Zaključak je uglavnom tačan: **stari sistem je bio širi i feature-rich**, a novi sistem je čistiji, ali trenutno slabiji
po “batteries included” osećaju. To se vidi iz spiska koji pominje kolekcije, Carbon/date handling, Blade/BladeOne,
Mail, Queues, ORM-style magiju, string helpere, I18N, debug ekran i console UI.

Ali bih hladno presekao: **ne znači da sve to treba vratiti.**

## Moj review ovog audit-a

Najveći plus: audit je dobro prepoznao da su najviše stradali `Data`, `Persistence`, `View`, `Console`, `Auth`,
`Database` i developer experience slojevi. U drugom fajlu je to čak lepo sabijeno u recovery matrix: `Container` je
partial, `Collections` lost, `Database` partial, `Persistence` partial, `Strings` lost, `Auth` partial.

Najveći minus: audit koristi reči kao “sigurno”, “definitivno”, “apsolutno”, ali bez prave Git matrice:
`old file -> old feature -> new owner -> new file -> status -> test coverage`
To još nije dokaz. To je dobar prvi forensic signal, ne finalna presuda.

Drugi minus: previše je fokusiran na “veliki fajl = moćna komponenta”. To nije uvek istina. `Carbon.php` od 300KB i
`BladeOne.php` od 154KB mogu značiti moć, ali mogu značiti i vendor bloat. Ako su to spoljne biblioteke ubačene u repo,
ne treba ih vraćati kao Avax core. Treba ih eventualno podržati kroz adaptere.

Treći minus: `Drop` za Queues/Jobs je prebrz zaključak. Ne bih vraćao stari queue engine sada, ali queue/job capability
je bitan za framework. Samo ne sada. To treba staviti u **Later / Optional Capability**, ne “Drop”.

Četvrti minus: `Drop` za Mail/Notifications isto nije finalno. Mail ne ide u core v0, ali kao component kasnije ima
smisla. Ne treba ga baciti zauvek.

Peti minus: “God-objekti i Facade patterni” nisu ista stvar. God-object treba ukloniti. Facade može ostati u
`PublicSurface/Facades/` ako je tanak i delegira. To je bitna razlika.

## Ispravljena klasifikacija

Ovako bih ja podelio izgubljene stvari:

**RESTORE NOW:**

- Data collections
- Arrhae / array access
- DataPath / typed readers
- Container advanced metadata if stvarno služi framework-u
- Database advanced query builder
- Transactions
- Schema/migrations
- Persistence UnitOfWork
- IdentityMap
- Repositories
- Hydration
- Auth policies/gates if already existed
- Request/Response/Router middleware pipeline

**RESTORE LATER:**

- Text helpers
- Console UI: tables, progress bars, questions
- View templating basics
- Debug diagnostics UI
- Testing fakes: EventFake, CacheFake, MailFake later if those components exist

**ADAPTER / EXTERNAL DEPENDENCY:**

- Carbon-style date library
- Blade/BladeOne-style templating
- Symfony Mailer-style mail
- Whoops/Ignition-style debug screen

**POSTPONE:**

- Queues/Jobs
- Broadcasting/WebSockets
- Full I18N
- Notifications

## Najvažnija korekcija

Ne vraćaj stare feature-e kao stare komponente. Vraćaj ih ovako:
`Old feature -> New owner -> Clean contract -> Tests -> Docs -> Compatibility bridge if needed`

Primer:
`Old Collection.php -> components/Data/System/PublicSurface/Collection.php -> components/Data/System/Capabilities/Collections/ -> tests/Unit/Components/Data/...`
Ne: `restore old Collection.php as one 50KB god file`

Isto za ORM:
`Old EntityManager / UnitOfWork / IdentityMap -> components/Persistence/System/Capabilities/`
Ne: `Database owns ORM again`

## Moj hladan zaključak

Da, stari sistem je bio “moćniji” po količini gotovih funkcija. Ali deo te moći je verovatno bio **copied/vendor bloat**
i **god-object convenience**.

Ne treba žaliti za starom arhitekturom. Treba izvući vredne feature-e i vratiti ih u novu strukturu.

Prioritet:

1. Data
2. Database
3. Persistence
4. Container
5. HTTP/Router/Middleware
6. Auth
7. Console/Text/View

Ne vraćaj Mail, Queues, Broadcasting, I18N i Carbon-style sloj sada. To je kasnije. Sada ti treba da novo jezgro ne bude
samo lepo, nego korisno.
