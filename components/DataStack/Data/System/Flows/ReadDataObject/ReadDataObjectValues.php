<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Flows\ReadDataObject;

use Avax\Components\DataStack\Data\System\Capabilities\Shapes\ClassShape\DataField;

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
            if ($field->reflectionProperty !== null && $field->reflectionProperty->isInitialized(object: $object)) {
                $values[$field->name] = $field->reflectionProperty->getValue(object: $object);

                continue;
            }

            if (property_exists(object_or_class: $object, property: $field->name)) {
                $values[$field->name] = $object->{$field->name};
            }
        }

        return $values;
    }
}
