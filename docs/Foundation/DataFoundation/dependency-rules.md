# Dependency Rules

The dependency rules are stricter than naming convenience.

1. `Option` and `Result` are semantic values, not mini collections.
2. `Set`, `Map`, `Stack`, `Queue`, and `Pipeline` must preserve their own invariants instead of delegating their public
   meaning to `Collection`.
3. `Interop` can know about public types. Public types cannot know about `Interop`.
4. `Internal/` can be reused everywhere below the public API line, but it must not become a user-facing product.
5. `Arrhae` and `Collection` remain public root entrypoints, but they no longer define the entire component mission.
