# 🏗️ Avax Container: The Universal Orchestrator

> **The engine that powers object lifecycle and dependency management in Avax.**

---

## 🌟 Why Avax Container?

In a complex application, managing when and how objects are created can become a nightmare. The Avax Container isn't
just a [Service Locator](https://en.wikipedia.org/wiki/Service_locator_pattern) — it's a sophisticated **Resolution
Engine** designed for:

- **Zero-Config Injection**: Use `#[Inject]` and let the container do the rest.
- **Deep Observability**: Every micro-second of object creation is recorded for telemetry.
- **Strict Guarding**: Advanced security policies decide who gets which service.
- **Enterprise Lifetimes**: Manage singletons, clones, and custom scopes with ease.

---

## 🧠 Mental Model: "Flow-First"

The most important thing to understand about the container is that **Everything is a Flow**. We don't just "new up"
classes; we move a service request through a sequence of specialized stations.

1. **Think**: Analyze the class structure.
2. **Act**: Perform discrete building actions.
3. **Resolve**: Orchestrate the pipeline steps.
4. **Cache**: Remember the result for efficiency.
5. **Observe**: Record the outcome.

---

## 🗺️ Navigation Map (The Explorer's Guide)

| Station          | Location                  | Mental Model     | Role                                      |
|:-----------------|:--------------------------|:-----------------|:------------------------------------------|
| **The Brain**    | `Core/`                   | The Orchestrator | Kernel, Pipeline, and main logic.         |
| **The Alatnica** | `Features/`               | The Skills       | Actions like Instantiate, Inject, Invoke. |
| **The Vault**    | `Features/Operate/Scope/` | The Memory       | Managing lifetimes and singletons.        |
| **The Guard**    | `Guard/`                  | The Security     | Deciding what is allowed and what isn't.  |
| **The Eyes**     | `Observe/`                | The Telemetry    | Performance recording and debugging.      |
| **The Bridge**   | `Providers/`              | The Integration  | Ready-to-use Service Providers.           |

---

## 📕 Educational Resources

- **[A-Z Glossary](./glossary.md)**: Every technical term (DI, IoC, PSR) explained.
- **[Resolution Story](./concepts/resolution-flow.md)**: A narrative walk-through of a service request.
- **[Architecture Blueprint](./architecture.md)**: The map of how all files work together.

---

## 🚫 What Does NOT Belong Here

This is a **machinery layer**. Keep your "ingredients" (Business Logic, DB Schemas, Views) elsewhere. This folder only
cares about **how to put them together**.

### For Humans: What This Means

This is the kitchen's architecture and the chef's expertise. The actual "Pasta" and "Sauce" recipes live in the
Application layer. We just provide the stove, the pans, and the skill to make the dish perfectly every time.

---
> **Tip**: If you're new here, start with the **[Resolution Story](./concepts/resolution-flow.md)**. It's the easiest
> way to understand the system.
