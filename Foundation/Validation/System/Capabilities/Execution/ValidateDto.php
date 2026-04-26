<?php

declare(strict_types=1);

namespace Avax\Validation\System\Capabilities\Execution;

use Avax\Validation\System\Capabilities\Metadata\Attributes\Email;
use Avax\Validation\System\Capabilities\Metadata\Attributes\Required;
use Avax\Validation\System\Capabilities\Standard\Email\ValidateEmail;
use ReflectionClass;
use ReflectionProperty;

/**
 * Action Owner: Validates a Data Transfer Object (DTO) based on its Attributes.
 *
 * Part of the Foundation\Validation capability.
 */
final readonly class ValidateDto
{
    /**
     * Executes the validation logic on the given DTO.
     *
     * @param object $dto The Data Transfer Object to validate.
     *
     * @return ValidationResult The result containing any validation errors.
     */
    public function execute(object $dto) : ValidationResult
    {
        $result     = new ValidationResult();
        $reflection = new ReflectionClass(objectOrClass: $dto);

        foreach ($reflection->getProperties(filter: ReflectionProperty::IS_PUBLIC) as $property) {
            $this->validateProperty(property: $property, dto: $dto, result: $result);
        }

        return $result;
    }

    private function validateProperty(ReflectionProperty $property, object $dto, ValidationResult $result) : void
    {
        $propertyName = $property->getName();
        $value        = $property->isInitialized(object: $dto) ? $property->getValue(object: $dto) : null;

        foreach ($property->getAttributes() as $attribute) {
            $attributeInstance = $attribute->newInstance();

            match (true) {
                $attributeInstance instanceof Required                                                                    => $this->handleRequired(
                    value   : $value,
                    property: $propertyName,
                    message : $attributeInstance->message,
                    result  : $result
                ),
                $attributeInstance instanceof Email                                                                       => $this->handleEmail(
                    value   : (string) ($value ?? ''),
                    property: $propertyName,
                    message : $attributeInstance->message,
                    result  : $result
                ),
                $attributeInstance instanceof \Avax\Validation\System\Capabilities\Metadata\Attributes\MinLength          => $this->handleMinLength(
                    value    : $value,
                    property : $propertyName,
                    attribute: $attributeInstance,
                    result   : $result
                ),
                $attributeInstance instanceof \Avax\Validation\System\Capabilities\Metadata\Attributes\Min                => $this->handleMin(
                    value    : $value,
                    property : $propertyName,
                    attribute: $attributeInstance,
                    result   : $result
                ),
                $attributeInstance instanceof \Avax\Validation\System\Capabilities\Metadata\Attributes\PasswordComplexity => $this->handlePasswordComplexity(
                    value   : (string) ($value ?? ''),
                    property: $propertyName,
                    message : $attributeInstance->message,
                    result  : $result
                ),
                default                                                                                                   => null
            };
        }
    }

    private function handleRequired(mixed $value, string $property, string $message, ValidationResult $result) : void
    {
        if ($value === null || $value === '') {
            $result->addError(property: $property, message: $message);
        }
    }

    private function handleEmail(string $value, string $property, string $message, ValidationResult $result) : void
    {
        if ($value !== '' && ! (new ValidateEmail())->execute(email: $value)) {
            $result->addError(property: $property, message: $message);
        }
    }

    private function handleMinLength(mixed $value, string $property, \Avax\Validation\System\Capabilities\Metadata\Attributes\MinLength $attribute, ValidationResult $result) : void
    {
        if (is_string(value: $value) && strlen(string: $value) < $attribute->length) {
            $result->addError(property: $property, message: $attribute->getMessage(property: $property));
        }
    }

    private function handleMin(mixed $value, string $property, \Avax\Validation\System\Capabilities\Metadata\Attributes\Min $attribute, ValidationResult $result) : void
    {
        if ((is_int(value: $value) || is_float(value: $value)) && $value < $attribute->minimum) {
            $result->addError(property: $property, message: $attribute->getMessage(property: $property));
        }
    }

    private function handlePasswordComplexity(string $value, string $property, string $message, ValidationResult $result) : void
    {
        if ($value !== '' && (! preg_match(pattern: '/[A-Z]/', subject: $value) || ! preg_match(pattern: '/[a-z]/', subject: $value) || ! preg_match(pattern: '/\d/', subject: $value))) {
            $result->addError(property: $property, message: $message);
        }
    }
}
