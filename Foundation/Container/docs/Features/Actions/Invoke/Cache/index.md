# Cache

## What This Folder Represents

In-memory helpers that cache reflection objects for callables during invocation. It exists because reflection is
expensive and callables are often invoked repeatedly.

### For Humans: What This Means (Represent)

This folder makes invoking faster by remembering reflection results.

## What Belongs Here

- `ReflectionCache`: Key/value store for `ReflectionFunctionAbstract` objects.

### For Humans: What This Means (Belongs)

If it stores reflection results to avoid recomputing them, it belongs here.

## What Does NOT Belong Here

Long-lived caches (filesystem/redis), container lifecycles, or general caching utilities.

### For Humans: What This Means (Not Belongs)

This is a small, local cache for invocation only.

## How Files Collaborate

`InvocationExecutor` builds a cache key for the target and uses `ReflectionCache` to reuse reflection objects.

### For Humans: What This Means (Collaboration)

The executor asks the cache first, and only reflects if it’s missing.
