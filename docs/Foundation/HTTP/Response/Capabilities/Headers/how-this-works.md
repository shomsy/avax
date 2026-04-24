# Headers

The headers capability centralizes:

- header name validation
- header value validation
- case-insensitive storage
- append, replace, remove, read, and read-line behavior

`ResponseMessage` delegates all header handling here instead of keeping header logic inline.
