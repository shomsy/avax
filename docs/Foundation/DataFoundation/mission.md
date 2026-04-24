# Mission

DataFoundation is the foundation layer for expressing, organizing, and processing data in PHP.

Its job is to give PHP code semantically honest public types above raw arrays and scalars while keeping mechanics
reusable and boring underneath.

The package is:

- immutable-first, but pragmatic
- organized by public semantics first
- explicit about invariants
- careful about dependency direction

The package is not:

- a helper drawer
- an ORM
- a validator framework
- a serializer framework pretending to be a data model
- a place where `Collection` impersonates every other data concept
