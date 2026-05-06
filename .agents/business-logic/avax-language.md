# AvaX Language

This document defines AvaX terminology and mapping to filesystem.

## Core Terms

| Term          | Meaning                                         | Filesystem                              |
|---------------|-------------------------------------------------|-----------------------------------------|
| Flow          | One complete use case, action, or story         | `System/Flows/<ActionName>/`            |
| Capability    | Reusable muscle, mechanism, or platform feature | `System/Capabilities/<CapabilityName>/` |
| PublicSurface | Stable external entrypoint                      | `System/PublicSurface/`                 |
| Foundation    | Tiny neutral primitives                         | `System/Foundation/`                    |
| Configuration | Assembly, registration, wiring                  | `System/Configuration/`                 |

## Concept Terms (Not Folders)

These are concepts, not folders:

| Term               | Meaning                                                                                 |
|--------------------|-----------------------------------------------------------------------------------------|
| ExportedCapability | A decision, not a folder. A capability becomes exported when intentionally made stable. |
| InternalSystem     | A concept, not a folder. Represented by Flows, Capabilities, Configuration, Foundation. |
| Component          | Reusable platform muscle with canonical shape.                                          |
| Public API         | What external users may call. Lives in PublicSurface.                                   |

## Folder Decision

| If you want...        | Use...        | Not...                       |
|-----------------------|---------------|------------------------------|
| One action end-to-end | Flow          | Commands, Handlers, UseCases |
| Reusable behavior     | Capability    | Services, Managers, Helpers  |
| External entrypoint   | PublicSurface | Contracts, Adapters          |
| Tiny primitive        | Foundation    | Common, Utils                |
| Component assembly    | Configuration | Registries, Bootstrappers    |

## Naming Rules

- **Folder** = says flow or capability
- **Unit** = says responsibility
- **Function** = says exact action

## Platform Planes

| Plane         | Ownership                      |
|---------------|--------------------------------|
| Runtime       | Boot, lifecycle, request scope |
| Control       | Health, status, diagnostics    |
| Contract      | Public API, versioning         |
| Integration   | External infrastructure        |
| Reliability   | Retry, circuit, backoff        |
| Observability | Logs, metrics, traces          |
| Delivery      | Build, release, verification   |
| System-Design | Capacity, consistency          |

## Stage Boundaries

| Stage | Scope                                      |
|-------|--------------------------------------------|
| V1    | Kernel, boot, HTTP kernel, golden path     |
| V2    | Platform engines, integration, reliability |
| V3    | System-design, large-scale behavior        |

## Recovery Sources

| Source                | Status             |
|-----------------------|--------------------|
| CURRENT_TRUTH.md      | Current evidence   |
| EVIDENCE/EXECUTION.md | Active task        |
| Recovery reports      | Evidence from work |

---

This is the language of AvaX. Use it consistently.