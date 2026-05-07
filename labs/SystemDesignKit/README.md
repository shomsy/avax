# SystemDesignKit (labs/)

V3 Executable System Design Framework

## Status

**Experimental** — V3-01 schema validation complete.
**V2 Platform Baseline:** GREEN (all 72 components complete).
**Promotion:** Not yet promoted to `components/SystemDesign/`.

## Purpose

Model, validate, simulate, test, and explain large application architectures.

V3 does not just build applications.
V3 tests whether the architecture makes sense.

## V3-00 Foundation

- `System/PublicSurface/SystemDesignKit.php` — experimental public surface
- `System/Capabilities/Capacity/CapacityModel.php` — capacity model (traffic, storage, cache, queue, latency,
  availability)
- `Capacity/CapacityYamlParser.php` — capacity.yaml parser spike

## V3-01 Schema Validation

### Schemas

- `schemas/capacity-schema.yaml` — Schema for capacity.yaml files
- `schemas/scenarios-schema.yaml` — Schema for scenarios.yaml files
- `schemas/architecture-tests-schema.yaml` — Schema for architecture-tests.yaml files

### Capabilities

- `System/Capabilities/SchemaValidation/NativeYamlParser.php` — Native PHP YAML parser
- `System/Capabilities/SchemaValidation/SchemaValidator.php` — Schema validation engine
- `System/Capabilities/SchemaValidation/SchemaValidationResult.php` — Validation result value object

### Flows

- `System/Flows/ValidateCapacitySchema/ValidateCapacitySchema.php` — Validate capacity.yaml
- `System/Flows/ValidateScenariosSchema/ValidateScenariosSchema.php` — Validate scenarios.yaml
- `System/Flows/ValidateArchitectureTestsSchema/ValidateArchitectureTestsSchema.php` — Validate architecture-tests.yaml

### Foundation

- `System/Foundation/Failure/SchemaParseException.php` — YAML parse failure
- `System/Foundation/Failure/SchemaValidationException.php` — Schema validation failure

### Examples

Valid:

- `examples/valid-capacity.yaml`
- `examples/valid-scenarios.yaml`
- `examples/valid-architecture-tests.yaml`

Invalid (for testing):

- `examples/invalid-capacity-bad-values.yaml`
- `examples/invalid-capacity-missing-sections.yaml`
- `examples/invalid-scenarios-bad.yaml`
- `examples/invalid-architecture-tests-bad.yaml`

## Tree

```text
labs/SystemDesignKit/
  System/
    PublicSurface/
      SystemDesignKit.php
    Capabilities/
      Capacity/
        CapacityModel.php
      SchemaValidation/
        NativeYamlParser.php
        SchemaValidator.php
        SchemaValidationResult.php
    Flows/
      ValidateCapacitySchema/
        ValidateCapacitySchema.php
      ValidateScenariosSchema/
        ValidateScenariosSchema.php
      ValidateArchitectureTestsSchema/
        ValidateArchitectureTestsSchema.php
    Configuration/
    Foundation/
      Failure/
        SchemaParseException.php
        SchemaValidationException.php
  Capacity/
    CapacityYamlParser.php
  schemas/
    capacity-schema.yaml
    scenarios-schema.yaml
    architecture-tests-schema.yaml
  examples/
    valid-capacity.yaml
    valid-scenarios.yaml
    valid-architecture-tests.yaml
    invalid-capacity-bad-values.yaml
    invalid-capacity-missing-sections.yaml
    invalid-scenarios-bad.yaml
    invalid-architecture-tests-bad.yaml
  experiments/
  scenarios/
  spikes/
```

## Usage

```php
use Avax\Labs\SystemDesignKit\System\Flows\ValidateCapacitySchema\ValidateCapacitySchema;

$flow = new ValidateCapacitySchema();
$result = $flow->execute('reference-architectures/url-shortener/capacity.yaml');

if ($result->valid) {
    echo "Capacity model is valid.\n";
} else {
    foreach ($result->errors as $error) {
        echo "Error: {$error}\n";
    }
}
```

## MVP Scope

- [x] Capacity modeling (traffic, storage, cache, queue, latency)
- [x] Schema validation for capacity.yaml, scenarios.yaml, architecture-tests.yaml
- [ ] Reference architectures
- [ ] Failure simulation
- [ ] Architecture tests
- [ ] Scenario runner

## Promotion Criteria

Promotion to `components/SystemDesign/` requires:

- [x] V1 Kernel Green (PROVEN)
- [x] V2 platform baseline GREEN (PROVEN)
- [ ] At least 2 reference architectures validate
- [ ] At least 1 runnable example passes
- [ ] At least 3 failure scenarios catch real violations
- [ ] Architecture tests have meaningful assertions
- [ ] Public API classified as @experimental or @public

See: `EVIDENCE/plans/v3-reference-architecture-plan.md`
See: `EVIDENCE/avax-v3-executable-system-design-framework-plan.md`
