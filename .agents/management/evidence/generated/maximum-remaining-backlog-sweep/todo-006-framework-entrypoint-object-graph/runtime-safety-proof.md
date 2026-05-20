# Runtime Safety Proof

## Runtime Safety Change

Slice A removes lazy `RunApplication` dispatcher construction from `App`.

## Worker Safety Impact

Improved:

- required dispatcher graph exists before request handling
- no per-first-request lazy dispatcher assembly remains in `App`
- runtime flow no longer assembles its own default dependencies

Unchanged:

- request scope open/close behavior in `App::handle()`
- runtime context finish behavior
- existing metrics collection behavior

## Hot Path Impact

Request-time object graph assembly is reduced by removing `ensureInitialized()` from `App::handle()` and `App::handleRequest()`.

No benchmark claim is made.

## Remaining Runtime Safety Debt

- `App::handle()` still constructs request scope helper objects.
- `App::as*Kernel()` still constructs compatibility adapters.
- `BootDsl::create()` and `Avax::create()` still assemble object graphs.

## Classification

WORKER_SAFE for Slice A.

TODO-006 overall remains PARTIAL.
