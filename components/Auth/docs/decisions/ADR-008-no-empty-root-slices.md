# ADR-008: No Empty Root Slices

Status: accepted

A root slice may exist only when it has a real owner and real runtime or governance value.

Disallowed:

- cosmetic folders
- generic hallway folders
- second names for the same concept

Consequence:

- every root zone must expose either a real owner unit or a real package boundary
- if a slice cannot defend its ownership, it should not exist
