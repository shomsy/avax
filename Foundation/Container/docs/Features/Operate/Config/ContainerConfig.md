# ContainerConfig

## Quick Summary

- This file defines the core configuration DTO for the dependency injection container's operational behavior.
- It exists to centralize settings like cache directories, debug flags, and performance limits in a type-safe, immutable
  structure.
- It removes the complexity of passing loose arrays around the container by acting as the single source of truth for
  runtime "tuning".

### For Humans: What This Means (Summary)

This is the **Spec Sheet** for your container. It tells the container how deep it can go when resolving classes (
Performance), where to store its brain (Cache), and how loud it should shout when something goes wrong (Debug).

## Terminology (MANDATORY, EXPANSIVE)

- **Cache Directory**: The physical folder on your server where the container saves its optimizations.
    - In this file: The `$cacheDir` property.
    - Why it matters: Saving things to a file usually makes the next page load much faster.
- **Strict Mode**: A security and correctness setting.
    - In this file: The `$strict` property.
    - Why it matters: If enabled, the container will throw errors instead of "guessing" when it's confused, preventing
      hidden bugs.
- **Resolution Depth**: How many "nested" dependencies the container allows.
    - In this file: The `$maxResolutionDepth` property.
    - Why it matters: It acts as a safety valve. If you have a loop (A needs B, B needs A), this limit stops the
      container from crashing your computer by running forever.
- **Cache Manager Integration**: A way to plug in an existing caching system.
    - In this file: The `$cacheManager` property.
    - Why it matters: It allows the container to use your app's existing Redis or File cache instead of building its
      own.

### For Humans: What This Means (Terminology)

These settings control the **Speed** (Cache), **Safety** (Strict), and **Protection** (Resolution Depth) of the
container.

## Think of It

Think of it as the **Settings Menu** in a high-end camera:

- **Cache Dir**: Where the "SD Card" is located.
- **Debug**: Pro-mode overlays that show your ISO and shutter speed while shooting.
- **Strict**: A setting that refuses to take a photo if it's out of focus.
- **Max Depth**: A safety timer that turns off the sensor if it gets too hot.

### For Humans: What This Means (Analogy)

The settings don't take the picture, but they determine how the camera behaves in different light and how much it helps
you vs. how much it gets out of your way.

## Story Example

You are working on a massive project with thousands of classes. One day, you accidentally create a "Circular Dependency"
where three classes keep asking for each other in a loop. Without `maxResolutionDepth`, your server would run out of
memory and crash. But because your `ContainerConfig` has a limit of 50, the container stops at the 50th level and sends
you a clear error message: "Stop! You have a loop!"

### For Humans: What This Means (Story)

It’s your safety net. It prevents small mistakes from becoming catastrophic crashes.

## For Dummies

Imagine you're high-performance engine tuner.

1. **Production Mode**: You lock the engine cap and optimize for fuel efficiency.
2. **Development Mode**: You open the hood and attach a bunch of sensors so you can see every spark.
3. **The Limits**: You set a "Redline" (Resolution Depth) so the engine doesn't explode if something goes wrong.

### For Humans: What This Means (Walkthrough)

If your app feels slow, check the `cacheDir`. If your app is behaving weirdly, check the `strict` setting.

## How It Works (Technical)

`ContainerConfig` is an immutable PHP object using `readonly` properties. It provides environment-aware presets (
`production()`, `development()`, `testing()`) that encapsulate best practices. For example, `testing()` intentionally
disables the file cache to ensure every test run is fresh and predictable. The `withCacheAndLogging` method allows for
the late-injection of infrastructure components into the config DTO before it is passed to the orchestrator.

### For Humans: What This Means (Technical)

It’s a "Set-and-Forget" object. You create it once, hand it to the container, and the container follows those rules for
as long as it lives. You can't change the rules half-way through.

## Architecture Role

- **Lives in**: `Features/Operate/Config`
- **Role**: Operational tuning.
- **Consumer**: Used by `ContainerBuilder`, `Engine`, and `Resolver`.

### For Humans: What This Means (Architecture)

It provides the "Rules of Engagement" for the container's internal algorithms.

## Methods

### Method: __construct(...)

#### Technical Explanation: __construct

Initializes the configuration with typed defaults. Uses strict types for all parameters to ensure data integrity.

#### For Humans: What This Means

The primary way to create a fully customized set of rules for your container.

### Method: production()

#### Technical Explanation: production

Factory for performance defaults: Debug OFF, Strict ON, Moderate resolution depth.

#### For Humans: What This Means

The "Golden Settings" for your live website—it's fast, secure, and quiet.

### Method: development()

#### Technical Explanation: development

Factory for developer defaults: Debug ON, Strict OFF (for flexibility), Deep resolution depth.

#### For Humans: What This Means

The "Noisy Mode" where you get lots of helpful info while you're coding.

### Method: testing()

#### Technical Explanation: testing

Factory for test suites: Cache DISABLED, Debug ON, Low resolution depth.

#### For Humans: What This Means

The "Pure Mode" for your unit tests—no old files or hidden state, just clean execution.

### Method: fromArray(array $data)

#### Technical Explanation: fromArray

Maps a loosely typed PHP array (like from a `config.php` file) into the typed DTO.

#### For Humans: What This Means

Lets you keep your settings in a simple file and "upgrades" them into a smart object when the app starts.

### Method: withCacheAndLogging(...)

#### Technical Explanation: withCacheAndLogging

A specific modifier for injecting infrastructure implementations without breaking immutability.

#### For Humans: What This Means

Lets you plug your application's real cache and logging tools into the configuration.

## Risks & Trade-offs

- **Memory Cache**: If you don't provide a `cacheDir`, the container might re-reflect classes on every request, which is
  slow.
- **Strictness**: Enabling `strict` mode might cause legacy code to throw errors, requiring you to fix old bindings.

### For Humans: What This Means (Risks)

Don't forget to set a `cacheDir` in production, or your site will be slow! And be careful with `strict` mode if you're
working on an old project with messy code.

## Related Files & Folders

- `BootstrapProfile.php`: Wraps this config.
- `ContainerBuilder.php`: The main person who reads this config.
- `CacheManager.php`: The tool this config points to.

### For Humans: What This Means (Relationships)

If the **Container** is an employee, this **Config** is their employee handbook.
