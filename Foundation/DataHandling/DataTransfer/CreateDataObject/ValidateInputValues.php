<?php

declare(strict_types=1);

namespace Avax\DataHandling\DataTransfer\CreateDataObject;

use Avax\DataHandling\DataTransfer\Capabilities\ErrorReporting\DataTransferViolations;
use Avax\DataHandling\DataTransfer\Capabilities\FieldValidation\ValidateFieldRules;
use Avax\DataHandling\DataTransfer\Capabilities\FieldValidation\ValidateNullableField;
use Avax\DataHandling\DataTransfer\Capabilities\FieldValidation\ValidateRequiredField;
use Avax\DataHandling\DataTransfer\Configuration\DataTransferConfig;
use Avax\DataHandling\DataTransfer\InspectDataShape\DataShape;

final readonly class ValidateInputValues
{
    public function validate(DataShape $shape, array $values, DataTransferConfig $config, bool $validateRules = true) : DataTransferViolations
    {
        $violations = DataTransferViolations::empty();

        foreach ($shape->fields() as $field) {
            $resolved = $values[$field->name] ?? [
                'present' => false,
                'value'   => null,
                'path'    => null,
            ];

            $path = $resolved['path'];

            $requiredViolation = new ValidateRequiredField()->validate(
                field  : $field,
                present: (bool) $resolved['present'],
                path   : $path,
            );

            if ($requiredViolation !== null) {
                $violations = $violations->add(violation: $requiredViolation);
            }

            $nullableViolation = new ValidateNullableField()->validate(
                field  : $field,
                value  : $resolved['value'],
                present: (bool) $resolved['present'],
                path   : $path,
            );

            if ($nullableViolation !== null) {
                $violations = $violations->add(violation: $nullableViolation);
            }

            if (! $validateRules || ! $resolved['present']) {
                continue;
            }

            foreach (new ValidateFieldRules()->validate(field: $field, value: $resolved['value'], path: $path, config: $config) as $ruleViolation) {
                $violations = $violations->add(violation: $ruleViolation);
            }
        }

        return $violations;
    }
}
