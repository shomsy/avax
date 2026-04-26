<?php

declare(strict_types=1);

namespace components\DataFoundation\DataTransfer\Capabilities\FieldValidation;

use components\DataFoundation\DataTransfer\Capabilities\ErrorReporting\DataTransferViolation;
use components\DataFoundation\DataTransfer\Configuration\DataTransferConfig;
use components\DataFoundation\DataTransfer\Foundation\FieldPath;
use components\DataFoundation\DataTransfer\InspectDataShape\DataField;
use Throwable;

final readonly class ValidateFieldRules
{
    /**
     * @return DataTransferViolation[]
     */
    public function validate(DataField $field, mixed $value, FieldPath $path, DataTransferConfig $config) : array
    {
        $violations = [];

        foreach ($field->attributes as $attribute) {
            $configuredRule = $config->validationRuleFor(attributeClass: $attribute::class);

            if ($configuredRule !== null) {
                $violation = $this->runConfiguredRule(
                    rule : $configuredRule,
                    value: $value,
                    field: $field,
                    path : $path,
                );

                if ($violation !== null) {
                    $violations[] = $violation;
                }
            }

            if (method_exists(object_or_class: $attribute, method: 'validate')) {
                $violation = $this->runAttributeValidation(
                    attribute: $attribute,
                    value    : $value,
                    field    : $field,
                    path     : $path,
                );

                if ($violation !== null) {
                    $violations[] = $violation;
                }
            }
        }

        return $violations;
    }

    private function runConfiguredRule(object|callable $rule, mixed $value, DataField $field, FieldPath $path) : DataTransferViolation|null
    {
        if ($rule instanceof ValidationRuleInterface) {
            return $rule->validate(value: $value, field: $field, path: $path);
        }

        if (is_callable(value: $rule)) {
            $result = $rule($value, $field, $path);

            return $result instanceof DataTransferViolation ? $result : null;
        }

        return null;
    }

    private function runAttributeValidation(object $attribute, mixed $value, DataField $field, FieldPath $path) : DataTransferViolation|null
    {
        try {
            try {
                $attribute->validate(value: $value, property: $field->name);
            } catch (Throwable) {
                $attribute->validate($value, $field->name);
            }

            return null;
        } catch (Throwable $exception) {
            return new DataTransferViolation(
                path        : (string) $path,
                code        : 'field_rule_failed',
                message     : $exception->getMessage(),
                expectedType: $field->type->displayName(),
                actualType  : get_debug_type(value: $value),
                failedRule  : $attribute::class,
                previous    : $exception,
            );
        }
    }
}
