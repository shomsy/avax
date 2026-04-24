# Collection

`Collection` is the fluent state owner for DataFoundation.

It delegates most behavior into feature-sliced operation owners under `Collections/*`, keeping the public façade
readable while still supporting a broad in-memory API surface.
