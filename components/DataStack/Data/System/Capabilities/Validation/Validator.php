<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Validation;

/**
 * Validator - basic data validation rules.
 * Migrated from legacy DataFoundation.
 */
final class Validator
{
    public static function validate(array $data, array $rules): bool
    {
        foreach ($rules as $field => $rule) {
            if (! isset($data[$field]) && str_contains((string) $rule, 'required')) {
                return false;
            }
        }

        return true;
    }

    public static function fails(array $data, array $rules): bool
    {
        return ! self::validate($data, $rules);
    }
}
