# KernelTest

## Quick Summary

- This file contains PHPUnit tests that validate Container component behavior.
- It exists so changes to the container stay safe: the expected behavior is locked in by executable checks.
- It removes the complexity of “did we break something?” by turning expectations into repeatable assertions.

### For Humans: What This Means (Summary)

These tests are your safety net. If they pass, you didn’t break the promises this component makes.

## Terminology (MANDATORY, EXPANSIVE)

- **PHPUnit**: The test runner and assertion library.
    - In this file: it executes test methods and reports failures.
    - Why it matters: it’s the contract-enforcer for behavior.
- **Arrange / Act / Assert**: The mental model for tests.
    - In this file: you set up inputs (arrange), run the behavior (act), and check outcomes (assert).
    - Why it matters: it keeps tests readable and reliable.
- **Fixture**: Any data or helper used to run a test.
    - In this file: fixtures can be temp directories, fake implementations, or tiny helper classes.
    - Why it matters: good fixtures make tests stable; bad fixtures make them flaky.
- **Regression**: A bug that returns after being fixed.
    - In this file: each test is a guardrail against a known class of regressions.
    - Why it matters: regressions waste time and trust.

### For Humans: What This Means (Terms)

If you don’t name your expectations, you’ll re-learn the same bugs repeatedly. Tests are how you stop that.

## Think of It

Think of this file like a smoke alarm. It’s not part of the house’s normal function, but it screams when something is
wrong.

### For Humans: What This Means (Think)

You don’t “use” tests in production—you rely on them to catch problems before production.

## Story Example

You refactor part of the container (resolution, scopes, boot flow). Locally everything seems fine. You run the test
suite and this file tells you immediately whether the refactor kept the original behavior.

### For Humans: What This Means (Story)

The tests let you move fast without guessing.

## For Dummies

1. Read the test method name: it tells you what promise is being checked.
2. Look at the setup: it shows what the container needs for this scenario.
3. Look at the assertions: they define “success”.
4. If it fails, treat the failure as either:
    - a real regression (most likely), or
    - a test that depended on unstable state (needs isolation).

### For Humans: What This Means (Dummies)

When a test fails, it’s not “annoying noise”. It’s a clue about a promise that just got broken.

## How It Works (Technical)

PHPUnit discovers the test class and runs its public test methods. The test methods arrange container state (builders,
bindings, fake implementations), execute the behavior under test (resolve, boot, run), and assert the observable
results.

### For Humans: What This Means (How)

It’s just “set it up, run it, check it”—but done consistently so failures are meaningful.

## Architecture Role

- Why this file lives in `tests/`: it validates the container from the outside, like a user of the API.
- What depends on it: your confidence in changes, CI gates, and safe refactors.
- What it depends on: PHPUnit and the container APIs under test.
- System-level reasoning: a container is a foundational component; tests keep its behavior stable as the project grows.

### For Humans: What This Means (Role)

If the container is your plumbing, tests are the pressure checks that stop leaks.

## Methods

This section documents the public methods defined in this file (tests and any helper classes used by tests).

### For Humans: What This Means (Methods)

If you want to understand what this file proves, scan the method list and their intent.

### Method: testKernelMiddlewareConfigFallbacksToEmptyArray()

#### Technical Explanation (testKernelMiddlewareConfigFallbacksToEmptyArray)

This method is part of the test scenario in this file. It either executes a test assertion (for PHPUnit test methods) or
supports the test flow (for helper/fake classes).

##### For Humans: What This Means (testKernelMiddlewareConfigFallbacksToEmptyArray)

When you run the test suite, this is one of the steps that proves a specific promise the container makes.

##### Parameters (testKernelMiddlewareConfigFallbacksToEmptyArray)

- See the method signature; parameters here are test inputs or collaborators for the scenario.

##### Returns (testKernelMiddlewareConfigFallbacksToEmptyArray)

- Returns `void`. In tests, return values are usually less important than assertions.

##### Throws (testKernelMiddlewareConfigFallbacksToEmptyArray)

- PHPUnit assertions may fail; the underlying code under test may throw depending on the scenario.

##### When to Use It (testKernelMiddlewareConfigFallbacksToEmptyArray)

- You don’t call this manually in production; it’s executed by PHPUnit as part of validation.

##### Common Mistakes (testKernelMiddlewareConfigFallbacksToEmptyArray)

- Making this test depend on external state (filesystem, globals) without isolating it.

### Method: testKernelMiddlewareConfigResolverFailureFallsBackToEmptyArray()

#### Technical Explanation (testKernelMiddlewareConfigResolverFailureFallsBackToEmptyArray)

This method is part of the test scenario in this file. It either executes a test assertion (for PHPUnit test methods) or
supports the test flow (for helper/fake classes).

##### For Humans: What This Means (testKernelMiddlewareConfigResolverFailureFallsBackToEmptyArray)

When you run the test suite, this is one of the steps that proves a specific promise the container makes.

##### Parameters (testKernelMiddlewareConfigResolverFailureFallsBackToEmptyArray)

- See the method signature; parameters here are test inputs or collaborators for the scenario.

##### Returns (testKernelMiddlewareConfigResolverFailureFallsBackToEmptyArray)

- Returns `void`. In tests, return values are usually less important than assertions.

##### Throws (testKernelMiddlewareConfigResolverFailureFallsBackToEmptyArray)

- PHPUnit assertions may fail; the underlying code under test may throw depending on the scenario.

##### When to Use It (testKernelMiddlewareConfigResolverFailureFallsBackToEmptyArray)

- You don’t call this manually in production; it’s executed by PHPUnit as part of validation.

##### Common Mistakes (testKernelMiddlewareConfigResolverFailureFallsBackToEmptyArray)

- Making this test depend on external state (filesystem, globals) without isolating it.

### Method: get(string $path, callable|array|string $action)

#### Technical Explanation (get)

This method is part of the test scenario in this file. It either executes a test assertion (for PHPUnit test methods) or
supports the test flow (for helper/fake classes).

##### For Humans: What This Means (get)

When you run the test suite, this is one of the steps that proves a specific promise the container makes.

##### Parameters (get)

- See the method signature; parameters here are test inputs or collaborators for the scenario.

##### Returns (get)

- Returns `RouteRegistrarProxy`. In tests, return values are usually less important than assertions.

##### Throws (get)

- PHPUnit assertions may fail; the underlying code under test may throw depending on the scenario.

##### When to Use It (get)

- You don’t call this manually in production; it’s executed by PHPUnit as part of validation.

##### Common Mistakes (get)

- Making this test depend on external state (filesystem, globals) without isolating it.

### Method: post(string $path, callable|array|string $action)

#### Technical Explanation (post)

This method is part of the test scenario in this file. It either executes a test assertion (for PHPUnit test methods) or
supports the test flow (for helper/fake classes).

##### For Humans: What This Means (post)

When you run the test suite, this is one of the steps that proves a specific promise the container makes.

##### Parameters (post)

- See the method signature; parameters here are test inputs or collaborators for the scenario.

##### Returns (post)

- Returns `RouteRegistrarProxy`. In tests, return values are usually less important than assertions.

##### Throws (post)

- PHPUnit assertions may fail; the underlying code under test may throw depending on the scenario.

##### When to Use It (post)

- You don’t call this manually in production; it’s executed by PHPUnit as part of validation.

##### Common Mistakes (post)

- Making this test depend on external state (filesystem, globals) without isolating it.

### Method: put(string $path, callable|array|string $action)

#### Technical Explanation (put)

This method is part of the test scenario in this file. It either executes a test assertion (for PHPUnit test methods) or
supports the test flow (for helper/fake classes).

##### For Humans: What This Means (put)

When you run the test suite, this is one of the steps that proves a specific promise the container makes.

##### Parameters (put)

- See the method signature; parameters here are test inputs or collaborators for the scenario.

##### Returns (put)

- Returns `RouteRegistrarProxy`. In tests, return values are usually less important than assertions.

##### Throws (put)

- PHPUnit assertions may fail; the underlying code under test may throw depending on the scenario.

##### When to Use It (put)

- You don’t call this manually in production; it’s executed by PHPUnit as part of validation.

##### Common Mistakes (put)

- Making this test depend on external state (filesystem, globals) without isolating it.

### Method: patch(string $path, callable|array|string $action)

#### Technical Explanation (patch)

This method is part of the test scenario in this file. It either executes a test assertion (for PHPUnit test methods) or
supports the test flow (for helper/fake classes).

##### For Humans: What This Means (patch)

When you run the test suite, this is one of the steps that proves a specific promise the container makes.

##### Parameters (patch)

- See the method signature; parameters here are test inputs or collaborators for the scenario.

##### Returns (patch)

- Returns `RouteRegistrarProxy`. In tests, return values are usually less important than assertions.

##### Throws (patch)

- PHPUnit assertions may fail; the underlying code under test may throw depending on the scenario.

##### When to Use It (patch)

- You don’t call this manually in production; it’s executed by PHPUnit as part of validation.

##### Common Mistakes (patch)

- Making this test depend on external state (filesystem, globals) without isolating it.

### Method: delete(string $path, callable|array|string $action)

#### Technical Explanation (delete)

This method is part of the test scenario in this file. It either executes a test assertion (for PHPUnit test methods) or
supports the test flow (for helper/fake classes).

##### For Humans: What This Means (delete)

When you run the test suite, this is one of the steps that proves a specific promise the container makes.

##### Parameters (delete)

- See the method signature; parameters here are test inputs or collaborators for the scenario.

##### Returns (delete)

- Returns `RouteRegistrarProxy`. In tests, return values are usually less important than assertions.

##### Throws (delete)

- PHPUnit assertions may fail; the underlying code under test may throw depending on the scenario.

##### When to Use It (delete)

- You don’t call this manually in production; it’s executed by PHPUnit as part of validation.

##### Common Mistakes (delete)

- Making this test depend on external state (filesystem, globals) without isolating it.

### Method: options(string $path, callable|array|string $action)

#### Technical Explanation (options)

This method is part of the test scenario in this file. It either executes a test assertion (for PHPUnit test methods) or
supports the test flow (for helper/fake classes).

##### For Humans: What This Means (options)

When you run the test suite, this is one of the steps that proves a specific promise the container makes.

##### Parameters (options)

- See the method signature; parameters here are test inputs or collaborators for the scenario.

##### Returns (options)

- Returns `RouteRegistrarProxy`. In tests, return values are usually less important than assertions.

##### Throws (options)

- PHPUnit assertions may fail; the underlying code under test may throw depending on the scenario.

##### When to Use It (options)

- You don’t call this manually in production; it’s executed by PHPUnit as part of validation.

##### Common Mistakes (options)

- Making this test depend on external state (filesystem, globals) without isolating it.

### Method: head(string $path, callable|array|string $action)

#### Technical Explanation (head)

This method is part of the test scenario in this file. It either executes a test assertion (for PHPUnit test methods) or
supports the test flow (for helper/fake classes).

##### For Humans: What This Means (head)

When you run the test suite, this is one of the steps that proves a specific promise the container makes.

##### Parameters (head)

- See the method signature; parameters here are test inputs or collaborators for the scenario.

##### Returns (head)

- Returns `RouteRegistrarProxy`. In tests, return values are usually less important than assertions.

##### Throws (head)

- PHPUnit assertions may fail; the underlying code under test may throw depending on the scenario.

##### When to Use It (head)

- You don’t call this manually in production; it’s executed by PHPUnit as part of validation.

##### Common Mistakes (head)

- Making this test depend on external state (filesystem, globals) without isolating it.

### Method: any(string $path, callable|array|string $action)

#### Technical Explanation (any)

This method is part of the test scenario in this file. It either executes a test assertion (for PHPUnit test methods) or
supports the test flow (for helper/fake classes).

##### For Humans: What This Means (any)

When you run the test suite, this is one of the steps that proves a specific promise the container makes.

##### Parameters (any)

- See the method signature; parameters here are test inputs or collaborators for the scenario.

##### Returns (any)

- Returns `array`. In tests, return values are usually less important than assertions.

##### Throws (any)

- PHPUnit assertions may fail; the underlying code under test may throw depending on the scenario.

##### When to Use It (any)

- You don’t call this manually in production; it’s executed by PHPUnit as part of validation.

##### Common Mistakes (any)

- Making this test depend on external state (filesystem, globals) without isolating it.

### Method: fallback(callable|array|string $handler)

#### Technical Explanation (fallback)

This method is part of the test scenario in this file. It either executes a test assertion (for PHPUnit test methods) or
supports the test flow (for helper/fake classes).

##### For Humans: What This Means (fallback)

When you run the test suite, this is one of the steps that proves a specific promise the container makes.

##### Parameters (fallback)

- See the method signature; parameters here are test inputs or collaborators for the scenario.

##### Returns (fallback)

- Returns `void`. In tests, return values are usually less important than assertions.

##### Throws (fallback)

- PHPUnit assertions may fail; the underlying code under test may throw depending on the scenario.

##### When to Use It (fallback)

- You don’t call this manually in production; it’s executed by PHPUnit as part of validation.

##### Common Mistakes (fallback)

- Making this test depend on external state (filesystem, globals) without isolating it.

### Method: resolve(Request $request)

#### Technical Explanation (resolve)

This method is part of the test scenario in this file. It either executes a test assertion (for PHPUnit test methods) or
supports the test flow (for helper/fake classes).

##### For Humans: What This Means (resolve)

When you run the test suite, this is one of the steps that proves a specific promise the container makes.

##### Parameters (resolve)

- See the method signature; parameters here are test inputs or collaborators for the scenario.

##### Returns (resolve)

- Returns `ResponseInterface`. In tests, return values are usually less important than assertions.

##### Throws (resolve)

- PHPUnit assertions may fail; the underlying code under test may throw depending on the scenario.

##### When to Use It (resolve)

- You don’t call this manually in production; it’s executed by PHPUnit as part of validation.

##### Common Mistakes (resolve)

- Making this test depend on external state (filesystem, globals) without isolating it.

### Method: emergency(string|Stringable $message, array $context = [])

#### Technical Explanation (emergency)

This method is part of the test scenario in this file. It either executes a test assertion (for PHPUnit test methods) or
supports the test flow (for helper/fake classes).

##### For Humans: What This Means (emergency)

When you run the test suite, this is one of the steps that proves a specific promise the container makes.

##### Parameters (emergency)

- See the method signature; parameters here are test inputs or collaborators for the scenario.

##### Returns (emergency)

- Returns `void`. In tests, return values are usually less important than assertions.

##### Throws (emergency)

- PHPUnit assertions may fail; the underlying code under test may throw depending on the scenario.

##### When to Use It (emergency)

- You don’t call this manually in production; it’s executed by PHPUnit as part of validation.

##### Common Mistakes (emergency)

- Making this test depend on external state (filesystem, globals) without isolating it.

### Method: alert(string|Stringable $message, array $context = [])

#### Technical Explanation (alert)

This method is part of the test scenario in this file. It either executes a test assertion (for PHPUnit test methods) or
supports the test flow (for helper/fake classes).

##### For Humans: What This Means (alert)

When you run the test suite, this is one of the steps that proves a specific promise the container makes.

##### Parameters (alert)

- See the method signature; parameters here are test inputs or collaborators for the scenario.

##### Returns (alert)

- Returns `void`. In tests, return values are usually less important than assertions.

##### Throws (alert)

- PHPUnit assertions may fail; the underlying code under test may throw depending on the scenario.

##### When to Use It (alert)

- You don’t call this manually in production; it’s executed by PHPUnit as part of validation.

##### Common Mistakes (alert)

- Making this test depend on external state (filesystem, globals) without isolating it.

### Method: critical(string|Stringable $message, array $context = [])

#### Technical Explanation (critical)

This method is part of the test scenario in this file. It either executes a test assertion (for PHPUnit test methods) or
supports the test flow (for helper/fake classes).

##### For Humans: What This Means (critical)

When you run the test suite, this is one of the steps that proves a specific promise the container makes.

##### Parameters (critical)

- See the method signature; parameters here are test inputs or collaborators for the scenario.

##### Returns (critical)

- Returns `void`. In tests, return values are usually less important than assertions.

##### Throws (critical)

- PHPUnit assertions may fail; the underlying code under test may throw depending on the scenario.

##### When to Use It (critical)

- You don’t call this manually in production; it’s executed by PHPUnit as part of validation.

##### Common Mistakes (critical)

- Making this test depend on external state (filesystem, globals) without isolating it.

### Method: error(string|Stringable $message, array $context = [])

#### Technical Explanation (error)

This method is part of the test scenario in this file. It either executes a test assertion (for PHPUnit test methods) or
supports the test flow (for helper/fake classes).

##### For Humans: What This Means (error)

When you run the test suite, this is one of the steps that proves a specific promise the container makes.

##### Parameters (error)

- See the method signature; parameters here are test inputs or collaborators for the scenario.

##### Returns (error)

- Returns `void`. In tests, return values are usually less important than assertions.

##### Throws (error)

- PHPUnit assertions may fail; the underlying code under test may throw depending on the scenario.

##### When to Use It (error)

- You don’t call this manually in production; it’s executed by PHPUnit as part of validation.

##### Common Mistakes (error)

- Making this test depend on external state (filesystem, globals) without isolating it.

### Method: warning(string|Stringable $message, array $context = [])

#### Technical Explanation (warning)

This method is part of the test scenario in this file. It either executes a test assertion (for PHPUnit test methods) or
supports the test flow (for helper/fake classes).

##### For Humans: What This Means (warning)

When you run the test suite, this is one of the steps that proves a specific promise the container makes.

##### Parameters (warning)

- See the method signature; parameters here are test inputs or collaborators for the scenario.

##### Returns (warning)

- Returns `void`. In tests, return values are usually less important than assertions.

##### Throws (warning)

- PHPUnit assertions may fail; the underlying code under test may throw depending on the scenario.

##### When to Use It (warning)

- You don’t call this manually in production; it’s executed by PHPUnit as part of validation.

##### Common Mistakes (warning)

- Making this test depend on external state (filesystem, globals) without isolating it.

### Method: notice(string|Stringable $message, array $context = [])

#### Technical Explanation (notice)

This method is part of the test scenario in this file. It either executes a test assertion (for PHPUnit test methods) or
supports the test flow (for helper/fake classes).

##### For Humans: What This Means (notice)

When you run the test suite, this is one of the steps that proves a specific promise the container makes.

##### Parameters (notice)

- See the method signature; parameters here are test inputs or collaborators for the scenario.

##### Returns (notice)

- Returns `void`. In tests, return values are usually less important than assertions.

##### Throws (notice)

- PHPUnit assertions may fail; the underlying code under test may throw depending on the scenario.

##### When to Use It (notice)

- You don’t call this manually in production; it’s executed by PHPUnit as part of validation.

##### Common Mistakes (notice)

- Making this test depend on external state (filesystem, globals) without isolating it.

### Method: info(string|Stringable $message, array $context = [])

#### Technical Explanation (info)

This method is part of the test scenario in this file. It either executes a test assertion (for PHPUnit test methods) or
supports the test flow (for helper/fake classes).

##### For Humans: What This Means (info)

When you run the test suite, this is one of the steps that proves a specific promise the container makes.

##### Parameters (info)

- See the method signature; parameters here are test inputs or collaborators for the scenario.

##### Returns (info)

- Returns `void`. In tests, return values are usually less important than assertions.

##### Throws (info)

- PHPUnit assertions may fail; the underlying code under test may throw depending on the scenario.

##### When to Use It (info)

- You don’t call this manually in production; it’s executed by PHPUnit as part of validation.

##### Common Mistakes (info)

- Making this test depend on external state (filesystem, globals) without isolating it.

### Method: debug(string|Stringable $message, array $context = [])

#### Technical Explanation (debug)

This method is part of the test scenario in this file. It either executes a test assertion (for PHPUnit test methods) or
supports the test flow (for helper/fake classes).

##### For Humans: What This Means (debug)

When you run the test suite, this is one of the steps that proves a specific promise the container makes.

##### Parameters (debug)

- See the method signature; parameters here are test inputs or collaborators for the scenario.

##### Returns (debug)

- Returns `void`. In tests, return values are usually less important than assertions.

##### Throws (debug)

- PHPUnit assertions may fail; the underlying code under test may throw depending on the scenario.

##### When to Use It (debug)

- You don’t call this manually in production; it’s executed by PHPUnit as part of validation.

##### Common Mistakes (debug)

- Making this test depend on external state (filesystem, globals) without isolating it.

### Method: log($level, string|Stringable $message, array $context = [])

#### Technical Explanation (log)

This method is part of the test scenario in this file. It either executes a test assertion (for PHPUnit test methods) or
supports the test flow (for helper/fake classes).

##### For Humans: What This Means (log)

When you run the test suite, this is one of the steps that proves a specific promise the container makes.

##### Parameters (log)

- See the method signature; parameters here are test inputs or collaborators for the scenario.

##### Returns (log)

- Returns `void`. In tests, return values are usually less important than assertions.

##### Throws (log)

- PHPUnit assertions may fail; the underlying code under test may throw depending on the scenario.

##### When to Use It (log)

- You don’t call this manually in production; it’s executed by PHPUnit as part of validation.

##### Common Mistakes (log)

- Making this test depend on external state (filesystem, globals) without isolating it.

## Risks, Trade-offs & Recommended Practices

- Risk: Flaky tests caused by globals, time, or filesystem state.
    - Why it matters: flaky tests destroy trust and slow you down.
    - Design stance: tests must be deterministic.
    - Recommended practice: isolate globals, use temp dirs, and avoid relying on execution order.
- Trade-off: Integration tests are slower but more realistic.
    - Why it matters: they catch bugs unit tests miss.
    - Design stance: keep a balanced mix.
    - Recommended practice: keep integration tests focused and avoid unnecessary setup.

### For Humans: What This Means (Risks)

Fast tests keep you moving; realistic tests keep you safe. You need both.

## Related Files & Folders

- `docs_md/Container.md`: The main container API these tests aim to protect.
- `docs_md/Core/index.md`: Kernel and resolution pipeline documentation for deeper internals.
- `docs_md/Features/index.md`: Higher-level features that tests often exercise.

### For Humans: What This Means (Related)

When a test fails, the related docs help you find the right mental model for the failing behavior.

### Method: app(...)

#### Technical Explanation (app)

This method is part of the file’s public/protected behavior surface. It exists to make a specific step in the
container’s workflow explicit and reusable.

##### For Humans: What This Means (app)

When you call this (or when the container calls it), you’re asking the system to do one focused thing without you having
to manually wire the details.

##### Parameters (app)

- See the PHP signature in the source file for exact types and intent.

##### Returns (app)

- See the PHP signature and implementation for what comes back and why it matters.

##### Throws (app)

- Any thrown exceptions here are part of the “contract” you need to be ready for when integrating this method.

##### When to Use It (app)

- Use it when you want this unit of behavior, not when you want to re-implement the underlying steps.

##### Common Mistakes (app)

- Calling it in the wrong lifecycle moment (before the container is configured/booted).
- Treating it as a pure function when it may read or affect container state.

### Method: config(...)

#### Technical Explanation (config)

This method is part of the file’s public/protected behavior surface. It exists to make a specific step in the
container’s workflow explicit and reusable.

##### For Humans: What This Means (config)

When you call this (or when the container calls it), you’re asking the system to do one focused thing without you having
to manually wire the details.

##### Parameters (config)

- See the PHP signature in the source file for exact types and intent.

##### Returns (config)

- See the PHP signature and implementation for what comes back and why it matters.

##### Throws (config)

- Any thrown exceptions here are part of the “contract” you need to be ready for when integrating this method.

##### When to Use It (config)

- Use it when you want this unit of behavior, not when you want to re-implement the underlying steps.

##### Common Mistakes (config)

- Calling it in the wrong lifecycle moment (before the container is configured/booted).
- Treating it as a pure function when it may read or affect container state.
