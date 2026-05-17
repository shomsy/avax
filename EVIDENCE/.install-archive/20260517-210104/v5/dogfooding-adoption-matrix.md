# Dogfooding Adoption Matrix

## Status

**Stage:** V5 Readiness — Dogfooding Adoption Matrix
**Date:** 2026-05-10
**Mode:** Standard

---

## 1. Purpose

This matrix tracks which AvaX components are used by other AvaX components internally.

Per `how-to-dogfooding.md`: "If AvaX owns a capability in one component, no other component may reimplement that
capability locally."

---

## 2. Canonical Capability Owners

Per how-to-dogfooding.md §6:

| Capability                                    | Canonical Owner                  | Area        |
|-----------------------------------------------|----------------------------------|-------------|
| Local filesystem operations                   | Application/Filesystem           | Application |
| Disk/object abstraction                       | Application/Storage              | Application |
| Hydration and validation                      | DataStack/DataTransfer           | DataStack   |
| Request DTO lifecycle                         | HTTP/SecureRequest               | HTTP        |
| Route registration and matching               | HTTP/Router                      | HTTP        |
| Controller creation and dependency resolution | Application/Container            | Application |
| Callable payload safety                       | Foundation/CallableSerialization | Foundation  |
| Retry, timeout, circuit, fallback, bulkhead   | Operations/Resilience            | Operations  |
| Process-based execution                       | Operations/Parallelism           | Operations  |
| Cooperative task scheduling                   | Operations/Concurrency           | Operations  |
| Correlation, traces, metrics, logs, audit     | Operations/Observability         | Operations  |
| Query execution and transactions              | DataStack/Database               | DataStack   |
| Background job execution                      | Operations/Queue                 | Operations  |
| Outbox, inbox, envelopes, consumers           | Operations/MessageBus            | Operations  |
| Error classification and rendering            | Framework ErrorHandling          | Framework   |
| Data redaction                                | Security/Redaction               | Security    |

---

## 3. Adoption Matrix

### 3.1 Filesystem Adoption

| Consumer               | Uses Filesystem? | How                                             | Status                                                   |
|------------------------|------------------|-------------------------------------------------|----------------------------------------------------------|
| Application/Storage    | Yes              | LocalDisk -> Filesystem                         | GREEN                                                    |
| Operations/Filesystem  | Yes              | Imports Filesystem classes                      | GREEN (but capability names need fixing)                 |
| Application/Facade     | Yes              | Storage facade -> Filesystem                    | GREEN                                                    |
| Framework Runtime      | No               | Direct mkdir/file_put_contents in Server/Config | **RED** — should use Filesystem                          |
| PreCommit capabilities | No               | Direct file_get_contents/file_put_contents      | **YELLOW** — tooling-like but runs in production context |

**Adoption Rate:** 3/5 consumers use Filesystem correctly. 2 use raw file operations.

### 3.2 Storage Adoption

| Consumer                  | Uses Storage? | How                                                               | Status                                            |
|---------------------------|---------------|-------------------------------------------------------------------|---------------------------------------------------|
| Application/Facade        | Yes           | Storage facade                                                    | GREEN                                             |
| Integration/ObjectStorage | Partial       | Owns external object storage, does not import Application/Storage | **YELLOW** — may be legitimate (different domain) |
| Operations/Filesystem     | No            | Owns filesystem-level operations, not storage                     | GREEN (different domain)                          |

**Adoption Rate:** 1/3 direct consumers. ObjectStorage is separate domain.

### 3.3 Router Adoption

| Consumer                       | Uses Router? | How                             | Status |
|--------------------------------|--------------|---------------------------------|--------|
| Framework (HandleIncomingHttp) | Yes          | MatchHttpRoute -> Router        | GREEN  |
| API/SchemaGeneration           | Yes          | Reads route metadata            | GREEN  |
| Application/Facade             | Yes          | Route facade                    | GREEN  |
| HTTP System                    | Yes          | Multiple flows and capabilities | GREEN  |

**Adoption Rate:** 4/4 — full adoption.

### 3.4 Container Adoption

| Consumer                   | Uses Container? | How                                   | Status                          |
|----------------------------|-----------------|---------------------------------------|---------------------------------|
| Framework (RunApplication) | Partial         | RouteFacadeContainer (minimal PSR-11) | **YELLOW** — not full Container |
| Application/Container      | Owns            | —                                     | GREEN                           |

**Adoption Rate:** 1/2 — framework uses minimal container, not full Application/Container.

### 3.5 DataTransfer Adoption

| Consumer             | Uses DataTransfer? | How                                | Status                          |
|----------------------|--------------------|------------------------------------|---------------------------------|
| API/SchemaGeneration | Yes                | Reads DataObject shape, validation | GREEN                           |
| HTTP/SecureRequest   | Yes                | Validation context                 | GREEN                           |
| DataStack/Database   | Partial            | Uses DataStack/Data types          | **YELLOW** — needs verification |

**Adoption Rate:** 2/3 confirmed.

### 3.6 Resilience Adoption

| Consumer              | Uses Resilience?     | How                               | Status                                                             |
|-----------------------|----------------------|-----------------------------------|--------------------------------------------------------------------|
| HTTP/Client           | Yes                  | Request options, failure handling | GREEN                                                              |
| Operations/MessageBus | Yes                  | Consumer, PublishToOutbox         | GREEN                                                              |
| Operations/Queue      | No evidence found    | —                                 | **RED** — Queue should use Resilience for retry, dead letter       |
| Operations/Queue      | Has local DeadLetter | Local implementation              | **RED** — confirmed duplicate (see duplicate-capability-owners.md) |

**Adoption Rate:** 2/4 — Queue does not use Resilience.

### 3.7 Observability Adoption

| Consumer           | Uses Observability? | How                                                       | Status                                            |
|--------------------|---------------------|-----------------------------------------------------------|---------------------------------------------------|
| Application/Cache  | Yes                 | Distributed cache uses Observability                      | GREEN                                             |
| Operations/Logging | Partial             | Observability uses Logging, not vice versa                | **YELLOW** — Logging should emit to Observability |
| Framework Runtime  | Partial             | HandleRuntimeFailure uses Logging, not full Observability | **YELLOW**                                        |

**Adoption Rate:** 1/3 confirmed, 2 partial.

### 3.8 Queue Adoption

| Consumer               | Uses Queue? | How                                             | Status                                                |
|------------------------|-------------|-------------------------------------------------|-------------------------------------------------------|
| Operations/Mail        | Yes         | MailQueue                                       | GREEN                                                 |
| DeveloperTools/Testing | Yes         | QueueFake                                       | GREEN                                                 |
| Framework              | Partial     | RegisterQueueCommands, ApplicationBuilder       | GREEN                                                 |
| HTTP/AfterResponse     | Yes         | AfterResponse uses Queue                        | GREEN                                                 |
| DataStack/Data         | Partial     | Has own PriorityQueue and Queue data structures | **YELLOW** — these are data structures, not job queue |

**Adoption Rate:** 4/5 — DataStack/Data Queue is a data structure, not a job queue. Acceptable.

### 3.9 Logging Adoption

| Consumer                 | Uses Logging? | How                  | Status |
|--------------------------|---------------|----------------------|--------|
| Operations/Observability | Yes           | RecordLog -> Logging | GREEN  |
| Framework                | Partial       | HandleRuntimeFailure | GREEN  |

**Adoption Rate:** 2/2.

### 3.10 CallableSerialization Adoption

| Consumer               | Uses CallableSerialization? | How                           | Status                                                        |
|------------------------|-----------------------------|-------------------------------|---------------------------------------------------------------|
| Operations/Parallelism | Yes                         | SymfonyProcessParallelRuntime | GREEN                                                         |
| Operations/Queue       | No evidence found           | —                             | **RED** — Queue should use CallableSerialization for payloads |

**Adoption Rate:** 1/2 — Queue does not use CallableSerialization.

### 3.11 Redaction Adoption

| Consumer                 | Uses Security/Redaction? | How                                                           | Status                        |
|--------------------------|--------------------------|---------------------------------------------------------------|-------------------------------|
| Operations/Logging       | No                       | Has local Redaction capability                                | **RED** — confirmed duplicate |
| Operations/Observability | No                       | Has local Redaction capability                                | **RED** — confirmed duplicate |
| Security/Redaction       | Owns                     | DataClassifier, PatternMatcher, PolicyEngine, RedactionEngine | GREEN                         |

**Adoption Rate:** 0/2 consumers use canonical owner. Both reimplement locally.

---

## 4. Raw Operation Audit

### 4.1 Raw File Operations Outside Filesystem Owner

| Location                                                                         | Operation                                           | Assessment                                                     |
|----------------------------------------------------------------------------------|-----------------------------------------------------|----------------------------------------------------------------|
| `framework/System/Capabilities/Runtime/.../RunApplicationOnPhpBuiltInServer.php` | mkdir, file_put_contents, fclose                    | **YELLOW** — Dev server setup, acceptable for bootstrap        |
| `framework/System/Capabilities/Configuration/RegisterConfigCommands.php`         | mkdir, file_put_contents                            | **YELLOW** — Config file creation, should use Filesystem       |
| `framework/System/Capabilities/PreCommit/**`                                     | file_get_contents, file_put_contents, mkdir, fwrite | **YELLOW** — Pre-commit tooling, borderline production context |
| `framework/System/Capabilities/ExternalState/.../Redis.php`                      | serialize, unserialize                              | **RED** — Should use CallableSerialization                     |
| `components/Identity/Security/.../SecurityConfigurationStore.php`                | serialize                                           | **RED** — Should use CallableSerialization or Cryptography     |
| `components/Identity/Access/.../AuthorizationEngine.php`                         | md5(serialize())                                    | **RED** — Unsafe hash, should use proper hashing               |
| `components/Security/Cryptography/.../DecryptValue.php`                          | unserialize                                         | **YELLOW** — Cryptography component, may be legitimate         |
| `components/DataStack/Data/.../Set.php, Bag.php, etc.`                           | serialize                                           | **YELLOW** — Data structures, may need local serialization     |

### 4.2 Raw serialize/unserialize Outside CallableSerialization Owner

| Location                                                          | Operation             | Assessment                            |
|-------------------------------------------------------------------|-----------------------|---------------------------------------|
| `framework/System/Capabilities/ExternalState/.../Redis.php`       | serialize/unserialize | **RED**                               |
| `components/Identity/Security/.../SecurityConfigurationStore.php` | serialize             | **RED**                               |
| `components/Identity/Access/.../AuthorizationEngine.php`          | md5(serialize())      | **RED**                               |
| `components/DataStack/Data/.../data structures`                   | serialize             | **YELLOW** — Data structure internals |
| `components/Security/Cryptography/.../`                           | serialize/unserialize | **YELLOW** — Encryption component     |

---

## 5. Adoption Summary

| Capability            | Consumers Found | Using Canonical | Not Using |           Adoption Rate |
|-----------------------|----------------:|----------------:|----------:|------------------------:|
| Filesystem            |               5 |               3 |         2 |                     60% |
| Storage               |               3 |               1 |         0 | 33% (1 separate domain) |
| Router                |               4 |               4 |         0 |                    100% |
| Container             |               2 |               1 |         0 |         50% (1 minimal) |
| DataTransfer          |               3 |               2 |         0 |                     67% |
| Resilience            |               4 |               2 |         1 |                     50% |
| Observability         |               3 |               1 |         0 |         33% (2 partial) |
| Queue                 |               5 |               4 |         0 |  80% (1 data structure) |
| Logging               |               2 |               2 |         0 |                    100% |
| CallableSerialization |               2 |               1 |         1 |                     50% |
| Redaction             |               3 |               0 |         2 |                      0% |

---

## 6. Dogfooding Health Score

| Area                  | Score | Status |
|-----------------------|-------|--------|
| Router                | 100%  | GREEN  |
| Logging               | 100%  | GREEN  |
| Queue                 | 80%   | GREEN  |
| DataTransfer          | 67%   | YELLOW |
| Filesystem            | 60%   | YELLOW |
| Container             | 50%   | YELLOW |
| Resilience            | 50%   | YELLOW |
| CallableSerialization | 50%   | YELLOW |
| Observability         | 33%   | RED    |
| Storage               | 33%   | RED    |
| Redaction             | 0%    | RED    |

**Overall Dogfooding Health:** YELLOW — 3 GREEN, 5 YELLOW, 3 RED

---

## 7. Required Actions by Priority

| Priority | Action                                              | Component(s)                                 | Severity |
|----------|-----------------------------------------------------|----------------------------------------------|----------|
| P0       | Remove local Redaction, use Security/Redaction      | Operations/Logging, Operations/Observability | High     |
| P0       | Queue must use Resilience for retry/deadletter      | Operations/Queue                             | High     |
| P0       | Queue must use CallableSerialization for payloads   | Operations/Queue                             | High     |
| P1       | Config commands should use Filesystem               | framework/System/Capabilities/Configuration  | Medium   |
| P1       | PreCommit should use Filesystem for file I/O        | framework/System/Capabilities/PreCommit      | Medium   |
| P1       | Redis driver should use CallableSerialization       | framework/System/Capabilities/ExternalState  | Medium   |
| P1       | SecurityConfigurationStore should not serialize raw | components/Identity/Security                 | Medium   |
| P2       | AuthorizationEngine should not use md5(serialize()) | components/Identity/Access                   | Medium   |
| P2       | Observability should fully use Logging, not partial | Operations/Observability                     | Low      |
| P2       | Framework should use full Observability pipeline    | Framework Runtime                            | Low      |

---

## 8. Evidence Pointers

| Claim                                 | Evidence                                                                                        |
|---------------------------------------|-------------------------------------------------------------------------------------------------|
| Filesystem adoption                   | `grep -r "use Avax.*Filesystem"` — 12 consumers found                                           |
| Router adoption                       | `grep -r "use Avax.*Router"` — 15+ consumers found                                              |
| Resilience adoption                   | `grep -r "use Avax.*Resilience"` — 5 consumers found                                            |
| Queue not using Resilience            | No `use Avax.*Resilience` in Queue component                                                    |
| Queue not using CallableSerialization | Only Parallelism uses CallableSerialization                                                     |
| Redaction duplication                 | `find components -path "*/Capabilities/Redaction"` — 3 results                                  |
| Raw serialize in Redis                | `framework/System/Capabilities/ExternalState/System/Capabilities/Drivers/Redis.php`             |
| Raw serialize in Security             | `components/Identity/Security/System/Capabilities/Configuration/SecurityConfigurationStore.php` |
| md5(serialize) in Auth                | `components/Identity/Access/System/Capabilities/Authorization/AuthorizationEngine.php`          |
