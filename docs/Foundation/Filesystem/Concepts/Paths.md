# Paths

`Paths/*` is the lowest-level capability slice.

It answers simple questions about a path:

- does it exist
- is it writable
- is it a directory
- does it have the expected permissions
- can permissions be changed

These classes are intentionally narrow and avoid becoming a second general-purpose facade.
