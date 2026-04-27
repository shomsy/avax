<?php

declare(strict_types=1);

namespace Avax\DataFoundation\DataTransfer\Capabilities\ValueConversion;

use Avax\DataFoundation\DataTransfer\InspectDataShape\DataField;
use Throwable;

final readonly class ConvertValueWithCustomCaster
{
    public function convert(mixed $value, DataField $field, ValueConversionContext $context) : mixed
    {
        $caster = $this->resolveCaster(field: $field, context: $context);

        if ($caster === null) {
            return $value;
        }

        try {
            if ($caster instanceof ValueCasterInterface) {
                return $caster->cast(value: $value, field: $field, context: $context);
            }

            if (is_callable(value: $caster)) {
                return $caster($value, $field, $context);
            }

            if (is_object(value: $caster) && method_exists(object_or_class: $caster, method: 'cast')) {
                return $caster->cast(value: $value, field: $field, context: $context);
            }

            if (is_object(value: $caster) && method_exists(object_or_class: $caster, method: 'convert')) {
                return $caster->convert(value: $value, field: $field, context: $context);
            }
        } catch (Throwable $exception) {
            throw ValueConversionFailed::forField(
                path        : (string) $context->path,
                expectedType: $field->type->displayName(),
                actualValue : $value,
                message     : sprintf('Custom caster failed for field "%s": %s', $field->name, $exception->getMessage()),
                previous    : $exception,
            );
        }

        throw ValueConversionFailed::forField(
            path        : (string) $context->path,
            expectedType: $field->type->displayName(),
            actualValue : $value,
            message     : sprintf('Custom caster for field "%s" is not callable.', $field->name),
        );
    }

    private function resolveCaster(DataField $field, ValueConversionContext $context) : object|callable|null
    {
        $caster      = null;
        $casterClass = $field->casterClass();

        if ($casterClass !== null) {
            $caster = new $casterClass();
        }

        $primaryType = $field->type->primaryName();

        if ($caster === null && $primaryType !== null) {
            $configured = $context->config->casterFor(class: $primaryType);

            if (is_string(value: $configured) && class_exists(class: $configured)) {
                $caster = new $configured();
            } elseif ($configured !== null) {
                $caster = $configured;
            }
        }

        return $caster;
    }
}
