# Intermediate Representation

The Database component now exposes a dedicated query IR layer under `Foundation/Database/System/Capabilities/Query/IR`.

Use this layer when you need:

- deterministic query fingerprints
- static validation before SQL rendering
- canonical payloads for caching or diffing
- a stable structure for future query analysis

Core parts:

- `IRBuilder` for fluent node construction
- `IRTransformer` for SQL rendering
- `IRValidator` for structural checks
- `IRNormalizer` for canonical arrays and hashes
- `IRCache` for memoized derived artifacts

The IR is intentionally separate from the main `QueryState` builder path so advanced concerns do not overload the common
path.
