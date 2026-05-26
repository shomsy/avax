# Review Pack Generator Repair

**Date:** 2026-05-26

## Bug Fixed: STATS.md Template Interpolation

`tooling/governance/generate-review-packs.php` now computes STATS.md values before rendering markdown.

Fixed values:

```text
generatedAt: computed timestamp
otherFiles: total files minus PHP files minus markdown files
phpLinesApprox: rounded proportional estimate, guarded for zero files
```

The generated STATS.md output interpolates variables only. It no longer emits unevaluated PHP concatenation or expression fragments.

## Additional Fixes

1. `ARCHITECTURE.md` is included in pack `01-governance-architecture`.
2. Metadata validation fails pack generation if generated metadata contains unevaluated PHP source or template fragments.
3. The generator writes root `README.md`, `MANIFEST.md`, and `manifest.json`.
4. The manifest distinguishes source files copied before metadata from actual ZIP entries after metadata.
5. Forbidden paths and secret-like files are filtered from pack staging.
