<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\SecureRequest\System\PublicSurface;

use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\DefaultValue;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\MapFrom;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Optional;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Required;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation\DataTransferViolation;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation\DataTransferViolations;
use Avax\Components\HTTP\SecureRequest\System\Capabilities\SecureRequestValidation\ValidationContext;
use Avax\Components\HTTP\SecureRequest\System\Foundation\Failure\SecureRequestAuthorizationFailed;
use Avax\Components\HTTP\SecureRequest\System\Foundation\Failure\SecureRequestValidationFailed;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;

/**
 * SecureRequest — HTTP request-as-DTO.
 *
 * Canonical style: public typed properties + PHP attributes.
 * No constructor required. No rules() method.
 *
 * Lifecycle order:
 * 1. beforeHydration
 * 2. hydrate public typed properties from input
 * 3. afterHydration
 * 4. beforeValidation
 * 5. attribute validation (Required, StringType, Min, Max, etc.)
 * 6. withValidation (custom hook)
 * 7. afterValidation
 * 8. if violations: failedValidation + throw
 * 9. authorize
 * 10. if ! authorize: throw
 * 11. passedValidation
 */
abstract class SecureRequest
{
    /**
     * @var array<string, mixed>
     */
    private array $_input = [];

    /**
     * Get the raw input data.
     *
     * @return array<string, mixed>
     */
    public function getInput() : array
    {
        return $this->_input;
    }

    /**
     * @param array<string, mixed> $input
     *
     * @internal
     */
    public function setInput(array $input) : void
    {
        $this->_input = $input;
    }

    /**
     * Run the full SecureRequest lifecycle.
     *
     * @param array<string, mixed> $input
     *
     * @internal
     */
    public function runLifecycle(array $input) : void
    {
        $this->beforeHydration();
        $this->setInput($input);

        // Step 2: Hydrate public typed properties
        $this->hydrateProperties($input);

        $this->afterHydration();
        $this->beforeValidation();

        // Step 5: Attribute validation
        $violations = $this->validateAttributes($input);

        if ($violations !== []) {
            $collection = new DataTransferViolations($violations);
            $this->failedValidation($collection);

            throw new SecureRequestValidationFailed(
                message   : 'SecureRequest validation failed.',
                violations: $collection,
            );
        }

        $this->afterValidation();

        // Step 6: Custom validation hook
        $context = new ValidationContext(
            request: $this,
            input  : $input,
        );
        $this->withValidation($context);

        if ($context->hasViolations()) {
            $this->failedValidation($context->violations());

            throw new SecureRequestValidationFailed(
                message   : 'SecureRequest custom validation failed.',
                violations: $context->violations(),
            );
        }

        // Step 9: Authorization
        if (! $this->authorize()) {
            throw new SecureRequestAuthorizationFailed();
        }

        // Step 11: Passed
        $this->passedValidation();
    }

    /**
     * Lifecycle: before hydration begins.
     */
    protected function beforeHydration() : void {}

    /**
     * Hydrate public typed properties from input.
     *
     * @param array<string, mixed> $input
     */
    private function hydrateProperties(array $input) : void
    {
        $reflectionClass = new ReflectionClass($this);
        $properties      = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            $name      = $property->getName();
            $inputName = $this->resolveInputName($property);

            if (! array_key_exists($inputName, $input)) {
                continue;
            }

            $value = $input[$inputName];

            // Apply default if value is null and property has DefaultValue
            if ($value === null) {
                $defaultAttrs = $property->getAttributes(DefaultValue::class);
                if ($defaultAttrs !== []) {
                    $value = $defaultAttrs[0]->newInstance()->value;
                }
            }

            if ($value !== null) {
                $property->setValue($this, $value);
            }
        }
    }

    private function resolveInputName(ReflectionProperty $property) : string
    {
        $mapFromAttrs = $property->getAttributes(MapFrom::class);
        if ($mapFromAttrs !== []) {
            return $mapFromAttrs[0]->newInstance()->name;
        }

        return $property->getName();
    }

    /**
     * Lifecycle: after hydration completes.
     */
    protected function afterHydration() : void {}

    /**
     * Lifecycle: before attribute validation.
     */
    protected function beforeValidation() : void {}

    /**
     * Validate public typed properties against attributes.
     *
     * @param array<string, mixed> $input
     *
     * @return list<DataTransferViolation>
     */
    private function validateAttributes(array $input) : array
    {
        $reflectionClass = new ReflectionClass($this);
        $properties      = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

        $violations = [];

        foreach ($properties as $property) {
            $name      = $property->getName();
            $inputName = $this->resolveInputName($property);
            $hasValue  = array_key_exists($inputName, $input);
            $value     = $hasValue ? $input[$inputName] : null;

            // Required check
            $isRequired = $property->getAttributes(Required::class) !== [];
            $isOptional = $property->getAttributes(Optional::class) !== [];

            if ($isRequired && ! $hasValue) {
                $violations[] = new DataTransferViolation(
                    field  : $name,
                    message: sprintf('Field "%s" is required.', $name),
                );
                continue;
            }

            if ($isOptional && ! $hasValue) {
                continue;
            }

            // Type validation
            if ($value !== null) {
                $propertyType = $property->getType();
                if ($propertyType instanceof ReflectionNamedType) {
                    $typeName  = $propertyType->getName();
                    $typeValid = match ($typeName) {
                        'string' => is_string($value),
                        'int'    => is_int($value),
                        'float'  => is_float($value) || is_int($value),
                        'bool'   => is_bool($value),
                        'array'  => is_array($value),
                        'mixed'  => true,
                        default  => $value instanceof $typeName,
                    };
                    if (! $typeValid) {
                        $violations[] = new DataTransferViolation(
                            field  : $name,
                            message: sprintf('Field "%s" must be of type %s.', $name, $typeName),
                        );
                        continue;
                    }
                }

                // Attribute validation
                foreach ($property->getAttributes() as $attribute) {
                    $instance = $attribute->newInstance();
                    if (method_exists($instance, 'validate')) {
                        try {
                            $instance->validate($value, $name);
                        } catch (InvalidArgumentException $e) {
                            $violations[] = new DataTransferViolation(
                                field  : $name,
                                message: $e->getMessage(),
                            );
                        }
                    }
                }
            }
        }

        return $violations;
    }

    /**
     * Lifecycle: when validation fails.
     */
    protected function failedValidation(DataTransferViolations $violations) : void {}

    /**
     * Lifecycle: after attribute validation succeeds.
     */
    protected function afterValidation() : void {}

    /**
     * Custom validation hook.
     * Override for complex cross-field validation.
     */
    protected function withValidation(ValidationContext $context) : void {}

    /**
     * Determine if the user is authorized.
     * Override in subclasses.
     */
    public function authorize() : bool
    {
        return true;
    }

    /**
     * Lifecycle: after all validation succeeds.
     */
    protected function passedValidation() : void {}
}
