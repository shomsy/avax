<?php

declare(strict_types=1);

namespace Avax\Components\Application\Validation\System\Capabilities\Execution;

use Avax\Components\Application\Validation\System\Capabilities\Metadata\Attributes\Email;
use Avax\Components\Application\Validation\System\Capabilities\Metadata\Attributes\Min;
use Avax\Components\Application\Validation\System\Capabilities\Metadata\Attributes\MinLength;
use Avax\Components\Application\Validation\System\Capabilities\Metadata\Attributes\PasswordComplexity;
use Avax\Components\Application\Validation\System\Capabilities\Metadata\Attributes\Required;
use ReflectionClass;
use ReflectionProperty;

final readonly class ValidateDto
{
    public function execute(object $dto) : ValidationResult
    {
        $validationResult = new ValidationResult;
        $reflectionClass  = new ReflectionClass($dto);

        foreach ($reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
            $this->validateProperty($reflectionProperty, $dto, $validationResult);
        }

        return $validationResult;
    }

    private function validateProperty(ReflectionProperty $reflectionProperty, object $dto, ValidationResult $validationResult) : void
    {
        $propertyName = $reflectionProperty->getName();
        $value = $reflectionProperty->isInitialized($dto) ? $reflectionProperty->getValue($dto) : null;

        foreach ($reflectionProperty->getAttributes() as $attribute) {
            $instance = $attribute->newInstance();

            match (true) {
                $instance instanceof Required  => $this->handleRequired($value, $propertyName, $instance->message, $validationResult),
                $instance instanceof Email     => $this->handleEmail((string) ($value ?? ''), $propertyName, $instance->message, $validationResult),
                $instance instanceof MinLength => $this->handleMinLength($value, $propertyName, $instance, $validationResult),
                $instance instanceof Min       => $this->handleMin($value, $propertyName, $instance, $validationResult),
                $instance instanceof PasswordComplexity => $this->handlePasswordComplexity((string) ($value ?? ''), $propertyName, $instance->message, $validationResult),
                default                        => null
            };
        }
    }

    private function handleRequired(mixed $value, string $property, string $message, ValidationResult $validationResult) : void
    {
        if ($value === null || $value === '') {
            $validationResult->addError($property, $message);
        }
    }

    private function handleEmail(string $value, string $property, string $message, ValidationResult $validationResult) : void
    {
        if ($value !== '' && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $validationResult->addError($property, $message);
        }
    }

    private function handleMinLength(mixed $value, string $property, MinLength $minLength, ValidationResult $validationResult) : void
    {
        if (is_string($value) && mb_strlen($value) < $minLength->length) {
            $validationResult->addError($property, $minLength->getMessage($property));
        }
    }

    private function handleMin(mixed $value, string $property, Min $min, ValidationResult $validationResult) : void
    {
        if ((is_int($value) || is_float($value)) && $value < $min->minimum) {
            $validationResult->addError($property, $min->getMessage($property));
        }
    }

    private function handlePasswordComplexity(string $value, string $property, string $message, ValidationResult $validationResult) : void
    {
        if ($value !== '' && (in_array(preg_match('/[A-Z]/', $value), [0, false], true) || in_array(preg_match('/[a-z]/', $value), [0, false], true) || in_array(preg_match('/\d/', $value), [0, false], true))) {
            $validationResult->addError($property, $message);
        }
    }
}
