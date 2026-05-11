<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Flows\CreateDataObject;

use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\CastWith;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\DefaultValue;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\ListOf;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\MapFrom;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Optional;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Required;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\CacheDataShape;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\DataField;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\DataShape;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\DataShapeCompiler;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\InspectDataShape;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\ReadClassDataShape;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation\DataTransferFailure;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation\DataTransferViolation;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation\DataTransferViolations;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\ValueConversion\ValueCasterInterface;
use Avax\Components\DataStack\DataTransfer\System\Configuration\DataTransferConfig;
use BackedEnum;
use InvalidArgumentException;
use ReflectionClass;

/**
 * CreateDataObject — hydrates and validates a typed object from input.
 *
 * Uses DataShapeCompiler for metadata resolution (compiled → cached → live reflection).
 * DataTransfer facade delegates here.
 */
final readonly class CreateDataObject
{
    public function __construct(
        private DataTransferConfig|null $dataTransferConfig = null,
        private DataShapeCompiler|null  $dataShapeCompiler = null,
    ) {}

    /**
     * @template T of object
     * @param class-string<T>             $class
     * @param array<string, mixed>|object $input
     *
     * @phpstan-return T
     * @throws DataTransferFailure
     */
    public function create(string $class, array|object $input): object
    {
        $config = $this->dataTransferConfig ?? DataTransferConfig::default();
        $inputData = is_array($input) ? $input : (array) $input;

        $shape = $this->resolveShape($class, $config);

        // Merge constructor fields with public-only fields for complete field coverage.
        // The original create() read all public/protected properties regardless of constructor.
        $fields = $this->allFieldsForCreate($shape);

        $values = [];
        $violations = [];

        foreach ($fields as $field) {
            $inputName = $field->inputName;
            $hasValue = array_key_exists($inputName, $inputData);
            $value = $inputData[$inputName] ?? null;

            if ($field->isRequired() && ! $hasValue) {
                $violations[] = new DataTransferViolation(
                    field: $field->name,
                    message: sprintf('Field "%s" is required.', $field->name),
                );
                continue;
            }

            if ($field->hasAttribute(Optional::class) && ! $hasValue) {
                continue;
            }

            if (! $hasValue) {
                if ($field->hasDefaultAttribute()) {
                    $value = $field->defaultFromAttribute();
                }
            }

            if ($value !== null) {
                $value = $this->hydrateValue(
                    field: $field,
                    value: $value,
                    violations: $violations,
                );
            }

            $values[$field->name] = $value;
        }

        if ($violations !== []) {
            throw new DataTransferFailure(
                message: 'Data validation failed.',
                violations: new DataTransferViolations($violations),
            );
        }

        /** @phpstan-var T */
        return $this->instantiateForShape($shape, $values);
    }

    /**
     * Merge constructor fields with public-only fields for the create() path.
     *
     * @return array<string, DataField>
     */
    private function allFieldsForCreate(DataShape $shape): array
    {
        $fields = $shape->constructorFields();
        foreach ($shape->publicPropertyFields() as $name => $field) {
            if (! isset($fields[$name])) {
                $fields[$name] = $field;
            }
        }

        return $fields;
    }

    /**
     * Hydrate an existing object's public properties from input and validate.
     *
     * Returns violations instead of throwing. Caller decides whether to throw.
     * This enables SecureRequest to delegate hydration/validation to DataTransfer.
     *
     * Only hydrates properties that are either in the input or have Required/Optional/DefaultValue attributes.
     * Other public properties are left untouched.
     *
     * @param array<string, mixed> $input
     *
     * @return list<DataTransferViolation>
     */
    public function hydrateInto(object $object, array $input): array
    {
        $config = $this->dataTransferConfig ?? DataTransferConfig::default();
        $shape = $this->resolveShape($object::class, $config);
        $fields = $shape->publicPropertyFields();

        $violations = [];

        foreach ($fields as $field) {
            $inputName = $field->inputName;
            $hasValue = array_key_exists($inputName, $input);
            $value = $input[$inputName] ?? null;

            $isRequired = $field->isRequired();
            $isOptional = $field->hasAttribute(Optional::class);
            $hasDefault = $field->hasDefaultAttribute();

            // Skip properties not in input and without DTO attributes.
            if (! $hasValue && ! $isRequired && ! $isOptional && ! $hasDefault) {
                continue;
            }

            if ($isRequired && ! $hasValue) {
                $violations[] = new DataTransferViolation(
                    field: $field->name,
                    message: sprintf('Field "%s" is required.', $field->name),
                );
                continue;
            }

            if ($isOptional && ! $hasValue) {
                continue;
            }

            if (! $hasValue) {
                if ($field->hasDefaultAttribute()) {
                    $value = $field->defaultFromAttribute();
                }
            }

            if ($value !== null) {
                $value = $this->hydrateValue(
                    field: $field,
                    value: $value,
                    violations: $violations,
                );
            }

            if ($violations === [] || ! $this->hasFieldViolation($violations, $field->name)) {
                $field->reflectionProperty?->setValue($object, $value);
            }
        }

        return $violations;
    }

    /**
     * Check if violations already contain an error for the given field.
     *
     * @param list<DataTransferViolation> $violations
     */
    private function hasFieldViolation(array $violations, string $field): bool
    {
        foreach ($violations as $violation) {
            if ($violation->field === $field) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param class-string $class
     */
    private function resolveShape(string $class, DataTransferConfig $config): DataShape
    {
        if ($this->dataShapeCompiler !== null) {
            return $this->dataShapeCompiler->resolve($class, $config);
        }

        // Fallback to in-memory InspectDataShape cache
        $cache = new CacheDataShape();

        return (new InspectDataShape(
            dataTransferConfig: $config,
            cacheDataShape: $cache,
        ))->inspect($class);
    }

    /**
     * @param list<DataTransferViolation> $violations
     */
    private function hydrateValue(
        DataField $field,
        mixed $value,
        array &$violations,
    ): mixed {
        // Custom caster
        $casterClass = $field->casterClass();
        if ($casterClass !== null) {
            $caster = new $casterClass();
            if ($caster instanceof ValueCasterInterface) {
                // Full interface needs DataField + ValueConversionContext — not wired yet
            } elseif (method_exists($caster, 'cast')) {
                return $caster->cast($value, $field->name);
            }
        }

        // ListOf nested DTO casting
        $listItemClass = $field->listItemClass();
        if ($listItemClass !== null) {
            if (! is_array($value)) {
                $violations[] = new DataTransferViolation(
                    field: $field->name,
                    message: sprintf('Field "%s" must be an array for list casting.', $field->name),
                );

                return $value;
            }

            /** @phpstan-ignore-next-line */
            return $this->castObjectList($value, $listItemClass, $field->name, $violations);
        }

        // Nested DTO / backed enum casting
        $primaryType = $field->dataFieldType->primaryName();
        if ($primaryType !== null && ! $field->dataFieldType->isMixed() && ! $field->dataFieldType->isArray() && ! $field->dataFieldType->isScalar()) {
            if (is_subclass_of($primaryType, BackedEnum::class)) {
                return $primaryType::from($value);
            }

            if (class_exists($primaryType) && ! enum_exists($primaryType)) {
                if (is_object($value) && $value instanceof $primaryType) {
                    return $value;
                }
                if (is_array($value)) {
                    return $this->create(class: $primaryType, input: $value);
                }
            }
        }

        // Type validation
        $this->validateType($field, $value, $violations);

        // Attribute validation
        foreach ($field->attributes as $attribute) {
            $violation = $this->validateAttributeValue($attribute, $value, $field->name);
            if ($violation !== null) {
                $violations[] = $violation;
            }
        }

        return $value;
    }

    /**
     * @param array<int, mixed>           $value
     * @param class-string                $itemClass
     * @param list<DataTransferViolation> $violations
     *
     * @return list<object>
     */
    private function castObjectList(
        array $value,
        string $itemClass,
        string $fieldName,
        array &$violations,
    ): array {
        $items = [];
        foreach ($value as $index => $item) {
            if (is_object($item) && $item instanceof $itemClass) {
                $items[] = $item;
            } elseif (is_array($item)) {
                /** @phpstan-ignore-next-line */
                $items[] = $this->create(class: $itemClass, input: $item);
            } else {
                $violations[] = new DataTransferViolation(
                    field: $fieldName,
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
        DataField $field,
        mixed $value,
        array &$violations,
    ): void {
        $primaryType = $field->dataFieldType->primaryName();
        if ($primaryType === null || $field->dataFieldType->isMixed()) {
            return;
        }

        $typeValid = match ($primaryType) {
            'string' => is_string($value),
            'int' => is_int($value),
            'float' => is_float($value) || is_int($value),
            'bool' => is_bool($value),
            'array' => is_array($value),
            default => $value instanceof $primaryType,
        };

        if (! $typeValid) {
            $violations[] = new DataTransferViolation(
                field: $field->name,
                message: sprintf('Field "%s" must be of type %s.', $field->name, $primaryType),
            );
        }
    }

    private function validateAttributeValue(
        object $attribute,
        mixed $value,
        string $fieldName,
    ) : DataTransferViolation|null
    {
        if (! method_exists($attribute, 'validate')) {
            return null;
        }

        try {
            $attribute->validate($value, $fieldName);

            return null;
        } catch (InvalidArgumentException $e) {
            return new DataTransferViolation(
                field: $fieldName,
                message: $e->getMessage(),
            );
        }
    }

    /**
     * Instantiate using DataShape — handles constructor and property-based hydration.
     *
     * @param array<string, mixed> $values
     */
    private function instantiateForShape(DataShape $shape, array $values): object
    {
        $reflectionClass = new ReflectionClass($shape->class);
        $constructorFields = $shape->constructorFields();

        // If there are constructor fields, use constructor injection
        if ($constructorFields !== []) {
            $ctorArgs = [];
            foreach ($constructorFields as $field) {
                if (array_key_exists($field->name, $values)) {
                    $ctorArgs[$field->name] = $values[$field->name];
                }
            }

            $instance = $reflectionClass->newInstance(...$ctorArgs);

            // Also hydrate public-only properties not covered by constructor
            foreach ($shape->publicPropertyFields() as $field) {
                if (! $field->isConstructorField && array_key_exists($field->name, $values)) {
                    $field->reflectionProperty?->setValue($instance, $values[$field->name]);
                }
            }

            return $instance;
        }

        // No constructor — property-based hydration
        $object = $reflectionClass->newInstance();
        foreach ($shape->publicPropertyFields() as $field) {
            if (array_key_exists($field->name, $values)) {
                $field->reflectionProperty?->setValue($object, $values[$field->name]);
            }
        }

        return $object;
    }
}
