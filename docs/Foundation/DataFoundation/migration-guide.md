# Migration Guide

## Namespace rename

Primary namespace:

- old: `Avax\DataModeling\...`
- new: `Avax\DataFoundation\...`

The old namespace is no longer shipped.
Consumers must switch imports to `Avax\DataFoundation\...`.

## Root folder rename

Primary implementation folder:

- old: `Foundation/DataModeling`
- new: `Foundation/DataFoundation`

`Foundation/DataModeling` has been removed.

## Public API change

`pull()` changed shape on both root facades.

- old intent: remove and return a value through an ambiguous `mixed` contract
- new shape: return `Pair<removedValue, nextState>`

Example:

```php
use Avax\DataFoundation\Collection;

$result = (new Collection(['name' => 'Alice']))->pull('name');
$removed = $result->first();
$next = $result->second();
```

## New lanes

The former collection-centric component is now expanded with:

- `Values`
- `Composites`
- `Collections` family types beyond generic `Collection`
- `Structures`
- `Flows`
- `Interop`

## What did not change

- `Arrhae` remains the raw array facade
- `Collection` remains the fluent generic collection engine
- immutable-first semantics remain the default
