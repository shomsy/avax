<?php

declare(strict_types=1);

namespace Avax\DataHandling\DataTransfer\CreateDataObject;

use Avax\DataHandling\DataTransfer\Capabilities\ErrorReporting\DataTransferFailure;
use Avax\DataHandling\DataTransfer\Capabilities\ErrorReporting\DataTransferViolations;
use Avax\DataHandling\DataTransfer\Capabilities\FieldValidation\DataValidationFailed;
use Avax\DataHandling\DataTransfer\Configuration\DataTransferConfig;
use Avax\DataHandling\DataTransfer\Foundation\FieldPath;
use Avax\DataHandling\DataTransfer\Foundation\InputData;
use Throwable;

final readonly class CreateDataObject
{
    public function __construct(private DataTransferConfig|null $config = null) {}

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public function create(string $class, array|object $input, FieldPath|null $path = null) : object
    {
        $config = $this->config ?? DataTransferConfig::default();
        $path   ??= FieldPath::root();

        try {
            $shape     = new ReadTargetDataShape(config: $config)->read(class: $class);
            $inputData = InputData::from(input: $input);

            $matched = new MatchInputToDataShape()->match(
                shape : $shape,
                input : $inputData,
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

            $this->throwIfInvalid(violations: $violations);

            return new InstantiateDataObject()->instantiate(shape: $shape, values: $converted['values']);
        } catch (Throwable $exception) {
            throw new ReportDataObjectCreationFailure()->report(exception: $exception);
        }
    }

    private function throwIfInvalid(DataTransferViolations $violations) : void
    {
        if ($violations->isEmpty()) {
            return;
        }

        throw new DataValidationFailed(
            message   : 'Data object validation failed.',
            violations: $violations,
        );
    }
}
