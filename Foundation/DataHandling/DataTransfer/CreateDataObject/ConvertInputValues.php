<?php

declare(strict_types=1);

namespace Avax\DataHandling\DataTransfer\CreateDataObject;

use Avax\DataHandling\DataTransfer\Capabilities\ErrorReporting\DataTransferFailure;
use Avax\DataHandling\DataTransfer\Capabilities\ErrorReporting\DataTransferViolations;
use Avax\DataHandling\DataTransfer\Capabilities\ValueConversion\ConvertValueToDeclaredType;
use Avax\DataHandling\DataTransfer\Capabilities\ValueConversion\ValueConversionContext;
use Avax\DataHandling\DataTransfer\Configuration\DataTransferConfig;
use Avax\DataHandling\DataTransfer\InspectDataShape\DataShape;

final readonly class ConvertInputValues
{
    public function convert(DataShape $shape, array $values, DataTransferConfig $config) : array
    {
        $converted  = [];
        $violations = DataTransferViolations::empty();

        foreach ($shape->fields() as $field) {
            $resolved = $values[$field->name] ?? null;

            if ($resolved === null || ! $resolved['present']) {
                continue;
            }

            if ($resolved['value'] === null) {
                $converted[$field->name] = $resolved;

                continue;
            }

            try {
                $converted[$field->name] = [
                    ...$resolved,
                    'value' => new ConvertValueToDeclaredType()->convert(
                        value  : $resolved['value'],
                        field  : $field,
                        context: new ValueConversionContext(config: $config, path: $resolved['path']),
                    ),
                ];
            } catch (DataTransferFailure $failure) {
                $violations = $violations->merge(violations: $failure->violations());
            }
        }

        return [
            'values'     => $converted,
            'violations' => $violations,
        ];
    }
}
