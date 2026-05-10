# How SchemaGeneration Works

## 1. What This Component Does

Generates JSON Schema (draft 2020-12) documents from AvaX DataObject metadata. It reads property types, nullable flags, default values, and validation attributes from DataObject classes and converts them into structured JSON Schema that can be used for:

- Request payload validation
- Response payload documentation
- Client-side schema generation
- API contract publishing

The component converts PHP type information and validation attributes (Min, Max, Between, Email, RegexPattern, etc.) into their JSON Schema equivalents (minLength, maxLength, minimum, maximum, format, pattern, etc.).

## 2. What This Component Does NOT Do

- **Does not validate arbitrary JSON against remote schemas** — only validates PHP arrays against locally generated JsonSchemaDocument instances
- **Does not generate OpenAPI/Swagger documents** — produces raw JSON Schema only
- **Does not handle nested DataObject recursion** — nested objects are represented as generic `type: object`, not as expanded schemas
- **Does not read runtime validation state** — only reads static metadata (types, attributes, defaults)
- **Does not replace the DataTransfer component** — depends on it for shape inspection; does not duplicate that behavior
- **Does not support JSON Schema drafts older than 2020-12**

## 3. Public API

The public surface is a single static facade: `SchemaGeneration`

```php
use Avax\Components\API\SchemaGeneration\System\PublicSurface\SchemaGeneration;

// Generate schema from a DataObject class or instance
$schema = SchemaGeneration::fromDataObject(CreateUserRequest::class);
$schema = SchemaGeneration::fromDataObject($userObject);

// Generate schema specifically for request payloads
$requestSchema = SchemaGeneration::request(StoreUserRequest::class);

// Generate schema specifically for response payloads (excludes hidden fields)
$responseSchema = SchemaGeneration::response(UserResponse::class);

// Validate a payload array against a schema
$result = SchemaGeneration::validatePayload($payload, $schema);

if ($result->valid) {
    // payload passes
} else {
    foreach ($result->errors as $error) {
        // "Required field 'email' is missing."
    }
}
```

### Return Types

| Type | Purpose |
|------|---------|
| `JsonSchemaDocument` | Immutable value object containing schema ID and schema array. Has `toJson()` method for JSON output. |
| `PayloadValidationResult` | Immutable result with `valid` (bool) and `errors` (list\<string\>). Factory methods: `valid()`, `invalid(array $errors)`. |

### Exception

| Type | When Thrown |
|------|-------------|
| `SchemaGenerationFailed` | Unsupported type, missing data shape, or JSON encoding failure. Extends `LogicException`. |

## 4. Internal Flow

### Schema Generation Flow

```
SchemaGeneration::fromDataObject($class)
  └─ GenerateSchemaFromDataObject::execute()
       ├─ ReadDataObjectShape::read()
       │    └─ InspectDataShape::inspect()    ← from DataTransfer
       │         └─ Returns DataShape (fields, types, attributes)
       │
       └─ ConvertDataObjectShapeToJsonSchema::convert()
            ├─ Iterates DataShape fields
            ├─ Skips hidden fields
            ├─ For each field:
            │    ├─ typeToJsonSchemaFragment()  ← maps PHP type to JSON type
            │    └─ ConvertValidationAttributeToSchemaRule::apply()  ← adds constraints
            └─ Returns JsonSchemaDocument
```

### Request/Response Flows

Both `request()` and `response()` follow the same path through `ReadDataObjectShape` and `ConvertDataObjectShapeToJsonSchema`, but:

- **Request schema**: includes all non-hidden fields (all fields are visible for input)
- **Response schema**: converter skips fields marked as hidden via `isHidden()` on DataField

### Validation Flow

```
SchemaGeneration::validatePayload($payload, $schema)
  └─ ValidatePayloadAgainstSchema::execute()
       ├─ Checks required fields exist in payload
       ├─ For each payload field present in schema:
       │    └─ validateField()
       │         ├─ Type check (string, integer, number, boolean, object, array)
       │         ├─ String constraints (minLength, maxLength, pattern, email format)
       │         └─ Numeric constraints (minimum, maximum)
       └─ Returns PayloadValidationResult
```

## 5. Dependencies

| Dependency | Source | Used By |
|-----------|--------|---------|
| `DataObject` | `Avax\Components\DataStack\DataTransfer\System\PublicSurface\DataObject` | All flows — the source of shape metadata |
| `InspectDataShape` | `Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\InspectDataShape` | `ReadDataObjectShape` — reads property types and attributes |
| `DataShape` | `Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\DataShape` | Shape inspection result |
| `DataField` | `Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\DataField` | Individual field metadata |
| Validation attributes | `Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\*` | `ConvertValidationAttributeToSchemaRule` — maps attributes to schema rules |

No external packages. No network I/O. No database access.

## 6. Failure Behavior

All failures throw `SchemaGenerationFailed` (extends `LogicException`). Three factory methods:

| Factory | Trigger | Message |
|---------|---------|---------|
| `fromDataTransferFailure()` | DataTransfer inspection fails | `"Schema generation failed: {original message}"` |
| `unsupportedType()` | PHP type cannot map to JSON Schema | `"Cannot convert type '{type}' to JSON Schema in {context}."` |
| `missingDataShape()` | Class does not exist or is not a DataObject | `"Cannot read data shape for '{class}'. Class does not exist or is not a DataObject."` |

The component does not catch-and-ignore. Failures propagate immediately.

`JsonSchemaDocument::toJson()` throws `SchemaGenerationFailed` if `json_encode()` returns false (should not happen with valid schema arrays).

## 7. Runtime Safety

- **No I/O**: No filesystem, network, or database calls. Pure computation from reflection metadata.
- **Immutable return types**: Both `JsonSchemaDocument` and `PayloadValidationResult` are `readonly` classes.
- **Test injection point**: `SchemaGeneration::setAssembly()` allows replacing the assembly for testing. Should not be used in production code.
- **Static facade with lazy assembly**: The assembly is built once on first call and cached. Thread-safe for long-lived runtimes since the assembly is stateless.
- **No hidden state**: Flows are `readonly` classes. No mutable instance properties.

## 8. Examples

### Basic Schema Generation

```php
final class CreateUserRequest extends DataObject
{
    #[StringType]
    #[Min(3)]
    #[Max(50)]
    public string $name;

    #[Email]
    public string $email;

    #[IntegerType]
    #[Min(18)]
    #[Max(120)]
    public int $age;

    #[StringType]
    #[RegexPattern('/^[A-Za-z]+$/')]
    public ?string $middleName = null;
}

$schema = SchemaGeneration::fromDataObject(CreateUserRequest::class);
echo $schema->toJson();
```

Output:

```json
{
    "$schema": "https://json-schema.org/draft/2020-12/schema",
    "type": "object",
    "properties": {
        "name": { "type": "string", "minLength": 3, "maxLength": 50 },
        "email": { "type": "string", "format": "email" },
        "age": { "type": "integer", "minimum": 18, "maximum": 120 },
        "middleName": { "type": "string", "pattern": "^[A-Za-z]+$" }
    },
    "required": ["name", "email", "age"]
}
```

### Response Schema with Hidden Fields

```php
final class UserResponse extends DataObject
{
    public string $name;
    
    #[Hidden]
    public string $passwordHash;
    
    public string $email;
}

$schema = SchemaGeneration::response(UserResponse::class);
// passwordHash is excluded from the schema
```

### Payload Validation

```php
$schema = SchemaGeneration::request(CreateUserRequest::class);

$result = SchemaGeneration::validatePayload([
    'name' => 'Jo',       // too short (min 3)
    'email' => 'not-an-email',
    // age is missing (required)
], $schema);

// $result->valid === false
// $result->errors:
//   - "Required field 'age' is missing."
//   - "Field 'name' must be at least 3 characters."
//   - "Field 'email' must be a valid email address."
```

## 9. Known Limits

| Limit | Detail | Severity |
|-------|--------|----------|
| **No nested DataObject expansion** | Nested DataObject properties resolve to `type: object` without expanding their internal schema | Medium |
| **No array item type inference** | `array` properties map to `type: object` without `additionalProperties` constraints | Medium |
| **No union type support** | PHP union types (e.g., `string|int`) are not explicitly supported; only the primary type is used | Medium |
| **No `anyOf` / `oneOf` / `allOf`** | Generator does not produce combinator schemas | Low |
| **Validation is subset-only** | `ValidatePayloadAgainstSchema` implements only required, type, string length, pattern, email, and numeric range. Does not validate nested objects, array items, or `additionalProperties` | Medium |
| **No schema composition** | Cannot generate `$ref` references between schemas | Low |
| **No format enumeration** | Only `email` format is mapped. Other JSON Schema formats (date-time, uri, uuid) are not supported | Low |

## 10. Current Status

**YELLOW**

| Evidence | Status |
|----------|--------|
| Public API | GREEN — 4 static methods + 1 injection point |
| Flows | GREEN — 4 flows, all tested |
| Capabilities | GREEN — 5 capabilities, all tested |
| Foundation types | GREEN — 3 types (JsonSchemaDocument, PayloadValidationResult, SchemaGenerationFailed) |
| Configuration | GREEN — BuildSchemaGeneration + SchemaGenerationAssembly |
| Tests | GREEN — 8 test classes covering flows, capabilities, and foundation |
| Documentation | YELLOW — this file is the first component documentation |
| Validation edge cases | YELLOW — nested objects, union types, and array items are not fully covered |
| JSON Schema draft compliance | YELLOW — claims 2020-12 but does not implement full spec |

### To Reach GREEN

- Document nested DataObject expansion strategy (support or explicitly reject)
- Add tests or explicit documentation for union type behavior
- Decide whether `ValidatePayloadAgainstSchema` should expand to full JSON Schema validation or remain a focused subset
- If remaining a subset, document which JSON Schema keywords are intentionally unsupported
