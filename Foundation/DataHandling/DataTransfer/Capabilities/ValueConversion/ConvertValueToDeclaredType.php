<?php

declare(strict_types=1);

namespace Avax\DataHandling\DataTransfer\Capabilities\ValueConversion;

use Avax\DataHandling\DataTransfer\InspectDataShape\DataField;
use Traversable;

final readonly class ConvertValueToDeclaredType
{
    public function convert(mixed $value, DataField $field, ValueConversionContext $context) : mixed
    {
        if ($value === null || $field->type->isMixed()) {
            return $value;
        }

        $value = new ConvertValueWithCustomCaster()->convert(value: $value, field: $field, context: $context);

        if ($field->type->isArray()) {
            if (! is_array(value: $value) && ! $value instanceof Traversable) {
                throw ValueConversionFailed::forField(
                    path        : (string) $context->path,
                    expectedType: 'array',
                    actualValue : $value,
                    message     : sprintf('Field "%s" must be an array.', $field->name),
                );
            }

            return new ConvertValueToDataObjectList()->convert(value: $value, field: $field, context: $context);
        }

        if ($field->type->isBackedEnum()) {
            return new ConvertValueToEnum()->convert(value: $value, field: $field, context: $context);
        }

        if ($field->type->isScalar()) {
            return new ConvertValueToScalar()->convert(value: $value, field: $field, context: $context);
        }

        if ($field->type->isClass()) {
            return new ConvertValueToNestedDataObject()->convert(value: $value, field: $field, context: $context);
        }

        return $value;
    }
}
