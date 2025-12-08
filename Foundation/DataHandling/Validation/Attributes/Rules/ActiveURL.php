<?php

declare(strict_types=1);

/**
 * ActiveURL Attribute class to enforce the validation of URLs.
 * This attribute can be applied to properties to ensure they are active URLs.
 * The requirement for the URL to be active involves both validation of URL format and DNS resolution.
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * Validates if the provided value is an active URL.
 *
 * This method checks if the given value is a valid URL format and also verifies
 * whether the host of the URL has a DNS A record. This dual-check ensures that
 * the URL is both syntactically correct and points to an existing domain.
 *
 * @param mixed  $value    The value to be validated as an active URL.
 * @param string $property The name of the property being validated, used in the exception message.
 *
 * @throws \Avax\Exceptions\ValidationException if the value is not a valid or active URL.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
class ActiveURL
{
    /**
     * @throws \Avax\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        if (! filter_var($value, FILTER_VALIDATE_URL) || ! checkdnsrr(parse_url((string) $value, PHP_URL_HOST), 'A')) {
            throw new ValidationException(message: $property . ' must be an active URL.');
        }
    }
}
