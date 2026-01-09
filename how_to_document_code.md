Above ordering can evolve, but keep it consistent across pages.

Each module section lists classes folder-by-folder, file-by-file.

## Per-Class Page Template

Each class page should contain:
1) Hero section: name, purpose, source path.
2) Class Overview card with a short summary.
3) `<details class="more">` with long-form explanation:
   - Why the class exists
   - How it fits in the container lifecycle
   - Terminology definitions
4) Story Example section (where relevant)
5) Methods section with per-method cards:
   - Short summary on top
   - `<details class="more">` with the long explanation

## What "More" Should Include

For every class and public method, the "More" section should be an essay-level,
human-readable explanation:
- Explain what happens internally, step-by-step.
- Include "why" it exists, not just "what" it does.
- Define technical terms in plain language.
- Mention scope/lifetime behavior when relevant.

## Standard Terms (explain consistently)

- Abstract: the identifier used to request a service (often an interface).
- Concrete: the class or factory that creates the instance.
- Binding: the rule that maps abstract to concrete.
- Scope: the lifetime boundary for shared instances.
- Resolution: turning an abstract into a concrete instance.
- Prototype: a precomputed build recipe used to avoid repeated reflection.
- Pipeline: a fixed sequence of runtime steps in resolution.

## Story Example Template

Use this narrative consistently to teach dependency inversion:

```
interface UserRepositoryInterface {}
class DatabaseUserRepository implements UserRepositoryInterface {}

class UserService {
    public function __construct(private UserRepositoryInterface $repo) {}
}

$container = ContainerBuilder::create()->build();
$container->bind(UserRepositoryInterface::class, DatabaseUserRepository::class);

$service = $container->get(UserService::class);
// Container builds UserService and injects DatabaseUserRepository.
```

Explain the internal flow:
1) The binding is stored in the DefinitionStore.
2) ResolutionEngine inspects constructor parameters.
3) It finds the binding for the interface.
4) It builds the repository, then the service.
5) It caches the instance if the lifetime requires it.

## Docblock Rules (PSR Style)

Keep docblocks short and factual:
- Purpose in 1-2 sentences.
- Params/returns with types.
- Exceptions thrown.
- Invariants or constraints if any.

Use `@see` to link to the HTML docs:

```
/**
 * Resolve a service by identifier.
 *
 * @param string $id
 * @return mixed
 * @throws ResolutionException
 * @see docs/container/Resolve/ResolutionEngine.html#resolve
 */
```

## Writing Style

- English only.
- No marketing tone. Clear, human, technical.
- Prefer short sentences for summaries, longer paragraphs for "More".
- Explain "why" and "how", not just "what".

## Order of Documentation

Document modules in this order:
1) Core (Facade, Engine, Builder, Config, Contracts, Exceptions, Value objects)
2) Resolve (Engine, Context, Pipeline, Pipes)
3) Think (Prototypes, Cache, Analyze)
4) Act (Inject/Invoke)
5) Operate (Lifecycle/Scope)
6) Observe + Guard + Tools

## Maintenance Rules

- Update HTML docs when method behavior changes.
- Keep `@see` links aligned with class/method anchors.
- Always add anchors for new public methods.
