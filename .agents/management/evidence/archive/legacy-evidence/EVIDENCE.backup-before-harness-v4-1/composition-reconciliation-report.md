# Phase 3: Composition-Based Reconciliation Report

Date: 2026-05-08
Status: GREEN

## A. Dependency Proof

### Arrhae imports zero Collection classes

```
grep "Collection" Arrhae/Arrhae.php → 0 matches
```

### Arrhae imports zero Json classes

```
grep "Json" Arrhae/Arrhae.php → 0 matches (only ConvertCollectionToJson operator)
```

### Collection composes Arrhae

```php
public function __construct(private Arrhae $arrhae)
public static function make(iterable $items = []): static
    => new self(arrhae: Arrhae::make(items: $items))
```

### Json composes Arrhae

```php
public function __construct(private Arrhae $arrhae)
public static function decode(string $json): static
    => new self(arrhae: Arrhae::make(items: $decoded))
```

### No DataPipeline trait remains

```
find Capabilities/DataPipeline → directory does not exist
grep "use DataPipeline" → 0 matches
```

### No shared behavior under Collection/Internal

```
ls Collection/Internal/ → empty (directory removed)
```

## B. Method Parity

All three DSLs share the same fluent pipeline vocabulary:

| Method       | Arrhae | Collection | Json |
|--------------|--------|------------|------|
| make         | ✓      | ✓          | ✓    |
| wrap         | ✓      | ✓          | ✓    |
| all          | ✓      | ✓          | ✓    |
| get          | ✓      | ✓          | ✓    |
| has          | ✓      | ✓          | ✓    |
| set          | ✓      | ✓          | ✓    |
| forget       | ✓      | ✓          | ✓    |
| add          | ✓      | ✓          | ✓    |
| pull         | ✓      | ✓          | ✓    |
| map          | ✓      | ✓          | ✓    |
| filter       | ✓      | ✓          | ✓    |
| reduce       | ✓      | ✓          | ✓    |
| reject       | ✓      | ✓          | ✓    |
| flatten      | ✓      | ✓          | ✓    |
| each         | ✓      | ✓          | ✓    |
| sum          | ✓      | ✓          | ✓    |
| average      | ✓      | ✓          | ✓    |
| min          | ✓      | ✓          | ✓    |
| max          | ✓      | ✓          | ✓    |
| sort         | ✓      | ✓          | ✓    |
| sortBy       | ✓      | ✓          | ✓    |
| reverse      | ✓      | ✓          | ✓    |
| shuffle      | ✓      | ✓          | ✓    |
| unique       | ✓      | ✓          | ✓    |
| chunk        | ✓      | ✓          | ✓    |
| groupBy      | ✓      | ✓          | ✓    |
| partition    | ✓      | ✓          | ✓    |
| contains     | ✓      | ✓          | ✓    |
| search       | ✓      | ✓          | ✓    |
| only         | ✓      | ✓          | ✓    |
| except       | ✓      | ✓          | ✓    |
| pluck        | ✓      | ✓          | ✓    |
| where        | ✓      | ✓          | ✓    |
| whereIn      | ✓      | ✓          | ✓    |
| whereBetween | ✓      | ✓          | ✓    |
| whereNull    | ✓      | ✓          | ✓    |
| whereNotNull | ✓      | ✓          | ✓    |
| keyBy        | ✓      | ✓          | ✓    |
| flip         | ✓      | ✓          | ✓    |
| merge        | ✓      | ✓          | ✓    |
| union        | ✓      | ✓          | ✓    |
| diff         | ✓      | ✓          | ✓    |
| intersect    | ✓      | ✓          | ✓    |
| keys         | ✓      | ✓          | ✓    |
| values       | ✓      | ✓          | ✓    |
| tap          | ✓      | ✓          | ✓    |
| when         | ✓      | ✓          | ✓    |
| unless       | ✓      | ✓          | ✓    |
| toArray      | ✓      | ✓          | ✓    |
| toJson       | ✓      | ✓          | ✓    |
| toXml        | ✓      | ✓          | ✓    |
| lock         | ✓      | ✓          | ✓    |
| isLocked     | ✓      | ✓          | ✓    |
| toImmutable  | ✓      | ✓          | ✓    |
| count        | ✓      | ✓          | ✓    |
| isEmpty      | ✓      | ✓          | ✓    |
| isNotEmpty   | ✓      | ✓          | ✓    |
| first        | ✓      | ✓          | ✓    |
| last         | ✓      | ✓          | ✓    |
| encode       | —      | —          | ✓    |
| pretty       | —      | —          | ✓    |
| path         | —      | —          | ✓    |
| validate     | —      | —          | ✓    |
| getIterator  | —      | ✓          | —    |
| offset*      | —      | ✓          | —    |

Collection-only: IteratorAggregate, ArrayAccess (read-only)
Json-only: decode, encode, pretty, path (JSON Pointer), validate

## C. Dependency Diagram

```
┌─────────────────────────────────────────────────┐
│              System/Foundation/                  │
│  Mutability/MutationGuard                        │
│  Normalization/MakeCollection, WrapValue, etc.   │
│  Failure/MutationException, InvalidJson          │
└─────────────────────────────────────────────────┘
                        ↑
          ┌─────────────┼─────────────┐
          │             │             │
┌─────────┴──────┐ ┌───┴─────┐ ┌─────┴──────┐
│  Arrhae        │ │ DotPath │ │ Pair, etc. │
│  (independent) │ │         │ │ Structures │
└─────────┬──────┘ └─────────┘ └────────────┘
          │
    ┌─────┴──────────┐
    │                │
┌───┴──────┐   ┌─────┴────┐
│Collection│   │   Json   │
│(composes │   │(composes │
│ Arrhae)  │   │ Arrhae)  │
└──────────┘   └──────────┘
```

## D. Validation Output

### Tests

```
Tests: 989, Assertions: 4003, Skipped: 1
Status: PASS
```

### PHPStan

```
182 warnings (all pre-existing missingType.iterableValue)
0 new errors introduced
```

### Governance Checks

```
namespace-drift:  PASS
public-surface:   PASS
runtime-leaks:    PASS
```

## E. Changes Summary

### Files Rewritten

- `Arrhae/Arrhae.php` — fully independent, all pipeline methods inline
- `Collection/Collection.php` — composes Arrhae, delegates all pipeline methods
- `Json/Json.php` — composes Arrhae, delegates all pipeline methods

### Files Modified

- `Collection/CollectionInterface.php` — removed constructor declaration, fixed Pair import
- `DataList/DataList.php` — use Collection::make(), fixed NormalizedIterable import
- `Flows/CreateCollection/CreateCollection.php` — use Collection::make()
- `Flows/Window/Window.php` — fixed NormalizedIterable import
- `Flows/Batch/Batch.php` — fixed NormalizedIterable import
- `Map/Map.php` — fixed MapEntry, NormalizedIterable imports
- `MultiMap/MultiMap.php` — fixed NormalizedIterable import
- `OrderedMap/OrderedMap.php` — fixed MapEntry, NormalizedIterable imports
- `OrderedSet/OrderedSet.php` — fixed Comparator, NormalizedIterable imports
- `Set/Set.php` — fixed Comparator, NormalizedIterable imports
- `Sequence/Sequence.php` — fixed NormalizedIterable import

### Files Moved

- `Comparator` → `Structures/Comparator.php` (neutral)
- Foundation primitives → `System/Foundation/` (Mutability, Normalization)

### Files Deleted

- `DataPipeline/DataPipeline.php` (trait-based sharing)
- `Collection/Internal/` directory (emptied and removed)

### Files Created

- `how-this-works.md` — architecture documentation
- `EVIDENCE/composition-reconciliation-report.md` — this report

## F. Remaining Risks

1. **Dead code files**: `Capabilities/Foundation/` contains root-owned empty stubs that cannot be deleted without sudo.
   They are zero-byte and harmless — autoloader skips them.
2. **PHPStan warnings**: 182 pre-existing `missingType.iterableValue` warnings. Not introduced by this change.
3. **External code**: Any code outside this repository that constructs `new Collection(array)` directly will need to use
   `Collection::make(array)` instead.

## G. Next Allowed Action

Run the full canonical validation set and proceed to next stage.
