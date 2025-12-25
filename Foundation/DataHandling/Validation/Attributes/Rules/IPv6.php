<?php

declare(strict_types=1);

/**
 * Attribute class to designate a property that must be a valid IPv6 address.
 * The use of this attribute triggers validation against the IPv6 format.
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * Validates that the given value is a proper IPv6 address. If not, it throws a ValidationException.
 *
 * @param mixed  $value    The value to be validated as an IPv6 address.
 * @param string $property The name of the property being validated.
 *
 * @throws \Avax\Exceptions\ValidationException If the value is not a valid IPv6 address.
 *
 * The use of filter_var function with FILTER_VALIDATE_IP and FILTER_FLAG_IPV6 ensures that only valid IPv6 formats are
 * accepted, reinforcing data integrity especially where IP addresses are critical.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
class IPv6
{
    /**
     * @throws \Avax\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        if (! filter_var(value: $value, filter: FILTER_VALIDATE_IP, options: FILTER_FLAG_IPV6)) {
            throw new ValidationException(message: $property . ' must be a valid IPv6 address.');
        }
    }
}
