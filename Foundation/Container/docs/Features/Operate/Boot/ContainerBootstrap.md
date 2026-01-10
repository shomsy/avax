# ContainerBootstrap

## Quick Summary

- This file defines a higher-level “enterprise bootstrap orchestrator” for building a full container with integrated infrastructure and environment-specific settings.
- It exists so you can bootstrap a container in a profile-driven way (development, production, or custom config) while automatically handling database-backed service definitions and telemetry.
- It removes the complexity of low-level wiring by delegating to the `ContainerBuilder` while managing high-level application lifecycle steps.

### For Humans: What This Means (Summary)

This is the "General Manager" of your application's startup. It doesn't do the heavy lifting of building the engine itself (the `ContainerBuilder` does that), but it makes sure the right fuel is selected (profile), the seats are installed (database definitions), and the flight recorder is on (telemetry).

## Terminology (MANDATORY, EXPANSIVE)

- **Bootstrap Profile**: A bundled configuration object that tells the orchestrator how to behave in different environments.
  - In this file: Represented by the `BootstrapProfile` class.
  - Why it matters: It allows you to switch between "Development Mode" (lots of logs, slow) and "Production Mode" (fast, secure) with one line of code.
- **Container Builder**: The low-level architect that actually puts the container together.
  - In this file: Used via `\Avax\Container\Features\Core\ContainerBuilder`.
  - Why it matters: It separates the *definition* of services from the actual *execution* of the application.
- **Base Bindings**: Core objects that are registered into the container before anything else.
  - In this file: The profile and config objects are registered as singletons.
  - Why it matters: These objects are needed by almost every other service during the boot process.
- **Service Definition Repository**: A database-backed store that contains descriptions of your app's services.
  - In this file: Initialized if a `QueryBuilder` is provided.
  - Why it matters: It allows you to change how your app wires itself together without changing a single line of PHP code—just update a row in the database.
- **Telemetry & Monitoring**: The systems that watch how the container performs.
  - In this file: Initialized in `initializeMonitoring()`.
  - Why it matters: In a complex enterprise app, you need to know if the container is slowing down or if a specific service is failing to load.

### For Humans: What This Means (Terminology)

This class uses a few high-level concepts to make sure your app starts up professionally. It uses "Profiles" for environment settings, a "Builder" to do the assembly, "Repositories" to load service lists from a database, and "Telemetry" to make sure everything stays healthy.

## Think of It

Think of it like a **Professional Kitchen Designer**:

- **Profile**: Are we designing a home kitchen or a 5-star restaurant?
- **Builder**: The construction crew that actually installs the cabinets and plumbing.
- **Infrastructure**: Connecting the gas, water, and electricity (Logging, Caching).
- **Definitions**: The list of appliances ordered from the warehouse (Database).
- **Validation**: The final inspection before the health department arrives.

### For Humans: What This Means (Analogy)

The Designer (this class) coordinates everyone else to make sure that when the chef arrives, the kitchen is ready to go.

## Story Example

Imagine you are launching a new e-commerce site. In your `index.php`, you don't want to manually set up loggers and database connections. You simply call `ContainerBootstrap::production($db)->bootstrap()`. The orchestrator picks the production rules, tells the builder to start assembly, pulls your "PaymentService" and "CatalogService" definitions from the database, checks that they are valid, and starts recording metrics so you can see how fast the site loads.

### For Humans: What This Means (Story)

You get a production-ready system with one command instead of a 500-line setup script.

## For Dummies

This is the "Start" button for your entire application.

1. **Pick a Mode**: Do you want production speed or development debugging?
2. **Give it a Database**: (Optional) Tell it where to find your service list.
3. **Boot**: It runs a sequence of steps:
    - Sets up core settings.
    - Connects things like Cache and Logs.
    - Loads all your classes and services.
    - Checks for errors.
    - Starts the performance tracker.
4. **Result**: You get a powerful object (The Container) that is ready to run your app.

### For Humans: What This Means (Walkthrough)

It’s like a smart startup sequence for your computer—you just turn it on, and it handles the BIOS, the OS, and the Background Apps automatically.

## How It Works (Technical)

The `bootstrap()` method executes a strict linear pipeline:

1. **Builder Creation**: It instantiates a `ContainerBuilder`.
2. **configureBuilder()**: It registers the configuration and infrastructure (Cache/Log) into the builder.
3. **loadServiceDefinitions()**: It queries the database (if provided) and registers every service found into the builder.
4. **build()**: It triggers the builder to create the final immutable `Container`.
5. **Post-Build**: It runs `validateConfiguration()` to catch "dangling" dependencies and `initializeMonitoring()` to start the telemetry sink.

### For Humans: What This Means (Technical)

It follows a "Define-then-Execute" pattern. We define all the rules and services in the "Builder" first, then we "Seal" it into a final container that the app uses to run.

## Architecture Role

- **Lives in**: `Features/Operate/Boot`
- **Why?**: It acts as the bridge between "Library Code" (The Container internals) and "Application Code" (Your business logic).
- **Dependencies**: It relies on the `Core` builder, `Config` DTOs, and `Observe` telemetry systems.
- **Role**: Orchestrator. It doesn't "do" the logic; it "commands" other components to do their part in order.

### For Humans: What This Means (Architecture)

If the application fails to start or your services aren't loading, this class is your "Black Box" recorder and command center.

## Methods

### Method: __construct(BootstrapProfile $profile, QueryBuilder|null $queryBuilder = null)

#### Technical Explanation: __construct

Initializes the orchestrator with a static configuration profile and an optional query builder for database-backed operations.

#### For Humans: What This Means

It sets the "Ground Rules" (Profile) and gives the class a "Phone Line" to the database (QueryBuilder) if you have one.

### Method: fromConfigFile(string $configPath, QueryBuilder|null $queryBuilder = null)

#### Technical Explanation: fromConfigFile

A static factory that loads a PHP array from a file, maps it to a `BootstrapProfile`, and returns a new instance.

#### For Humans: What This Means

Allows you to keep your app settings in a simple `.php` file and load them easily.

### Method: development(QueryBuilder|null $queryBuilder = null)

#### Technical Explanation: development

Static factory that returns an instance pre-configured with the `development` profile defaults (high logging, no compilation).

#### For Humans: What This Means

The "Fast Start" for developers who want to see errors and work quickly.

### Method: production(QueryBuilder|null $queryBuilder = null)

#### Technical Explanation: production

Static factory that returns an instance pre-configured with the `production` profile defaults (compilation enabled, maximum performance).

#### For Humans: What This Means

The "Golden Rule" for your live servers—it’s optimized for speed and security.

### Method: bootstrap()

#### Technical Explanation: bootstrap

The main entry point. Orchestrates the 5-step lifecycle: configure builder, load definitions, build container, validate, and monitor.

#### For Humans: What This Means

The "Go" button. It runs the entire sequence and hands you back a working application container.

### Method: configureBuilder(ContainerBuilder $builder)

#### Technical Explanation: configureBuilder

Coordinates the registration of core configuration objects and infrastructure components into the builder's registrar.

#### For Humans: What This Means

Sets up the "Internal Tools" like logging and caching into the builder's memory.

### Method: bindInfrastructure(ContainerBuilder $builder)

#### Technical Explanation: bindInfrastructure

Initializes and binds singleton instances for `CacheManagerIntegration`, `LoggerFactoryIntegration`, and DB-backed repositories.

#### For Humans: What This Means

Plugs in the cables for the database, the logs, and the cache so the app can communicate with the outside world.

### Method: setupAliases(ContainerBuilder $builder)

#### Technical Explanation: setupAliases

Registers friendly string aliases (e.g., 'cache', 'logger') for complex class names to simplify resolution.

#### For Humans: What This Means

Creates "Nicknames" for services so you can just ask for 'cache' instead of a super long class name.

### Method: loadServiceDefinitions(ContainerBuilder $builder)

#### Technical Explanation: loadServiceDefinitions

Fetches active service definitions from the database based on the current environment and registers them with the builder.

#### For Humans: What This Means

Opens the "Inventory List" in the database and tells the builder what classes it needs to know about.

### Method: registerService(ContainerBuilder $builder, ServiceDefinitionEntity $service)

#### Technical Explanation: registerService

Maps a database record (`ServiceDefinitionEntity`) into the appropriate builder method (`singleton`, `scoped`, or `bind`) based on lifetime.

#### For Humans: What This Means

Translates a single row from your database into a real rule that the container understands.

### Method: validateConfiguration(Container $container)

#### Technical Explanation: validateConfiguration

Triggers the `ServiceValidator` on the final container to detect circular dependencies or missing mandatory parameters.

#### For Humans: What This Means

The "Final Check" to make sure no one forgot to plug in a dependency.

### Method: initializeMonitoring(Container $container)

#### Technical Explanation: initializeMonitoring

Enables telemetry collection and logs a 'bootstrap_completed' event to the configured metrics collector.

#### For Humans: What This Means

Turns on the "Dashcam" and "Performance Monitoring" so you can see how the app is behaving in the real world.

## Risks & Trade-offs

- **Performance**: Loading definitions from a database adds a small overhead at startup.
- **Complexity**: This orchestrator assumes a specific structure (Profiles, QueryBuilder). If your app is extremely simple, this might be overkill.
- **Dependency**: It ties your container startup to a database connection if you use repository-backed services.

### For Humans: What This Means (Risks)

It’s built for "Grown Up" enterprise apps. It might feel a bit heavy if you're just building a tiny script, but it’s a lifesaver for anything bigger.

## Related Files & Folders

- `BootstrapProfile.php`: The data object that controls this class.
- `ContainerBuilder.php`: The actual engine-builder used by this orchestrator.
- `Observe/Metrics/`: The system that receives the telemetry data.
- `Features/Define/Store/`: Where the database record models live.

### For Humans: What This Means (Relationships)

This class is the "Conductor" of the orchestra—it doesn't play the instruments, but it tells the "Profile", "Builder", and "Metrics" exactly when to start playing.
