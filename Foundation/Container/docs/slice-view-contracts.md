# Slice View Contracts

This document defines canonical contracts for logical slice containers and slice views in the DI system.

## What Is A Slice View?

A **slice view** is a read-only projection of the container's service graph that exposes only the services relevant to a specific slice (flow, capability, configuration, or foundation). Slice views enforce visibility boundaries at runtime and provide diagnostics for slice composition.

Slice views are NOT separate container instances. They are logical filters over the same authored registry and compiled metadata.

## Slice View Types

### Root Composition View

The **root composition view** exposes all services without filtering. It represents the complete composed system.

**What it sees:**
- All authored registrations (flow, capability, configuration, foundation)
- All compiled metadata
- All ownership metadata

**What it may export:**
- Everything (full graph)

**What remains private/internal:**
- Nothing (it is the complete truth)

**Use cases:**
- System-wide diagnostics (`debugGraph()` without slice filter)
- Full validation (`validate()` without slice filter)
- Compile artifact generation
- Graph export (machine-readable and human-readable)

**Code surface:**
```php
$container->debugGraph(); // root view - all slices
$container->validate();   // root view - all slices
$container->compileContainer(); // root view
```

### Flow Slice View

The **flow slice view** exposes services belonging to a specific flow slice.

**What it sees:**
- Services owned by the flow slice
- Imported capabilities (resolved through import declarations)
- Configuration and foundation dependencies that the flow explicitly depends on

**What it may export:**
- The flow's entry point (if marked public/shared)
- Diagnostic information about the flow's graph

**What remains private/internal:**
- Other flow slices
- Other capability slices
- Un-imported shared capabilities

**Use cases:**
- Testing a specific flow in isolation
- Understanding flow dependencies
- Flow-level impact analysis

**Code surface:**
```php
$container->debugGraph('flow.login'); // flow slice view
$container->validate(['flow.login']);  // validate flow slice
```

**Visibility rules:**
- Flow can see its own private/internal services
- Flow can see imported capabilities (must declare import)
- Flow CANNOT see other flows without explicit cross-flow export/import
- Flow CANNOT see un-imported capabilities

### Capability Slice View

The **capability slice view** exposes services that implement a shared capability.

**What it sees:**
- Services marked as capability
- Exported services from other slices that the capability depends on

**What it may export:**
- The capability's public interface
- Capability composition diagnostics

**What remains private/internal:**
- Internal implementation details of the capability
- Un-exported dependencies

**Use cases:**
- Capability composition analysis
- Capability migration planning
- Shared service discovery

**Code surface:**
```php
$container->debugGraph('capability.payments'); // capability slice view
$container->validate(['capability.payments']);  // validate capability
```

**Visibility rules:**
- Capability can see its own services
- Capability can see explicitly exported dependencies
- Capability CANNOT see private/internal services of consuming flows

### Configuration Slice View

The **configuration slice view** exposes services related to configuration and wiring.

**What it sees:**
- Services with category `configuration`
- Imported capabilities needed for configuration
- Foundation dependencies

**What it may export:**
- Configuration structure
- Wiring diagnostics

**What remains private/internal:**
- Flow-specific configuration
- Runtime service state

**Use cases:**
- Configuration audit
- Wiring verification

### Foundation Slice View

The **foundation slice view** exposes low-level primitives.

**What it sees:**
- Services with category `foundation`
- No dependencies on other slices (foundation must be self-contained)

**What it may export:**
- Primitive services
- Foundation composition

**What remains private/internal:**
- Nothing (foundation is deliberately minimal)

**Use cases:**
- Foundation-level verification
- Primitive availability checking

## Visibility Matrix

| Viewer → | Root | Flow | Capability | Configuration | Foundation |
|:---------|:----:|:----:|:----------:|:-------------:|:----------:|
| **Sees own private** | ✓ | ✓ | ✓ | ✓ | ✓ |
| **Sees own internal** | ✓ | ✓ | ✓ | ✓ | ✓ |
| **Sees own shared exported** | ✓ | ✓ | ✓ | ✓ | ✓ |
| **Sees own shared exported and imported** | ✓ | ✓ | ✓ | ✓ | N/A |
| **Sees other flow private** | ✓ | ✗ | ✗ | ✗ | N/A |
| **Sees other flow internal** | ✓ | ✗ | ✗ | ✗ | N/A |
| **Sees other capability private** | ✓ | ✗ | ✗ | ✗ | N/A |
| **Sees other capability internal** | ✓ | ✗ | ✗ | ✗ | N/A |
| **Sees capability without import** | ✓ | ✗ | N/A | ✗ | N/A |
| **Sees configuration** | ✓ | via dep | via dep | ✓ | ✓ |
| **Sees foundation** | ✓ | via dep | via dep | via dep | ✓ |

## Cross-Slice Access Rules

### Export Contract

A service declares export intent via:

```php
$container->singleton(PaymentGateway::class, StripeGateway::class)
    ->asCapability('capability.payments')
    ->asShared()
    ->export(); // makes it available to importers
```

### Import Contract

A slice declares import intent via:

```php
$container->bind(LoginFlow::class, LoginFlow::class)
    ->asFlow('flow.login')
    ->import('capability.payments'); // declares dependency
```

### Validation Rules

The `validate()` method enforces:

1. **Export requirement**: Shared services accessed from other slices must be exported
2. **Import declaration**: Consuming slices must declare imports to access shared services
3. **Visibility enforcement**: Private and internal services are not accessible cross-slice
4. **No silent access**: Cross-slice access failures produce clear diagnostic messages

### Error Examples

```
Cannot access 'stripe-gateway' from 'flow.checkout': service is private to 'capability.payments'
```

```
Cannot access 'capability.payments' from 'flow.login': capability not imported
```

```
Cannot access 'internal-service' from 'flow.reporting': service is internal to 'flow.login'
```

## Diagnostics Output

Each slice view produces diagnostic artifacts:

- **Slice manifest**: List of services in the slice
- **Dependency graph**: Services the slice depends on
- **Dependent graph**: Services that depend on the slice
- **Impact report**: What changes would affect this slice
- **Dead registrations**: Services not reachable from any entry point
- **Duplicate concepts**: Multiple services claiming the same logical concept

## Acceptance Criteria

### AC-101: Slice-Local Visibility Is Enforced Through Real Slice Views

- **Criterion**: Slice views filter services based on ownership metadata and import/export declarations
- **Evidence**: 
  - `debugGraph('flow.login')` returns only flow.login's services + imported capabilities
  - `validate(['flow.login'])` reports cross-slice access violations
- **Tests**: `SliceVisibilitySmokeTest.php` (to be created)

### AC-102: Capability Exports/Imports Are Explicit and Diagnosable

- **Criterion**: Every cross-slice access is traceable to explicit export/import declarations
- **Evidence**:
  - `describeService()` shows `imports` and `exports` arrays
  - Cross-slice violations include both consumer and provider in error message
- **Tests**: `ExportImportDiagnosticsSmokeTest.php` (to be created)

### AC-105: Graph Artifacts Are Exportable

- **Criterion**: Graph can be exported in machine-readable (JSON) and human-readable (text/graphviz) formats
- **Evidence**:
  - `debugGraph()` returns JSON-serializable structure
  - Compile report includes graph snapshot
- **Tests**: `GraphExportSmokeTest.php` (to be created)

---

*This contract is owned by the architecture-contract agent. Codex implements runtime enforcement.*
