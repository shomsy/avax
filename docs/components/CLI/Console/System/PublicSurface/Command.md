# Command

The `Command` class is the abstract base for all CLI commands in Avax. It provides common functionality for
argument/option access and terminal output.

## Responsibilities

- **Signature Parsing**: Automatically extracts argument and option names from the `$signature` string.
- **Input Binding**: Maps positional arguments from the input to named parameters.
- **Validation**: Ensures all required arguments are provided before `handle()` is called.
- **Output Convenience**: Provides helper methods for writing info, errors, warnings, and comments.

## Important Methods

### run(ConsoleInput $consoleInput, ConsoleOutput $consoleOutput): int

The internal execution wrapper.

- Sets up input/output instances.
- Parses signature and binds named arguments.
- Validates required arguments.
- Calls `handle()`.

### handle(): int

The method that subclasses must implement to perform the actual command work.

### argument(string|int $key, mixed $default = null): mixed

Retrieves a positional argument by name or index.

### option(string $key, mixed $default = null): mixed

Retrieves an option value.
