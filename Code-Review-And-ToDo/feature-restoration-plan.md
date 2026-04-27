# Feature Restoration Plan

## 1. RESTORE NOW (High Priority)

Ovi feature-i su kritični za Avax v0. Moraju biti vraćeni sa čistim vlasništvom, jasnim ugovorima i striktnim testovima,
bez unošenja starog koda u obliku *God objekata*.

- **Data collections & Arrhae / array access** -> `components/Data`
- **DataPath / typed readers** -> `components/Data`
- **Container advanced metadata** (samo ako stvarno služi framework-u) -> `components/Container`
- **Database advanced query builder** -> `components/Database`
- **Database transactions & Schema/migrations** -> `components/Database`
- **Persistence UnitOfWork & IdentityMap** -> `components/Persistence`
- **Persistence Repositories & Hydration** -> `components/Persistence`
- **Request/Response/Router middleware pipeline** -> `components/HTTP`
- **Auth policies/gates** (ako su već postojali) -> `components/Auth`

## 2. RESTORE LATER (Medium Priority)

Ovi feature-i su korisni za Developer Experience, ali ne smeju blokirati izgradnju osnovne arhitekture.

- **Text helpers** -> `components/Text`
- **Console UI** (tables, progress bars, questions) -> `components/Console`
- **View templating basics** (jednostavan engine) -> `components/View`
- **Debug diagnostics UI** -> `components/DumpDebugger`
- **Testing fakes** (EventFake, CacheFake, MailFake) -> Dodati tek kada osnovne komponente budu stabilne.

## 3. REPLACE WITH ADAPTER / EXTERNAL DEPENDENCY

Ovi feature-i su preteški za održavanje u core-u i mirišu na *vendor bloat*. Treba definisati interfejse unutar Avax-a i
omogućiti korišćenje spoljnih biblioteka kroz adaptere.

- **Carbon-style date library** (Koristiti native PHP 8.4 Clock i DateTimeInterface, Carbon samo kao adapter).
- **Blade/BladeOne-style templating** (Avax nudi bazični renderer, a Blade prepustiti adapteru).
- **Symfony Mailer-style mail** (Mail feature nije core).
- **Whoops/Ignition-style debug screen**.

## 4. KEEP DROPPED

Feature-i i paterni koje svesno izbacujemo i ne vraćamo u Avax.

- God-objekti koji krše odgovornosti.
- Globalni Facade-i koji izvršavaju logiku (dozvoljeni su samo tanki Facade-i u `PublicSurface/Facades/`).

## 5. POSTPONE

Funkcionalnosti koje su bitne za napredne aplikacije, ali apsolutno nisu neophodne u Avax v0.

- **Queues/Jobs**
- **Broadcasting/WebSockets**
- **Full I18N (Lokalizacija)**
- **Notifications**

---
*Next steps: Draft ADRs for Collections (Data), Persistence separation, View engine, and Date strategy.*
