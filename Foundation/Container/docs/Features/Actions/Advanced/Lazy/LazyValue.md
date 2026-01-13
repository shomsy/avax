# LazyValue

## Quick Summary

LazyValue provides the concrete implementation of lazy initialization, wrapping expensive or rarely-used objects in a
deferred creation mechanism. It accepts a factory closure during construction and creates the actual object only when
first accessed via the get() method. This implementation optimizes application startup performance by avoiding immediate
instantiation of resource-intensive dependencies while maintaining transparent access to the created objects.

### For Humans: What This Means (Summary)

Imagine LazyValue as a smart package that contains the instructions for building something expensive, but doesn't
actually build it until you open the package. It's like a DIY furniture kit that stays as a compact box until you need
the assembled piece—saving space and effort for items you might never use. Once you do need it, it builds perfectly and
then stays built for future use.

## Terminology (MANDATORY, EXPANSIVE)**Factory Closure

**: A callable that encapsulates the logic for creating an expensive object, allowing deferred execution. In this file,
this is stored in the constructor and called during lazy initialization. It matters because it separates creation logic
from access logic.

**Deferred Execution**: Postponing the execution of expensive operations until absolutely necessary. In this file, this
is achieved through the lazy get() method. It matters because it optimizes resource usage and startup time.

**Initialization Flag**: A boolean marker that tracks whether the lazy object has been created yet. In this file, $
initialized prevents redundant creation attempts. It matters because it ensures thread-safe lazy initialization.

**Cached Value**: The stored result of expensive object creation that is reused for subsequent access. In this file, $
value holds the created object after first access. It matters because it provides efficient access after initial
creation cost.

**Exception Safety**: Ensuring that lazy initialization doesn't get stuck in failure loops. In this file, the finally
block guarantees initialization marking even on exceptions. It matters because it prevents infinite retry loops on
failed creations.

### For Humans: What This Means

These are the lazy value implementation vocabulary. Factory closure is the recipe. Deferred execution is cooking later.
Initialization flag is the "cooked" sticker. Cached value is the prepared meal. Exception safety is not burning the
kitchen down.

## Think of It

Picture a vending machine that doesn't load products until someone inserts money and makes a selection. LazyValue is
that smart vending machine for object creation—keeping the creation instructions ready but not executing them until
demanded. When you finally need the object, it creates it perfectly and keeps it available for future requests.

### For Humans: What This Means (Think)

This analogy shows why LazyValue exists: intelligent deferred creation. Without it, expensive objects would be created
upfront, wasting resources on things that might never be used. LazyValue creates the smart vending system that makes
object creation efficient and on-demand.

## Story Example

Before LazyValue existed, developers had to implement lazy loading manually with complex checks and caching logic
scattered throughout their code. Database connections, external API clients, and heavy computation objects all had to be
wrapped with custom lazy logic. With LazyValue, lazy initialization became a simple wrapper. Any expensive object could
now be made lazy by wrapping it in a LazyValue, eliminating custom implementation and ensuring consistent behavior.

### For Humans: What This Means (Story)

This story illustrates the implementation burden LazyValue solves: manual lazy loading. Without it, every expensive
object required custom lazy logic, leading to inconsistent and error-prone code. LazyValue creates the universal lazy
wrapper that makes any object lazy with minimal effort.

## For Dummies

Let's break this down like a smart coffee maker:

1. **The Problem**: Brewing coffee immediately wastes energy and time on coffee that might not be drunk.

2. **LazyValue's Job**: It's a coffee maker that only brews when you actually want coffee.

3. **How You Use It**: Wrap your expensive object creation in a LazyValue with a factory closure.

4. **What Happens Inside**: The factory closure creates the object only when get() is called the first time.

5. **Why It's Helpful**: Applications start faster and don't waste resources on unused expensive objects.

Common misconceptions:

- "LazyValue creates objects multiple times" - It creates once and caches forever.
- "It's just a wrapper" - It's a carefully designed lazy initialization pattern.
- "It makes everything slower" - Only first access is slower; it's faster overall for unused objects.

### For Humans: What This Means (Dummies)

LazyValue isn't just wrapping—it's smart resource management. It takes expensive object creation and makes it efficient
without changing how you use the objects. You get performance benefits with zero API changes.

## How It Works (Technical)

LazyValue stores a factory closure and tracks initialization state. On first get() call, it executes the factory, caches
the result, and marks as initialized. The finally block ensures initialization state is set even on exceptions,
preventing retry loops. Subsequent calls return the cached value directly.

### For Humans: What This Means (How)

Under the hood, it's elegantly simple. Store the creation recipe, track if it's been used, create on first request,
remember the result. The exception handling ensures robustness. It's like a smart cache that creates content on demand.

## Architecture Role

LazyValue sits at the implementation layer of lazy initialization, providing the concrete mechanism while implementing
the LazyInterface contract. It enables transparent lazy loading for any object type through composition.

### For Humans: What This Means (Role)

In the lazy initialization architecture, LazyValue is the workhorse—the actual implementation that makes lazy loading
work. It follows the interface contract while providing the robust, efficient implementation that applications rely on.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what
happens?” cheat sheet.

### Method: __construct(Closure $factory): void

#### Technical Explanation (__construct)

Creates a new LazyValue instance with the provided factory closure that will create the actual object when needed.

##### For Humans: What This Means (__construct)

This is how you set up lazy loading for an object. You give it the recipe (factory closure) for creating the expensive
object, and it stores it away until needed.

##### Parameters (__construct)

- `Closure $factory`: A closure that creates and returns the actual object when executed.

##### Returns (__construct)

- `void`: Constructor doesn't return anything; it just sets up the lazy wrapper.

##### Throws (__construct)

- None. Constructor only stores the factory closure.

##### When to Use It (__construct)

- When wrapping expensive object creation with lazy behavior.
- In dependency injection containers for optional or rarely-used services.
- When implementing lazy loading in application code.

##### Common Mistakes (__construct)

- Passing closures that have side effects or dependencies that might not be available later.
- Forgetting that the closure will execute in the context where get() is called, not where constructed.
- Using closures that return different objects on each call (defeats lazy caching).

### Method: get(): mixed

#### Technical Explanation (get)

Returns the lazily initialized object, executing the factory closure on first access and caching the result. Uses a
finally block to ensure initialization state is set even if factory throws, preventing infinite retry loops.

##### For Humans: What This Means (get)

This is the method you call to get your lazy object. The first time, it creates the expensive object using your factory.
Every time after that, it just gives you the same object back instantly.

##### Parameters (get)

- None.

##### Returns (get)

- `mixed`: The lazily created object, which can be any type returned by the factory.

##### Throws (get)

- `\Throwable`: Any exception thrown by the factory closure during object creation.

##### When to Use It (get)

- When you need access to the lazy-wrapped object in your application.
- In place of direct object access for lazily initialized dependencies.
- When implementing lazy loading in your own code.

##### Common Mistakes (get)

- Calling get() in tight loops without caching the result.
- Not handling exceptions that might be thrown by the factory.
- Assuming get() is always fast (first call can be expensive).

## Risks, Trade-offs & Recommended Practices

**Risk**: First access latency can be unpredictable in production.

**Why it matters**: Expensive object creation happens at runtime when first accessed, not during startup.

**Design stance**: Document lazy behavior and monitor first-access performance.

**Recommended practice**: Use lazy loading for truly optional dependencies and profile initialization costs.

**Risk**: Failed initialization leaves lazy object in an unusable state.

**Why it matters**: If factory throws, the lazy object becomes permanently unusable.

**Design stance**: Design factories to be robust and consider retry mechanisms for transient failures.

**Recommended practice**: Test lazy object creation thoroughly and handle initialization failures gracefully.

**Risk**: Memory leaks from cached objects that are never garbage collected.

**Why it matters**: Lazy objects hold references to created objects indefinitely.

**Design stance**: Use lazy loading judiciously and consider weak references for large objects.

**Recommended practice**: Profile memory usage and use lazy loading primarily for expensive-to-create objects.

### For Humans: What This Means (Risks)

LazyValue is powerful but requires careful consideration of its trade-offs. The performance benefits are real, but so
are the potential pitfalls. The key is using it for the right scenarios and designing robust factory closures.

## Related Files & Folders

**LazyInterface**: Defines the contract that LazyValue implements. You use it when typing lazy objects. It provides the
behavioral specification.

**Advanced/**: Contains LazyValue as part of advanced injection capabilities. You encounter it when using lazy features.
It provides the broader context for advanced injection.

**Actions/**: Includes lazy initialization as part of the injection action system. You use it when implementing lazy
injection behaviors. It connects lazy loading to dependency injection.

### For Humans: What This Means (Related)

LazyValue works with a complete lazy ecosystem. The interface defines the contract, Advanced provides context, Actions
connects to injection. Together they create comprehensive lazy initialization capabilities.