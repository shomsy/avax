# How This Works

`Query/IR` is the intermediate representation layer for query analysis and deterministic SQL generation.

- `IRBuilder.php` builds `QueryNode` graphs fluently.
- `IRTransformer.php` renders IR into SQL and fingerprints.
- `IRValidator.php` performs structural validation before execution.
- `IRNormalizer.php` produces canonical payloads for diffing and caching.
- `IRCache.php` stores derived artifacts keyed by normalized IR.
- `Nodes/` contains the actual immutable query parts.

This slice exists so optimization, validation, and telemetry can operate on one stable shape instead of raw builder
state.
