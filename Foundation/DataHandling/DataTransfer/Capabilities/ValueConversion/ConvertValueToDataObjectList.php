<?php

declare(strict_types=1);

namespace Avax\DataHandling\DataTransfer\Capabilities\ValueConversion;

use Avax\DataHandling\DataTransfer\CreateDataObject\CreateDataObject;
use Avax\DataHandling\DataTransfer\InspectDataShape\DataField;
use Traversable;

final readonly class ConvertValueToDataObjectList
{
    public function convert(mixed $value, DataField $field, ValueConversionContext $context) : array
    {
        $itemClass = $field->listItemClass();

        if ($itemClass === null) {
            return is_array(value: $value) ? $value : iterator_to_array(iterator: $value);
        }

        if (! is_array(value: $value) && ! $value instanceof Traversable) {
            throw ValueConversionFailed::forField(
                path        : (string) $context->path,
                expectedType: 'array',
                actualValue : $value,
                message     : sprintf('Field "%s" must be an array of %s.', $field->name, $itemClass),
            );
        }

        $items   = $value instanceof Traversable ? iterator_to_array(iterator: $value) : $value;
        $objects = [];

        foreach ($items as $index => $item) {
            if ($item instanceof $itemClass) {
                $objects[$index] = $item;

                continue;
            }

            if (! is_array(value: $item) && ! is_object(value: $item)) {
                throw ValueConversionFailed::forField(
                    path        : (string) $context->path->append(segment: $index),
                    expectedType: $itemClass,
                    actualValue : $item,
                    message     : sprintf('List item "%s" must be an array or object.', $index),
                );
            }

            $objects[$index] = new CreateDataObject(config: $context->config)->create(
                class: $itemClass,
                input: $item,
                path : $context->path->append(segment: $index),
            );
        }

        return $objects;
    }
}
