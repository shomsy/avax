Aye aye, kapetane. Evo tvoje **osvežene, refaktorisane i realno ostvarive To-Do liste** za Routing sistem, sada u *
*verziji 2.0** — potpuno usklađenu sa tvojim **postojećim klasama**, bez viškova i nepotrebnih duplikata.

---

## ✅ **GEMINI ROUTING TODO – CLEAN VERSION (v2.0)**

### ✅ CONTEXT

Tvoj sistem već ima:

- `AccessControlService` → sve što ti treba za RBAC/permission logic
- `AuthorizeMiddleware` → već koristi `route:authorization` iz request-a
- `PermissionMiddleware`, `RoleMiddleware`, `GuardInterface`, `AuthenticationServiceInterface` ➤ sve postoji

---

## 🔥 ROUTING NEXT STEPS – REAL-LIFE TODO

---

### ✅ **1. Inject `authorization` u Request iz `RouteDefinition`**

💡 Ako ruta ima `$definition->authorization`, ubaci to u `Request`:

```php
$request = $request->withAttribute('route:authorization', $definition->authorization);
```

📍 Lokacija: `RoutePipeline::handle()`

---

### ✅ **2. Auto-dodaj `AuthorizeMiddleware` u `RoutePipeline`**

💡 Ako `RouteDefinition` ima `authorization`, automatski ubaci `AuthorizeMiddleware` u middleware stack:

```php
if ($definition->authorization !== null) {
    array_unshift($middleware, AuthorizeMiddleware::class);
}
```

📍 Lokacija: `RoutePipeline::create()` ili `through()`

---

### ✅ **3. Koristi `AccessControlService` umesto novog `AuthorizationService`**

💡 Već sadrži:

- `hasPermission()`
- `hasRole()`
- `canAccessRoute()` i slične metode (ako ih dodaš)

📍 Injektuj `AccessControlService` direktno u `AuthorizeMiddleware`.

---

### ✅ **4. Očisti `To-Do` listu od viška**

❌ NE TREBA ti više:

- `AuthorizationServiceInterface`
- `DefaultAuthorizationService`
- Custom `AuthorizeMiddleware`
- `authorization()` helper (osim ako baš želiš sugar syntax)

---

### ✅ **5. (Optional) Route Middleware Prioriteti**

💡 Omogući DSL:

```php
Route::middleware([
    'auth' => 10,
    'rateLimit' => 5,
])
```

📍 U `RoutePipeline::through()`:

- Sortiraj po vrednosti ako array ima `string => int` mapu
- Izvuci samo `keys` nakon `asort()`

---

### ✅ **6. (Optional) RouteStage za logovanje, tracing, itd.**

💡 Dodaj `RouteStage` interface:

```php
interface RouteStage {
    public function handle(Request $request, Closure $next): ResponseInterface;
}
```

📍 Podržano u `RoutePipeline::stages([])`

---

### ✅ **7. (Optional) PHP 8 Attributes `#[Route(...)]`**

💡 Za kasnije, kada budeš radio controller auto-scan i declarative routing.

---

## 🧩 KLASE / FAJLOVI KOJI SE KORISTE (POSTOJEĆI)

| Sloj / Namena        | Klasa                                        |
|----------------------|----------------------------------------------|
| ✅ Auth enforcement   | `AuthorizeMiddleware`                        |
| ✅ RBAC engine        | `AccessControlService`                       |
| ✅ Auth token/session | `AuthenticationServiceInterface`             |
| ✅ Auth check         | `auth()` helper                              |
| ✅ Permissions/roles  | `PermissionMiddleware`, `RoleMiddleware`     |
| ✅ Auth Identity      | `GuardInterface`, `SessionGuard`, `JwtGuard` |

---

## 🧠 SPREMNO ZA IMPLEMENTACIJU

- Tvoje rute već imaju `->authorize(...)`
- Imaš `RouteDefinition::authorization`
- Samo treba da ih povežeš u `RoutePipeline` uz `AuthorizeMiddleware`

---