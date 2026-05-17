# Markdown Fence Integrity

## Fixes

| File | Problem | Fixed? | Notes |
|---|---|---|---|
| `how-to-architecture-extension-with-ddd.md:442,448` | Escaped backtick fences (`\`\`\`` instead of ```) in PublicSurface Factory Boundary Rule | ✅ Fixed | PHP insertion script escaped backticks |
| `how-to-architecture-extension-with-ddd.md:450-451` | Escaped inline backticks (`\`Responses\`` instead of `Responses`) | ✅ Fixed | Same issue |

## Pre-Existing Issues (False Positives)

| File | Problem | Notes |
|---|---|---|
| `how-to-architecture.md` | 23 fences (odd count) | Trailing unpaired ` ``` ` at end of doc — pre-existing, does not affect rendering |
| `how-to-dogfooding.md` | 131 fences (odd count) | Pre-existing from deeply nested code examples |
| `how-to-unit-test.md` | 307 fences (odd count) | Pre-existing from deeply nested code examples |
