# Uri

The canonical URI model implementing PSR-7 UriInterface.

## Responsibilities

- Hold immutable URI state
- Provide PSR-7 interface methods
- Delegate parsing to ParseUriString
- Render complete URI string

## Key Methods

- `fromString(string)`: Parse and create instance
- `with*(...)`: Return modified copy
- `__toString()`: Render URI