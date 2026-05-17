# Term
Contract

# Classification
Interface Definition Pattern

# Purpose
Defines a public API promise or interface that implementations must fulfill. A contract specifies the shape, methods, and behavioral guarantees that any conforming implementation must provide, without dictating how those guarantees are achieved.

# Why Allowed
Contract is the standard term for interfaces that define public API guarantees in PHP frameworks. Laravel uses Contracts extensively through its `laravel/contracts` package, where interfaces like `Cache`, `Queue`, `Session`, and `Mail` define the framework's service abstractions. PSR standards published by PHP-FIG are contracts — PSR-3 (Logger Interface), PSR-6/PSR-16 (Caching), PSR-7 (HTTP Message), PSR-11 (Container), PSR-14 (Event Dispatcher), and PSR-18 (HTTP Client) are all contracts that implementations agree to fulfill. Symfony uses interfaces with similar semantic meaning, though it prefers the "Interface" suffix. The term "Contract" communicates a stronger guarantee than "Interface" — it implies a formal agreement between the framework and its consumers, a stable public surface, and backward compatibility promises. A contract is not a generic interface — it defines a boundary that implementations must respect and that consumers can depend on.

# Allowed Contexts
- Interface definitions that represent public API guarantees
- Framework integration points where multiple implementations are supported
- Component boundaries where the public surface is explicitly defined
- PSR-compatible abstractions and interoperability layers
- Service abstractions that allow swapable implementations

# Forbidden Misuse
- As a generic bucket for unrelated interfaces that do not represent public API guarantees
- As a dumping ground for types, DTOs, or value objects that do not fit elsewhere
- Creating a Contracts/ folder that collects every interface in the system without discrimination
- Naming concrete classes with "Contract" suffix (contracts are interfaces, not implementations)
- Using "Contract" to describe implementation details rather than public promises

# Ecosystem References
- https://laravel.com/docs/contracts
- https://www.php-fig.org/psr/
- https://github.com/laravel/framework/blob/11.x/src/Illuminate/Contracts
- https://symfony.com/doc/current/contributing/code/interface.html

# Allowed Patterns
- CacheContract
- EventContract
- RepositoryContract
- SessionContract
- MailerContract
- QueueContract

# Forbidden Patterns
- Contracts/ (as folder for random interfaces without public API significance)
- ContractManager (managers are forbidden; contracts are interfaces, not managers)
- DataContract (too vague — what data? what guarantee?)
- Contract/Contract (recursive, meaningless)
