# RequestFromGlobalsTest

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
Think of this file like a smoke alarm. It’s not part of the house’s normal function, but it screams when something is wrong.

### For Humans: What This Means (Think)
You don’t “use” tests in production—you rely on them to catch problems before production.

## Story Example
You refactor part of the container (resolution, scopes, boot flow). Locally everything seems fine. You run the test suite and this file tells you immediately whether the refactor kept the original behavior.

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
PHPUnit discovers the test class and runs its public test methods. The test methods arrange container state (builders, bindings, fake implementations), execute the behavior under test (resolve, boot, run), and assert the observable results.

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

### Method: __construct(private bool $hasSession, #[SensitiveParameter] private mixed $session)

#### Technical Explanation (__construct)
This method is part of the test scenario in this file. It either executes a test assertion (for PHPUnit test methods) or supports the test flow (for helper/fake classes).

##### For Humans: What This Means (__construct)
When you run the test suite, this is one of the steps that proves a specific promise the container makes.

##### Parameters (__construct)
- See the method signature; parameters here are test inputs or collaborators for the scenario.

##### Returns (__construct)
- Returns `void`. In tests, return values are usually less important than assertions.

##### Throws (__construct)
- PHPUnit assertions may fail; the underlying code under test may throw depending on the scenario.

##### When to Use It (__construct)
- You don’t call this manually in production; it’s executed by PHPUnit as part of validation.

##### Common Mistakes (__construct)
- Making this test depend on external state (filesystem, globals) without isolating it.


### Method: has(string $id)

#### Technical Explanation (has)
This method is part of the test scenario in this file. It either executes a test assertion (for PHPUnit test methods) or supports the test flow (for helper/fake classes).

##### For Humans: What This Means (has)
When you run the test suite, this is one of the steps that proves a specific promise the container makes.

##### Parameters (has)
- See the method signature; parameters here are test inputs or collaborators for the scenario.

##### Returns (has)
- Returns `bool`. In tests, return values are usually less important than assertions.

##### Throws (has)
- PHPUnit assertions may fail; the underlying code under test may throw depending on the scenario.

##### When to Use It (has)
- You don’t call this manually in production; it’s executed by PHPUnit as part of validation.

##### Common Mistakes (has)
- Making this test depend on external state (filesystem, globals) without isolating it.


### Method: get(string $id)

#### Technical Explanation (get)
This method is part of the test scenario in this file. It either executes a test assertion (for PHPUnit test methods) or supports the test flow (for helper/fake classes).

##### For Humans: What This Means (get)
When you run the test suite, this is one of the steps that proves a specific promise the container makes.

##### Parameters (get)
- See the method signature; parameters here are test inputs or collaborators for the scenario.

##### Returns (get)
- Returns `mixed`. In tests, return values are usually less important than assertions.

##### Throws (get)
- PHPUnit assertions may fail; the underlying code under test may throw depending on the scenario.

##### When to Use It (get)
- You don’t call this manually in production; it’s executed by PHPUnit as part of validation.

##### Common Mistakes (get)
- Making this test depend on external state (filesystem, globals) without isolating it.


### Method: testCreateFromGlobalsIgnoresInvalidSessionBinding()

#### Technical Explanation (testCreateFromGlobalsIgnoresInvalidSessionBinding)
This method is part of the test scenario in this file. It either executes a test assertion (for PHPUnit test methods) or supports the test flow (for helper/fake classes).

##### For Humans: What This Means (testCreateFromGlobalsIgnoresInvalidSessionBinding)
When you run the test suite, this is one of the steps that proves a specific promise the container makes.

##### Parameters (testCreateFromGlobalsIgnoresInvalidSessionBinding)
- See the method signature; parameters here are test inputs or collaborators for the scenario.

##### Returns (testCreateFromGlobalsIgnoresInvalidSessionBinding)
- Returns `void`. In tests, return values are usually less important than assertions.

##### Throws (testCreateFromGlobalsIgnoresInvalidSessionBinding)
- PHPUnit assertions may fail; the underlying code under test may throw depending on the scenario.

##### When to Use It (testCreateFromGlobalsIgnoresInvalidSessionBinding)
- You don’t call this manually in production; it’s executed by PHPUnit as part of validation.

##### Common Mistakes (testCreateFromGlobalsIgnoresInvalidSessionBinding)
- Making this test depend on external state (filesystem, globals) without isolating it.


### Method: testCreateFromGlobalsUsesSessionInterface()

#### Technical Explanation (testCreateFromGlobalsUsesSessionInterface)
This method is part of the test scenario in this file. It either executes a test assertion (for PHPUnit test methods) or supports the test flow (for helper/fake classes).

##### For Humans: What This Means (testCreateFromGlobalsUsesSessionInterface)
When you run the test suite, this is one of the steps that proves a specific promise the container makes.

##### Parameters (testCreateFromGlobalsUsesSessionInterface)
- See the method signature; parameters here are test inputs or collaborators for the scenario.

##### Returns (testCreateFromGlobalsUsesSessionInterface)
- Returns `void`. In tests, return values are usually less important than assertions.

##### Throws (testCreateFromGlobalsUsesSessionInterface)
- PHPUnit assertions may fail; the underlying code under test may throw depending on the scenario.

##### When to Use It (testCreateFromGlobalsUsesSessionInterface)
- You don’t call this manually in production; it’s executed by PHPUnit as part of validation.

##### Common Mistakes (testCreateFromGlobalsUsesSessionInterface)
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
This method is part of the file’s public/protected behavior surface. It exists to make a specific step in the container’s workflow explicit and reusable.

##### For Humans: What This Means (app)
When you call this (or when the container calls it), you’re asking the system to do one focused thing without you having to manually wire the details.

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

### Method: appInstance(...)

#### Technical Explanation (appInstance)
This method is part of the file’s public/protected behavior surface. It exists to make a specific step in the container’s workflow explicit and reusable.

##### For Humans: What This Means (appInstance)
When you call this (or when the container calls it), you’re asking the system to do one focused thing without you having to manually wire the details.

##### Parameters (appInstance)
- See the PHP signature in the source file for exact types and intent.

##### Returns (appInstance)
- See the PHP signature and implementation for what comes back and why it matters.

##### Throws (appInstance)
- Any thrown exceptions here are part of the “contract” you need to be ready for when integrating this method.

##### When to Use It (appInstance)
- Use it when you want this unit of behavior, not when you want to re-implement the underlying steps.

##### Common Mistakes (appInstance)
- Calling it in the wrong lifecycle moment (before the container is configured/booted).
- Treating it as a pure function when it may read or affect container state.

### Method: setUp(...)

#### Technical Explanation (setUp)
This method is part of the file’s public/protected behavior surface. It exists to make a specific step in the container’s workflow explicit and reusable.

##### For Humans: What This Means (setUp)
When you call this (or when the container calls it), you’re asking the system to do one focused thing without you having to manually wire the details.

##### Parameters (setUp)
- See the PHP signature in the source file for exact types and intent.

##### Returns (setUp)
- See the PHP signature and implementation for what comes back and why it matters.

##### Throws (setUp)
- Any thrown exceptions here are part of the “contract” you need to be ready for when integrating this method.

##### When to Use It (setUp)
- Use it when you want this unit of behavior, not when you want to re-implement the underlying steps.

##### Common Mistakes (setUp)
- Calling it in the wrong lifecycle moment (before the container is configured/booted).
- Treating it as a pure function when it may read or affect container state.

### Method: tearDown(...)

#### Technical Explanation (tearDown)
This method is part of the file’s public/protected behavior surface. It exists to make a specific step in the container’s workflow explicit and reusable.

##### For Humans: What This Means (tearDown)
When you call this (or when the container calls it), you’re asking the system to do one focused thing without you having to manually wire the details.

##### Parameters (tearDown)
- See the PHP signature in the source file for exact types and intent.

##### Returns (tearDown)
- See the PHP signature and implementation for what comes back and why it matters.

##### Throws (tearDown)
- Any thrown exceptions here are part of the “contract” you need to be ready for when integrating this method.

##### When to Use It (tearDown)
- Use it when you want this unit of behavior, not when you want to re-implement the underlying steps.

##### Common Mistakes (tearDown)
- Calling it in the wrong lifecycle moment (before the container is configured/booted).
- Treating it as a pure function when it may read or affect container state.
