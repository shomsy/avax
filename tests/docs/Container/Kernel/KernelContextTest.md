# KernelContextTest

## Quick Summary

Technical: Validates `KernelContext` invariants around child propagation, cycle detection, metadata guards, and
overwrite semantics.

### For Humans: What This Means

Ensures the resolution state carrier keeps its promises and catches misuse early while enforcing first-write-wins
metadata.

## Terminology

- **Child Context**: A derived context with incremented depth and parent linkage.
- **Cycle Detection**: Ability to detect ancestor service IDs to prevent infinite recursion.
- **Meta Guards**: `setMetaOnce` blocks conflicting writes; `resolvedWith` throws on double completion.

### For Humans: What This Means

Children inherit settings; cycles are flagged; first metadata write wins; conflicting metadata raises; double resolution
is blocked.

## Think of It

Like a breadcrumb trail that also enforces “write once” rules.

### For Humans: What This Means

You can track where you’ve been, but you can’t rewrite history.

## Story Example

You resolve `A` → `B` → `C`; `C` can see `A` and `B` via `contains()`. If you try to set the same meta twice with a
different value, you get an exception; if you mark an instance twice, you get an exception.

### For Humans: What This Means

The context protects itself from inconsistent state while tracking the path.

## For Dummies

1. Build a root context.
2. Create a child; depth increments; flags inherit.
3. Detect ancestors with `contains()`.
4. `setMetaOnce` conflicting write → exception.
5. `resolvedWith` twice → exception.
6. `overwriteWith` merges metadata and swaps instance.

### For Humans: What This Means

Basic guardrails that prevent state corruption.

## How It Works (Technical)

Exercises `child()`, `contains()`, `setMetaOnce()`, `resolvedWith()`, and `overwriteWith()` to assert propagation,
guarded metadata, and merge semantics.

### For Humans: What This Means

The test touches every core behavior that keeps context safe.

## Architecture Role

- **Lives in**: `tests/Kernel`
- **Role**: Guardrail for resolution state management.

### For Humans: What This Means

Protects the state carrier that the engine relies on.

## Methods

### Method: testChildContextInheritsParentDepth() {#method-testchildcontextinheritsparentdepth}

Technical: Asserts depth increments, parent links, flags, trace ID, and overrides propagate to the child.

### For Humans: What This Means

Children remember where they came from and carry the same settings.

#### Parameters

- None.

#### Returns

- `void`

#### Throws

- None.

#### When to use it

- Always run; ensures propagation stays correct.

#### Common mistakes

- Forgetting to propagate flags when cloning contexts.

### Method: testCycleDetection() {#method-testcycledetection}

Technical: Verifies `contains()` detects ancestors and ignores non-ancestors.

### For Humans: What This Means

Makes sure recursion loops are catchable.

#### Parameters

- None.

#### Returns

- `void`

#### Throws

- None.

#### When to use it

- Run in suite to guard recursion safety.

#### Common mistakes

- Changing `contains()` logic without updating this test.

### Method: testSetMetaOnceThrowsOnConflictingValue() {#method-testsetmetaoncethrowsonconflictingvalue}

Technical: Confirms `setMetaOnce` keeps the first value and throws when a conflicting value is written.

### For Humans: What This Means

The first meta wins; conflicting writes are rejected loudly.

#### Parameters

- None.

#### Returns

- `void`

#### Throws

- `LogicException` on conflicting writes.

#### When to use it

- Always; prevents unintended meta overwrites.

#### Common mistakes

- Assuming a later write will override the first; use `putMeta()` to overwrite intentionally.

### Method: testResolvedWithThrowsOnDoubleCall() {#method-testresolvedwiththrowsondoublecall}

Technical: Ensures calling `resolvedWith` twice raises `LogicException`.

### For Humans: What This Means

You can’t mark a resolution twice.

#### Parameters

- None.

#### Returns

- `void`

#### Throws

- `LogicException` on second call.

#### When to use it

- Run in suite to protect resolution lifecycle.

#### Common mistakes

- Allowing multiple resolutions to overwrite instance.

### Method: testOverwriteWithMergesMetadataAndOverridesInstance() {#method-testoverwritewithmergesmetadataandoverridesinstance}

Technical: Validates `overwriteWith()` replaces the resolved instance without mutating existing metadata.

### For Humans: What This Means

When decorating, you can swap the instance while keeping prior metadata intact.

#### Parameters

- None.

#### Returns

- `void`

#### Throws

- None.

#### When to use it

- When ensuring decorators can replace the instance without losing metadata.

#### Common mistakes

- Expecting metadata merge; this operation swaps the instance only.

## Risks & Trade-offs

- Tests will need updates if context fields change.

### For Humans: What This Means

Keep tests in sync when adding/removing context properties.

## Related Files & Folders

- `Core/Kernel/Contracts/KernelContext.php`

### For Humans: What This Means

The test guards the core resolution state carrier.
