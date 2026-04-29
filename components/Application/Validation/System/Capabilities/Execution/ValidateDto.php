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
    public function execute(object $dto): ValidationResult
    {
        $result = new ValidationResult();
        $reflection = new ReflectionClass($dto);

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $this->validateProperty($property, $dto, $result);
        }

        return $result;
    }

    private function validateProperty(ReflectionProperty $property, object $dto, ValidationResult $result): void
    {
        $propertyName = $property->getName();
        $value = $property->isInitialized($dto) ? $property->getValue($dto) : null;

        foreach ($property->getAttributes() as $attribute) {
            $instance = $attribute->newInstance();

            match (true) {
                $instance instanceof Required => $this->handleRequired($value, $propertyName, $instance->message, $result),
                $instance instanceof Email => $this->handleEmail((string)($value ?? ''), $propertyName, $instance->message, $result),
                $instance instanceof MinLength => $this->handleMinLength($value, $propertyName, $instance, $result),
                $instance instanceof Min => $this->handleMin($value, $propertyName, $instance, $result),
                $instance instanceof PasswordComplexity => $this->handlePasswordComplexity((string)($value ?? ''), $propertyName, $instance->message, $result),
                default => null
            };
        }
    }

    private function handleRequired(mixed $value, string $property, string $message, ValidationResult $result): void
    {
        if ($value === null || $value === '') {
            $result->addError($property, $message);
        }
    }

    private function handleEmail(string $value, string $property, string $message, ValidationResult $result): void
    {
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $result->addError($property, $message);
        }
    }

    private function handleMinLength(mixed $value, string $property, MinLength $attr, ValidationResult $result): void
    {
        if (is_string($value) && mb_strlen($value) < $attr->length) {
            $result->addError($property, $attr->getMessage($property));
        }
    }

    private function handleMin(mixed $value, string $property, Min $attr, ValidationResult $result): void
    {
        if ((is_int($value) || is_float($value)) && $value < $attr->minimum) {
            $result->addError($property, $attr->getMessage($property));
        }
    }

    private function handlePasswordComplexity(string $value, string $property, string $message, ValidationResult $result): void
    {
        if ($value !== '' && (!preg_match('/[A-Z]/', $value) || !preg_match('/[a-z]/', $value) || !preg_match('/\d/', $value))) {
            $result->addError($property, $message);
        }
    }
}
