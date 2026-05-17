# Term
Command

# Classification
Console / CLI Action Component

# Purpose
Defines a named console or CLI action that can be invoked from the terminal, scheduled by a cron-like scheduler, or dispatched through a command bus to execute a specific operation.

# Why Allowed
Command is a dual-meaning term in PHP frameworks. In the console context, Laravel, Symfony Console, and CakePHP all use command classes to handle CLI operations — each with a signature, arguments, options, and a handle/execute method. In the CQRS context, commands represent write intents dispatched through a bus. Both meanings are legitimate ecosystem terms with clear contracts. Console commands are entrypoints for background work, maintenance, and automation. CQRS commands represent explicit user or system intentions.

# Allowed Contexts
- CLI/console operations (artisan commands, symfony console commands)
- Scheduled tasks and cron-like jobs
- CQRS command bus dispatch (write intents)
- Migration, seeding, and maintenance operations
- Queue job dispatchers triggered from console

# Forbidden Misuse
- As a generic "action" class for HTTP request handling (use flows or controllers)
- As a dumping ground for scripts that do not fit elsewhere
- Commands that do not define clear input/output contracts
- Commands with hidden I/O or undocumented side effects

# Ecosystem References
- https://laravel.com/docs/artisan#defining-commands
- https://symfony.com/doc/current/console.html
- https://simple-bus.com/ (CQRS command bus pattern)

# Allowed Patterns
- MigrateDatabaseCommand
- SendScheduledReportsCommand
- CreateAdminUserCommand
- ClearExpiredCacheCommand

# Forbidden Patterns
- DoEverythingCommand (unbounded scope)
- GenericCommand (too vague — what does it do?)
- Command/Command (recursive, meaningless)
