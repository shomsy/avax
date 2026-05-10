# HOW_THIS_WORKS — DataStack/DataTransfer

## What This Component Does

DataTransfer is AvaX's DTO (Data Transfer Object) engine. It handles three responsibilities:

1. **Hydration** — creates typed objects from arrays or other objects using reflection
2. **Validation** — enforces field-level rules via PHP 8 attributes (`#[Required]`, `#[Email]`, `#[Max]`, etc.)
3. **Serialization** — converts typed objects back to arrays, JSON, stdClass, flat arrays, or JSON:API format

It is NOT HTTP-specific. It does not know about requests, containers, or heavy framework services. It works with plain PHP objects that have public typed properties.

## What This Component Does NOT Do

- Does NOT handle HTTP request parsing or routing
- Does NOT perform database queries or persistence
- Does NOT manage business logic or domain rules beyond field-level validation
- Does NOT handle authentication, authorization, or security (delegates to other components)
- Does NOT do heavy engine behavior — it is a thin doorway to focused Flows

## Public API

Two entry points:

### DataObject (abstract base class)

Extend this for your typed DTOs. Uses public typed properties + PHP attributes, no constructor required:

```php
use Avax\Components\DataStack\DataTransfer\System\PublicSurface\DataObject;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\{Required, Email, Max};

final class UserInput extends DataObject
{
    #[Required]
    #[Email]
    public string $email;

    #[Required]
    #[Max(100)]
    public string $name;

    #[Optional]
    public ?string $bio = null;
}
```

Convenience methods on every DataObject:
- `$dto->toArray()` — serialize to associative array
- `$dto->toJson(int $flags = 0)` — serialize to JSON string
- `$dto->toStdClass()` — serialize to stdClass

### DataTransfer (static facade)

```php
use Avax\Components\DataStack\DataTransfer\System\PublicSurface\DataTransfer;

// Create with validation — throws DataTransferFailure on error
$dto = DataTransfer::create(UserInput::class, ['email' => 'test@example.com', 'name' => 'Test']);

// Create without throwing — returns DataTransferResult (success/failure)
$result = DataTransfer::tryCreate(UserInput::class, ['email' => 'bad', 'name' => 'Test']);
if ($result->isFailure()) {
    $violations = $result->violations();
}

// Serialization
$array   = DataTransfer::toArray($dto);
$json    = DataTransfer::toJson($dto);
$flat    = DataTransfer::toFlatArray($dto);
$std     = DataTransfer::toStdClass($dto);
$jsonApi = DataTransfer::toJsonApi($dto, 'user');
```

### Configuration

```php
DataTransfer::configure(
    (new DataTransferConfig())
        ->withUnknownFieldPolicy(UnknownFieldPolicy::Reject) // or Ignore, Collect
        ->withNamingPolicy(fn(string $field) => strtolower($field))
);
```

## Internal Flow

### Creation Flow: DataTransfer::create() → CreateDataObject

1. **Resolve input name** — checks for `#[MapFrom('different_key')]` attribute, falls back to property name
2. **Check required/optional** — `#[Required]` fields must be present; `#[Optional]` fields may be absent
3. **Apply defaults** — `#[DefaultValue('x')]` provides fallback when field is absent
4. **Hydrate value** — recursively handles:
   - Custom casters via `#[CastWith(MyCaster::class)]` (partial — ValueCasterInterface needs DataField + ValueConversionContext, not fully wired)
   - Nested DTO lists via `#[ListOf(ChildDto::class)]`
   - Nested single DTOs (if type is a class, recursively calls create)
   - Backed enums (resolves via `EnumType::from($value)`)
5. **Type validation** — checks scalar types (string, int, float, bool, array) and class instances
6. **Attribute validation** — calls `validate($value, $field)` on any attribute that has the method
7. **Instantiate** — supports both constructor-promoted properties AND property-based hydration (no-constructor style)

### Hydrate-Into Flow: CreateDataObject::hydrateInto()

Hydrates an existing object's public properties from input. Returns violations list instead of throwing. Only touches properties that are in the input or have DTO attributes. Leaves other properties untouched (prevents overwriting internal state).

### Serialization Flow: DataTransfer::toArray() → SerializeDataObject

1. **Read values** — InspectDataShape reads class shape via reflection, respects `#[Hidden]` fields
2. **Normalize** — NormalizeDataObjectValue recursively converts nested objects/arrays/scalars
3. **Encode** — ConvertDataObjectToJson wraps toArray with json_encode + JSON_THROW_ON_ERROR

### JSON:API Flow

Converts to `['data' => ['type' => $type, 'id' => $id, 'attributes' => [...]]]` format. Requires the object to have an `id` property.

## Dependencies

- **PHP 8+** — relies on reflection, typed properties, attributes
- **No external packages** — pure PHP, no Laravel/Symfony dependency
- **Internal dependencies:**
  - `ReadClassDataShape` — reflection-based class inspection with caching
  - `CacheDataShape` — in-memory shape cache (keyed by class + config object ID)
  - `NormalizeDataObjectValue` — recursive value normalization with depth tracking (max depth configurable, default 32)
  - All validation attributes are self-contained

## Failure Behavior

| Scenario | Behavior |
|----------|----------|
| Missing `#[Required]` field | `DataTransferFailure` with `DataTransferViolation` per missing field |
| Type mismatch (e.g. string where int expected) | `DataTransferViolation` added to violations, then thrown as `DataTransferFailure` |
| Validation attribute fails (e.g. `#[Email]` on bad value) | `InvalidArgumentException` caught, wrapped in `DataTransferViolation` |
| `#[ListOf]` on non-array input | `DataTransferViolation` for that field |
| Nested DTO creation fails | `DataTransferFailure` bubbles up with all violations collected |
| JSON encode fails | `JsonException` thrown (not caught) |
| Circular reference during serialization | Depth limit (default 32) prevents infinite recursion |

`DataTransferResult` provides a try/catch-free path:
- `result->isSuccess()` / `result->isFailure()`
- `result->violations()` — always returns DataTransferViolations (empty on success)
- `result->object()` — throws if failure
- `result->failureReason()` — throws if success

## Runtime Safety

- **Depth limiting** — serialization has configurable max depth (default 32) to prevent infinite recursion on circular references
- **State reset** — `DataTransfer implements ResettableState` with `resetState()` to clear static config for testing
- **Immutable config** — `DataTransferConfig` is `readonly`, all `with*()` methods return new instances
- **No hidden I/O** — no database, filesystem, or network calls in the transfer engine
- **No container dependency** — does not call DI container or service locator

## Examples

### Basic DTO

```php
final class CreateUserInput extends DataObject
{
    #[Required]
    #[Email]
    public string $email;

    #[Required]
    #[Min(2)]
    #[Max(100)]
    public string $name;
}

$input = DataTransfer::create(CreateUserInput::class, [
    'email' => 'user@example.com',
    'name'  => 'John',
]);
```

### Nested DTO with ListOf

```php
final class OrderItem extends DataObject
{
    #[Required]
    public string $productId;

    #[Required]
    public int $quantity;
}

final class CreateOrderInput extends DataObject
{
    #[Required]
    public string $customerId;

    #[ListOf(OrderItem::class)]
    public array $items;
}
```

### Field Mapping

```php
final class ApiInput extends DataObject
{
    #[MapFrom('user_email')]
    #[Required]
    public string $email;
}
// Input array uses 'user_email', DTO property is 'email'
```

### Try-Catch-Free Validation

```php
$result = DataTransfer::tryCreate(LoginInput::class, $requestBody);

if ($result->hasViolations()) {
    return response()->json(['errors' => $result->violations()->toArray()], 422);
}

$loginInput = $result->object();
```

### Legacy DTO Compatibility

```php
// AbstractDTO provides constructor-based hydration for migration
final class LegacyDto extends AbstractDTO
{
    public string $name;
    public int $age;
}

// Still works, but new code should use DataObject + attributes
$dto = new LegacyDto(['name' => 'Test', 'age' => 30]);
```

## Known Limits

1. **ValueCasterInterface not fully wired** — The interface expects `DataField` and `ValueConversionContext` parameters, but `CreateDataObject::hydrateValue()` only calls `$caster->cast($value, $fieldName)` with a simple two-argument fallback. Full interface integration is not implemented.
2. **Unknown field policy declared but not enforced** — `DataTransferConfig` has `UnknownFieldPolicy` (Reject/Ignore/Collect), but `CreateDataObject::create()` does not check for extra fields in input that don't map to properties. The `Collect` and `Reject` policies are defined but not yet applied in the creation flow.
3. **No union type support** — Type validation uses `ReflectionNamedType` and does not handle `string|int` union types or nullable types through reflection beyond checking `isBuiltin()`.
4. **Validation rules in config not used** — `DataTransferConfig::validationRules` is stored but never consulted during validation. All validation currently happens through attribute `validate()` methods.
5. **Naming policy in config not applied** — `DataTransferConfig::inputNameFor()` exists but `CreateDataObject::resolveInputName()` only checks `#[MapFrom]`, not the config naming policy.
6. **Serializer depth limit is soft** — The `NormalizeDataObjectValue` depth tracking prevents infinite recursion but does not throw a descriptive error when the limit is reached — behavior depends on the normalization implementation.

## Current Status

- **Hydration:** Implemented — supports public typed properties, constructor promotion, nested DTOs, backed enums, ListOf
- **Validation:** Partially implemented — attribute-based validation works, but config-based validation rules and unknown field policies are defined but not wired
- **Serialization:** Implemented — toArray, toJson, toStdClass, toFlatArray, toJsonApi all work
- **Data shape inspection:** Implemented — with reflection caching
- **Legacy compatibility:** Implemented — AbstractDTO provides migration path
- **Value casting:** Partially implemented — CastWith attribute works with simple `cast($value, $fieldName)` pattern, full ValueCasterInterface not wired
- **Tests:** Exist in central test tree — verify through `vendor/bin/phpunit`
