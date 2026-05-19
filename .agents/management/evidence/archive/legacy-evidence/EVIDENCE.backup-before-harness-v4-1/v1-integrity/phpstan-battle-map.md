# PHPStan Battle Map

## Overview

This document represents the current reality of PHPStan violations after stabilizing the broken references (Stage 1).
The goal is to provide a structured map to address these errors systematically without masking them arbitrarily.

### Current Error Count

Total Errors: **8930**

## Top Error Families

- **Other** (Miscellaneous type checks): 3875
- **wrong named argument**: 1907
- **wrong constructor/method call**: 1618
- **unknown method**: 539
- **wrong parameter type**: 260
- **nullable mismatch**: 248
- **unknown class/method/property**: 238
- **unknown property**: 137
- **wrong return type**: 72
- **generic/PHPDoc drift**: 36

## Error Classification

### Unknown-Class Errors (Structural/Typo)

- unknown class/method/property: 238
- unknown method: 539
- unknown property: 137
  *(Total: 914)*

### Type-Quality Errors (API Contract)

- wrong named argument: 1907
- wrong constructor/method call: 1618
- wrong parameter type: 260
- nullable mismatch: 248
- wrong return type: 72
- generic/PHPDoc drift: 36
  *(Total: 4141)*

## Scope Separation

### Production Errors

Production components account for approximately **8526** errors.
The vast majority stem from legacy components utilizing positional arguments, obsolete dependency injections, or
misaligned interface implementations.

### Test Errors

The `tests/` directory contains **404** errors.

- Other: 260
- unknown class/method/property: 50
- wrong named argument: 37
- wrong constructor/method call: 36
- unknown method: 16
- wrong parameter type: 3
- nullable mismatch: 2

*(Note: Test repairs are postponed until production component static analysis is Green).*

## Strategic Repair Targets (First 5)

Per the architectural recovery roadmap, component-scoped repairs will proceed in this strict order to unblock kernel
integrity before broad capabilities:

1. **`framework/System`**: The core orchestrator of the V1 Kernel lifecycle. Must be clean before validating V1 Proof. (
   Stage 4)
2. **`components/Application/Cache`**: A foundational operational requirement with high occurrences of legacy namespace
   drift. (Stage 5)
3. **`components/HTTP/Request` & `components/HTTP/Response`**: Critical Kernel-path components enforcing PSR-7
   compliance. (Stage 6)
4. **`components/DataStack/Database`**: Heaviest concentration of type errors and outdated architecture; needed for data
   integrity. (Stage 7)
5. **`Operations Core`** (`Resilience`, `MessageBus`, `Queue`, `ApplicationWorkflow`, `Observability`): Reliability
   primitives required before the V2 platform can unlock. (Stage 8)
