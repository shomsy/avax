<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Flows\CreateDataObject;

use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\CastWith;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\DefaultValue;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\ListOf;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\MapFrom;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Optional;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Required;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation\DataTransferFailure;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation\DataTransferViolation;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation\DataTransferViolations;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\ValueConversion\ValueCasterInterface;
use Avax\Components\DataStack\DataTransfer\System\Configuration\DataTransferConfig;
use BackedEnum;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;

/**
 * CreateDataObject — hydrates and validates a typed object from input.
 *
 * Owns the full reflection/hydration/casting/validation engine.
 * DataTransfer facade delegates here.
 */
final readonly class CreateDataObject
{
    public function __construct(
        private ?DataTransferConfig $dataTransferConfig = null,
    ) {}

    /**
     * @template T of object
     * @param class-string<T>             $class
     * @param array<string, mixed>|object $input
     *
     * @return T
     * @throws DataTransferFailure
     */
    public function create(string $class, array|object $input) : object
    {
        $config    = $this->dataTransferConfig ?? DataTransferConfig::default();
        $inputData = is_array($input) ? $input : (array) $input;

        $reflectionClass = new ReflectionClass($class);
        $properties      = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);

        $values     = [];
        $violations = [];

        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $inputName    = $this->resolveInputName($property);
            $hasValue     = array_key_exists($inputName, $inputData);
            $value        = $inputData[$inputName] ?? null;

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

            if (! $hasValue) {
                $defaultAttrs = $property->getAttributes(DefaultValue::class);
                if ($defaultAttrs !== []) {
                    $value = $defaultAttrs[0]->newInstance()->value;
                }
            }

            if ($value !== null) {
                $value = $this->hydrateValue(
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

        return $this->instantiate($reflectionClass, $values);
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
     * @param list<DataTransferViolation> $violations
     */
    private function hydrateValue(
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
                // Full interface needs DataField + ValueConversionContext — not wired yet
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

            return $this->castObjectList($value, $itemClass, $fieldName, $violations);
        }

        // Nested DTO / backed enum casting
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
                    return $this->create(class: $typeName, input: $value);
                }
            }
        }

        // Type validation
        $this->validateType($property, $value, $fieldName, $violations);

        // Attribute validation
        foreach ($property->getAttributes() as $attribute) {
            $instance  = $attribute->newInstance();
            $violation = $this->validateAttributeValue($instance, $value, $fieldName);
            if ($violation !== null) {
                $violations[] = $violation;
            }
        }

        return $value;
    }

    /**
     * @param list<DataTransferViolation> $violations
     */
    private function castObjectList(
        array  $value,
        string $itemClass,
        string $fieldName,
        array  &$violations,
    ) : array
    {
        $items = [];
        foreach ($value as $index => $item) {
            if (is_object($item) && $item instanceof $itemClass) {
                $items[] = $item;
            } elseif (is_array($item)) {
                $items[] = $this->create(class: $itemClass, input: $item);
            } else {
                $violations[] = new DataTransferViolation(
                    field  : $fieldName,
                    message: sprintf('Field "%s[%d]" is not a valid %s.', $fieldName, $index, $itemClass),
                );
            }
        }

        return $items;
    }

    /**
     * @param list<DataTransferViolation> $violations
     */
    private function validateType(
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

        $typeName = $propertyType->getName();

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
                field  : $fieldName,
                message: sprintf('Field "%s" must be of type %s.', $fieldName, $typeName),
            );
        }
    }

    private function validateAttributeValue(
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
     * Instantiate object — constructor-promoted or property-based hydration.
     *
     * @param array<string, mixed> $values
     */
    private function instantiate(ReflectionClass $reflectionClass, array $values) : object
    {
        $constructor = $reflectionClass->getConstructor();
        if ($constructor !== null && $constructor->getNumberOfParameters() > 0) {
            return $reflectionClass->newInstance(...$values);
        }

        // Property-based hydration (no-constructor style)
        $instance = $reflectionClass->newInstance();
        foreach ($values as $name => $value) {
            if ($value !== null) {
                $reflectionClass->getProperty($name)->setValue($instance, $value);
            }
        }

        return $instance;
    }
}
