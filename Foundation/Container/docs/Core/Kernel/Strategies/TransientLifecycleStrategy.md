# TransientLifecycleStrategy

## Quick Summary

Implements a no-cache policy: services are never stored or reused. It exists to ensure each resolution creates a fresh
instance.

### For Humans: What This Means (Summary)

It always builds a new instance; nothing is kept between resolutions.

## Terminology (MANDATORY, EXPANSIVE)- **Transient**: Lifetime where instances aren’t cached.

- **Store/has/retrieve/clear**: Lifecycle operations that become no-ops or defaults here.

### For Humans: What This Means

Transient means no keeping; store/has/retrieve don’t cache anything; clear does nothing.

## Think of It

Like disposable cups: use once and throw away—no storage, no reuse.

### For Humans: What This Means (Think)

Every time you need one, you get a new one; nothing is saved.

## Story Example

A stateless utility is marked transient. Each resolution constructs a new object; `has` always false, `retrieve` always
null, ensuring no shared state.

### For Humans: What This Means (Story)

You always get a fresh instance so state can’t bleed between calls.

## For Dummies

- `store` is empty.
- `has` always returns false.
- `retrieve` always returns null.
- `clear` does nothing.

Common misconceptions: expecting any caching—there is none.

### For Humans: What This Means (Dummies)

Don’t expect reuse; it’s intentionally fresh every time.

## How It Works (Technical)

Implements `LifecycleStrategy` with no-op `store/clear`, `has` false, `retrieve` null.

### For Humans: What This Means (How)

It fulfills the interface but intentionally avoids caching.

## Architecture Role

Provides transient lifetime behavior. Used when services must be recreated per resolution and hold no shared state.

### For Humans: What This Means (Role)

It’s the policy for “always new” services.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what
happens?” cheat sheet.

### Method: store(string $abstract, mixed $instance): void

#### Technical Explanation (store)

No-op; transient strategy doesn’t cache.

##### For Humans: What This Means (store)

Ignores storing—nothing is saved.

##### Parameters (store)

- `string $abstract`: Service ID.
- `mixed $instance`: Instance (unused).

##### Returns (store)

- `void`

##### Throws (store)

- None.

##### When to Use It (store)

Called by interface contract; intentionally does nothing.

##### Common Mistakes (store)

Expecting this to cache anything.

### Method: has(string $abstract): bool

#### Technical Explanation (has)

Always returns false; transient strategies never have cached instances.

##### For Humans: What This Means (has)

Says “no cached instance” every time.

##### Parameters (has)

- `string $abstract`: Service ID.

##### Returns (has)

- `bool`: False.

##### Throws (has)

- None.

##### When to Use It (has)

Interface compliance; signals no cache.

##### Common Mistakes (has)

Using this to gate creation expecting true.

### Method: retrieve(string $abstract): mixed

#### Technical Explanation (retrieve)

Always returns null; no cached instance exists.

##### For Humans: What This Means (retrieve)

You’ll never get a stored instance; it’s always null.

##### Parameters (retrieve)

- `string $abstract`: Service ID.

##### Returns (retrieve)

- `mixed`: Null.

##### Throws (retrieve)

- None.

##### When to Use It (retrieve)

Interface compliance.

##### Common Mistakes (retrieve)

Assuming a value will appear here.

### Method: clear(): void

#### Technical Explanation (clear)

No-op; nothing to clear in transient strategy.

##### For Humans: What This Means (clear)

Does nothing because nothing was stored.

##### Parameters (clear)

- None.

##### Returns (clear)

- `void`

##### Throws (clear)

- None.

##### When to Use It (clear)

Interface compliance.

##### Common Mistakes (clear)

Expecting cleanup effects.

## Risks, Trade-offs & Recommended Practices

- **Risk: Performance cost**. Always creating new instances can be slower; use only when needed.
- **Trade-off: Freshness vs overhead**. Guarantees no shared state but costs construction time.
- **Practice: Use for stateless/lightweight services**. Avoid transient for heavy constructions unless required.

### For Humans: What This Means (Risks)

You pay in performance for fresh instances; use it when you need absolute isolation or statelessness.

## Related Files & Folders

- `docs_md/Core/Kernel/Strategies/index.md`: Strategies overview.
- `docs_md/Core/Kernel/Contracts/LifecycleStrategy.md`: Contract implemented here.

### For Humans: What This Means (Related)

See the overview and contract to understand how this policy fits with others.
