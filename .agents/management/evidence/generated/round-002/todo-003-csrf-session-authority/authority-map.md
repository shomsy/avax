# CSRF/Session Authority Map — TODO-003

Generated: 2026-05-20

## 1. Current CSRF Token Sources of Truth

### 1.1 CsrfToken (components/HTTP/Security/.../Csrf/CsrfToken.php)
- **Session key**: `_token`
- **Interface**: Static methods only (`token()`, `generate()`, `rotate()`)
- **Session access**: Direct `$_SESSION` reads/writes
- **Session start**: Calls `session_start()` if not active (except CLI)
- **Ownership**: None — bypasses Session component entirely
- **Risk**: HIGH — direct `$_SESSION` mutation, implicit session_start

### 1.2 CsrfTokens (components/HTTP/Security/.../Csrf/CsrfTokens.php)
- **Session key**: `_csrf_tokens` (plural — stores array with timestamps)
- **Interface**: DI-constructed (`Session $session`, `LoggerInterface $logger`)
- **Session access**: Via `Session` PublicSurface (`$this->session->get/put`)
- **Features**: Token expiration (30 min), max 5 tokens per session, token consumption on validation
- **Ownership**: Most complete CSRF authority — uses proper Session dependency
- **Risk**: LOW — correctly delegates to Session

### 1.3 CsrfTokenGenerator (components/HTTP/Security/.../Csrf/CsrfTokenGenerator.php)
- **Session key**: `_csrf_token` (singular)
- **Interface**: No constructor DI; direct `$_SESSION` access
- **Session access**: Direct `$_SESSION` reads/writes
- **Session start**: Guards with `session_status() === PHP_SESSION_ACTIVE` — only writes if active
- **Ownership**: None — bypasses Session component
- **Risk**: HIGH — direct `$_SESSION` mutation, no session lifecycle awareness

### 1.4 CsrfVerifier (components/HTTP/Security/.../Csrf/CsrfVerifier.php)
- **Session key**: `_token` (same as CsrfToken)
- **Interface**: Static `verify(string $token, ?string $sessionToken)`
- **Session access**: Direct `$_SESSION['_token']` fallback
- **Risk**: MEDIUM — read-only but uses conflicting key

### 1.5 csrf_token() shortcut (components/HTTP/Security/.../PublicSurface/shortcuts.php)
- **Session key**: `_csrf_token` (same as CsrfTokenGenerator)
- **Interface**: Global function via `session()` helper
- **Session access**: Via `SessionInterface` (through `app()` container)
- **Risk**: MEDIUM — uses proper session abstraction but key conflicts with CsrfTokenGenerator

### 1.6 VerifyCsrfToken middleware (components/HTTP/Security/.../Flows/VerifyCsrfToken/VerifyCsrfToken.php)
- **Session key**: Does NOT read session directly — delegates to `CsrfTokens->validateToken()`
- **Token extraction**: Accepts `_token` or `_csrf_token` from request body
- **Risk**: LOW — correct delegation, but accepts multiple token names

### 1.7 Security PublicSurface (components/HTTP/Security/.../PublicSurface/Security.php)
- **Delegates to**: `CsrfTokens` only
- **Risk**: LOW — correct single delegation

## 2. Current Session Source of Truth

### 2.1 SessionScope (components/HTTP/Session/.../PublicSurface/SessionScope.php)
- **Role**: Internal session state holder
- **Session access**: Calls `session_start()`, reads `session_id()`, writes `$_SESSION = $this->data`
- **Storage**: Delegates persistence to `SessionStoreInterface`
- **Ownership**: True in-memory session authority
- **Risk**: MEDIUM — `$_SESSION = $this->data` in `sync()` overwrites entire superglobal

### 2.2 NativeSessionStore (components/HTTP/Session/.../Capabilities/Storage/NativeSessionStore.php)
- **Role**: Persistence layer for PHP native sessions
- **Session access**: Direct `$_SESSION` read/write; `session_start()`, `session_destroy()`, `setcookie()`
- **Ownership**: Storage authority — but overlaps with SessionScope's `sync()`
- **Risk**: HIGH — double-write: both SessionScope.sync() AND NativeSessionStore.write() modify `$_SESSION`

### 2.3 Session (components/HTTP/Session/.../PublicSurface/Session.php)
- **Role**: Public facade — delegates to SessionScope
- **Session access**: Via SessionScope only (no direct `$_SESSION`)
- **Ownership**: Public API authority
- **Risk**: LOW — correct delegation

### 2.4 Session shortcuts (components/HTTP/Session/.../PublicSurface/shortcuts.php)
- **session()**: Resolves `SessionInterface` from container
- **session_flash()**: Resolves `SessionInterface` from container
- **Risk**: LOW — proper delegation

## 3. Duplicate Token/Helper Paths

| # | Component | Session Key | Access Method | Can Start Session? |
|---|-----------|-------------|---------------|-------------------|
| 1 | CsrfToken | `_token` | Direct `$_SESSION` | YES |
| 2 | CsrfTokens | `_csrf_tokens` | Via Session PublicSurface | NO (delegates) |
| 3 | CsrfTokenGenerator | `_csrf_token` | Direct `$_SESSION` | NO (guards) |
| 4 | CsrfVerifier | `_token` | Direct `$_SESSION` fallback | NO (read-only) |
| 5 | csrf_token() shortcut | `_csrf_token` | Via SessionInterface | NO (delegates) |

**Four different session keys for CSRF tokens**: `_token`, `_csrf_token`, `_csrf_tokens`, `_token` (again).

## 4. Unsafe Behaviors

### BLOCKER: Direct `$_SESSION` mutation outside Session authority
- `CsrfToken::generate()` — writes `$_SESSION['_token']`
- `CsrfToken::rotate()` — writes `$_SESSION['_token']`
- `CsrfTokenGenerator::storeToken()` — writes `$_SESSION['_csrf_token']`
- `CsrfVerifier::verify()` — reads `$_SESSION['_token']`

### BLOCKER: Double session_start behavior
- `SessionScope::start()` calls `session_start()`
- `NativeSessionStore::ensureStarted()` calls `session_start()`
- `CsrfToken::generate()` calls `session_start()`

### BLOCKER: Conflicting token keys
- `_token` (CsrfToken, CsrfVerifier)
- `_csrf_token` (CsrfTokenGenerator, csrf_token() shortcut)
- `_csrf_tokens` (CsrfTokens — array model)

### HIGH: NativeSessionStore + SessionScope double-write
- `SessionScope::sync()` does `$_SESSION = $this->data`
- `NativeSessionStore::write()` does `$_SESSION = $data`
- These conflict when both are used together

### HIGH: Long-lived worker risk
- `CsrfToken` and `CsrfTokenGenerator` use static methods / no DI
- No session reset between requests in persistent runtimes
- `$_SESSION` superglobal persists across requests in FrankenPHP/RoadRunner

## 5. Smallest Safe Remediation Slice

### Decision: Consolidate to CsrfTokens as single CSRF authority

**Rationale**: CsrfTokens is the only CSRF class that:
1. Uses DI (depends on Session, not direct `$_SESSION`)
2. Has token expiration and consumption
3. Has logging/auditability
4. Is already used by VerifyCsrfToken middleware and Security PublicSurface

**Remediation plan**:

1. **CsrfToken.php**: Remove session mutation. Make it a thin wrapper that delegates to CsrfTokens, OR deprecate and remove. Given this is a bounded security fix, make it delegate to a passed CsrfTokens instance rather than duplicating logic.

   Actually, CsrfToken is static-only and has no DI path. The cleanest approach: **remove direct session access** and make it a pure token value class (no session I/O). Static `generate()` returns a token string without writing to session. `rotate()` is removed.

2. **CsrfTokenGenerator.php**: **Remove entirely or convert to thin delegation to CsrfTokens**. It duplicates CsrfTokens functionality with inferior implementation. Since the shortcut `csrf_token()` uses `_csrf_token` key (matching CsrfTokenGenerator), we need to align keys.

   Better approach: **CsrfTokenGenerator delegates to CsrfTokens internally**. It becomes a thin adapter that maps the `_csrf_token` key to the CsrfTokens model.

3. **CsrfVerifier.php**: **Remove direct `$_SESSION` read**. Accept session token as required parameter. Callers must fetch token from proper Session authority.

4. **csrf_token() shortcut**: Already uses SessionInterface. Change key from `_csrf_token` to use CsrfTokens via Security, or keep the key but ensure it's the canonical write path.

5. **SessionScope.sync() + NativeSessionStore double-write**: `NativeSessionStore` is the persistence layer. `SessionScope.sync()` should NOT write `$_SESSION` directly — it should only update in-memory state. The store's `write()` handles persistence.

   Fix: Remove `$_SESSION = $this->data` from `SessionScope::sync()`. The sync should only mark dirty; `save()` calls the store.

### Minimal slice:
1. CsrfToken: Remove session I/O; make it a pure value class
2. CsrfTokenGenerator: Delegate to CsrfTokens
3. CsrfVerifier: Remove `$_SESSION` fallback; require explicit session token
4. csrf_token() shortcut: Use CsrfTokens via Security or Session consistently
5. SessionScope.sync(): Remove `$_SESSION` write

This is the smallest slice that eliminates:
- Direct `$_SESSION` CSRF mutation outside Session authority
- Conflicting token generation paths
- Double session_start
- Double `$_SESSION` writes

## 6. Authority After Remediation

```
Session PublicSurface (Session.php)
  └── SessionScope (in-memory state, NO direct $_SESSION write)
        └── SessionStoreInterface (persistence)
              └── NativeSessionStore (PHP native $_SESSION persistence)

Security PublicSurface (Security.php)
  └── CsrfTokens (single CSRF authority, via Session)

CsrfToken: Pure value class (no session I/O)
CsrfTokenGenerator: Thin adapter → CsrfTokens
CsrfVerifier: Pure comparison (no $_SESSION access)
csrf_token() shortcut: Delegates via Security → CsrfTokens
```

One session lifecycle authority: **SessionScope** (backed by SessionStoreInterface).
One CSRF token authority: **CsrfTokens** (backed by Session).
No direct `$_SESSION` access outside NativeSessionStore.
No session_start outside SessionScope.
