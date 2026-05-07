# Console

The `Console` class is the entry point for the CLI application. It manages command registration, resolution, and the
main execution loop.

## Responsibilities

- **Command Registry**: Maintains an internal mapping of command names to `Command` instances.
- **Input Handling**: Shifts the script name from `argv` and manages built-in flags like `--help` and `--version`.
- **Command Execution**: Orchestrates the handoff between `ConsoleInput`, `ConsoleOutput`, and the resolved `Command`.

## Important Methods

### run(?array $argv = null): int

The main entry point.

- Parses the command name.
- Handles global flags.
- Resolves the command or prints the command list if not found.
- Triggers `$command->run()`.

### register(Command $command): self

Adds a command to the registry.

### list(): void

Prints the usage information and a list of all registered commands with their descriptions.
