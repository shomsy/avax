<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\PublicSurface;

use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\CastWith;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\DefaultValue;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\ListOf;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\MapFrom;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Optional;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Required;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation\DataTransferFailure;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation\DataTransferResult;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation\DataTransferViolation;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation\DataTransferViolations;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\ValueConversion\ValueCasterInterface;
use Avax\Components\DataStack\DataTransfer\System\Configuration\DataTransferConfig;
use Avax\Components\DataStack\DataTransfer\System\Flows\SerializeDataObject\SerializeDataObject;
use Avax\Framework\System\Capabilities\StateReset\ResettableState;
use BackedEnum;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use stdClass;
use Throwable;

/**
 * DataTransfer - Complete DTO System
 *
 * Public API:
 * - DataTransfer::create(ClassName::class, $input)
 * - DataTransfer::tryCreate(ClassName::class, $input)
 * - DataTransfer::toArray($object)
 * - DataTransfer::toJson($object)
 */
final class DataTransfer implements ResettableState
{
    private static ?DataTransferConfig $dataTransferConfig = null;

    public static function configure(DataTransferConfig $dataTransferConfig) : void
    {
        self::$dataTransferConfig = $dataTransferConfig;
    }

    /**
     * @template T of object
     * @param class-string<T>             $class
     * @param array<string, mixed>|object $input
     */
    public static function tryCreate(string $class, array|object $input) : DataTransferResult
    {
        try {
            return DataTransferResult::success(object: self::create(class: $class, input: $input));
        } catch (DataTransferFailure $failure) {
            return DataTransferResult::failure(dataTransferFailure: $failure);
        } catch (Throwable $exception) {
            return DataTransferResult::failure(
                dataTransferFailure: new DataTransferFailure(
                                         message : 'Data transfer failed.',
                                         previous: $exception,
                                     ),
            );
        }
    }

    /**
     * @template T of object
     * @param class-string<T>             $class
     * @param array<string, mixed>|object $input
     *
     * @return T
     */
    public static function create(string $class, array|object $input) : object
    {
        self::$dataTransferConfig ??= DataTransferConfig::default();
        $inputData                = is_array($input) ? $input : (array) $input;

        $reflectionClass = new ReflectionClass($class);
        $properties      = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);

        $values     = [];
        $violations = [];

        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $inputName    = self::resolveInputName($property);
            $hasValue     = array_key_exists($inputName, $inputData);
            $value        = $inputData[$inputName] ?? null;

            // Required check
            $isRequired = $property->getAttributes(Required::class) !== [];
            $isOptional = $property->getAttributes(Optional::class) !== [];

            if ($isRequired && ! $hasValue) {
                $violations[] = new DataTransferViolation(
                    field  : $propertyName,
                    message: sprintf('Field "%s" is required.', $propertyName),
                );
                continue;
            }

            if ($isOptional && ! $hasValue) {
                continue;
            }

            // Default value
            if (! $hasValue) {
                $defaultAttrs = $property->getAttributes(DefaultValue::class);
                if ($defaultAttrs !== []) {
                    $value = $defaultAttrs[0]->newInstance()->value;
                }
            }

            // Cast and hydrate value
            if ($value !== null) {
                $value = self::hydrateValue(
                    property  : $property,
                    value     : $value,
                    violations: $violations,
                    fieldName : $propertyName,
                );
            }

            $values[$propertyName] = $value;
        }

        if ($violations !== []) {
            throw new DataTransferFailure(
                message   : 'Data validation failed.',
                violations: new DataTransferViolations($violations),
            );
        }

        // Constructor-promoted DTO: use named arguments
        $constructor = $reflectionClass->getConstructor();
        if ($constructor !== null && $constructor->getNumberOfParameters() > 0) {
            return new $class(...$values);
        }

        // Property-based hydration (e.g. SecureRequest subclasses)
        $instance = $reflectionClass->newInstance();
        foreach ($values as $name => $value) {
            if ($value !== null) {
                $prop = $reflectionClass->getProperty($name);
                $prop->setValue($instance, $value);
            }
        }

        return $instance;
    }

    private static function resolveInputName(ReflectionProperty $property) : string
    {
        $mapFromAttrs = $property->getAttributes(MapFrom::class);
        if ($mapFromAttrs !== []) {
            return $mapFromAttrs[0]->newInstance()->name;
        }

        return $property->getName();
    }

    /**
     * @param list<DataTransferViolation> $violations
     */
    private static function hydrateValue(
        ReflectionProperty $property,
        mixed              $value,
        array              &$violations,
        string             $fieldName,
    ) : mixed
    {
        // Custom caster
        $castAttrs = $property->getAttributes(CastWith::class);
        if ($castAttrs !== []) {
            $casterClass = $castAttrs[0]->newInstance()->casterClass;
            $caster      = new $casterClass();
            if ($caster instanceof ValueCasterInterface) {
                // Full interface requires DataField + ValueConversionContext — skip for now
                // Simple casters (non-interface) are handled below
            } elseif (method_exists($caster, 'cast')) {
                return $caster->cast($value, $fieldName);
            }
        }

        // ListOf nested DTO casting
        $listAttrs = $property->getAttributes(ListOf::class);
        if ($listAttrs !== []) {
            $itemClass = $listAttrs[0]->newInstance()->class;
            if (! is_array($value)) {
                $violations[] = new DataTransferViolation(
                    field  : $fieldName,
                    message: sprintf('Field "%s" must be an array for list casting.', $fieldName),
                );

                return $value;
            }

            $items = [];
            foreach ($value as $index => $item) {
                if (is_object($item) && $item instanceof $itemClass) {
                    $items[] = $item;
                } elseif (is_array($item)) {
                    $items[] = self::create(class: $itemClass, input: $item);
                } else {
                    $violations[] = new DataTransferViolation(
                        field  : $fieldName,
                        message: sprintf('Field "%s[%d]" is not a valid %s.', $fieldName, $index, $itemClass),
                    );
                }
            }

            return $items;
        }

        // Nested DTO casting
        $propertyType = $property->getType();
        if ($propertyType instanceof ReflectionNamedType && ! $propertyType->isBuiltin()) {
            $typeName = $propertyType->getName();

            if (is_subclass_of($typeName, BackedEnum::class)) {
                return $typeName::from($value);
            }

            if (class_exists($typeName) && ! enum_exists($typeName)) {
                if (is_object($value) && $value instanceof $typeName) {
                    return $value;
                }
                if (is_array($value)) {
                    return self::create(class: $typeName, input: $value);
                }
            }
        }

        // Type validation
        self::validateType($property, $value, $fieldName, $violations);

        // Attribute validation
        foreach ($property->getAttributes() as $attribute) {
            $instance  = $attribute->newInstance();
            $violation = self::validateAttributeValue($instance, $value, $fieldName);
            if ($violation !== null) {
                $violations[] = $violation;
            }
        }

        return $value;
    }

    /**
     * @param list<DataTransferViolation> $violations
     */
    private static function validateType(
        ReflectionProperty $property,
        mixed              $value,
        string             $fieldName,
        array              &$violations,
    ) : void
    {
        $propertyType = $property->getType();
        if (! $propertyType instanceof ReflectionNamedType) {
            return;
        }

        $typeName   = $propertyType->getName();
        $allowsNull = $propertyType->allowsNull();

        $typeValid = match ($typeName) {
            'string' => is_string($value),
            'int'    => is_int($value),
            'float'  => is_float($value) || is_int($value),
            'bool'   => is_bool($value),
            'array'  => is_array($value),
            'mixed'  => true,
            default  => $value instanceof $typeName,
        };

        if (! $typeValid && ! ($allowsNull && $value === null)) {
            $violations[] = new DataTransferViolation(
                field  : $fieldName,
                message: sprintf('Field "%s" must be of type %s.', $fieldName, $typeName),
            );
        }
    }

    private static function validateAttributeValue(
        object $attribute,
        mixed  $value,
        string $fieldName,
    ) : ?DataTransferViolation
    {
        if (! method_exists($attribute, 'validate')) {
            return null;
        }

        try {
            $attribute->validate($value, $fieldName);

            return null;
        } catch (InvalidArgumentException $e) {
            return new DataTransferViolation(
                field  : $fieldName,
                message: $e->getMessage(),
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public static function toArray(object $object) : array
    {
        return new SerializeDataObject()->toArray(object: $object);
    }

    public static function toJson(object $object, int $flags = 0) : string
    {
        return new SerializeDataObject()->toJson(object: $object, flags: $flags);
    }

    public static function toStdClass(object $object) : stdClass
    {
        return new SerializeDataObject()->toStdClass(object: $object);
    }

    /**
     * @return array<string, mixed>
     */
    public static function toFlatArray(object $object) : array
    {
        return new SerializeDataObject()->toFlatArray(object: $object);
    }

    /**
     * @return array{data: array{type: string, id: mixed, attributes: array<string, mixed>}}
     */
    public static function toJsonApi(object $object, string $type) : array
    {
        return new SerializeDataObject()->toJsonApi(object: $object, type: $type);
    }

    public static function config() : DataTransferConfig
    {
        return self::$dataTransferConfig ??= DataTransferConfig::default();
    }

    public function resetState() : void
    {
        self::$dataTransferConfig = null;
    }
}
