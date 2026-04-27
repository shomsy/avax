<?php

declare(strict_types=1);

namespace Avax\DataFoundation\DataTransfer\CreateDataObject;

use Avax\DataFoundation\DataTransfer\Foundation\FieldPath;
use Avax\DataFoundation\DataTransfer\InspectDataShape\DataField;

final readonly class ResolveFieldInputValue
{
    public function resolve(DataField $field, array $matchedFields, FieldPath $basePath) : array
    {
        if (array_key_exists(key: $field->name, array: $matchedFields)) {
            return $matchedFields[$field->name];
        }

        if ($field->hasDefaultAttribute()) {
            return [
                'present' => true,
                'value'   => $field->defaultFromAttribute(),
                'path'    => $basePath->append(segment: $field->name),
            ];
        }

        if ($field->hasDefaultValue) {
            return [
                'present' => true,
                'value'   => $field->defaultValue,
                'path'    => $basePath->append(segment: $field->name),
            ];
        }

        if ($field->type->allowsNull || ! $field->isRequired()) {
            return [
                'present' => true,
                'value'   => null,
                'path'    => $basePath->append(segment: $field->name),
            ];
        }

        return [
            'present' => false,
            'value'   => null,
            'path'    => $basePath->append(segment: $field->name),
        ];
    }
}
