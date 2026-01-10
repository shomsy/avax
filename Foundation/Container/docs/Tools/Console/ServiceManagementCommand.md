# ServiceManagementCommand

## Quick Summary

Provides full CRUD-style management for service definitions from the CLI: listing, adding, updating, removing (soft), importing, exporting, and showing details. It exists to keep service inventory consistent without manually editing code or databases.

### For Humans: What This Means (Summary)

This is the command-line control panel for registering and maintaining services in the container.

## Terminology (MANDATORY, EXPANSIVE)

- **Service definition**: Metadata describing a service (class, lifetime, dependencies, tags, config).
- **Service repository**: Storage layer for service definitions with query and persistence methods.
- **Soft delete**: Marking a service inactive instead of removing it permanently.
- **Import/Export**: JSON-based bulk operations to move definitions between environments.
- **Lifetime**: Scope of a service (singleton, scoped, transient) defined by `ServiceLifetime` enum.

### For Humans: What This Means (Terms)

Definitions are the records of how services are built; the repository saves them; soft delete just turns them off; import/export moves them around; lifetime controls how long an instance lives.

## Think of It

It’s like a package manager for your services: you can list, add, update, remove, and sync them across environments.

### For Humans: What This Means (Think)

You manage services with commands the way you manage packages with a package manager.

## Story Example

A new caching service needs registration. `container:services add cache --class=App\Cache --lifetime=singleton --tags=infra` adds it. Later, an old service is retired with `remove`, and definitions are exported to production as JSON. The inventory stays accurate across environments.

### For Humans: What This Means (Story)

You add, adjust, or retire services with simple commands, then share the updated list with other environments.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means (Dummies)

If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

- Choose an action: list, add, update, remove, import, export, show.
- Provide required options (class, lifetime, tags, dependencies, file paths).
- Run the command; it validates input and updates the repository.
- Use JSON format for automation or table output for humans.

Common misconceptions: remove doesn’t hard-delete; JSON options must be valid; lifetime must match enum values.

## How It Works (Technical)

Configures arguments and options for action, service ID, class, lifetime, tags, dependencies, environment, config, file, and format. `execute` dispatches to helpers per action. Helpers interact with `ServiceDefinitionRepository` to fetch, create, update, import, export, or mark inactive definitions. Input parsing includes JSON decoding for config and comma parsing for tags/dependencies. Errors surface with console feedback.

### For Humans: What This Means (How)

It reads your command and routes to the right handler, which talks to the service repository to do the work, then prints results.

## Architecture Role

Lives in `Tools/Console` as an operational admin tool. Depends on service repository, service entities, lifetime enum, and Symfony console for IO. Complements inspection and diagnostics by letting you change definitions from the same toolkit.

### For Humans: What This Means (Role)

It’s the admin side of the console tools—after inspecting or diagnosing, you use this to fix or adjust services.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct

#### Technical Explanation (Construct)

Stores the `ServiceDefinitionRepository` used for all CRUD and bulk operations.

##### For Humans: What This Means (Construct)

Keeps a reference to where service definitions are saved and read.

##### Parameters (__construct)
- `ServiceDefinitionRepository $serviceRepo`: Repository for service definitions.

##### Returns (__construct)
- `void`

##### Throws (__construct)
- None.

##### When to Use It (__construct)
Instantiate before running any service management actions.

##### Common Mistakes (__construct)
Passing a repository that lacks required persistence adapters.

### Method: configure

#### Technical Explanation (Configure)

Defines arguments and options: action, service ID, class, lifetime, tags, dependencies, environment, config JSON, file, and format; sets description.

##### For Humans: What This Means (Configure)

Declares which flags and arguments the command understands.

##### Parameters (configure)
- None.

##### Returns (configure)
- `void`

##### Throws (configure)
- None.

##### When to Use It (configure)
Framework calls automatically; extend here when adding new actions.

##### Common Mistakes (configure)
Forgetting to add validation when introducing new options.

### Method: execute

#### Technical Explanation (Execute)

Parses action and delegates to the appropriate helper method, handling success/failure codes and error reporting.

##### For Humans: What This Means (Execute)

Runs the requested operation and reports success or failure.

##### Parameters (execute)
- `InputInterface $input`
- `OutputInterface $output`

##### Returns (execute)
- `int`: Status code.

##### Throws (execute)
- Exceptions from handlers are caught to return failure and display messages.

##### When to Use It (execute)
Invoked by Symfony when the command runs.

##### Common Mistakes (execute)
Calling with an unknown action results in an exception.

### Method: listServices

#### Technical Explanation (List)

Fetches all services, outputs in JSON or table form with key fields, and prints summary stats. Handles empty repository gracefully.

##### For Humans: What This Means (List)

Shows you the current service inventory and counts.

##### Parameters (listServices)
- `SymfonyStyle $io`
- `InputInterface $input`

##### Returns (listServices)
- `void`

##### Throws (listServices)
- `Exception` for repository errors.

##### When to Use It (listServices)
When you need an overview of registered services.

##### Common Mistakes (listServices)
Forgetting to request JSON when scripting automation.

### Method: addService

#### Technical Explanation (Add)

Validates required ID and class, parses lifetime, tags, dependencies, environment, and config JSON, creates a `ServiceDefinitionEntity`, and saves it active with timestamps.

##### For Humans: What This Means (Add)

Registers a new service with all its settings and dependencies.

##### Parameters (addService)
- `SymfonyStyle $io`
- `InputInterface $input`

##### Returns (addService)
- `void`

##### Throws (addService)
- `InvalidArgumentException` for missing/invalid inputs; repository exceptions.

##### When to Use It (addService)
When onboarding a new service into the container.

##### Common Mistakes (addService)
Supplying invalid JSON or missing class/lifetime; forgetting tags or dependencies where needed.

### Method: parseCommaSeparated

#### Technical Explanation (Parse)

Splits a comma-separated string into trimmed values, returning an empty array for null input.

##### For Humans: What This Means (Parse)

Turns tag or dependency lists from strings into arrays.

##### Parameters (parseCommaSeparated)
- `string|null $value`

##### Returns (parseCommaSeparated)
- `array<int,string>`

##### Throws (parseCommaSeparated)
- None.

##### When to Use It (parseCommaSeparated)
Internal helper for options like tags/dependencies.

##### Common Mistakes (parseCommaSeparated)
Passing already-parsed arrays; this expects a string.

### Method: updateService

#### Technical Explanation (Update)

Validates service ID, loads existing definition, applies selective updates from provided options (class, lifetime, tags, dependencies, environment, config), and saves the updated entity.

##### For Humans: What This Means (Update)

Edits specific fields of an existing service without recreating it.

##### Parameters (updateService)
- `SymfonyStyle $io`
- `InputInterface $input`
- `string|null $serviceId`

##### Returns (updateService)
- `void`

##### Throws (updateService)
- `InvalidArgumentException` for missing ID or bad input; `RuntimeException` if service missing.

##### When to Use It (updateService)
When you need to change a service’s class, scope, tags, deps, environment, or config.

##### Common Mistakes (updateService)
Providing no updates; skipping JSON validation for config changes.

### Method: removeService

#### Technical Explanation (Remove)

Validates service ID, loads the service, creates a new entity marked inactive (soft delete), and saves it.

##### For Humans: What This Means (Remove)

Turns off a service without deleting its history.

##### Parameters (removeService)
- `SymfonyStyle $io`
- `string|null $serviceId`

##### Returns (removeService)
- `void`

##### Throws (removeService)
- `InvalidArgumentException` for missing ID; `RuntimeException` if service not found.

##### When to Use It (removeService)
When retiring a service safely.

##### Common Mistakes (removeService)
Assuming it deletes data; it only marks inactive.

### Method: importServices

#### Technical Explanation (Import)

Validates file path, loads and decodes JSON, calls repository import, and reports imported/skipped counts plus errors.

##### For Humans: What This Means (Import)

Reads a JSON file of services and loads them into the repository, telling you what succeeded or failed.

##### Parameters (importServices)
- `SymfonyStyle $io`
- `InputInterface $input`

##### Returns (importServices)
- `void`

##### Throws (importServices)
- `InvalidArgumentException` or `RuntimeException` for missing files or invalid JSON.

##### When to Use It (importServices)
When syncing definitions from another environment or backup.

##### Common Mistakes (importServices)
Using malformed JSON or wrong file paths.

### Method: exportServices

#### Technical Explanation (Export)

Validates output file option, optionally filters by environment, exports definitions from the repository, encodes to pretty JSON, writes to disk, and reports counts.

##### For Humans: What This Means (Export)

Writes current service definitions to a JSON file you can share or back up.

##### Parameters (exportServices)
- `SymfonyStyle $io`
- `InputInterface $input`

##### Returns (exportServices)
- `void`

##### Throws (exportServices)
- `RuntimeException` on write failures; `DateMalformedStringException` via repository.

##### When to Use It (exportServices)
Before migrations, backups, or promoting definitions to other environments.

##### Common Mistakes (exportServices)
Writing to unwritable locations; forgetting to filter by environment when needed.

### Method: showService

#### Technical Explanation (Show)

Validates service ID, fetches the service, outputs details in JSON or table form including tags, dependencies, config, and timestamps.

##### For Humans: What This Means (Show)

Shows the full record for one service so you can verify its configuration.

##### Parameters (showService)
- `SymfonyStyle $io`
- `string|null $serviceId`
- `InputInterface $input`

##### Returns (showService)
- `void`

##### Throws (showService)
- `InvalidArgumentException` for missing ID; `RuntimeException` if not found.

##### When to Use It (showService)
When verifying a single service’s settings or debugging a definition.

##### Common Mistakes (showService)
Forgetting to request JSON for automation; misreading timestamps without timezone context.

## Risks, Trade-offs & Recommended Practices

- **Risk: Invalid JSON**. Bad config or import data can fail silently; validate and catch errors.
- **Risk: Mis-scoped lifetimes**. Wrong lifetime choices cause leaks or churn; align with usage patterns.
- **Trade-off: Soft delete vs cleanup**. Keeping inactive services preserves history but may clutter lists; periodically prune.
- **Practice: Use JSON format in CI**. Automate checks and imports with JSON output/input.
- **Practice: Audit changes**. Track adds/updates/removes for governance and rollback.

### For Humans: What This Means (Risks)

Check your JSON, choose lifetimes carefully, clean up inactive entries, script outputs for automation, and log changes for safety.

## Related Files & Folders

- `docs_md/Tools/Console/InspectCommand.md`: Inspect services you manage here.
- `docs_md/Tools/Console/ContainerInspectCommand.md`: Validate repository changes across the system.
- `docs_md/Tools/Console/index.md`: Folder overview and other commands.

### For Humans: What This Means (Related)

Manage services with this command, inspect them individually, and run the broader container inspection to ensure everything stays healthy.
