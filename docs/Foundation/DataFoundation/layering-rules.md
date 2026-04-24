# Layering Rules

DataFoundation follows one layering doctrine:

`public semantics up, reusable mechanics down`

Allowed dependency direction:

- `Values` -> `Internal`
- `Composites` -> `Internal`
- `Collections` -> `Internal`
- `Structures` -> `Internal`, and when justified `Collections`
- `Flows` -> `Internal`, `Collections`, and when justified `Structures`
- `Interop` -> any public DataFoundation type plus `Internal/Conversion`

Forbidden dependency direction:

- `Values` -> `Collections`, `Structures`, `Flows`, `Interop`
- `Composites` -> `Interop`
- `Public types` -> `Interop`
- sibling cross-lane dependencies that erase type identity

Practical reading rule:

- if a lower layer disappears, the public type should still make semantic sense
- if a higher layer disappears, lower mechanics should still remain boring and reusable
