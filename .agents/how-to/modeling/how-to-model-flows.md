# How To Model Flows

## Status

**MANDATORY** — This document defines non-negotiable flow modeling and public API design rules for AvaX.

A rule without an explicit exception **MUST** be treated as mandatory.

Code review **MUST NOT** mark a scope GREEN when a mandatory rule is violated.

## Normative Language

The words **MUST**, **MUST NOT**, **REQUIRED**, **MANDATORY**, **SHOULD**, **SHOULD NOT**, **MAY**, **FORBIDDEN**, **BLOCKER**, **HIGH**, **MEDIUM**, **LOW** are governance keywords.

- **MUST / REQUIRED / MANDATORY**: non-negotiable rule.
- **MUST NOT / FORBIDDEN**: prohibited pattern.
- **SHOULD**: expected default unless documented exception exists.
- **SHOULD NOT**: discouraged pattern requiring justification.
- **MAY**: optional behavior.
- **BLOCKER**: violation prevents GREEN status.
- **HIGH**: must be fixed before production-complete unless explicitly accepted.
- **MEDIUM**: must be tracked and fixed or explicitly deferred.
- **LOW**: cleanup or documentation issue.

A rule without an explicit exception **MUST** be treated as mandatory.

Code review **MUST NOT** mark a scope GREEN when a mandatory rule is violated.

---

## 1. Purpose

This document defines how AvaX models Flows as use-case, command, and application-level behavior units.

It establishes that:

```text
public runtime APIs should be short, intention-revealing, and use-case oriented
string selectors are first-class developer experience, not second-class citizens
fluent APIs express intent at the correct abstraction level
```

A Flow is not a technical category.

A Flow is a named business or runtime action that completes a story.

---

## 2. Core Flow Rule

**Status:** MANDATORY
**Severity:** BLOCKER

A Flow in AvaX answers one question:

```text
What happens from start to finish?
```

A Flow owns one complete action.

A Flow is the AvaX representation of a use case, command, or application-level behavior.

A Flow is not:

```text
a technical category
a helper bucket
a service collection
a handler warehouse
a command/query grouping folder
```

A Flow folder is named as an action:

```text
GOOD:
  Flows/RegisterUser/
  Flows/HandleIncomingHttp/
  Flows/ChangePassword/
  Flows/PublishPendingEvent/
  Flows/VerifyRuntimeSafety/

BAD:
  Flows/UserFlow/
  Flows/RequestFlow/
  Flows/MigrationHandler/
  Flows/EventProcessor/
  Flows/Handlers/
  Flows/Services/
```

A Flow may contain:

```text
local value objects
local events
local decisions
small helpers that belong exclusively to that flow
```

Shared last.

Do not extract flow-local concepts into shared Capabilities too early.

---

## 3. Runtime API Rule

**Status:** MANDATORY
**Severity:** BLOCKER

Public runtime APIs must be:

```text
short
intention-revealing
use-case oriented
action-named
```

A public API method should read like what the caller wants to happen, not how the internals work.

```php
// GOOD — intention-revealing, use-case oriented
$auth->login($credentials);
$auth->logout($user);
$auth->changePassword($user, $old, $new);
$storage->put($path, $contents);
$queue->dispatch($job);
$cache->remember($key, $ttl, $callback);

// BAD — technical, plumbing-exposing, generic
$auth->execute(LoginCommand::fromArray($data));
$auth->handleAction(new LoginRequestHandler(...));
$storage->performWriteOperation($config);
$queue->process(Dispatchable::fromJob($job));
```

The caller expresses **what** it wants.

The boundary owns normalization, wrapping, defaults, and internal value-object construction.

This rule is the flow-level expression of the Intent-First Fluent API Rule in `how-to-clean-code.md`.

---

## 4. Fluent API Rule

**Status:** MANDATORY
**Severity:** HIGH

Fluent APIs are good when they express intent at the correct abstraction level.

Fluent APIs are bad when they expose internal mechanics or force chain plumbing.

```php
// GOOD — fluent, intention-revealing, natural
Auth::area('admin')->login($credentials);
Storage::driver('s3')->put($path, $contents);
Cache::store('redis')->remember($key, $ttl, $callback);
Queue::connection('sqs')->dispatch($job);

// BAD — chain length is not the problem, exposed mechanics is
Auth::getInstance()->getCapability('login')->getHandler()->execute(
    LoginCommand::fromArray($data)
);

// BAD — nested construction at call site violates intent-first
$this->auth->login(
    Credentials::fromArray(
        InputSanitizer::clean($request->input('credentials'))
    )
);
```

Fluent means **natural at the correct abstraction**, not "short chain length".

A three-step fluent chain that reads like intent is better than a one-step call that exposes internals.

A five-step fluent chain that reads like plumbing is worse than a single method that hides mechanics.

Fluent APIs must not:

```text
expose internal capability resolution
force call-site value-object construction
require knowledge of internal registry keys
depend on service locator patterns
hide expensive discovery at runtime
```

---

## 5. Flow Mapping Rule

**Status:** MANDATORY
**Severity:** BLOCKER

Every Flow must map to a real behavior that the system performs.

A Flow must answer:

```text
What triggers this flow?
What input does it accept?
What output or side effect does it produce?
What capabilities does it reuse?
What failure modes does it handle?
```

Flow mapping evidence should be visible in:

```text
the flow class name
the flow method signature
the flow's constructor dependencies
the flow's docblock or inline intent documentation
```

A Flow without a clear trigger, input, and output is not a Flow — it is a technical bucket.

---

## 6. Complex Named Flow Rule

**Status:** MANDATORY
**Severity:** HIGH

When a Flow involves multiple steps, phases, or sub-actions, the Flow name must still describe the complete action, not the internal phases.

```text
GOOD:
  Flows/OnboardNewUser/
  Flows/ProcessPaymentRefund/
  Flows/MigrateDatabaseSchema/

BAD:
  Flows/StepOneUserSetup/
  Flows/PhaseOnePayment/
  Flows/FirstMigrationStep/
```

Internal phases belong inside the Flow, not as sibling Flow folders.

If a sub-phase is reused by other Flows, extract it to a Capability.

---

## 7. Public String Selector Rule

**Status:** MANDATORY
**Severity:** BLOCKER

String selectors are **first-class developer experience** in AvaX.

They are not second-class citizens.
They are not a hack.
They are not a compromise.
They are an intentional design choice.

### 7.1 What Is a String Selector?

A string selector is a human-readable identifier passed to a public API that selects behavior, configuration, or a runtime instance.

```php
// String selectors in action
Auth::area('admin')->login($credentials);
Storage::driver('s3')->put($path, $contents);
Cache::store('redis')->get($key);
Queue::connection('sqs')->dispatch($job);
Logger::channel('payments')->info('payment received');
```

`'admin'`, `'s3'`, `'redis'`, `'sqs'`, `'payments'` are string selectors.

### 7.2 String Selectors Are First-Class

String selectors are the **primary** way operators and developers select runtime behavior in AvaX.

They are preferred over:

```text
enum values passed from distant code
configuration objects passed just to select a driver
factory classes instantiated at call sites
container tags resolved manually
```

A good public API accepts a string selector and normalizes internally.

### 7.3 String Selector Requirements

String selectors require:

```text
1. Registry/Catalog/RuntimePlan validation — the selector must be validated
   against a known set of registered options at registration or boot time.

2. Fail-fast on unknown selector — unknown selectors must fail during
   configuration, provider registration, compile, verify, or boot.
   They must not fail deep inside runtime business code.

3. Observable error message — the error must explain which selector was
   unknown, what selectors are available, and where the selector is configured.

4. No service locator resolution — the string selector must not trigger
   a service locator lookup or class_exists discovery at runtime.

5. Explicit registration — every string selector must be explicitly
   registered in a ServiceProvider, Configuration, or Builder.

6. Test coverage — contract tests must prove valid selectors work and
   invalid selectors fail with clear messages.
```

### 7.4 String Selector Forbidden Patterns

String selectors must not:

```text
be resolved via service locator pattern
use class_exists() at runtime
use dynamic class discovery
use string-to-class-name convention mapping without explicit registry
be silently ignored when unknown
fall back to a default without explicit configuration
be used as a raw array key for internal lookup tables exposed to callers
```

```php
// BAD — service locator via string selector
public function getDriver(string $name): DriverInterface
{
    $class = 'App\\Drivers\\' . ucfirst($name) . 'Driver';
    return new $class(); // dynamic class discovery from string
}

// BAD — silent fallback
public function getStore(string $name): StoreInterface
{
    return $this->stores[$name] ?? new NullStore(); // unknown selector silently swallowed
}

// GOOD — explicit registry, fail-fast
final readonly class StorageRegistry
{
    public function __construct(
        private array $drivers = [],
    ) {}

    public function driver(string $name): DriverInterface
    {
        if (! isset($this->drivers[$name])) {
            throw new UnknownDriverException(
                "Unknown storage driver '{$name}'. "
                . "Registered drivers: " . implode(', ', array_keys($this->drivers))
            );
        }

        return $this->drivers[$name];
    }
}
```

### 7.5 String Selector Ownership

The container **builds and resolves** the object graph.

The container is **NOT** the semantic selector registry.

String selector registries are explicit registries owned by:

```text
Component Capabilities
Configuration/Builders
Explicit registry classes
PublicSurface delegation targets
```

The container may resolve dependencies for registered selector implementations, but the selector-to-implementation mapping is owned by the component, not by the container's auto-wiring.

### 7.6 String Selector and Fluent API

String selectors naturally compose with fluent APIs:

```php
// Natural composition
Storage::driver('s3')->put($path, $contents);
Auth::area('admin')->login($credentials);
Cache::store('redis')->remember($key, $ttl, $callback);
```

The fluent chain selects, then acts.

This is the intended AvaX pattern for runtime APIs.

---

## 8. Flow Method Naming Rule

**Status:** MANDATORY
**Severity:** HIGH

Flow method names must use domain verbs, not generic execution words.

```text
GOOD:
  register()
  login()
  logout()
  changePassword()
  dispatch()
  publish()
  verify()
  migrate()
  handleIncomingHttp()

BAD:
  execute()
  handle()
  process()
  run()
  perform()
  do()
  dispatchAction()
  executeCommand()
```

A method named `execute()` or `handle()` reveals nothing about the domain.

A method named `login()` or `dispatch()` reveals exactly what happens.

Generic execution names are acceptable only when:

```text
the class name already contains the domain verb (HandleIncomingHttp->handle is acceptable)
the Flow is a generic dispatcher that delegates to named internal handlers
the pattern is mandated by an interface that the Flow implements
```

Even then, the public-facing method should prefer domain verbs when ergonomics matter.

---

## 9. Input Rule

**Status:** MANDATORY
**Severity:** HIGH

### 9.1 Single Input Object Rule

Flow methods should accept a single coherent input object when the input has multiple fields or requires validation.

```php
// GOOD — single input object for complex input
$auth->login(LoginRequest::fromArray([
    'email' => $email,
    'password' => $password,
    'remember' => $remember,
]));

// GOOD — natural inputs for simple cases
$auth->login($email, $password);
```

### 9.2 Variadic Parameter Rule

Variadic parameters (`...$args`) should not be used for domain inputs.

Variadic parameters are acceptable only for:

```text
middleware stacks
pipeline stages
tag lists
filter chains
```

Domain inputs should be named parameters or single input objects.

### 9.3 Input Normalization

Public APIs should accept the most natural input for the caller and normalize internally.

```php
// PublicSurface accepts natural input
$auth->login($request);

// Internally normalizes to domain object
final readonly class Login
{
    public function __invoke(mixed $input): Result
    {
        $credentials = $this->normalizeCredentials($input);
        return $this->capability->authenticate($credentials);
    }
}
```

---

## 10. Configuration Rule

**Status:** MANDATORY
**Severity:** BLOCKER

String selector configuration belongs in:

```text
System/Configuration/
System/Configuration/Builders/
ServiceProvider classes
Explicit registry classes
```

String selector configuration must not:

```text
occur in runtime execution code
use class_exists() gating
use dynamic discovery
use environment reads at runtime
use filesystem scans at runtime
```

```php
// GOOD — configuration-time registration
final readonly class RegisterStorageDrivers
{
    public function __invoke(Container $container): void
    {
        $container->tag('local', StorageDriver::class);
        $container->tag('s3', StorageDriver::class);
        $container->tag('gcs', StorageDriver::class);
    }
}

// BAD — runtime discovery
public function put(string $driver, string $path, string $contents): void
{
    if (class_exists('App\\Drivers\\' . ucfirst($driver) . 'Driver')) {
        $driverClass = 'App\\Drivers\\' . ucfirst($driver) . 'Driver';
        (new $driverClass())->put($path, $contents);
    }
}
```

---

## 11. Design Pattern Rule

**Status:** MANDATORY
**Severity:** HIGH

Flow modeling patterns must follow these principles:

```text
Flow = use case / command / application-level behavior
Capability = reusable behavior powering multiple flows
PublicSurface = stable entry point receiving and delegating
Configuration = assembly, registration, wiring
Foundation = tiny neutral primitives
```

Do not map flows to technical patterns:

```text
BAD — technical pattern mapping:
  Flows/Commands/
  Flows/Queries/
  Flows/Handlers/
  Flows/Processors/
  Flows/Services/

GOOD — behavioral mapping:
  Flows/RegisterUser/
  Flows/ReadUserProfile/
  Flows/ChangePassword/
  Flows/PublishPendingEvent/
```

Technical patterns (Command, Query, Handler, Processor) describe **how** something is implemented.

Behavioral names describe **what** the system does.

AvaX prefers **what** over **how** at the Flow level.

---

## 12. PublicSurface Rule

**Status:** MANDATORY
**Severity:** BLOCKER

PublicSurface classes for Flows must:

```text
accept natural inputs
delegate to internal Flows or Capabilities
not own runtime machinery
not assemble object graphs
not instantiate dependencies other than simple value objects/DTOs
expose short, intention-revealing method names
accept string selectors where appropriate
```

```php
// GOOD — PublicSurface receives, delegates, accepts string selector
final readonly class Storage
{
    public static function driver(string $name): StorageDriver
    {
        return app(StorageRegistry::class)->driver($name);
    }
}

// BAD — PublicSurface assembles and owns machinery
final readonly class Storage
{
    public static function driver(string $name): StorageDriver
    {
        $builders = [
            's3' => BuildS3Driver::class,
            'local' => BuildLocalDriver::class,
        ];

        $builder = new $builders[$name]();
        return $builder->build();
    }
}
```

---

## 13. Classification Rules

### 13.1 BLOCKER Findings

The following are BLOCKER findings that prevent GREEN status:

```text
Flow named as a technical category (UserFlow, RequestHandler, EventProcessor)
Public API method named generically (execute, handle, process) when domain verb is available
String selector resolved via service locator or dynamic class discovery
String selector silently ignored or defaulted when unknown
PublicSurface owning runtime machinery or assembling object graphs
Flow without clear trigger, input, and output
```

### 13.2 HIGH Findings

The following are HIGH findings that must be fixed before production-complete:

```text
Flow method using variadic parameters for domain inputs
String selector without explicit registration
String selector without fail-fast validation
Fluent API exposing internal mechanics
Flow-local concept extracted to shared Capability prematurely
Configuration-time logic occurring in runtime execution code
```

### 13.3 MEDIUM Findings

The following are MEDIUM findings that must be tracked and fixed or explicitly deferred:

```text
String selector error message not explaining available options
Flow documentation missing or not explaining trigger/input/output
Public API accepting more natural input but not normalizing internally
```

### 13.4 LOW Findings

The following are LOW findings:

```text
Flow class over 300 lines without responsibility split consideration
String selector name not matching convention (kebab-case vs snake-case inconsistency)
Missing example usage in documentation
```

---

## 14. Component Examples

### 14.1 Auth/Identity

```text
System/
  PublicSurface/
    Identity.php          → login(), logout(), user(), guest(), check(), register(), changePassword()

  Flows/
    RegisterUser/
      RegisterUser.php    → register(RegisterRequest): Result
    Login/
      Login.php           → login(Credentials): AuthResult
    Logout/
      Logout.php          → logout(User): void
    ChangePassword/
      ChangePassword.php  → changePassword(User, OldPassword, NewPassword): Result
    VerifyCredentials/
      VerifyCredentials.php → verify(Credentials): VerifiedUser

  Capabilities/
    CredentialVerification/
    SessionManagement/
    TokenIssuance/
    PasswordHashing/
    UserProvisioning/

  Configuration/
    BuildAuthRuntime.php
    RegisterAuthDefaults.php
    AssembleAuthIdentityGraph.php
```

### 14.2 Tokens

```text
System/
  PublicSurface/
    Token.php             → issue(), revoke(), verify(), refresh()

  Flows/
    IssueToken/
      IssueToken.php      → issue(TokenRequest): Token
    RevokeToken/
      RevokeToken.php     → revoke(TokenId): void
    VerifyToken/
      VerifyToken.php     → verify(RawToken): TokenPayload
    RefreshToken/
      RefreshToken.php    → refresh(RefreshToken): TokenPair

  Capabilities/
    JwtSigning/
    TokenValidation/
    TokenStorage/
    RefreshRotation/

  Configuration/
    BuildTokenRuntime.php
    RegisterTokenStores.php
```

### 14.3 Access

```text
System/
  PublicSurface/
    Access.php            → check(), grant(), revoke(), elevate()

  Flows/
    CheckPermission/
      CheckPermission.php  → check(User, Permission): bool
    GrantPermission/
      GrantPermission.php  → grant(User, Permission): void
    RevokePermission/
      RevokePermission.php → revoke(User, Permission): void
    ElevateRole/
      ElevateRole.php      → elevate(User, Role, Duration): Elevation

  Capabilities/
    PolicyEvaluation/
    RoleResolution/
    PermissionCache/
    AdminElevation/

  Configuration/
    BuildAccessRuntime.php
    RegisterAccessPolicies.php
```

### 14.4 Storage

```text
System/
  PublicSurface/
    Storage.php           → driver($name), put(), get(), delete(), exists()

  Flows/
    PutObject/
      PutObject.php       → put(Path, Contents, Options): ObjectRef
    GetObject/
      GetObject.php       → get(Path): Contents
    DeleteObject/
      DeleteObject.php    → delete(Path): void
    CheckObjectExists/
      CheckObjectExists.php → exists(Path): bool

  Capabilities/
    S3ObjectStorage/
    LocalObjectStorage/
    GcsObjectStorage/
    ObjectValidation/
    PathNormalization/

  Configuration/
    BuildStorageRuntime.php
    RegisterStorageDrivers.php
    AssembleStorageRegistry.php
```

### 14.5 ExternalIdentity

```text
System/
  PublicSurface/
    ExternalIdentity.php  → provider($name), sync(), diagnose(), metadata()

  Flows/
    SyncIdentity/
      SyncIdentity.php    → sync(Identity, Provider): SyncResult
    DiagnoseIdentity/
      DiagnoseIdentity.php → diagnose(Identity): Diagnosis
    FetchProviderMetadata/
      FetchProviderMetadata.php → metadata(Provider): Metadata

  Capabilities/
    OAuthProvider/
    OidcProvider/
    FederationMetadata/
    IdentitySync/
    ScimProvisioning/
    Diagnostics/

  Configuration/
    BuildExternalIdentityRuntime.php
    RegisterExternalIdentityProviders.php
    AssembleAuthExternalIdentityGraph.php
```

---

## 15. Source Principles and Rationale

### 15.1 Why Flows Over UseCases

AvaX uses `Flows/` instead of `UseCases/` because:

```text
Flow implies a complete action from start to finish.
Use case is a DDD/application services term that invites technical categorization.
Flow names naturally read as verbs (RegisterUser, ChangePassword).
Use case names naturally read as nouns (RegisterUserUseCase, ChangePasswordUseCase).
AvaX prefers verb-first naming at the public boundary.
```

### 15.2 Why String Selectors Over Enums

String selectors are preferred over enum values at public boundaries because:

```text
Strings are the natural configuration language for operators.
Strings appear in config files, env vars, CLI arguments, and admin panels.
Enums require import statements and namespace awareness at call sites.
Strings compose naturally with fluent APIs (driver('s3'), store('redis')).
Enums force coupling between caller and enum definition location.
Strings can be validated against a registry at boot time with fail-fast.
Strings are the industry standard for driver/selector/configuration names.
```

This does not mean strings everywhere.

Internally, validated string selectors may be wrapped in value objects:

```php
// Public API accepts string
$storage->driver('s3')->put($path, $contents);

// Internal capability receives value object
final readonly class PutObject
{
    public function __invoke(DriverName $name, Path $path, Contents $contents): ObjectRef
    {
        $driver = $this->registry->resolve($name);
        return $driver->store($path, $contents);
    }
}
```

### 15.3 Why Fluent APIs Over Long Method Names

Fluent APIs are preferred over long method names because:

```text
Fluent chains can be built incrementally (select, then configure, then act).
Long method names become unwieldy (loginToAdminAreaWithRememberMeAndMFA).
Fluent chains read like natural language (Auth::area('admin')->remember()->login()).
Fluent boundaries can normalize defaults at each step.
Fluent chains can be extended without breaking existing callers.
```

Fluent does not mean "chain for chain's sake".

A single method is fine when it is the natural call:

```php
// Fine — single method is natural
$auth->login($credentials);
$cache->get($key);

// Better — fluent adds needed selection
Auth::area('admin')->login($credentials);
Cache::store('redis')->get($key);
Storage::driver('s3')->put($path, $contents);
```

### 15.4 Why Domain Verbs Over Generic execute()

Domain verbs are preferred over generic `execute()` because:

```text
Domain verbs reveal intent at the call site.
execute() requires reading the class name or implementation to understand behavior.
login() is self-documenting.
execute() is self-concealing.
Domain verbs enable IDE autocomplete to reveal available actions.
execute() appears on every command/handler, providing no differentiation.
Domain verbs support the Intent-First Fluent API rule.
```

### 15.5 Why Single Input Object Over Variadic

Single input objects are preferred for complex inputs because:

```text
Named fields are self-documenting.
Variadic parameters hide field meaning ($a, $b, $c vs Input { a, b, c }).
Input objects can carry validation logic.
Variadic parameters cannot carry validation or invariants.
Input objects evolve without breaking method signatures.
Variadic parameter changes are breaking changes.
```

Simple inputs are fine as individual parameters:

```php
// Fine — simple, obvious inputs
$auth->login($email, $password);

// Better — complex input with validation, options, metadata
$auth->register(RegisterRequest::fromArray([
    'email' => $email,
    'password' => $password,
    'name' => $name,
    'consent' => $consent,
    'referrer' => $referrer,
]));
```

### 15.6 Why Configuration-Time Over Runtime Selection

Configuration-time registration is preferred over runtime discovery because:

```text
Failures happen at boot, not during request handling.
Object graphs are built once, not per request.
Dependencies are explicit, not discovered.
Testing is easier — registries can be seeded with known values.
Performance is better — no reflection, class_exists, or filesystem scans.
Security is stronger — no dynamic class loading from user-controlled strings.
```

---

## 16. Relationship to Other Governance Documents

This document is the flow-level expression of rules defined in:

```text
how-to-clean-code.md §5.4.1
  Intent-First Fluent API Rule
  Call sites express what, not how
  Boundaries normalize internally

how-to-design-components.md §6.3-6.4
  Flows/ contain end-to-end behavior
  Capabilities/ contain reusable behavior
  Flow names as actions, not categories

how-to-runtime-composition.md §1-2
  Runtime execution must not assemble dependencies
  String selector registries are built at configuration time
  Container is not the semantic selector registry

how-to-architecture.md
  Single Preferred Entry Rule
  Boundary Value Object Rule
  Horizontal Blindness / Outward-Only Dependency Law
```

When rules conflict, apply the precedence defined in AGENTS.md §3.

---

## Appendix A: Quick Reference

| Rule                          | Severity  | Enforcement                     |
|-------------------------------|-----------|---------------------------------|
| Flow named as action          | BLOCKER   | Review, folder structure check  |
| Public API intention-revealing| BLOCKER   | Review, call site audit         |
| String selector fail-fast     | BLOCKER   | Review, negative tests          |
| String selector no service loc| BLOCKER   | Review, grep, static analysis   |
| PublicSurface delegates only  | BLOCKER   | Review, composition leak check  |
| Domain verb method names      | HIGH      | Review, method name audit       |
| Single input for complex data | HIGH      | Review, signature audit         |
| Fluent API not exposing internals | HIGH  | Review, call site audit         |
| Config-time selector registry | HIGH      | Review, runtime composition check |
| Error message quality         | MEDIUM    | Review, negative test output    |
| Flow documentation            | MEDIUM    | Review, docs check              |
| Line count review             | LOW       | Review, static analysis         |

---

## Appendix B: Decision Test

When unsure whether something is a good Flow design, apply this test:

```text
1. Can a new developer understand what this does from the name alone?
2. Does the public method read like intent, not plumbing?
3. Are string selectors validated and fail-fast?
4. Does the PublicSurface receive and delegate?
5. Is assembly happening in Configuration?
6. Are internal mechanics hidden behind the boundary?
7. Does the Flow have a clear trigger, input, and output?
8. Are domain verbs used over generic execute/handle/process?
```

If any answer is "no", the design needs improvement.
