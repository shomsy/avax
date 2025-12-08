🧙‍♂️ **Grim-terface v2.9 — Feature-Sliced Evolution Protocol**
💾 Mode: **Session Roadmap v2 (W)** → **Deep Explain (S)**
----------------------------------------------------------

# ⚙️ **SESSION ROADMAP V2 — “From Engine to Framework”**

Tvoj trenutni Session je već foundation-grade.
Sada ćemo ga podići na sledeći nivo — da postane **full domain subsystem**, spreman za enterprise integracije i DDD modularnost.

Plan je podeljen u **5 faza**, svaka Feature-Sliced i Pragmatic.

---

## 🧩 **FAZA 1 — CORE REFINEMENT**

🎯 **Cilj:** Očistiti “core” i pripremiti ga za evoluciju
📦 **Slojevi:** Contracts / Adapters / Actions

### Koraci

1. **Refaktoriši `AbstractSession` → `BaseSession`**

   * Ukloni “utility overload”: izdvoj `TTLBehavior`, `CryptoBehavior`, `FlashBehavior`.
   * `BaseSession` treba da zna samo: `start`, `set`, `get`, `delete`, `flush`, `id`.

2. **Dodaj “SessionContext” value object**

   ```php
   final class SessionContext {
       public function __construct(
           public readonly string $id,
           public readonly string $namespace,
           public readonly int $createdAt,
           public readonly int $lastActiveAt,
       ) {}
   }
   ```

   🔸 Umesto `meta` array–a, koristi ovaj kontekst za introspekciju i metrics.

3. **Uvedi `SessionCryptoInterface`**

   ```php
   interface SessionCryptoInterface {
       public function encrypt(string $plain): string;
       public function decrypt(string $cipher): string;
   }
   ```

   i u `BaseSession` koristi ga kroz dependency injection.

4. **Builder unifikacija**

   * `SessionBuilder` postaje ulazna tačka za sve konfiguracije (driver, ttl, secure, crypto).
   * Omogućava “builder chaining” za nove feature-e.

---

## 🧱 **FAZA 2 — TTL FEATURE SLICE**

🎯 **Cilj:** TTL management kao zaseban feature (Feature-Sliced princip)
📦 **Slojevi:** `/Features/TTL/Actions`, `/Features/TTL/Contracts`, `/Features/TTL/Adapters`

### Struktura

```
Session/
└── Features/
    └── TTL/
        ├── Actions/
        │   ├── TouchTTL.php
        │   ├── ExtendTTL.php
        │   └── ExpireData.php
        ├── Contracts/
        │   └── TTLManagerInterface.php
        └── Adapters/
            └── InMemoryTTLManager.php
```

### Ključne metode

```php
interface TTLManagerInterface {
    public function touch(string $key, int $seconds): void;
    public function hasExpired(string $key): bool;
    public function cleanup(): void;
}
```

💡 `BaseSession` ne zna TTL mehaniku — poziva je kroz kompoziciju (port–adapter princip).

---

## 🧩 **FAZA 3 — POLICY SYSTEM**

🎯 **Cilj:** Uvesti sigurnosne i vremenske politike
📦 **Slojevi:** `/Features/Policy/Contracts`, `/Features/Policy/DSL`, `/Features/Policy/Adapters`

### Primer API-ja

```php
$session->policy()
    ->maxIdle(900)
    ->maxLifetime(3600)
    ->requireSecureTransport()
    ->disallowCrossAgent();
```

### Implementacija

* `SessionPolicyInterface` definiše pravila
* `PolicyEnforcer` proverava ih pri svakoj `set()` ili `get()`
* Greške bacaju `PolicyViolationException`

💡 Ovo ti daje **“session firewall”** direktno u domain sloju.

---

## 🧩 **FAZA 4 — EVENT SYSTEM (OBSERVABILITY)**

🎯 **Cilj:** Omogućiti audit, hooks i event-driven integracije
📦 **Slojevi:** `/Features/Events/Actions`, `/Features/Events/Contracts`, `/Features/Events/Adapters`

### Primer

```php
$session->on('expire', fn($ctx) => $logger->info("Session {$ctx->id} expired"));
```

### Arhitektura

* `SessionEventEmitterInterface` (core port)
* `EventRegistry` za callback-ove
* Dekorator `ObservableSessionDecorator`

💡 Daje mogućnost plugin integracija (telemetry, logging, analytics).

---

## 🧩 **FAZA 5 — PERSISTENCE & SNAPSHOTS**

🎯 **Cilj:** Uvesti snapshot sistem i persistence
📦 **Slojevi:** `/Features/Snapshot`, `/Adapters/PersistentStores`

### Primer API-ja

```php
$snapshot = $session->snapshot();
...
$session->restore($snapshot);
```

### Tip

* `SessionState` value object (`key`, `data`, `meta`)
* `PersistentStoreInterface` za long-term čuvanje (Redis, DB)
* Opcioni `VersionedSnapshotStore` (time-travel debugging)

💡 Omogućava “session rollback” i distributed state sharing.

---

# ✅ **SESSION EVOLUTION – SUMMARY**

| Faza | Naziv                  | Ključni efekat                   |
| ---- | ---------------------- | -------------------------------- |
| 1    | Core Refinement        | Manji, čvršći, crypto-ready base |
| 2    | TTL Feature Slice      | Modularni expiration sistem      |
| 3    | Policy System          | Security i compliance sloj       |
| 4    | Event System           | Observability i telemetry hooks  |
| 5    | Persistence & Snapshot | Rollback i distributed state     |

🚀 Nakon ovoga tvoj `Session` postaje **foundation-level subsystem** koji može samostalno živeti kao PSR-biblioteka, framework plugin, ili cloud-ready service.

---

# 📖 **STEP-BY-STEP EXPLAIN — Session Arhitektura**

## 🔹 1. Contract Layer

> “Šta sistem obećava da zna da radi”

* `SessionInterface` → osnovni API (`get`, `set`, `delete`, `flush`)
* `SessionStoreInterface` → storage abstraction
* `SessionCryptoInterface` → security port
* `TTLManagerInterface` → time abstraction
* `SessionPolicyInterface` → compliance rules

👉 *Ovaj sloj definiše granice domena.*

---

## 🔹 2. Adapters Layer

> “Kako domen priča sa spoljnim svetom”

Implementira portove:

* `NativeAdapter` (wraps $_SESSION)
* `RedisAdapter`, `ArrayAdapter`, `JwtAdapter`, itd.
* `OpenSSLAdapter` (crypto)
* `InMemoryTTLManager` (TTL kontrola)

💡 Ovo su **pluggable strategije**, menjaju se bez refaktorisanja domena.

---

## 🔹 3. Actions Layer

> “Šta sistem *radi* (verbs)”

Svaka radnja (use-case) ima svoju klasu:

* `Start`, `Invalidate`, `Remember`, `Flash`, `TouchTTL`, `ExpireData`

Prednost:

* svaka akcija se testira izolovano
* session postaje *skup ponašanja*, a ne “service”.

---

## 🔹 4. Features Layer

> “Mini domeni unutar domena”

Svaki feature ima svoj bounded context:

* TTL
* Crypto
* Policy
* Events
* Snapshots

💡 *FSD modularnost unutar DDD strukture* — doslovno mini moduli.

---

## 🔹 5. Builder & DSL Layer

> “Kako developer koristi engine”

`SessionBuilder` spaja sve slojeve:

```php
$session = (new SessionBuilder())
    ->driver('redis')
    ->crypto(OpenSSLAdapter::fromKey($key))
    ->ttl(3600)
    ->policy(fn($p) => $p->maxIdle(900)->secureOnly())
    ->start();
```

💡 Ovde živi “developer experience” — tvoj DSL.

---

## 🔹 6. Decorators Layer

> “Cross-cutting behavior”

* `LoggableSessionDecorator`
* `ObservableSessionDecorator`
* `EncryptedSessionDecorator`

Komponuju se kao Lego:

```php
$session = new ObservableSessionDecorator(
    new LoggableSessionDecorator(
        new EncryptedSessionDecorator($base)
    )
);
```

💡 Ovo čini Session **middleware-like**, modularan i elegantan.

---

## 🔹 7. Manager Layer

> “Orkestrator”

`SessionManager` može upravljati više sesija:

```php
$manager->forUser($id)->start();
$manager->forApiToken($token)->invalidate();
```

To omogućava multi-context (user/admin/system) sesije.

---

# 🧠 **Epiloška istina**

Tvoj Session nije “klasa za $_SESSION” —
to je **domain engine** koji:

* implementira DDD u praksi,
* koristi FSD modularnost,
* ima fluent DSL kao moderni frameworks,
* i može da posluži kao **state subsystem za bilo koji backend**.

U ovom momentu — ti ne pišeš framework.
Ti pišeš **jezgro jezika za session i state menadžment**.

---

Želiš da pređemo odmah na **Faza 2 – TTL Feature Slice** i da ti generišem kompletne PHP fajlove (`TTLManagerInterface`, `TouchTTL`, `InMemoryTTLManager`, i kako se integriše u BaseSession`)?

🎯 Hotkeys

* **W ⚙️**: Da, idi na TTL Feature Slice implementaciju
* **A 🔐**: Radije generiši Crypto Layer (SessionCryptoInterface + OpenSSLAdapter)
* **S 🧱**: Nacrtaj Feature-Sliced mapu sa svim novim modulima (TTL, Crypto, Policy, Events)
* **D 🧩**: Generiši kompletan Session lifecycle DSL primer
