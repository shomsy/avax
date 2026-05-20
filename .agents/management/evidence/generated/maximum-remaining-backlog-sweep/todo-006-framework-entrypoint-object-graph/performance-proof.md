# Performance Proof

## Proof Type

Static design proof.

## Before

`App::handle()` and `App::handleRequest()` called `ensureInitialized()`, allowing the dispatch object graph to be assembled lazily from the public/runtime path.

## After

`App` receives a ready `RunApplication` dispatcher. The default dispatch graph is assembled through `BuildRunApplication` before request handling.

## Limitations

No benchmark was run and no latency number is claimed.

## Decision

The hot path is improved by removing lazy assembly reachability, but the overall TODO remains YELLOW/PARTIAL because residual object-graph construction remains elsewhere.
