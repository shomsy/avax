# Term
Resolver

# Classification
JavaScript/Node — Lookup and Resolution Pattern

# Purpose
A function or module responsible for locating, computing, or selecting a value, module, route, or configuration from a set of candidates or a lookup strategy.

# Why Allowed
The term "resolver" is widely used across the JavaScript ecosystem with consistent semantics. Module bundlers use resolvers to determine how to locate imported files (webpack resolve, Vite resolve, esbuild resolve). GraphQL uses resolvers to compute field values from a schema. Dependency injection containers use resolvers to locate and instantiate services. URL and path resolvers appear in routing libraries, CSS processors (postcss-import), and build tooling. A resolver has a narrow responsibility: given an input (identifier, path, key, query), return the resolved target or report failure. It does not own the lifecycle of what it resolves.

# Allowed Contexts
- Module resolution in build tools (webpack, Vite, Rollup, esbuild)
- GraphQL field resolvers (Apollo, GraphQL Yoga, urql)
- Dependency injection and service location resolvers
- Route and URL resolvers in routing libraries
- DNS and hostname resolvers in network tooling
- Configuration value resolvers (environment, file, remote config merging)
- Import map and package resolution (import maps, Node.js resolution algorithm)

# Forbidden Misuse
- Naming a general query handler a "resolver" when it performs no lookup or selection
- Creating a Resolvers/ folder that contains full business logic handlers instead of pure resolution
- Calling a data transformer a "resolver" when it does not locate or select from candidates
- Using "resolver" to describe a component that both resolves and mutates state

# Ecosystem References
- https://webpack.js.org/configuration/resolve/
- https://graphql.org/learn/execution/
- https://vitejs.dev/config/shared-options.html#resolve
- https://esbuild.github.io/api/#resolve-extensions
- https://nodejs.org/api/modules.html#all-together

# Allowed Patterns
- moduleResolver
- graphqlUserResolver
- routeResolver
- dnsResolver
- packageResolver
- configValueResolver

# Forbidden Patterns
- Resolver (as a folder name)
- ResolverManager
- DataResolver (too vague — should specify what is being resolved)
- ResolverHelper
- GlobalResolver
