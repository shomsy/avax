# Capability Ownership Map

## Status

**Stage:** V5 Readiness — Capability Ownership Scan
**Date:** 2026-05-10
**Mode:** Standard

---

## 1. Purpose

This document maps every capability across all 74 AvaX components to identify:

- Who owns what capability
- Where duplicate capability names exist
- Where duplicate capability implementations may exist
- Where the "one capability = one owner" rule is satisfied or violated

---

## 2. Full Capability Inventory by Area

### 2.1 API Area (5 components)

| Component        | Capabilities | Flows | Public Surface | Notes                                           |
|------------------|-------------:|------:|---------------:|-------------------------------------------------|
| ApiBlueprint     |           10 |    12 |              6 | Well-developed API description component        |
| Contracts        |            5 |     4 |              1 | Uses concept word "Contracts" as component name |
| GraphQL          |            6 |     4 |              5 | GraphQL schema and execution                    |
| OpenAPI          |            6 |     3 |              4 | OpenAPI specification generation                |
| SchemaGeneration |            5 |     5 |              1 | Schema generation capabilities                  |

### 2.2 Application Area (11 components)

| Component    | Capabilities | Flows | Public Surface | Notes                                     |
|--------------|-------------:|------:|---------------:|-------------------------------------------|
| Cache        |            9 |     4 |              8 | Full cache platform                       |
| Config       |            7 |     2 |              3 | Configuration management                  |
| Container    |            9 |    12 |              3 | DI container platform                     |
| DateTime     |            4 |     6 |              2 | Date/time handling                        |
| Facade       |            1 |     2 |              8 | Facade pattern                            |
| FeatureFlags |            1 |     2 |              2 | Feature flag management                   |
| Filesystem   |            2 |    11 |              2 | **OVERLAP**: Local paths/permissions only |
| Localization |            1 |     0 |              2 | i18n/l10n                                 |
| Pipeline     |            1 |     2 |              2 | Pipeline orchestration                    |
| Storage      |            3 |     8 |              1 | **OVERLAP**: Disks, paths, visibility     |
| Text         |            5 |     1 |              2 | Text processing                           |
| Validation   |            4 |     2 |              3 | Data validation                           |

### 2.3 CLI Area (2 components)

| Component | Capabilities | Flows | Public Surface | Notes                 |
|-----------|-------------:|------:|---------------:|-----------------------|
| Console   |            4 |     3 |              2 | CLI console           |
| System    |            0 |     0 |              0 | Empty component shell |

### 2.4 DataStack Area (4 components)

| Component    | Capabilities | Flows | Public Surface | Notes                        |
|--------------|-------------:|------:|---------------:|------------------------------|
| Data         |            7 |    14 |             20 | Data objects, DTOs, shape    |
| DataTransfer |            6 |     3 |              2 | Data transfer between layers |
| Database     |            9 |     6 |             11 | Database operations          |
| Persistence  |            9 |     9 |              4 | Persistence layer            |

### 2.5 DeveloperTools Area (6 components)

| Component      | Capabilities | Flows | Public Surface | Notes                 |
|----------------|-------------:|------:|---------------:|-----------------------|
| CodeGeneration |            1 |     2 |              1 | Code generation       |
| Diagnostics    |            1 |     2 |              4 | Diagnostics           |
| Documentation  |            0 |     0 |              0 | Empty component shell |
| DumpDebugger   |            1 |     2 |              1 | Dump debugging        |
| Dx             |            4 |     2 |              1 | Developer experience  |
| System         |            0 |     0 |              0 | Empty component shell |
| Testing        |            2 |     2 |              1 | Testing utilities     |

### 2.6 Foundation Area (1 component)

| Component             | Capabilities | Flows | Public Surface | Notes                          |
|-----------------------|-------------:|------:|---------------:|--------------------------------|
| CallableSerialization |            4 |     2 |              1 | Closure/callable serialization |

### 2.7 HTTP Area (13 components)

| Component          | Capabilities | Flows | Public Surface | Notes                                      |
|--------------------|-------------:|------:|---------------:|--------------------------------------------|
| AfterResponse      |            1 |     2 |              1 | Post-response handling                     |
| ApiVersioning      |            2 |     2 |              2 | API version management                     |
| Client             |            7 |     1 |              2 | HTTP client                                |
| ContentNegotiation |            2 |     2 |              6 | Content type negotiation                   |
| Context            |            1 |     2 |              3 | HTTP context                               |
| Dispatcher         |            2 |     1 |              1 | Request dispatch                           |
| Middleware         |            1 |     1 |              2 | Middleware pipeline                        |
| Request            |            5 |     3 |              2 | HTTP request handling                      |
| Response           |            4 |     2 |              4 | HTTP response handling                     |
| Router             |            4 |     6 |              4 | URL routing                                |
| SecureRequest      |            2 |     0 |              1 | Secure request resolution                  |
| Security           |            3 |     1 |              2 | HTTP security (CSRF, headers, signed URLs) |
| Session            |            7 |     9 |              4 | Session management                         |
| System             |            0 |     0 |              0 | Empty component shell                      |
| URI                |            1 |     2 |              1 | URI handling                               |

### 2.8 Identity Area (7 components)

| Component        | Capabilities | Flows | Public Surface | Notes                           |
|------------------|-------------:|------:|---------------:|---------------------------------|
| Access           |           11 |     1 |              2 | Access control                  |
| Auth             |            6 |     9 |              5 | Authentication                  |
| Credentials      |            2 |     2 |              1 | Credential management           |
| ExternalIdentity |            3 |     2 |              1 | External identity providers     |
| Security         |            1 |     1 |              2 | Identity security configuration |
| System           |            0 |     0 |              0 | Empty component shell           |
| Tenancy          |           10 |     2 |              1 | Multi-tenancy                   |
| Tokens           |            2 |     4 |              2 | Token management                |

### 2.9 Integration Area (1 component)

| Component     | Capabilities | Flows | Public Surface | Notes                              |
|---------------|-------------:|------:|---------------:|------------------------------------|
| ObjectStorage |            3 |     3 |              1 | External object storage (S3, etc.) |

### 2.10 Operations Area (17 components)

| Component           | Capabilities | Flows | Public Surface | Notes                                            |
|---------------------|-------------:|------:|---------------:|--------------------------------------------------|
| ApplicationWorkflow |           11 |     1 |              4 | Workflow orchestration                           |
| BackgroundProcesses |            4 |     4 |              1 | Background processing                            |
| Concurrency         |            7 |     5 |              2 | Concurrency control                              |
| Delivery            |            4 |     9 |              1 | Delivery pipeline                                |
| Events              |            3 |     2 |              2 | Event dispatch                                   |
| Filesystem          |            2 |     6 |              1 | **OVERLAP**: Adapters, drivers (forbidden names) |
| Logging             |            5 |     2 |              4 | Logging platform                                 |
| Mail                |            4 |     2 |              4 | Email delivery                                   |
| MemoryLifecycle     |            4 |     7 |              1 | Memory lifecycle management                      |
| MessageBus          |            6 |     6 |              4 | Message bus                                      |
| Notifications       |            1 |     0 |              1 | Notification delivery                            |
| Observability       |           11 |     9 |              1 | Full observability platform                      |
| Parallelism         |            4 |     2 |              2 | Parallel execution                               |
| Queue               |            5 |     3 |              6 | Queue management                                 |
| Realtime            |            3 |     5 |              1 | Real-time communication                          |
| Resilience          |           13 |    10 |              1 | Resilience patterns                              |
| RuntimeSupervision  |            5 |     5 |              1 | Runtime supervision                              |
| Scheduler           |            2 |     2 |              4 | Task scheduling                                  |
| System              |            0 |     0 |              0 | Empty component shell                            |
| Tasks               |            4 |     4 |              1 | Task management                                  |

### 2.11 Presentation Area (2 components)

| Component | Capabilities | Flows | Public Surface | Notes                 |
|-----------|-------------:|------:|---------------:|-----------------------|
| System    |            0 |     0 |              0 | Empty component shell |
| View      |            2 |     1 |              3 | View rendering        |

### 2.12 Security Area (7 components)

| Component      | Capabilities | Flows | Public Surface | Notes                                    |
|----------------|-------------:|------:|---------------:|------------------------------------------|
| Cryptography   |            1 |     4 |              1 | Cryptographic operations                 |
| DataProtection |            3 |     3 |              1 | Data protection                          |
| Hashing        |            1 |     2 |              1 | Password hashing                         |
| Privacy        |            3 |     3 |              1 | Data privacy (export, delete, retention) |
| Redaction      |            4 |     3 |              1 | Data redaction engine                    |
| Secrets        |            2 |     2 |              1 | Secrets management                       |
| System         |            0 |     0 |              0 | Empty component shell                    |

### 2.13 SystemDesign Area (4 entries — 0 real components)

| Component               | Capabilities | Flows | Public Surface | Notes                          |
|-------------------------|-------------:|------:|---------------:|--------------------------------|
| System                  |            0 |     0 |              0 | Empty shell                    |
| examples                |            0 |     0 |              0 | Not a component (examples dir) |
| reference-architectures |            0 |     0 |              0 | Not a component (docs dir)     |
| schemas                 |            0 |     0 |              0 | Not a component (schema dir)   |

**Note:** SystemDesign area contains 4 directories but none are real components. These are documentation/reference
directories that should not appear in component inventory.

---

## 3. Duplicate Capability Names

### 3.1 Exact Name Duplicates

| Capability Name | Owners                                                                                        | Assessment                                                                                                                                                                                                                                        |
|-----------------|-----------------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| **Health**      | Cache, ObjectStorage, Delivery, MemoryLifecycle, Observability, RuntimeSupervision (6 owners) | **LEGITIMATE** — each component checks its own health. This is a per-component capability, not a shared one.                                                                                                                                      |
| **Storage**     | Cache, Session (2 owners)                                                                     | **LEGITIMATE** — Cache storage vs Session storage are different domains.                                                                                                                                                                          |
| **Audit**       | Session, Observability, Security/System (3 owners)                                            | **NEEDS REVIEW** — Session audit vs Observability audit vs Security audit may overlap. Session audit is session-specific, Observability audit is general telemetry, Security audit is security-specific. Likely legitimate but need verification. |
| **Redaction**   | Security/Redaction (canonical)                                                                | **RESOLVED V5-01** — Logging and Observability migrated to use Security/Redaction::redactLog(). Duplicate implementations deleted.                                                                                                                |
| **DeadLetter**  | Resilience/DeadLetter (pattern), Queue/MemoryQueue (inline)                                   | **RESOLVED V5-01** — Empty Queue/DeadLetter directory removed. Queue's inline dead letter tracking is legitimate queue-specific behavior.                                                                                                         |
| **Encryption**  | Cryptography, Secrets (2 owners)                                                              | **NEEDS REVIEW** — Cryptography owns general encryption; Secrets owns secret encryption. May be legitimate if Secrets delegates to Cryptography.                                                                                                  |

### 3.2 Forbidden Folder Names in Capabilities

| Component                | Forbidden Capability Name | Assessment                                                                           |
|--------------------------|---------------------------|--------------------------------------------------------------------------------------|
| Operations/Filesystem    | `Adapters/`               | **VIOLATION** — "Adapters" is a concept word, not a capability name per AGENTS.md §9 |
| Operations/Filesystem    | `Drivers/`                | **VIOLATION** — "Drivers" is a concept word, not a capability name                   |
| Operations/Observability | `Drivers/`                | **VIOLATION** — "Drivers" is a concept word                                          |

---

## 4. Duplicate Capability Implementations

### 4.1 Filesystem (Application vs Operations)

| Aspect       | Application/Filesystem       | Operations/Filesystem       |
|--------------|------------------------------|-----------------------------|
| Capabilities | LocalPaths, LocalPermissions | Adapters, Drivers           |
| Flows        | 11                           | 6                           |
| Assessment   | Local filesystem operations  | Filesystem adapters/drivers |

**Finding:** These are different capabilities. Application/Filesystem owns local filesystem operations.
Operations/Filesystem owns filesystem adapters and drivers for external filesystems. However, the naming uses forbidden
concept words ("Adapters", "Drivers").

**Action Required:** Rename Operations/Filesystem capabilities to say what they do (e.g., `S3FilesystemAdapter` ->
`StoreInS3`, `LocalFilesystemDriver` -> `ReadWriteLocalFilesystem`).

### 4.2 Storage (Application vs Integration)

| Aspect       | Application/Storage            | Integration/ObjectStorage          |
|--------------|--------------------------------|------------------------------------|
| Capabilities | Disks, StoredPaths, Visibility | Health, Ports, StoreObjects        |
| Flows        | 8                              | 3                                  |
| Assessment   | Abstract storage abstraction   | External object storage (S3, etc.) |

**Finding:** These are legitimately different. Application/Storage is the abstract storage layer (disks, paths,
visibility). Integration/ObjectStorage is the external integration (S3, GCS, etc.). ObjectStorage should use Storage's
capabilities where applicable.

### 4.3 Logging (Operations/Logging vs Operations/Observability)

| Aspect       | Operations/Logging                                 | Operations/Observability                                                                                     |
|--------------|----------------------------------------------------|--------------------------------------------------------------------------------------------------------------|
| Capabilities | ErrorHandling, Logger, Redaction, Writers, Writing | Audit, Correlation, Drivers, Health, Logging, Logs, Metrics, MetricsCollector, Redaction, Telemetry, Tracing |
| Overlap      | **Redaction**, **Logging/Logs**                    | **Redaction**, **Logging/Logs**                                                                              |

**Finding:** Operations/Observability has `Logging` and `Logs` capabilities AND `Redaction`. Operations/Logging has
`Logger`, `Writers`, `Writing` AND `Redaction`. This is a clear overlap.

**Action Required:** Logging component should own logging. Observability should own telemetry, metrics, tracing,
correlation. Observability should use Logging for log emission. Redaction should be owned by Security/Redaction and used
by both.

### 4.4 Redaction (Security vs Operations) — **RESOLVED V5-01**

| Aspect       | Security/Redaction                                            | Operations/Logging      | Operations/Observability |
|--------------|---------------------------------------------------------------|-------------------------|--------------------------|
| Capabilities | DataClassifier, PatternMatcher, PolicyEngine, RedactionEngine | ~~Redaction~~ (deleted) | ~~Redaction~~ (deleted)  |

**Resolution:** Logging and Observability migrated to use `Security/Redaction::redactLog()`. Duplicate implementations (
SecretRedactor.php, RedactSensitiveData.php) deleted. Empty directories removed. See
`EVIDENCE/v5/redaction-ownership-closure.md`.

### 4.5 DeadLetter (Resilience vs Queue) — **RESOLVED V5-01**

| Aspect       | Operations/Resilience           | Operations/Queue      |
|--------------|---------------------------------|-----------------------|
| Capabilities | DeadLetter (pattern definition) | Inline in MemoryQueue |

**Resolution:** Empty `Queue/System/Capabilities/DeadLetter/` directory removed. Queue's inline dead letter tracking in
MemoryQueue is legitimate queue-specific behavior, not a duplicate capability. See
`EVIDENCE/v5/deadletter-ownership-closure.md`.

### 4.6 Encryption (Cryptography vs Secrets)

| Aspect       | Security/Cryptography | Security/Secrets             |
|--------------|-----------------------|------------------------------|
| Capabilities | Encryption (general)  | Encryption (secret-specific) |

**Finding:** Both are in the Security area. Cryptography owns general encryption. Secrets may need to encrypt secrets.
This may be legitimate if Secrets delegates to Cryptography.

**Action Required:** Verify that Secrets/Encryption delegates to Cryptography/Encryption. If it reimplements, fix.

### 4.7 Audit (Session vs Observability vs Security)

| Aspect       | HTTP/Session           | Operations/Observability | Security/System        |
|--------------|------------------------|--------------------------|------------------------|
| Capabilities | Audit (session events) | Audit (telemetry audit)  | Audit (security audit) |

**Finding:** These are likely legitimate domain-specific audit capabilities. Session audit tracks session events.
Observability audit tracks system events. Security audit tracks security events.

**Assessment:** Likely legitimate — different audit domains. But need to verify they use a common audit infrastructure
if one exists.

---

## 5. Empty Component Shells

The following components have 0 capabilities, 0 flows, and 0 public surface:

| Component | Action Required |
|-----------|----------------|----------------|
| CLI/System | Remove or promote to real component |
| DeveloperTools/Documentation | Remove or promote to real component |
| DeveloperTools/System | Remove or promote to real component |
| HTTP/System | Remove or promote to real component |
| Identity/System | Remove or promote to real component |
| Operations/System | Remove or promote to real component |
| Presentation/System | Remove or promote to real component |
| Security/System | Remove or promote to real component (may own Audit capability) |
| SystemDesign/System | Remove or promote to real component |
| SystemDesign/examples | Not a component — should be in examples/ root |
| SystemDesign/reference-architectures | Not a component — should be in docs/ |
| SystemDesign/schemas | Not a component — should be in docs/ or framework |

**Finding:** 12 empty shells. 3 are not components at all (SystemDesign directories). 9 are `System/` shells that may be
governance artifacts.

---

## 6. Ownership Summary

| Metric                                      |                                                       Count |
|---------------------------------------------|------------------------------------------------------------:|
| Total component directories                 |                                                          78 |
| Real components (non-empty)                 |                                                          66 |
| Empty component shells                      |                                                          12 |
| Not-component directories                   | 3 (SystemDesign/examples, reference-architectures, schemas) |
| Duplicate capability names (exact)          |                                                           6 |
| Duplicate names needing review              |                                                           4 |
| Forbidden concept words in capability names |                                                           3 |
| Confirmed dogfooding violations             |              0 (Redaction and DeadLetter resolved in V5-01) |
| Potential dogfooding violations             |                    3 (Filesystem naming, Encryption, Audit) |

---

## 7. Dogfooding Risk Assessment

| Risk                       | Severity           | Description                                                                                         |
|----------------------------|--------------------|-----------------------------------------------------------------------------------------------------|
| Redaction duplication      | **Resolved V5-01** | Security/Redaction is canonical. Logging and Observability now use Security/Redaction::redactLog(). |
| DeadLetter duplication     | **Resolved V5-01** | Empty Queue/DeadLetter dir removed. Queue inline tracking is legitimate.                            |
| Forbidden capability names | Medium             | Operations/Filesystem uses "Adapters" and "Drivers"; Observability uses "Drivers"                   |
| Encryption overlap         | Low                | Both in Security area; may delegate correctly                                                       |
| Audit overlap              | Low                | Different audit domains; may share infrastructure                                                   |
| Empty component shells     | Low                | Governance artifacts, not production risk                                                           |

---

## 8. Evidence Pointers

| Claim                | Evidence                                                                                                                            |
|----------------------|-------------------------------------------------------------------------------------------------------------------------------------|
| Component inventory  | `find components -mindepth 2 -maxdepth 2 -type d`                                                                                   |
| Capability counts    | Per-component `find System/Capabilities -mindepth 1 -maxdepth 1 -type d`                                                            |
| Duplicate Health     | `find components -path "*/Capabilities/Health" -type d` (6 results)                                                                 |
| Duplicate Redaction  | `find components -path "*/Capabilities/Redaction" -type d` (2 results)                                                              |
| Duplicate DeadLetter | `find components -path "*/Capabilities/DeadLetter" -type d` (2 results)                                                             |
| Duplicate Encryption | `find components -path "*/Capabilities/Encryption" -type d` (2 results)                                                             |
| Forbidden "Adapters" | `components/Operations/Filesystem/System/Capabilities/Adapters/`                                                                    |
| Forbidden "Drivers"  | `components/Operations/Filesystem/System/Capabilities/Drivers/`, `components/Operations/Observability/System/Capabilities/Drivers/` |
| Empty shells         | Per-component capability/flow/public count = 0                                                                                      |

---

## 9. Next Allowed Actions

1. Verify Redaction delegation (Security -> Logging, Observability)
2. Verify DeadLetter delegation (Resilience -> Queue)
3. Verify Encryption delegation (Cryptography -> Secrets)
4. Rename forbidden concept words in Operations/Filesystem capabilities
5. Clean up empty component shells or promote them
