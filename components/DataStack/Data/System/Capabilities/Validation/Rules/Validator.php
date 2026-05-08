<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Validation\Rules;

/**
 * Validator - basic data validation rules.
 * Migrated from legacy DataFoundation.
 */
final class Validator
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $rules
     */
    public static function validate(array $data, array $rules): bool
    {
        return array_all($rules, fn ($rule, $field): bool => ! (! isset($data[$field]) && str_contains((string) $rule, 'required')));
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $rules
     */
    public static function fails(array $data, array $rules): bool
    {
        return ! self::validate($data, $rules);
    }
}
