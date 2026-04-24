<?php

declare(strict_types=1);

namespace Avax\DataHandling\DataTransfer\Compatibility;

use Avax\DataHandling\DataTransfer\Capabilities\ErrorReporting\DataTransferFailure;
use Avax\DataHandling\DataTransfer\Capabilities\ErrorReporting\DataTransferViolations;
use Avax\DataHandling\DataTransfer\Capabilities\FieldValidation\DataValidationFailed;
use Avax\DataHandling\DataTransfer\Configuration\DataTransferConfig;
use Avax\DataHandling\DataTransfer\CreateDataObject\ConvertInputValues;
use Avax\DataHandling\DataTransfer\CreateDataObject\MatchInputToDataShape;
use Avax\DataHandling\DataTransfer\CreateDataObject\ResolveFieldInputValue;
use Avax\DataHandling\DataTransfer\CreateDataObject\ValidateInputValues;
use Avax\DataHandling\DataTransfer\Foundation\FieldPath;
use Avax\DataHandling\DataTransfer\Foundation\InputData;
use Avax\DataHandling\DataTransfer\InspectDataShape\InspectDataShape;
use Avax\DataHandling\ObjectHandling\DTO\DTOValidationException;

final readonly class CreateLegacyDTO
{
    public function hydrate(object $target, array $data) : void
    {
        $config = DataTransferConfig::legacy();
        $path   = FieldPath::root();
        $shape  = new InspectDataShape(config: $config)->inspect(class: $target::class);

        $matched = new MatchInputToDataShape()->match(
            shape : $shape,
            input : InputData::from(input: $data),
            path  : $path,
            config: $config,
        );

        $resolved = [];

        foreach ($shape->fields() as $field) {
            $resolved[$field->name] = new ResolveFieldInputValue()->resolve(
                field        : $field,
                matchedFields: $matched['matched'],
                basePath     : $path,
            );
        }

        $violations = $matched['violations']
            ->merge(violations: new ValidateInputValues()->validate(
                shape        : $shape,
                values       : $resolved,
                config       : $config,
                validateRules: false,
            ));

        $converted  = new ConvertInputValues()->convert(shape: $shape, values: $resolved, config: $config);
        $violations = $violations->merge(violations: $converted['violations']);
        $violations = $violations->merge(violations: new ValidateInputValues()->validate(
            shape : $shape,
            values: $converted['values'],
            config: $config,
        ));

        $this->throwLegacyValidationException(violations: $violations);

        foreach ($shape->fields() as $field) {
            if (! $field->isPublicProperty || ! array_key_exists(key: $field->name, array: $converted['values'])) {
                continue;
            }

            $target->{$field->name} = $converted['values'][$field->name]['value'];
        }
    }

    private function throwLegacyValidationException(DataTransferViolations $violations) : void
    {
        if ($violations->isEmpty()) {
            return;
        }

        throw new DTOValidationException(
            message   : 'DTO hydration failed.',
            errors    : $violations->toLegacyErrors(),
            violations: $violations,
        );
    }
}
