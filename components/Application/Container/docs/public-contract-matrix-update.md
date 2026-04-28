# Public Contract and Diagnostics Matrix Update

This document updates the public contract matrix to reflect slice views, pooled lifetime, and new diagnostics surfaces.

## New Public API Surfaces

### Slice View APIs

| Surface                      | Owner             | Effect                                   | Compile/Runtime | Throws | Evidence                        |
|:-----------------------------|:------------------|:-----------------------------------------|:----------------|:-------|:--------------------------------|
| `debugGraph('flow.login')`   | `ServiceResolver` | Return filtered graph for specific slice | Runtime         | none   | `SliceVisibilitySmokeTest.php`  |
| `validate(['flow.login'])`   | `ServiceResolver` | Validate specific slice                  | Runtime         | none   | `SliceValidationSmokeTest.php`  |
| `describeService()['slice']` | `ServiceResolver` | Return slice metadata                    | Runtime         | none   | `SliceDiagnosticsSmokeTest.php` |
| `debugGraph()['slices']`     | `ServiceResolver` | Return all slice manifests               | Runtime         | none   | `SliceDiagnosticsSmokeTest.php` |

### Pooled Lifetime APIs

| Surface                        | Owner               | Effect                        | Compile/Runtime | Throws | Evidence                         |
|:-------------------------------|:--------------------|:------------------------------|:----------------|:-------|:---------------------------------|
| `pooled()`                     | `RegisterServices`  | Register with pooled lifetime | Authored only   | none   | `PooledLifetimeSmokeTest.php`    |
| `maxPoolSize(int)`             | `RegisterServices`  | Configure pool size           | Authored only   | none   | `PooledLifetimeSmokeTest.php`    |
| `onOverflow(strategy)`         | `RegisterServices`  | Configure overflow behavior   | Authored only   | none   | `PooledLifetimeSmokeTest.php`    |
| `resetWith(callable)`          | `RegisterServices`  | Declare reset behavior        | Authored only   | none   | `PooledLifetimeSmokeTest.php`    |
| `resettable()`                 | `RegisterServices`  | Use class reset() method      | Authored only   | none   | `PooledLifetimeSmokeTest.php`    |
| `debugScope()['pooled']`       | `ServiceResolver`   | Return pooled service state   | Runtime         | none   | `PooledDiagnosticsSmokeTest.php` |
| `runtimeReport()['poolStats']` | `ResolutionMetrics` | Return pool metrics           | Runtime         | none   | `PooledDiagnosticsSmokeTest.php` |

### New Diagnostics Outputs

| Output                                        | Content                     | Format      |
|:----------------------------------------------|:----------------------------|:------------|
| `debugGraph()['slices']`                      | All slice manifests         | JSON array  |
| `debugGraph()['sliceManifest']['flow.login']` | Services in flow.login      | JSON object |
| `debugGraph()['impact']`                      | Impact analysis per service | JSON object |
| `debugGraph()['deadRegistrations']`           | Unreachable services        | JSON array  |
| `debugGraph()['duplicateConcepts']`           | Conflicting concepts        | JSON array  |
| `validate()['ownership']`                     | Cross-slice access issues   | JSON array  |
| `validate()['lifetime']`                      | Lifetime misuse issues      | JSON array  |
| `validate()['policy']`                        | Policy violations           | JSON array  |
| `runtimeReport()['poolStats']`                | Pool utilization            | JSON object |
| `describeService()['poolConfig']`             | Pool configuration          | JSON object |
| `describeService()['imports']`                | Declared imports            | JSON array  |
| `describeService()['exports']`                | Declared exports            | JSON array  |
| `compileReport()['derivedSlices']`            | Derived slice metadata      | JSON object |

## Slice-View Diagnostics

### Slice Manifest Structure

```json
{
  "slices": {
    "flow.login": {
      "category": "flow",
      "visibility": "private",
      "services": ["LoginFlow", "LoginController", "LoginValidator"],
      "imports": ["capability.identity"],
      "exports": []
    },
    "capability.identity": {
      "category": "capability",
      "visibility": "shared",
      "services": ["PasswordHasher", "UserProvider"],
      "imports": [],
      "exports": ["capability.identity"]
    }
  }
}
```

### Cross-Slice Access Diagnostics

```json
{
  "ownership": [
    {
      "type": "unexported_access",
      "service": "stripe-gateway",
      "consumer": "flow.checkout",
      "message": "Cannot access 'stripe-gateway' from 'flow.checkout': service is private to 'capability.payments'"
    },
    {
      "type": "missing_import",
      "service": "capability.payments",
      "consumer": "flow.login",
      "message": "Cannot access 'capability.payments' from 'flow.login': capability not imported"
    }
  ]
}
```

## Pooled Lifetime Diagnostics

### Pool Configuration

```json
{
  "poolConfig": {
    "enabled": true,
    "maxSize": 10,
    "overflowStrategy": "EVICT",
    "resettable": true,
    "resetMethod": "reset"
  }
}
```

### Pool Statistics

```json
{
  "poolStats": {
    "pooled_http_client": {
      "size": 5,
      "maxSize": 10,
      "hits": 150,
      "misses": 3,
      "resets": 153,
      "disposals": 0,
      "evictions": 0
    }
  }
}
```

## Policy Findings

### Anti-Pattern Detection

| Pattern                         | Severity | Message                                            |
|:--------------------------------|:---------|:---------------------------------------------------|
| `shared_captures_scoped`        | ERROR    | Shared service captures scoped dependency          |
| `wider_scope_captures_narrower` | ERROR    | Service in wider scope captures narrower scope     |
| `disposable_transient`          | ERROR    | Transient service is marked disposable             |
| `unexported_cross_slice`        | WARNING  | Cross-slice access without export                  |
| `missing_import`                | WARNING  | Slice accesses capability without import           |
| `private_leak`                  | ERROR    | Private service accessed from outside owning slice |
| `cyclic_dependency`             | ERROR    | Circular dependency detected                       |
| `duplicate_concept`             | WARNING  | Multiple services claim same concept name          |

## Explainability Outputs

### Service Explanation

```php
$container->describeService(PaymentGateway::class);
```

```json
{
  "abstract": "PaymentGateway",
  "concrete": "StripeGateway",
  "owner": "capability.payments",
  "category": "capability",
  "visibility": "shared",
  "lifetime": "shared",
  "imports": [],
  "exports": ["capability.payments"],
  "dependencies": ["HttpClient", "Logger"],
  "dependents": ["flow.checkout", "flow.subscription"],
  "isDisposable": true,
  "isDeferred": false,
  "isDecorated": false,
  "condition": null,
  "overrideSource": null,
  "reason": "Expose one shared payment gateway to importing flows.",
  "provenance": "PaymentsServiceProvider"
}
```

### Graph Explanation

```php
$container->debugGraph('flow.login');
```

```json
{
  "slice": "flow.login",
  "manifest": {
    "services": ["LoginFlow", "LoginController", "LoginValidator"],
    "imports": ["capability.identity"],
    "exports": []
  },
  "dependencies": {
    "LoginFlow": ["capability.identity", "Logger"],
    "LoginController": ["LoginFlow"],
    "LoginValidator": []
  },
  "dependents": {
    "LoginFlow": ["LoginController"],
    "LoginController": [],
    "LoginValidator": ["LoginFlow"]
  },
  "impact": {
    "if_remove": ["LoginController", "LoginFlow"],
    "if_change": ["capability.identity"]
  },
  "deadRegistrations": [],
  "duplicateConcepts": []
}
```

## Graph Output Expectations

### Machine-Readable (JSON)

```php
$container->debugGraph(); // Returns JSON-serializable array
$container->compileReport(); // Includes graph snapshot
$container->runtimeReport(); // Includes live graph state
```

### Human-Readable (Text)

```php
$container->debugGraph(); // Pretty-printed array output
// Or via debug service
$container->debugService(Service::class); // Formatted service info
```

### Graphviz Export (Future)

```php
$container->exportGraph('graph.dot'); // DOT format for graphviz
$container->exportGraph('graph.png'); // PNG via graphviz
```

## Acceptance Criteria Coverage

| Criterion                         | API Coverage                                               |
|:----------------------------------|:-----------------------------------------------------------|
| AC-101 Slice-local visibility     | `debugGraph(slice)`, `validate([slice])`                   |
| AC-102 Capability exports/imports | `describeService()['imports/exports']`, validate ownership |
| AC-103 Pooled lifetime            | `pooled()`, `maxPoolSize()`, `resetWith()`, diagnostics    |
| AC-105 Graph artifacts            | `debugGraph()`, `compileReport()`, `runtimeReport()`       |
| AC-106 Structural diff            | `debugGraph()['slices']`, compile report metadata          |
| AC-110 Policy engine              | `validate()['policy']`, anti-pattern detection             |

---

*This update is owned by the architecture-contract agent. Codex implements the new surfaces.*
