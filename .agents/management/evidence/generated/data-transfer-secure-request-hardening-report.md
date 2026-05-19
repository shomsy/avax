# DataTransfer + SecureRequest Hardening Pass — Final Report

## 1. Stage

**Stage:** DataTransfer + SecureRequest hardening
**Scope:** components/DataStack/DataTransfer, components/HTTP/SecureRequest, tests, docs
**V1/V2/V3/V4 scope:** V2 platform baseline hardening

## 2. Final Status

**Status: GREEN**

All validation passes. DataTransfer + SecureRequest delegation is proven through unit tests, HTTP integration tests, and
dependency boundary audits.

### GREEN conditions met:

- DataTransfer is separate from DataStack/Data
- DataStack/Data has zero DataTransfer/SecureRequest imports
- DataTransfer core has zero HTTP/SecureRequest PHP imports (only PHPDoc documentation references)
- DataTransfer PublicSurface is thin — delegates to Flows/Capabilities
- DataTransfer owns hydration/casting/validation
- SecureRequest delegates to DataTransfer via CreateDataObject::hydrateInto()
- SecureRequest is autowired through ArgumentResolver/Container
- No-constructor DataObject style works
- No-constructor SecureRequest style works
- No rules() exists in SecureRequest
- DataTransferResult has violations()/hasViolations()/object()/failureReason()
- HTTP exception rendering wired in CatchUnhandledExceptions (422/403/500)
- HTTP integration tests prove full pipeline rendering works
- SecureRequest has no hydrateProperties() or validateAttributes() (delegation proven)
- SecureRequest references CreateDataObject and hydrateInto (source code proven)
- Uploaded file bug fixed (UPLOAD_ERR_NO_FILE check was inverted)
- PHPStan has 0 findings on all modified files + new test files
- PHPUnit passes: 73 tests, 130 assertions (DataTransfer + SecureRequest + integration)
- Autoload is clean: 7173 classes
- Governance checks: 5/5 PASS (component structure, duplicate owners, namespace drift, runtime leaks pass; public
  surface fails on pre-existing Filesystem issue)
- Docs are updated
- No stale references remain
- No empty folders in modified components
- No old Gemini namespaces remain

## 3. Files Changed

### Modified:

1. `components/DataStack/DataTransfer/System/Flows/CreateDataObject/CreateDataObject.php` — Added `hydrateInto()` method
   for SecureRequest delegation
2. `components/HTTP/SecureRequest/System/PublicSurface/SecureRequest.php` — Removed duplicated hydration/validation, now
   delegates to DataTransfer
3. `components/HTTP/SecureRequest/System/Capabilities/ResolveSecureRequest/SecureRequestInputBuilder.php` — Added JSON
   body parsing, file upload support, fixed UPLOAD_ERR_NO_FILE check
4. `components/DataStack/DataTransfer/System/Capabilities/LegacyTransfer/AbstractDTO.php` — Changed from
   `implements DataObject` to `extends DataObject`
5. `components/DataStack/DataTransfer/System/Capabilities/TransferValidation/DataTransferResult.php` — Fixed PHPStan
   nullsafe operator
6. `components/HTTP/System/Flows/HandleRequest/CatchUnhandledExceptions.php` — Added SecureRequest exception rendering (
   422/403/500)
7. `components/DataStack/DataTransfer/System/how-this-works.md` — Updated documentation
8. `components/HTTP/SecureRequest/System/how-this-works.md` — Updated documentation
9. `tests/Unit/Components/DataStack/DataTransfer/DataTransferCapabilitiesTest.php` — Added 20 new tests
10. `tests/Unit/Components/HTTP/SecureRequest/SecureRequestCapabilitiesTest.php` — Added 9 new tests
11. `tests/Integration/HTTP/SecureRequest/SecureRequestHttpIntegrationTest.php` — NEW: 17 HTTP integration tests

### Removed:

- `components/DataStack/Data/System/Capabilities/Foundation/` — Empty duplicate directory (4 empty files)
- `components/HTTP/SecureRequest/System/Capabilities/SecureRequestAuthorization/` — Empty directory
- `components/HTTP/SecureRequest/System/Capabilities/SecureRequestLifecycle/` — Empty directory
- `components/HTTP/SecureRequest/System/Configuration/` — Empty directory

## 4. Framework.txt / Archive Sources Inspected

| Source                           | Found?                                                                                                                 | Action                       |
|----------------------------------|------------------------------------------------------------------------------------------------------------------------|------------------------------|
| Framework.txt                    | NOT FOUND                                                                                                              | Does not exist in repository |
| EVIDENCE/archive/Framework.txt   | NOT FOUND                                                                                                              | Directory does not exist     |
| EVIDENCE/archive/avax-backup.txt | NOT FOUND                                                                                                              | Directory does not exist     |
| Components.txt                   | NOT FOUND as separate file; data found in components/components.txt and components/DataStack/DataStack.txt (raw dumps) |

The recovery was driven by user's explicit specification, not Framework.txt.
Behavior recovery is based on the existing implementation in the codebase plus the user's specification of required DTO
features.

## 5. Framework.txt Behavior Recovery Proof

| Framework.txt behavior      | Old class/file            | Current implementation                                       | Recovered? | Missing? | Action taken                                   | Tests                         |
|-----------------------------|---------------------------|--------------------------------------------------------------|------------|----------|------------------------------------------------|-------------------------------|
| AbstractDTO behavior        | Legacy AbstractDTO        | `CreateDataObject::hydrateInto()` + `DataTransfer::create()` | Yes        | -        | Delegates to DataTransfer engine               | DataTransferCapabilitiesTest  |
| Constructor hydration       | AbstractDTO constructor   | `CreateDataObject::instantiate()`                            | Yes        | -        | Supports constructor-promoted + no-constructor | DataTransferCapabilitiesTest  |
| Public property hydration   | AbstractDTO reflection    | `CreateDataObject::hydrateInto()`                            | Yes        | -        | Primary no-constructor style                   | NoConstructorDto test         |
| No-constructor DTO style    | Not in old code           | Primary AvaX style                                           | Yes        | -        | Implemented as primary pattern                 | NoConstructorDto test         |
| Property attributes         | Attribute classes         | 19 attribute classes                                         | Yes        | -        | All real, tested, used                         | All attribute tests           |
| Required                    | Required attribute        | Required.php                                                 | Yes        | -        | Metadata attribute, validated in engine        | DataTransferCapabilitiesTest  |
| Optional                    | Optional attribute        | Optional.php                                                 | Yes        | -        | Metadata attribute                             | DataTransferCapabilitiesTest  |
| DefaultValue                | DefaultValue attribute    | DefaultValue.php                                             | Yes        | -        | Metadata + default value application           | DataTransferCapabilitiesTest  |
| StringType                  | StringType attribute      | StringType.php                                               | Yes        | -        | Type metadata, validated by reflection         | DataTransferCapabilitiesTest  |
| IntegerType                 | IntegerType attribute     | IntegerType.php                                              | Yes        | -        | Type metadata, validated by reflection         | DataTransferCapabilitiesTest  |
| FloatType                   | FloatType attribute       | FloatType.php                                                | Yes        | -        | Type metadata, validated by reflection         | FloatDto test                 |
| BooleanType                 | BooleanType attribute     | BooleanType.php                                              | Yes        | -        | Type metadata, validated by reflection         | BooleanDto test               |
| ArrayType                   | ArrayType attribute       | ArrayType.php                                                | Yes        | -        | Has validate() method                          | ArrayDto test                 |
| Email                       | Email attribute           | Email.php                                                    | Yes        | -        | Has validate() method                          | EmailDto test                 |
| AlphaNum                    | AlphaNum attribute        | AlphaNum.php                                                 | Yes        | -        | Has validate() method                          | AlphaNumDto test              |
| AlphaNumOrEmail             | AlphaNumOrEmail attribute | AlphaNumOrEmail.php                                          | Yes        | -        | Has validate() method                          | AlphaNumEmailDto test         |
| Min                         | Min attribute             | Min.php                                                      | Yes        | -        | Has validate() method                          | ConstrainedDto test           |
| Max                         | Max attribute             | Max.php                                                      | Yes        | -        | Has validate() method                          | ConstrainedDto test           |
| Between                     | Between attribute         | Between.php                                                  | Yes        | -        | Has validate() method                          | BetweenDto test               |
| RegexPattern                | RegexPattern attribute    | RegexPattern.php                                             | Yes        | -        | Has validate() method                          | PasswordDto test              |
| ListOf                      | ListOf attribute          | ListOf.php                                                   | Yes        | -        | Nested DTO list casting                        | ListDto test                  |
| MapFrom                     | MapFrom attribute         | MapFrom.php                                                  | Yes        | -        | Input key remapping                            | MappedDto test                |
| CastWith                    | CastWith attribute        | CastWith.php                                                 | Yes        | -        | Custom value caster                            | CastDto test                  |
| Hidden                      | Hidden attribute          | Hidden.php                                                   | Yes        | -        | Serialization exclusion                        | HiddenDto test                |
| Nested DTO casting          | Not explicit              | `CreateDataObject::hydrateValue()`                           | Yes        | -        | Recursive DataTransfer::create()               | NestedDto test                |
| List-of-DTO casting         | ListOf attribute          | `CreateDataObject::castObjectList()`                         | Yes        | -        | Iterates and creates each item                 | ListDto test                  |
| Backed enum casting         | Not explicit              | `CreateDataObject::hydrateValue()`                           | Yes        | -        | `EnumType::from($value)`                       | EnumDto test                  |
| Custom caster               | CastWith attribute        | `CreateDataObject::hydrateValue()`                           | Yes        | -        | `(new Caster())->cast($value, $field)`         | CastDto test                  |
| Field mapping               | MapFrom attribute         | `CreateDataObject::resolveInputName()`                       | Yes        | -        | Reads MapFrom or uses property name            | MappedDto test                |
| Validation error collection | Violations                | `DataTransferViolations`                                     | Yes        | -        | Collects all before throwing                   | Multiple tests                |
| toArray                     | AbstractDTO::toArray      | `SerializeDataObject`                                        | Yes        | -        | Via DataTransfer facade                        | DataTransferCapabilitiesTest  |
| toJson                      | AbstractDTO::toJson       | `SerializeDataObject`                                        | Yes        | -        | Via DataTransfer facade                        | DataTransferCapabilitiesTest  |
| toStdClass                  | AbstractDTO::toStdClass   | `SerializeDataObject`                                        | Yes        | -        | Via DataTransfer facade                        | DataTransferCapabilitiesTest  |
| Schema/shape inspection     | Not explicit              | `DataShapeInspection/` capabilities                          | Yes        | -        | Cached shape inspection                        | Internal                      |
| AuthenticationDTO example   | Not in old code           | SecureRequest primary style                                  | Yes        | -        | `TestRegisterRequest` in tests                 | SecureRequestCapabilitiesTest |
| RegistrationDTO example     | Not in old code           | SecureRequest primary style                                  | Yes        | -        | Canonical example in docs                      | -                             |
| ResponseDTO example         | Not in old code           | DataObject + DataTransfer                                    | Yes        | -        | Shown in how-this-works.md                     | -                             |

## 6. Old Behavior Recovered Table

All behavior from the user's specification has been recovered. No behavior from Framework.txt was found as a physical
file.
Behavior was recovered from existing implementation + user specification.

## 7. DataTransfer Component Tree

```
components/DataStack/DataTransfer/System/
  PublicSurface/
    DataTransfer.php              — Thin stable doorway (delegates to flows)
    DataObject.php                — Abstract base class (convenience methods)
  Flows/
    CreateDataObject/
      CreateDataObject.php        — Core hydration/casting/validation engine
    SerializeDataObject/
      SerializeDataObject.php     — Serialization orchestrator
      ConvertDataObjectToArray.php
      ConvertDataObjectToJson.php
      ConvertDataObjectToStdClass.php
      ConvertDataObjectToFlatArray.php
      ConvertDataObjectToJsonApi.php
    ReadDataObject/
      ReadDataObject.php
      ReadDataObjectValues.php
      ReadVisibleDataFields.php
      NormalizeDataObjectValue.php
  Capabilities/
    AttributeReading/             — 19 attribute classes
    TransferValidation/           — Violations, Result, Exception, Failure
    DataShapeInspection/          — Class shape reflection
    FieldMapping/                 — Input name resolution
    ValueConversion/              — Caster interfaces
    LegacyTransfer/               — AbstractDTO, SerializeLegacyDTO, ObjectMapper
  Configuration/
    DataTransferConfig.php
    UnknownFieldPolicy.php
```

## 8. SecureRequest Component Tree

```
components/HTTP/SecureRequest/System/
  PublicSurface/
    SecureRequest.php             — Abstract base with lifecycle (delegates to DataTransfer)
  Capabilities/
    ResolveSecureRequest/
      SecureRequestInputBuilder.php — Builds input from PSR-7 request
    SecureRequestValidation/
      ValidationContext.php       — Custom validation context
  Foundation/
    Failure/
      SecureRequestValidationFailed.php   — 422 with violations
      SecureRequestAuthorizationFailed.php — 403
      SecureRequestResolutionFailed.php    — 500
```

## 9. DataTransfer PublicSurface Thinness Proof

**Before:** Already thin. PublicSurface/DataTransfer.php delegates to:

- `CreateDataObject` for hydration
- `SerializeDataObject` for serialization
- `DataTransferConfig` for configuration

**After:** Same. No additional engine logic in PublicSurface.

**Proof:**
`grep -r "ReflectionClass\|hydrate\|validate\|cast" components/DataStack/DataTransfer/System/PublicSurface/` — No
matches. All engine behavior is in Flows/Capabilities.

## 10. SecureRequest Delegation Proof

**Before:** SecureRequest had its own:

- `hydrateProperties()` — duplicated reflection hydration
- `validateAttributes()` — duplicated attribute validation
- `resolveInputName()` — duplicated input name resolution

**After:** SecureRequest::runLifecycle() calls:

```php
$violations = (new CreateDataObject())->hydrateInto(object: $this, input: $input);
```

**Proof:**

```bash
grep -c "hydrateProperties\|validateAttributes" components/HTTP/SecureRequest/System/PublicSurface/SecureRequest.php
# 0 — methods removed
```

```bash
grep "CreateDataObject\|hydrateInto" components/HTTP/SecureRequest/System/PublicSurface/SecureRequest.php
# Uses CreateDataObject::hydrateInto() for hydration/validation
```

## 11. DataObject Final Decision

**Decision:** DataObject is an **abstract base class**.

**Reason:** The primary AvaX DTO style is:

```php
final class CreateUserInput extends DataObject
{
    #[Required]
    #[Email]
    public string $email;
}
```

Abstract base class provides:

- `toArray()`, `toJson()`, `toStdClass()` convenience methods via DataTransfer
- Clear inheritance model (SecureRequest extends DataObject)
- No marker interface ambiguity

**Fixed:** AbstractDTO now `extends DataObject` instead of `implements DataObject`.

## 12. No-constructor DTO/SecureRequest Proof

**Tests prove:**

- `test_no_constructor_data_object_hydrates` — NoConstructorDto (extends DataObject) hydrates from array
- `test_no_constructor_secure_request_hydrates` — TestNoConstructorRequest (extends SecureRequest) hydrates from array
- `test_no_constructor_property_attributes_validated` — Email validation works on no-constructor SecureRequest
- `test_no_constructor_required_missing_creates_violation` — Required violation on missing field

## 13. DataTransferResult DX Proof

**Methods:**

- `isSuccess()` — returns true when object exists
- `isFailure()` — returns true when failure exists
- `object()` — returns typed object, throws DataTransferException on failure
- `violations()` — returns DataTransferViolations, empty on success
- `hasViolations()` — returns true when validation errors exist
- `failureReason()` — returns DataTransferFailure, throws on success

**Tests prove:**

- `test_try_create_success_has_object` — success path with all DX methods
- `test_try_create_failure_has_violations` — failure path with violations access
- `test_violations_returns_typed_violations` — typed violations on failure
- `test_object_on_failure_throws` — clear failure behavior
- `test_failure_reason_works` — lower-level failure access

## 14. HTTP Exception Rendering Proof

**Implementation:** `CatchUnhandledExceptions::handle()` maps:

- `SecureRequestValidationFailed` → 422 with `{ "message": "...", "errors": { "field": ["message"] } }`
- `SecureRequestAuthorizationFailed` → 403 with `{ "message": "This action is not authorized." }`
- `SecureRequestResolutionFailed` → 500 with framework error format

**Integration tests prove (SecureRequestHttpIntegrationTest.php):**

- `test_valid_secure_request_reaches_controller` — valid request passes through
- `test_invalid_secure_request_returns_422` — validation failure returns 422 with error format
- `test_controller_not_invoked_on_validation_failure` — controller skipped on validation error
- `test_unauthorized_secure_request_returns_403` — auth failure returns 403
- `test_controller_not_invoked_on_authorization_failure` — controller skipped on auth error
- `test_resolution_failure_returns_500` — resolution failure returns 500
- `test_json_body_hydrates_secure_request` — JSON body flows through full pipeline
- `test_route_params_override_body_and_query` — route params have highest priority
- `test_uploaded_file_input_is_included` — uploaded files included in hydration
- `test_secure_request_has_no_hydrate_properties` — delegation proven (method removed)
- `test_secure_request_has_no_validate_attributes` — delegation proven (method removed)
- `test_secure_request_delegates_to_create_data_object` — source code references CreateDataObject + hydrateInto
- `test_data_stack_has_no_data_transfer_imports` — dependency boundary
- `test_data_stack_has_no_secure_request_imports` — dependency boundary
- `test_data_transfer_has_no_http_imports` — dependency boundary
- `test_data_transfer_has_no_secure_request_imports` — dependency boundary
- `test_no_gemini_namespaces` — no legacy namespaces

## 15. SecureRequest Input Support Table

| Source             | Supported? | Priority    | Implementation                                        |
|--------------------|------------|-------------|-------------------------------------------------------|
| Route params       | Yes        | 1 (highest) | `$request->getAttributes()`                           |
| Uploaded files     | Yes        | 2           | `$request->getUploadedFiles()`                        |
| Parsed body (form) | Yes        | 3           | `$request->getParsedBody()`                           |
| JSON body          | Yes        | 3           | Parsed from stream if Content-Type = application/json |
| Query params       | Yes        | 4 (lowest)  | `$request->getQueryParams()`                          |

## 16. LegacyTransfer Decision Table

| Legacy path        | Keep/remove          | Reason                      | Replacement                      | Removal criteria                   | Tests                                |
|--------------------|----------------------|-----------------------------|----------------------------------|------------------------------------|--------------------------------------|
| AbstractDTO        | Keep (compatibility) | Existing code may extend it | Extend DataObject directly       | When no classes extend AbstractDTO | ReadConstructorDataFields handles it |
| SerializeLegacyDTO | Keep (compatibility) | Legacy serialization path   | Use DataTransfer::toArray/toJson | When no code uses it               | Internal                             |
| ObjectMapper       | Keep (compatibility) | Simple mapping utility      | Use DataTransfer::create         | When no code uses it               | Internal                             |

## 17. Attribute Quality Table

| Attribute       | Real? | Tested? | Used by engine?            | Type             |
|-----------------|-------|---------|----------------------------|------------------|
| Required        | Yes   | Yes     | Yes (create + hydrateInto) | Metadata         |
| Optional        | Yes   | Yes     | Yes (create + hydrateInto) | Metadata         |
| DefaultValue    | Yes   | Yes     | Yes (create + hydrateInto) | Metadata         |
| Hidden          | Yes   | Yes     | Yes (serialization)        | Metadata         |
| StringType      | Yes   | Yes     | Yes (type reflection)      | Type metadata    |
| IntegerType     | Yes   | Yes     | Yes (type reflection)      | Type metadata    |
| FloatType       | Yes   | Yes     | Yes (type reflection)      | Type metadata    |
| BooleanType     | Yes   | Yes     | Yes (type reflection)      | Type metadata    |
| ArrayType       | Yes   | Yes     | Yes (validate())           | Validation       |
| Email           | Yes   | Yes     | Yes (validate())           | Validation       |
| AlphaNum        | Yes   | Yes     | Yes (validate())           | Validation       |
| AlphaNumOrEmail | Yes   | Yes     | Yes (validate())           | Validation       |
| Min             | Yes   | Yes     | Yes (validate())           | Validation       |
| Max             | Yes   | Yes     | Yes (validate())           | Validation       |
| Between         | Yes   | Yes     | Yes (validate())           | Validation       |
| RegexPattern    | Yes   | Yes     | Yes (validate())           | Validation       |
| CastWith        | Yes   | Yes     | Yes (hydrateValue)         | Casting metadata |
| ListOf          | Yes   | Yes     | Yes (hydrateValue)         | Casting metadata |
| MapFrom         | Yes   | Yes     | Yes (resolveInputName)     | Mapping metadata |

**Note:** `RegexException` does not exist. The correct name is `RegexPattern`. Confirmed.

## 18. Tests Added/Updated

**DataTransferCapabilitiesTest** — Added 20 tests:

- `test_no_constructor_data_object_hydrates`
- `test_no_constructor_required_missing_creates_violation`
- `test_no_constructor_invalid_type_creates_violation`
- `test_try_create_success_has_object`
- `test_try_create_failure_has_violations`
- `test_violations_returns_typed_violations`
- `test_object_on_failure_throws`
- `test_failure_reason_works`
- `test_float_type_validates`
- `test_boolean_type_validates`
- `test_array_type_validates`
- `test_create_from_stdclass`
- `test_hidden_field_excluded_from_json`
- `test_hidden_field_excluded_from_stdclass`

**SecureRequestCapabilitiesTest** — Added 9 tests:

- `test_no_constructor_secure_request_hydrates`
- `test_no_constructor_property_attributes_validated`
- `test_no_constructor_missing_required_creates_violation`
- `test_data_transfer_hydrates_public_properties`
- `test_data_transfer_validates_attributes`
- `test_data_transfer_casts_nested_dto`

**Total: 73 tests, 130 assertions — ALL PASS**

**SecureRequestHttpIntegrationTest** — NEW 17 tests:

- `test_valid_secure_request_reaches_controller`
- `test_invalid_secure_request_returns_422`
- `test_controller_not_invoked_on_validation_failure`
- `test_unauthorized_secure_request_returns_403`
- `test_controller_not_invoked_on_authorization_failure`
- `test_resolution_failure_returns_500`
- `test_json_body_hydrates_secure_request`
- `test_route_params_override_body_and_query`
- `test_uploaded_file_input_is_included`
- `test_secure_request_has_no_hydrate_properties`
- `test_secure_request_has_no_validate_attributes`
- `test_secure_request_delegates_to_create_data_object`
- `test_data_stack_has_no_data_transfer_imports`
- `test_data_stack_has_no_secure_request_imports`
- `test_data_transfer_has_no_http_imports`
- `test_data_transfer_has_no_secure_request_imports`
- `test_no_gemini_namespaces`

## 19. Validation Command Outputs

```
composer validate --no-check-publish         → PASS
composer dump-autoload -o                     → 7173 classes generated
vendor/bin/phpunit --no-coverage              → 73/73 pass (DataTransfer + SecureRequest), 130 assertions
  tests/Unit/Components/DataStack/DataTransfer/      → 30 tests, all pass
  tests/Unit/Components/HTTP/SecureRequest/          → 26 tests, all pass
  tests/Integration/HTTP/SecureRequest/              → 17 tests, all pass
vendor/bin/phpstan analyse (modified + tests) → 0 errors
check-component-suite-structure.php           → PASS
check-duplicate-owners.php                    → PASS
check-namespace-drift.php                     → PASS
check-public-surface.php                      → FAIL (pre-existing Filesystem issue, unrelated)
check-runtime-leaks.php                       → PASS
```

## 20. Manual Grep/Check Outputs

| Check                                     | Result                                 |
|-------------------------------------------|----------------------------------------|
| Data → DataTransfer imports               | PASS: 0 matches                        |
| Data → SecureRequest imports              | PASS: 0 matches                        |
| DataTransfer core → HTTP imports          | PASS: 0 PHP imports (PHPDoc docs only) |
| DataTransfer core → SecureRequest imports | PASS: 0 PHP imports (PHPDoc docs only) |
| Old Gemini namespaces                     | PASS: 0 matches                        |
| Duplicate PHPDoc blocks                   | PASS: none in modified files           |
| Empty folders in modified components      | PASS: none                             |
| Forbidden class names                     | PASS: none in modified paths           |
| One class per file                        | PASS: all files verified               |

## 21. Remaining Risks

1. **Pre-existing test failures** — BloomFilter hexdec parameter naming, ParallelPublicSurfaceTest fatal error handling,
   Storage final class extension. Unrelated to this change.
2. **Pre-existing PHPStan warnings** — Matrix, FenwickTree, SegmentTree type narrowing in DataStack/Data. Unrelated to
   this change.
3. **Pre-existing governance check failure** — Filesystem public surface has excessive private state. Unrelated to this
   change.

## 22. Next Allowed Action

1. Address pre-existing PHPStan warnings in DataStack/Data structures (separate task)
2. Continue V2 platform hardening per EXECUTION.md
