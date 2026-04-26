<?php

declare(strict_types=1);

namespace Avax\DataFoundation\DataTransfer\Capabilities\ValueConversion;

use Avax\DataFoundation\DataTransfer\CreateDataObject\CreateDataObject;
use Avax\DataFoundation\DataTransfer\InspectDataShape\DataField;

final readonly class ConvertValueToNestedDataObject
{
    public function convert(mixed $value, DataField $field, ValueConversionContext $context) : object
    {
        $class = $field->type->primaryName();

        if ($class === null || ! class_exists(class: $class)) {
            throw ValueConversionFailed::forField(
                path        : (string) $context->path,
                expectedType: 'object',
                actualValue : $value,
                message     : sprintf('Field "%s" does not declare a data object class.', $field->name),
            );
        }

        if ($value instanceof $class) {
            return $value;
        }

        if (! is_array(value: $value) && ! is_object(value: $value)) {
            throw ValueConversionFailed::forField(
                path        : (string) $context->path,
                expectedType: $class,
                actualValue : $value,
                message     : sprintf('Field "%s" must be an array or object for nested data object conversion.', $field->name),
            );
        }

        return new CreateDataObject(config: $context->config)->create(
            class: $class,
            input: $value,
            path : $context->path,
        );
    }
}
