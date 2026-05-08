# DataTransfer — how this works

## What DataTransfer gives AvaX

DataTransfer is the typed object / DTO transfer engine. It converts raw array or object input into fully typed PHP
objects, validates them using PHP attributes, casts values to their expected types, and serializes them back to arrays,
JSON, or stdClass.

## Canonical style

DataObject classes use **public typed properties + PHP attributes**. No constructor is required.

```php
final class UserInput
{
    #[Required]
    #[StringType]
    #[Email]
    public string $email;

    #[Required]
    #[StringType]
    #[Min(3)]
    #[Max(50)]
    public string $username;

    #[DefaultValue(false)]
    public bool $isAdmin = false;
}
```

## Public API

```php
// Create — throws DataTransferFailure on validation error
$dto = DataTransfer::create(UserInput::class, $input);

// Try — returns DataTransferResult (success/failure), never throws for validation
$result = DataTransfer::tryCreate(UserInput::class, $input);
if ($result->isSuccess()) { $dto = $result->object(); }
if ($result->isFailure()) { $violations = $result->failureReason()->violations; }

// Serialize
$array   = DataTransfer::toArray($dto);
$json    = DataTransfer::toJson($dto);
$std     = DataTransfer::toStdClass($dto);
$flat    = DataTransfer::toFlatArray($dto);
$jsonApi = DataTransfer::toJsonApi($dto, 'user');
```

## Hydration

DataTransfer inspects public typed properties, reads values from input by property name (or mapped name via
`#[MapFrom]`), and sets them on the object. Constructor-promoted properties are also supported.

## Attribute-first validation

Validation is expressed through PHP attributes on properties. All violations are collected before throwing — you get the
full list of errors, not just the first one.

### Validation attributes

| Attribute                  | Purpose                                            |
|----------------------------|----------------------------------------------------|
| `#[Required]`              | Field must be present in input                     |
| `#[Optional]`              | Field may be absent                                |
| `#[DefaultValue(value)]`   | Use this value when field is absent                |
| `#[StringType]`            | Value must be a string                             |
| `#[IntegerType]`           | Value must be an integer                           |
| `#[FloatType]`             | Value must be a float                              |
| `#[BooleanType]`           | Value must be a boolean                            |
| `#[ArrayType]`             | Value must be an array                             |
| `#[Email]`                 | Value must be a valid email                        |
| `#[AlphaNum]`              | Value must be alphanumeric                         |
| `#[AlphaNumOrEmail]`       | Value must be alphanumeric or valid email          |
| `#[Min(n)]`                | String length, number, or array count must be >= n |
| `#[Max(n)]`                | String length, number, or array count must be <= n |
| `#[Between(min, max)]`     | Value must be within range                         |
| `#[RegexPattern('/.../')]` | Value must match regex                             |

### Mapping and casting attributes

| Attribute                         | Purpose                                 |
|-----------------------------------|-----------------------------------------|
| `#[MapFrom('input_key')]`         | Read from different input key           |
| `#[ListOf(ClassName::class)]`     | Cast array of arrays to list of objects |
| `#[CastWith(CasterClass::class)]` | Use custom caster for value conversion  |
| `#[Hidden]`                       | Exclude from serialization output       |

## Field mapping

`#[MapFrom('full_name')]` on `public string $firstName` reads input from `full_name` key instead of `firstName`.

## Casting

- **Nested DTO**: If a property type is a class and the input is an array, DataTransfer recursively creates the nested
  object.
- **ListOf**: `#[ListOf(AddressInput::class)]` on `public array $addresses` casts each array item to `AddressInput`.
- **Backed enum**: If property type is a backed enum, the input value is cast via `EnumType::from($value)`.
- **Custom caster**: `#[CastWith(MoneyCaster::class)]` calls `(new MoneyCaster())->cast($value, $fieldName)`.

## Unknown field policy

Configured via `DataTransferConfig`. Options: `Reject`, `Ignore`, `Collect`.

## Why separate from DataStack/Data?

DataStack/Data is the Data Engine: Collections, Maps, Sets, Sequences, Lenses, Codecs, Operators. DataTransfer is the
DTO engine: object hydration, validation, casting, serialization. They have different responsibilities. DataTransfer
depends on Data (for Arrhae/Collection in legacy support), but Data must not depend on DataTransfer.

## Why no HTTP coupling?

DataTransfer is runtime-agnostic. It works with any array or object input. HTTP-specific behavior belongs in
HTTP/SecureRequest, which uses DataTransfer for validation and hydration.
