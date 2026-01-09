# ContainerConfig

## Quick Summary
- This file defines the container’s runtime configuration (caching, debug/strict mode, telemetry flags, limits, namespaces).
- It exists so operational settings are structured, typed, and stable.
- It removes the complexity of “lots of scattered flags” by putting them into one immutable DTO.

### For Humans: What This Means
This is the container’s settings panel: where caches live, how strict it is, how deep resolution can go, and what namespaces are allowed.

## Terminology (MANDATORY, EXPANSIVE)
- **Cache directory**: Where the container stores runtime artifacts.
  - In this file: `cacheDir` and `prototypeCacheDir`.
  - Why it matters: permissions and performance depend on filesystem location.
- **Prototype cache**: Cached analysis results (Think-phase).
  - In this file: `prototypeCacheDir` specifically.
  - Why it matters: it speeds up reflection-heavy work.
- **Debug mode**: Extra verbosity for development.
  - In this file: `debug`.
  - Why it matters: helpful for debugging but costly and risky in production.
- **Strict mode**: Fail-fast behavior on undefined services/rules.
  - In this file: `strict`.
  - Why it matters: it prevents “surprise autowiring” and enforces registration discipline.
- **Allowed namespaces**: A whitelist restricting what autowiring is allowed to construct.
  - In this file: `allowedNamespaces`.
  - Why it matters: it’s a security boundary for what can be resolved.
- **Resolution depth**: Maximum dependency graph depth.
  - In this file: `maxResolutionDepth`.
  - Why it matters: prevents runaway recursion/circular resolution explosions.

### For Humans: What This Means
It’s the difference between “anything goes” dev mode and “safe and predictable” production mode.

## Think of It
Think of it like the rules of a factory:
- Where raw materials are stored (cache dirs).
- Safety rules (strict + namespace whitelist).
- Maximum assembly complexity (resolution depth).

### For Humans: What This Means
If you don’t define the rules, the factory becomes unpredictable and unsafe.

## Story Example
In production you set `compile: true` and point caches to `/var/cache/container`. In development you keep caches in `/tmp`, enable debug, and disable strict mode so you can iterate quickly.

### For Humans: What This Means
Same container, different priorities: speed of development vs safety/performance in production.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

Use `development()` locally, `production()` in prod, and `testing()` in CI. Use `fromArray()` when loading from a config file.

## How It Works (Technical)
This is a `final readonly` DTO with:
- constructor storing fields
- static `fromArray()` for external config loading
- static presets for environments
- `withCacheAndLogging()` returning `ContainerWithInfrastructure` to attach runtime dependencies (cache manager + logger factory).

### For Humans: What This Means
It’s data, not behavior—except a couple of “build from here” helpers.

## Architecture Role
- Why it lives here: it’s operational configuration, not service definition configuration.
- What depends on it: bootstrappers, cache integrations, policy enforcement.
- What it depends on: external cache/logging types only for integration helpers.
- System-level reasoning: typed config prevents “stringly-typed” production bugs.

### For Humans: What This Means
Typed config is boring—and boring is great in production.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation
Stores all runtime flags and directories immutably.

##### For Humans: What This Means
It locks in your container settings for the whole lifecycle.

##### Parameters
- `string $cacheDir`
- `string $prototypeCacheDir`
- `bool $debug`
- `bool $strict`
- `bool $telemetry`
- `int $maxResolutionDepth`
- `bool $compile`
- `array $allowedNamespaces`

##### Returns
- Returns nothing.

##### Throws
- No explicit exceptions.

##### When to Use It
- Prefer presets unless you’re building a custom profile.

##### Common Mistakes
- Using production paths in environments where they aren’t writable.

### Method: fromArray(array $config)

#### Technical Explanation
Creates a config instance from a raw associative array.

##### For Humans: What This Means
Load config from a file/env array.

##### Parameters
- `array $config`

##### Returns
- `self`

##### Throws
- No explicit exceptions here (but invalid types can still cause runtime issues later).

##### When to Use It
- When reading config files.

##### Common Mistakes
- Forgetting to set `cacheDir` and expecting it to derive everything.

### Method: development()

#### Technical Explanation
Returns development-optimized defaults.

##### For Humans: What This Means
Fast iteration and lots of diagnostics.

##### Returns
- `self`

### Method: production()

#### Technical Explanation
Returns production-optimized defaults.

##### For Humans: What This Means
Performance and safety first.

##### Returns
- `self`

### Method: testing()

#### Technical Explanation
Returns test-friendly defaults.

##### For Humans: What This Means
Deterministic behavior and isolated caches.

##### Returns
- `self`

### Method: withCacheAndLogging(CacheManager $cacheManager, LoggerFactory $loggerFactory)

#### Technical Explanation
Returns a composite object bundling this config with runtime infrastructure dependencies.

##### For Humans: What This Means
It attaches “real cache and logger objects” to an otherwise pure config.

##### Parameters
- `CacheManager $cacheManager`
- `LoggerFactory $loggerFactory`

##### Returns
- `ContainerWithInfrastructure`

##### Throws
- No explicit exceptions.

##### When to Use It
- When bootstrap needs both settings and runtime dependencies together.

##### Common Mistakes
- Treating the composite as a replacement for the config; it’s an add-on wrapper.

## Risks, Trade-offs & Recommended Practices
- Risk: `allowedNamespaces` misconfiguration.
  - Why it matters: too broad = security risk; too narrow = broken autowiring.
  - Design stance: whitelist narrowly in production.
  - Recommended practice: start narrow, expand intentionally, and test.

### For Humans: What This Means
Namespace whitelists are like guest lists: too open is unsafe, too strict blocks your friends.

## Related Files & Folders
- `docs_md/Features/Operate/Config/BootstrapProfile.md`: Bundles this config.
- `docs_md/Features/Think/Cache/CacheManagerIntegration.md`: Uses cache directory settings.

### For Humans: What This Means
Profile chooses the config; cache integration reads the directory choices.

