# Duplicate Capability Owners

## Status

**Stage:** V5 Readiness — Duplicate Capability Analysis
**Date:** 2026-05-10
**Mode:** Standard

---

## 1. Purpose

This document lists every confirmed and potential duplicate capability implementation across AvaX components.

A duplicate exists when two or more components implement the same capability instead of one owning and others using it.

---

## 2. Confirmed Duplicates

### 2.1 Redaction

| Owner                    | Capability                                                    | Type                   | Decision                                                |
|--------------------------|---------------------------------------------------------------|------------------------|---------------------------------------------------------|
| Security/Redaction       | DataClassifier, PatternMatcher, PolicyEngine, RedactionEngine | **Canonical owner**    | Keep as canonical                                       |
| Operations/Logging       | Redaction                                                     | Local reimplementation | **Violates dogfooding** — should use Security/Redaction |
| Operations/Observability | Redaction                                                     | Local reimplementation | **Violates dogfooding** — should use Security/Redaction |

**Root Cause:** Logging and Observability each implemented their own redaction rather than using the Security/Redaction
engine.

**Impact:** Three implementations of the same capability. Bug fixes, policy updates, and pattern changes must be applied
in three places.

**Required Action:** Remove Redaction capabilities from Logging and Observability. Both must use Security/Redaction.

**Severity:** High

---

### 2.2 DeadLetter

| Owner                 | Capability | Type                   | Decision                                                   |
|-----------------------|------------|------------------------|------------------------------------------------------------|
| Operations/Resilience | DeadLetter | **Canonical owner**    | Keep as canonical (pattern definition)                     |
| Operations/Queue      | DeadLetter | Local reimplementation | **Violates dogfooding** — should use Operations/Resilience |

**Root Cause:** Queue implemented its own dead letter handling rather than using the Resilience pattern.

**Impact:** Queue-specific dead letter behavior may diverge from the resilience pattern definition.

**Required Action:** Queue should delegate to Resilience/DeadLetter with queue-specific configuration (queue name, retry
policy, storage).

**Severity:** Medium

---

## 3. Potential Duplicates (Need Verification)

### 3.1 Encryption

| Owner                 | Capability | Type                            | Decision Pending |
|-----------------------|------------|---------------------------------|------------------|
| Security/Cryptography | Encryption | Possible canonical owner        | Need to verify   |
| Security/Secrets      | Encryption | May delegate or may reimplement | Need to verify   |

**Verification Required:** Read Secrets/Encryption implementation. If it calls Cryptography/Encryption, it's legitimate.
If it implements its own encryption, it's a duplicate.

**Severity:** Low (both in Security area, bounded scope)

---

### 3.2 Audit

| Owner                    | Capability | Type                   | Decision Pending         |
|--------------------------|------------|------------------------|--------------------------|
| HTTP/Session             | Audit      | Session-specific audit | Likely legitimate domain |
| Operations/Observability | Audit      | System telemetry audit | Likely legitimate domain |
| Security/System          | Audit      | Security audit         | Likely legitimate domain |

**Verification Required:** Verify these three audit capabilities do not reimplement the same underlying audit
infrastructure. If there is a common audit engine, all three should use it.

**Severity:** Low (different audit domains)

---

### 3.3 Filesystem Operations (Application vs Operations)

| Owner                  | Capability                   | Type                         | Decision Pending               |
|------------------------|------------------------------|------------------------------|--------------------------------|
| Application/Filesystem | LocalPaths, LocalPermissions | Local filesystem operations  | Likely legitimate              |
| Operations/Filesystem  | Adapters, Drivers            | External filesystem adapters | Needs rename (forbidden names) |

**Verification Required:** Verify Operations/Filesystem does not reimplement local filesystem operations that
Application/Filesystem already owns.

**Additional Issue:** Operations/Filesystem uses forbidden concept words "Adapters" and "Drivers" as capability folder
names.

**Severity:** Medium (naming violation, possible functional overlap)

---

## 4. Legitimate Shared Names (Not Duplicates)

These capability names appear in multiple components but represent legitimately different capabilities:

| Capability Name | Owners                                                                                 | Why It's Legitimate                                                                                   |
|-----------------|----------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------------------------|
| Health          | Cache, ObjectStorage, Delivery, MemoryLifecycle, Observability, RuntimeSupervision (6) | Each component checks its own health — self-diagnosis, not a shared capability                        |
| Storage         | Cache, Session (2)                                                                     | Cache storage vs session storage — different data domains, different TTL, different eviction policies |
| Logging         | Logging, Observability (2)                                                             | Logging owns the logger engine; Observability owns telemetry collection including log aggregation     |
| Drivers         | Filesystem, Observability (2)                                                          | Different driver types — filesystem drivers vs observability telemetry drivers                        |

---

## 5. Summary

| Category                                    |                             Count |
|---------------------------------------------|----------------------------------:|
| Confirmed duplicates                        |                                 2 |
| Components violating dogfooding             | 3 (Logging, Observability, Queue) |
| Potential duplicates (need verification)    |                                 3 |
| Legitimate shared names                     |                                 4 |
| Total capability names with multiple owners |                                 9 |

---

## 6. Required Actions

| Priority | Action                                                     | Component(s)                                    | Severity |
|----------|------------------------------------------------------------|-------------------------------------------------|----------|
| P1       | Remove local Redaction, delegate to Security/Redaction     | Operations/Logging, Operations/Observability    | High     |
| P2       | Remove local DeadLetter, delegate to Operations/Resilience | Operations/Queue                                | Medium   |
| P3       | Rename forbidden capability names (Adapters, Drivers)      | Operations/Filesystem, Operations/Observability | Medium   |
| P4       | Verify Encryption delegation                               | Security/Secrets -> Security/Cryptography       | Low      |
| P5       | Verify audit infrastructure sharing                        | Session, Observability, Security/System         | Low      |
| P6       | Verify filesystem capability separation                    | Application/Filesystem vs Operations/Filesystem | Low      |

---

## 7. Evidence Pointers

| Claim                      | Evidence                                                                                                                            |
|----------------------------|-------------------------------------------------------------------------------------------------------------------------------------|
| Redaction in Security      | `components/Security/Redaction/System/Capabilities/RedactionEngine/`                                                                |
| Redaction in Logging       | `components/Operations/Logging/System/Capabilities/Redaction/`                                                                      |
| Redaction in Observability | `components/Operations/Observability/System/Capabilities/Redaction/`                                                                |
| DeadLetter in Resilience   | `components/Operations/Resilience/System/Capabilities/DeadLetter/`                                                                  |
| DeadLetter in Queue        | `components/Operations/Queue/System/Capabilities/DeadLetter/`                                                                       |
| Encryption in Cryptography | `components/Security/Cryptography/System/Capabilities/Encryption/`                                                                  |
| Encryption in Secrets      | `components/Security/Secrets/System/Capabilities/Encryption/`                                                                       |
| Forbidden "Adapters"       | `components/Operations/Filesystem/System/Capabilities/Adapters/`                                                                    |
| Forbidden "Drivers"        | `components/Operations/Filesystem/System/Capabilities/Drivers/`, `components/Operations/Observability/System/Capabilities/Drivers/` |
