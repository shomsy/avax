# 📕 Glossary of Enterprise Terms

> **Your encyclopedia for the architectural patterns and concepts used throughout the Avax Container.**

---

## 🏛️ Foundations & Patterns

### Dependency Injection (DI)

[External Reference](https://en.wikipedia.org/wiki/Dependency_injection)

- **Why it matters**: It enables **loose coupling** and makes your code easy to test.

### Inversion of Control (IoC)

[External Reference](https://en.wikipedia.org/wiki/Inversion_of_control)

- **Analogy**: "Don't call us, we'll call you."

### Service Locator

[External Reference](https://en.wikipedia.org/wiki/Service_locator_pattern)

A pattern where a class asks a container for its dependencies.

- **⚠️ Warning**: Used everywhere, it becomes an anti-pattern because it hides dependencies.

### Service Identifier

The unique string key (usually the class name or an interface string) used to find a service in the container.

### Resolution

The orchestrated process of building, configuring, and wiring an object instance.

---

## ⚙️ The Resolution Engine

### Resolution Flow

[The Story](./concepts/resolution-flow.md)

The end-to-end journey of a service request through the container's pipeline.

### Instantiation

The physical creation of a PHP object instance (the `new` keyword moment).

### Security Policies

Rules enforced by [Guards](./Guard/index.md) that determine if a service can be resolved in the current context.

### Circular Dependency

[External Reference](https://en.wikipedia.org/wiki/Circular_dependency)

- **Detection**: Our engine stops this before it causes a stack overflow.

### Reflection API

[Developer Documentation](https://www.php.net/manual/en/book.reflection.php)

- **Usage**: We use it once to analyze your classes, then cache the result.

### Service Prototype

A pre-calculated blueprint of a class's needs.

- **Benefit**: Speeds up object creation by 30-50% compared to raw reflection.

### Definition Store

The "Recipe Book" where all your service configurations are stored.

---

## 🧪 Lifetimes & Observation

### Singleton Pattern

[External Reference](https://en.wikipedia.org/wiki/Singleton_pattern)

### Resolution Scope

[Concepts: Scopes](./concepts/scopes.md)

### Telemetry Component

[Observability Docs](./Observe/index.md)

- **Metrics**: Includes build time, hit rate, and depth.

---
> **Tip**: This glossary is a living document. If you find a term that isn't here, let us know!
