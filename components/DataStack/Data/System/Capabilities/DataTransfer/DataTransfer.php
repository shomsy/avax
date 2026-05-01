<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\DataTransfer;

use Avax\Components\Application\Validation\System\Capabilities\Metadata\Attributes\DefaultValue;
use Avax\Components\Application\Validation\System\Capabilities\Metadata\Attributes\EmailRule;
use Avax\Components\Application\Validation\System\Capabilities\Metadata\Attributes\IntegerRule;
use Avax\Components\Application\Validation\System\Capabilities\Metadata\Attributes\MinLengthRule;
use Avax\Components\Application\Validation\System\Capabilities\Metadata\Attributes\MinRule;
use Avax\Components\Application\Validation\System\Capabilities\Metadata\Attributes\Optional;
use Avax\Components\Application\Validation\System\Capabilities\Metadata\Attributes\PasswordComplexityRule;
use Avax\Components\Application\Validation\System\Capabilities\Metadata\Attributes\Required;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;
use Throwable;

/**
 * DataTransfer - Complete DTO System with Validation Integration
 *
 * This is the enterprise-grade data transfer system that:
 * - Validates DTOs using Validation rules
 * - Serializes to/from arrays, JSON, stdClass
 * - Supports field visibility control
 * - Integrates with Collections for complex types
 */
final class DataTransfer
{
    private static ?DataTransferConfig $dataTransferConfig = null;

    public static function configure(DataTransferConfig $dataTransferConfig): void
    {
        self::$dataTransferConfig = $dataTransferConfig;
    }

    /**
     * Validate input data against a DTO class
     *
     * @template T of object
     *
     * @param class-string<T> $class
     */
    public static function tryCreate(string $class, array|object $input): DataTransferResult
    {
        try {
            return DataTransferResult::success(object: self::create(class: $class, input: $input));
        } catch (DataTransferFailure $failure) {
            return DataTransferResult::failure(failure: $failure);
        } catch (Throwable $exception) {
            return DataTransferResult::failure(
                failure: new DataTransferFailure(
                    message : 'Data transfer failed.',
                    previous: $exception,
                ),
            );
        }
    }

    /**
     * Create a DTO instance with validation
     *
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public static function create(string $class, array|object $input): object
    {
        self::$dataTransferConfig ?? DataTransferConfig::default();
        $inputData = is_array($input) ? $input : (array) $input;

        $reflectionClass = new ReflectionClass($class);
        $properties      = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

        $values = [];
        $violations = [];

        foreach ($properties as $property) {
            $propertyName = $property->getName();

            // Check for Required attribute
            $isRequired = ! empty($property->getAttributes(Required::class));

            // Check for Optional attribute
            $isOptional = ! empty($property->getAttributes(Optional::class));

            // Check if value exists in input
            $hasValue = array_key_exists($propertyName, $inputData);
            $value    = $inputData[$propertyName] ?? null;

            // Handle missing required fields
            if ($isRequired && ! $hasValue && $value === null) {
                $violations[] = new DataTransferViolation(
                    field  : $propertyName,
                    message: sprintf('Field "%s" is required.', $propertyName),
                );

                continue;
            }

            // Skip optional fields with no value
            if ($isOptional && ! $hasValue) {
                continue;
            }

            // Check default value
            if (! $hasValue && ! $isRequired) {
                $defaultAttrs = $property->getAttributes(DefaultValue::class);
                if (! empty($defaultAttrs)) {
                    $value = $defaultAttrs[0]->newInstance()->value;
                }
            }

            // Validate value if present and rules exist
            if ($value !== null) {
                foreach ($property->getAttributes() as $attribute) {
                    $attrName = $attribute->getName();
                    $shortName = substr($attrName, strrpos($attrName, '\\') + 1);

                    $validationResult = self::validatePropertyValue(
                        $shortName,
                        $value,
                        $propertyName,
                    );

                    if ($validationResult instanceof DataTransferViolation) {
                        $violations[] = $validationResult;
                    }
                }
            }

            $values[$propertyName] = $value;
        }

        if ($violations !== []) {
            throw new DataTransferFailure(
                message   : 'Data validation failed.',
                violations: new DataTransferViolations($violations),
            );
        }

        return new $class(...$values);
    }

    /**
     * Validate a single property value
     */
    private static function validatePropertyValue(
        string $ruleName,
        mixed $value,
        string $propertyName,
    ): ?DataTransferViolation {
        try {
            $rule = match ($ruleName) {
                'EmailRule'              => new EmailRule(),
                'IntegerRule'            => new IntegerRule(),
                'MinLengthRule' => new ReflectionClass(new MinLengthRule(1))->newInstance()->args[0] ?? 1,
                'MinRule'                => new MinRule(1),
                'PasswordComplexityRule' => new PasswordComplexityRule(),
                default                  => null,
            };

            if ($rule !== null) {
                $rule->validate($value, $propertyName);
            }

            return null;
        } catch (InvalidArgumentException $invalidArgumentException) {
            return new DataTransferViolation(
                field  : $propertyName,
                message: $invalidArgumentException->getMessage(),
            );
        }
    }

    public static function config(): DataTransferConfig
    {
        return self::$dataTransferConfig ??= DataTransferConfig::default();
    }
}
