<?php

declare(strict_types=1);

namespace Avax\DataFoundation\DataTransfer\Capabilities\FieldValidation;

use Avax\DataFoundation\DataTransfer\Capabilities\ErrorReporting\DataTransferViolation;
use Avax\DataFoundation\DataTransfer\Foundation\FieldPath;
use Avax\DataFoundation\DataTransfer\InspectDataShape\DataField;

final readonly class ValidateRequiredField
{
    public function validate(DataField $field, bool $present, FieldPath $path) : DataTransferViolation|null
    {
        if ($present || ! $field->isRequired()) {
            return null;
        }

        return new DataTransferViolation(
            path        : (string) $path,
            code        : 'required_field_missing',
            message     : sprintf('Field "%s" is required.', $field->name),
            expectedType: $field->type->displayName(),
            actualType  : 'missing',
            failedRule  : self::class,
        );
    }
}
