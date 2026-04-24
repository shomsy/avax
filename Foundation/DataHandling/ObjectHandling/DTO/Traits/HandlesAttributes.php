<?php

declare(strict_types=1);

namespace Avax\DataHandling\ObjectHandling\DTO\Traits;

use InvalidArgumentException;
use ReflectionAttribute;
use Throwable;

/**
 * Trait HandlesAttributes
 *
 * **Purpose**: This trait provides reusable functionality to apply transformations
 * and validations to object fields via custom attributes. It centralizes attribute
 * handling logic for better maintainability and reusability.
 *
 * **Context in DDD**: Designed for entities or value objects that require attribute-driven
 * field transformations and validations, fostering clean and declarative object definitions.
 *
 * **Key Features**:
 * - Instantiation of attributes.
 * - Transformation of field values using attribute logic.
 * - Validation of field values against attribute-defined rules.
 */
trait HandlesAttributes
{
    /**
     * Applies the given attributes to modify and/or validate the value of a field.
     * Each attribute may define transformation (`apply`) logic or validation (`validate`) logic.
     *
     * **Usage in DDD**: Enables well-encapsulated validation and behavior adjustments
     * directly linked to the domain model's field attributes.
     *
     * @param string                $fieldName  The name of the field currently being processed.
     * @param mixed                 $value      The current value of the field, passed by reference for in-place
     *                                          updates.
     * @param ReflectionAttribute[] $attributes A collection of `ReflectionAttribute` instances to apply.
     *
     * @return mixed
     */
    protected function applyFieldAttributes(string $fieldName, mixed &$value, array $attributes) : mixed
    {
        foreach ($attributes as $attribute) {
            $instance = $this->instantiateAttribute($attribute);

            if (method_exists($instance, 'apply')) {
                $value = $this->applyAttribute(instance: $instance, value: $value, fieldName: $fieldName);
            }

            if (method_exists($instance, 'validate')) {
                $this->validateAttribute(instance: $instance, value: $value, fieldName: $fieldName);
            }
        }

        return $value;
    }

    /**
     * Instantiates an attribute safely and ensures its validity for further processing.
     *
     * **Delegated Responsibility**: Verifies the integrity of an attribute's instantiation,
     * ensuring downstream logic (like `apply` or `validate`) receives a valid object.
     *
     * @param ReflectionAttribute $attribute The `ReflectionAttribute` instance to instantiate.
     *
     * @return object The instantiated attribute object, ready for application.
     *
     * @throws InvalidArgumentException If attribute instantiation fails due to invalid parameters
     *                                  or a runtime exception.
     */
    private function instantiateAttribute(ReflectionAttribute $attribute) : object
    {
        try {
            return $attribute->newInstance();
        } catch (Throwable $e) {
            throw new InvalidArgumentException(
                message : sprintf(
                              'Failed to instantiate attribute of type "%s": %s',
                              $attribute->getName(),
                              $e->getMessage()
                          ),
                code    : 422,
                previous: $e
            );
        }
    }

    /**
     * Applies the transformation logic defined by the `apply` method of an attribute.
     *
     * **Domain Use Case**: Alters the field value as determined by attribute-specific
     * transformation rules, enriching the domain model with declarative behavior logic.
     *
     * @param object $instance  The instantiated attribute object with the `apply` method.
     * @param mixed  $value     The current field value to be transformed.
     * @param string $fieldName The name of the field to provide context in case of errors.
     * @return mixed The transformed value after applying the attribute's logic.
     *
     * @throws InvalidArgumentException If the `apply` method fails or is improperly implemented.
     */
    private function applyAttribute(object $instance, mixed $value, string $fieldName) : mixed
    {
        try {
            return $instance->apply($value);
        } catch (Throwable $e) {
            throw new InvalidArgumentException(
                message : sprintf(
                              'The "apply" method of attribute "%s" failed for field "%s": %s',
                              $instance::class,
                              $fieldName,
                              $e->getMessage()
                          ),
                code    : 422,
                previous: $e
            );
        }
    }

    /**
     * Validates a value against the rules defined by an attribute's `validate` method.
     *
     * **Domain Implication**: Ensures that field values adhere to domain-driven constraints encapsulated
     * by attributes, supporting robust domain model invariants.
     *
     * @param object $instance  The instantiated attribute object with validation capability.
     * @param mixed  $value     The current field value to be validated.
     * @param string $fieldName The name of the field being validated for error reporting.
     * @return void
     *
     * @throws InvalidArgumentException If validation rules are violated or improperly implemented.
     */
    private function validateAttribute(object $instance, mixed $value, string $fieldName) : void
    {
        try {
            $instance->validate(
                value   : $value,
                property: $fieldName
            );
        } catch (Throwable $e) {
            throw new InvalidArgumentException(
                message : sprintf(
                              'Validation failed for field "%s" with attribute "%s": %s',
                              $fieldName,
                              $instance::class,
                              $e->getMessage()
                          ),
                code    : 422,
                previous: $e
            );
        }
    }
}
