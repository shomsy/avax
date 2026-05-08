# How This Works — AvaX Data Engine

## Architecture Overview

The DataStack/Data component provides two distinct layers:

### 1. Forms / Engine (representation and pipeline power)

Forms are the representation layer. They do not enforce data structure invariants.
They provide fluent operations on arrays, items, and JSON documents.

| Form | Purpose | Composes |
|------|---------|----------|
| **Arrhae** | Array-native engine — provides array/pipeline power | Nothing (independent) |
| **Collection** | Item/object DSL — provides item/object pipeline language | Arrhae |
| **Json** | JSON document DSL — provides JSON document language | Arrhae (after decode) |

Key principle: Arrhae is independent. Collection and Json both compose Arrhae.
Arrhae imports zero Collection or Json classes.

### 2. Structures (invariant-bearing data structures)

Structures enforce real data-structure invariants. They are a different conceptual layer from Forms.

| Structure | Invariant |
|-----------|-----------|
| **Map** | Key/value mapping |
| **Set** | Uniqueness |
| **Sequence** | Ordered indexed values |
| **OrderedMap** | Insertion-order key/value |
| **OrderedSet** | Insertion-order uniqueness |
| **MultiMap** | One-to-many key/value |
| Queue (future) | FIFO |
| Stack (future) | LIFO |
| Deque (future) | Double-ended FIFO |
| PriorityQueue (future) | Priority ordering |

Structures may use Forms where convenient, but must not depend on Collection or Json.

### 3. Supporting Capabilities

| Capability | Purpose |
|------------|---------|
| **Shapes** | Class shape introspection (ClassShape, Visibility) |
| **Operators** | Data operations (Aggregate, Transform, Ordering, Search, Selection, Arrays) |
| **Codecs** | Encoding/decoding (JsonCodec, XmlCodec, ArrayCodec) |
| **Lenses** | Data path navigation (DotPath) |
| **Coercion** | DTO system, object mapping, value conversion |
| **Validation** | Validation rules |
| **Values** | Domain value objects (Temporal, Identity, Money, Numeric) |
| **Foundation** | Exceptions, guards, normalization primitives |

## Layering Rules

1. Arrhae has zero imports from Collection, Json, or any Structure.
2. Collection imports Arrhae; Json imports Arrhae.
3. Structures must not import Collection or Json.
4. PublicSurface provides facades for both Forms and Structures, but they represent different layers.

## Component Shape

Each capability follows the canonical AvaX shape:

```
System/
  Capabilities/
    <Capability>/          ← Folder says capability
      <Concern>/           ← Sub-folder says responsibility
        <Class>.php        ← Class says exact action
  Flows/                   ← Complete user/system actions
  Configuration/           ← Assembly and registration
  Foundation/              ← Neutral primitives
  PublicSurface/           ← Stable entry points
```
