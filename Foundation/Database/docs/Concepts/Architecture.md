# Database Architecture and Lifecycle

## The Kernel

The `Kernel` is the central "Brain" of the database component. It manages the boot process and ensures all necessary modules are loaded and ready.

### Responsibilities

- **Bootstrapping**: Orchestrating the transition from configuration to live objects.
- **Module Discovery**: Using the `Manifest` to find available feature modules.
- **Graceful Shutdown**: Ensuring resources (like connections) are cleaned up properly when the process ends.

## Modules and the Registry

The system is built as a collection of independent **Modules** (e.g., QueryBuilder, Transactions).

- **ModuleRegistry**: Acts as the "Librarian", holding the instances of all active modules and managing their specific lifecycles.
- **Registration Phase**: Where modules define their services and add tools to the Dependency Injection container.
- **Boot Phase**: Where modules start their internal engines and become fully operational.

## The Manifest

The `Manifest` is a static registry of all available modules. It allows the system to remain decoupled—adding a new feature is as simple as adding its class to the manifest.

## Lifecycle Interface

Every module must implement the `LifecycleInterface`, which enforces three key phases:

1. `register()`: Setup and dependency declaration.
2. `boot()`: Activation and state initialization.
3. `shutdown()`: Resource cleanup.

## Why this Architecture?

- **Modularity**: You only "pay" for the features you use. Unused modules are never booted.
- **Testability**: Independent modules are easier to mock and test in isolation.
- **Consistency**: All database features follow the same "Born -> Active -> Dead" lifecycle, making the system predictable.
