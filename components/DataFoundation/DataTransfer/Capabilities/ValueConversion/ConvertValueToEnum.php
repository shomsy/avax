<?php

declare(strict_types=1);

namespace Avax\DataFoundation\DataTransfer\Capabilities\ValueConversion;

use Avax\DataFoundation\DataTransfer\InspectDataShape\DataField;
use BackedEnum;

final readonly class ConvertValueToEnum
{
    public function convert(mixed $value, DataField $field, ValueConversionContext $context) : BackedEnum
    {
        $enumClass = $field->type->primaryName();

        if ($enumClass === null || ! $field->type->isBackedEnum()) {
            throw ValueConversionFailed::forField(
                path        : (string) $context->path,
                expectedType: 'backed-enum',
                actualValue : $value,
                message     : sprintf('Field "%s" does not declare a backed enum.', $field->name),
            );
        }

        if ($value instanceof $enumClass) {
            return $value;
        }

        /** @var class-string<BackedEnum> $enumClass */
        $enum = $enumClass::tryFrom(value: $value);

        if ($enum !== null) {
            return $enum;
        }

        throw ValueConversionFailed::forField(
            path        : (string) $context->path,
            expectedType: $enumClass,
            actualValue : $value,
            message     : sprintf(
                              'Field "%s" must be one of [%s].',
                              $field->name,
                              implode(separator: ', ', array: array_map(
                                  callback: static fn (BackedEnum $case) : string => (string) $case->value,
                                  array   : $enumClass::cases(),
                              )),
                          ),
        );
    }
}
