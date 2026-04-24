<?php

declare(strict_types=1);

namespace Avax\DataHandling\DataTransfer\CreateDataObject;

use Avax\DataHandling\DataTransfer\Capabilities\ErrorReporting\DataTransferViolation;
use Avax\DataHandling\DataTransfer\Capabilities\ErrorReporting\DataTransferViolations;
use Avax\DataHandling\DataTransfer\Capabilities\FieldMapping\MapInputNameToField;
use Avax\DataHandling\DataTransfer\Configuration\DataTransferConfig;
use Avax\DataHandling\DataTransfer\Configuration\UnknownFieldPolicy;
use Avax\DataHandling\DataTransfer\Foundation\FieldPath;
use Avax\DataHandling\DataTransfer\Foundation\InputData;
use Avax\DataHandling\DataTransfer\InspectDataShape\DataShape;

final readonly class MatchInputToDataShape
{
    public function match(DataShape $shape, InputData $input, FieldPath $path, DataTransferConfig $config) : array
    {
        $inputNameMap = new MapInputNameToField()->map(shape: $shape);
        $matched      = [];
        $unknown      = [];
        $violations   = DataTransferViolations::empty();

        foreach ($input->all() as $inputName => $value) {
            if (! is_string(value: $inputName) || ! array_key_exists(key: $inputName, array: $inputNameMap)) {
                $unknown[$inputName] = $value;

                if ($config->unknownFieldPolicy === UnknownFieldPolicy::Reject) {
                    $violations = $violations->add(
                        violation: new DataTransferViolation(
                                       path      : (string) $path->append(segment: (string) $inputName),
                                       code      : 'unknown_field',
                                       message   : sprintf('Unknown field "%s" for %s.', (string) $inputName, $shape->class),
                                       actualType: get_debug_type(value: $value),
                                   ),
                    );
                }

                continue;
            }

            $field                 = $inputNameMap[$inputName];
            $matched[$field->name] = [
                'present' => true,
                'value'   => $value,
                'path'    => $path->append(segment: $field->name),
            ];
        }

        return [
            'matched'    => $matched,
            'unknown'    => $unknown,
            'violations' => $violations,
        ];
    }
}
