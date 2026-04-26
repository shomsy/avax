<?php

declare(strict_types=1);

namespace Avax\DataFoundation\DataTransfer\Capabilities\ValueConversion;

use Avax\DataFoundation\DataTransfer\InspectDataShape\DataField;
use Stringable;

/** @noinspection PhpUnused */
final readonly class ConvertValueToScalar
{
    public function convert(mixed $value, DataField $field, ValueConversionContext $context) : mixed
    {
        return match ($field->type->primaryName()) {
            'int'    => $this->toInt(value: $value, field: $field, context: $context),
            'float'  => $this->toFloat(value: $value, field: $field, context: $context),
            'string' => $this->toString(value: $value, field: $field, context: $context),
            'bool'   => $this->toBool(value: $value, field: $field, context: $context),
            default  => $value,
        };
    }

    private function toInt(mixed $value, DataField $field, ValueConversionContext $context) : int
    {
        if (is_int(value: $value)) {
            return $value;
        }

        if (is_string(value: $value) && preg_match(pattern: '/^-?\d+$/', subject: $value) === 1) {
            return (int) $value;
        }

        throw ValueConversionFailed::forField(
            path        : (string) $context->path,
            expectedType: 'int',
            actualValue : $value,
            message     : sprintf('Field "%s" must be an integer.', $field->name),
        );
    }

    private function toFloat(mixed $value, DataField $field, ValueConversionContext $context) : float
    {
        if (is_float(value: $value) || is_int(value: $value)) {
            return (float) $value;
        }

        if (is_string(value: $value) && is_numeric(value: $value)) {
            return (float) $value;
        }

        throw ValueConversionFailed::forField(
            path        : (string) $context->path,
            expectedType: 'float',
            actualValue : $value,
            message     : sprintf('Field "%s" must be a float.', $field->name),
        );
    }

    private function toString(mixed $value, DataField $field, ValueConversionContext $context) : string
    {
        if (is_string(value: $value)) {
            return $value;
        }

        if (is_scalar(value: $value) || $value instanceof Stringable) {
            return (string) $value;
        }

        throw ValueConversionFailed::forField(
            path        : (string) $context->path,
            expectedType: 'string',
            actualValue : $value,
            message     : sprintf('Field "%s" must be a string.', $field->name),
        );
    }

    private function toBool(mixed $value, DataField $field, ValueConversionContext $context) : bool
    {
        if (is_bool(value: $value)) {
            return $value;
        }

        $filtered = filter_var(value: $value, filter: FILTER_VALIDATE_BOOL, options: FILTER_NULL_ON_FAILURE);

        if ($filtered !== null) {
            return $filtered;
        }

        throw ValueConversionFailed::forField(
            path        : (string) $context->path,
            expectedType: 'bool',
            actualValue : $value,
            message     : sprintf('Field "%s" must be a boolean.', $field->name),
        );
    }
}
