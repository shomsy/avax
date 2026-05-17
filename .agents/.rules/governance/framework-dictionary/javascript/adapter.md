# Term
Adapter

# Classification
JavaScript/Node — Boundary Pattern

# Purpose
A module that translates between two incompatible interfaces, allowing a component to interact with an external system, library, or protocol without coupling to its specific API.

# Why Allowed
The Adapter pattern is a canonical GoF pattern with a well-established presence in the JavaScript and Node.js ecosystems. It appears in database drivers (e.g., Sequelize adapters for MySQL, PostgreSQL, SQLite), ORM abstractions (Prisma engine adapters), HTTP client wrappers (Axios adapters for XHR, Node http, fetch), testing mock adapters (MSW, Nock), storage abstractions (ioredis-compatible adapters), and framework integration layers. A real adapter has a clear responsibility: it accepts calls in one interface shape and translates them to another. It is not a general-purpose wrapper or a place to stash unrelated code.

# Allowed Contexts
- Database driver adapters (ORM to database engine translation)
- HTTP client adapters (unified API over XHR, fetch, Node http)
- Storage adapters (key-value, file, S3 behind a common interface)
- Authentication adapters (OAuth, SAML, JWT behind a unified auth interface)
- Testing mock adapters (MSW handlers, Nock interceptors)
- Framework integration adapters (connecting external libraries into framework lifecycle)
- Message broker adapters (unified pub/sub over Redis, Kafka, RabbitMQ)

# Forbidden Misuse
- Naming a thin wrapper around a single function call an "adapter" when no interface translation occurs
- Creating an Adapters/ folder as a dumping ground for any code that touches external systems
- Calling a configuration loader an "adapter" when it performs no interface translation
- Using "adapter" to describe a component that owns full business logic rather than just translating interfaces

# Ecosystem References
- https://sequelize.org/docs/v/7/other-topics/other-databases/
- https://www.prisma.io/docs/orm/overview/databases/database-drivers
- https://axios-http.com/docs/adapter
- https://mswjs.io/docs/
- https://github.com/nock/nock

# Allowed Patterns
- postgresAdapter
- fetchHttpClientAdapter
- redisCacheAdapter
- oauthGoogleAdapter
- s3StorageAdapter
- kafkaMessageBrokerAdapter

# Forbidden Patterns
- Adapter (as a folder name)
- DatabaseAdapter (too broad — should specify which database)
- AdapterHelper
- UniversalAdapter (unless it genuinely adapts multiple specific interfaces)
- AdapterManager
