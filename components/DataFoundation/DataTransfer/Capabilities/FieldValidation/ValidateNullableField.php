<?php

declare(strict_types=1);

namespace Avax\DataFoundation\DataTransfer\Capabilities\FieldValidation;

use Avax\DataFoundation\DataTransfer\Capabilities\ErrorReporting\DataTransferViolation;
use Avax\DataFoundation\DataTransfer\Foundation\FieldPath;
use Avax\DataFoundation\DataTransfer\InspectDataShape\DataField;

final readonly class ValidateNullableField
{
    public function validate(DataField $field, mixed $value, bool $present, FieldPath $path) : DataTransferViolation|null
    {
        if (! $present || $value !== null || $field->type->allowsNull) {
            return null;
        }

        return new DataTransferViolation(
            path        : (string) $path,
            code        : 'null_not_allowed',
            message     : sprintf('Field "%s" cannot be null.', $field->name),
            expectedType: $field->type->displayName(),
            actualType  : 'null',
            failedRule  : self::class,
        );
    }
}
