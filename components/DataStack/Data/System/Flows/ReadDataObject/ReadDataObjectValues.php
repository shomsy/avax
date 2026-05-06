<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Flows\ReadDataObject;

use Avax\Components\DataStack\Data\System\Capabilities\DataShape\DataField;

final readonly class ReadDataObjectValues
{
    /**
     * @param  array<string, DataField>  $fields
     * @return array<string, mixed>
     */
    public function read(object $object, array $fields): array
    {
        $values = [];

        foreach ($fields as $field) {
            if ($field->property !== null && $field->property->isInitialized(object: $object)) {
                $values[$field->name] = $field->property->getValue(object: $object);

                continue;
            }

            if (property_exists(object_or_class: $object, property: $field->name)) {
                $values[$field->name] = $object->{$field->name};
            }
        }

        return $values;
    }
}
